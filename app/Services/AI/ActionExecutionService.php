<?php

namespace App\Services\AI;

use App\Services\AI\ValueObjects\CanonicalCommandEnvelope;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ActionExecutionService
{
    public function executeAction(
        string $idempotencyKey,
        string $logicalOperationId,
        int $attemptNumber,
        int $snapshotId,
        int $requestId,
        int $facilityId,
        array $inventoryIds,
        int $quantity,
        string $approvalNonce,
        string $authContextHash
    ): array {
        return DB::transaction(function () use (
            $idempotencyKey, $logicalOperationId, $attemptNumber,
            $snapshotId, $requestId, $facilityId, $inventoryIds,
            $quantity, $approvalNonce, $authContextHash
        ) {
            $driver = DB::getDriverName();

            // 1. Acquire Safety Epoch Serialization Lock
            $currentEpoch = 1;
            if ($driver !== 'sqlite') {
                $epochResult = DB::selectOne('CALL sp_begin_action_execution(?, ?, ?, ?, ?, ?)', [
                    $idempotencyKey,
                    $logicalOperationId,
                    $attemptNumber,
                    $approvalNonce,
                    'ALLOCATE_BLOOD',
                    $facilityId
                ]);
                $currentEpoch = $epochResult->current_epoch ?? 1;
            } else {
                // SQLite fallback simulation
                $currentEpoch = DB::table('ai_safety_epoch')->where('id', 1)->value('epoch_version');
                DB::table('action_executions')->insert([
                    'idempotency_key' => $idempotencyKey,
                    'logical_operation_id' => $logicalOperationId,
                    'attempt_number' => $attemptNumber,
                    'approval_nonce' => $approvalNonce,
                    'command_envelope_hash' => 'PENDING_VERIFICATION',
                    'safety_epoch' => $currentEpoch,
                    'command_type' => 'ALLOCATE_BLOOD',
                    'facility_id' => $facilityId,
                    'status' => 'EXECUTING',
                    'created_at' => now(),
                ]);
            }

            // 2. Query Authoritative Persisted Snapshot & Lineage Leaf
            $snapshot = DB::table('prediction_snapshots')->where('id', $snapshotId)->first();
            if (!$snapshot) {
                throw new InvalidArgumentException("Snapshot #{$snapshotId} not found.");
            }

            $lineage = DB::table('prediction_snapshot_lineage')->where('snapshot_id', $snapshotId)->first();
            if (!$lineage || !$lineage->is_leaf || $lineage->status !== 'APPROVED') {
                throw new InvalidArgumentException("Snapshot #{$snapshotId} is not an active APPROVED leaf node.");
            }

            if ($lineage->approval_nonce !== $approvalNonce) {
                throw new InvalidArgumentException("Approval nonce mismatch.");
            }

            // 3. Verify Authoritative Envelope Fingerprint
            $computedHash = CanonicalCommandEnvelope::computeHash(
                $snapshotId,
                $requestId,
                $facilityId,
                $logicalOperationId,
                $inventoryIds,
                $quantity,
                $authContextHash
            );

            if ($snapshot->command_envelope_hash && $snapshot->command_envelope_hash !== $computedHash) {
                throw new InvalidArgumentException("Command payload does not match stored approval envelope fingerprint.");
            }

            // Update command_envelope_hash on action_execution
            DB::table('action_executions')
                ->where('idempotency_key', $idempotencyKey)
                ->update(['command_envelope_hash' => $computedHash]);

            // 4. Stored Procedure Allocation & Outbox Enqueue
            if ($driver !== 'sqlite') {
                $allocatedRows = DB::select('CALL sp_allocate_blood_unit(?, ?, ?, ?, ?)', [
                    $logicalOperationId,
                    $requestId,
                    json_encode($inventoryIds),
                    $currentEpoch,
                    $approvalNonce
                ]);

                DB::select('CALL sp_enqueue_outbox_event(?, ?, ?)', [
                    'ACTION_COMPLETED',
                    $idempotencyKey,
                    json_encode($allocatedRows)
                ]);

                DB::select('CALL sp_complete_action_execution(?, ?)', [
                    $idempotencyKey,
                    json_encode($allocatedRows)
                ]);

                $allocatedUnits = array_column($allocatedRows, 'blood_unit_id');
            } else {
                // SQLite fallback simulation
                sort($inventoryIds, SORT_NUMERIC);
                $allocatedUnits = [];

                foreach ($inventoryIds as $unitId) {
                    DB::table('blood_unit_allocations')->insertOrIgnore([
                        'logical_operation_id' => $logicalOperationId,
                        'request_id' => $requestId,
                        'blood_unit_id' => $unitId,
                        'safety_epoch' => $currentEpoch,
                        'approval_nonce' => $approvalNonce,
                        'allocated_at' => now(),
                    ]);

                    DB::table('blood_units')
                        ->where('id', $unitId)
                        ->update(['availability_state' => 'ALLOCATED']);

                    $allocatedUnits[] = $unitId;
                }

                $intentId = hash('sha256', "ACTION_COMPLETED:{$idempotencyKey}");
                DB::table('outbox_events')->insertOrIgnore([
                    'notification_intent_id' => $intentId,
                    'idempotency_key' => $idempotencyKey,
                    'logical_operation_id' => $logicalOperationId,
                    'event_type' => 'ACTION_COMPLETED',
                    'payload' => json_encode($allocatedUnits),
                    'status' => 'QUEUED',
                    'created_at' => now(),
                ]);

                DB::table('action_executions')
                    ->where('idempotency_key', $idempotencyKey)
                    ->update([
                        'status' => 'COMPLETED',
                        'result_payload' => json_encode($allocatedUnits),
                        'completed_at' => now(),
                    ]);
            }

            return [
                'status' => 'COMPLETED',
                'logical_operation_id' => $logicalOperationId,
                'allocated_units' => $allocatedUnits,
            ];
        }, 3);
    }
}
