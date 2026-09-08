<?php

namespace Tests\Feature;

use App\Services\AI\ActionExecutionService;
use App\Services\AI\AIStateService;
use App\Services\AI\ConfidenceEngine;
use App\Services\AI\PredictionSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class SystemInvariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_invariant_1_blood_unit_single_active_allocation(): void
    {
        $unitId = DB::table('blood_units')->insertGetId([
            'unit_number' => 'UNIT-INV-1',
            'blood_group_id' => null,
            'component_id' => null,
            'blood_group' => 'O-',
            'component_type' => 'WHOLE_BLOOD',
            'lifecycle_state' => 'AVAILABLE',
            'availability_state' => 'IMMEDIATELY_USABLE',
            'location_state' => 'AT_ORIGIN',
            'testing_state' => 'PASSED',
            'volume_ml' => 450,
            'expiry_date' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('blood_unit_allocations')->insert([
            'logical_operation_id' => 'OP-INV-1',
            'request_id' => 10,
            'blood_unit_id' => $unitId,
            'safety_epoch' => 1,
            'allocated_at' => now(),
        ]);

        // Attempting duplicate allocation for same unit under same logical_operation_id fails UNIQUE constraint
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('blood_unit_allocations')->insert([
            'logical_operation_id' => 'OP-INV-1',
            'request_id' => 10,
            'blood_unit_id' => $unitId,
            'safety_epoch' => 1,
            'allocated_at' => now(),
        ]);
    }

    public function test_invariant_2_expired_or_superseded_snapshot_cannot_be_approved(): void
    {
        $confidenceEngine = new ConfidenceEngine();
        $aiStateService = new AIStateService();
        $snapshotService = new PredictionSnapshotService($confidenceEngine, $aiStateService);

        $snapshot = $snapshotService->createSnapshot('FACILITY', 1, 'O-', 2);

        // Mark snapshot as EXPIRED in lineage table
        DB::table('prediction_snapshot_lineage')
            ->where('snapshot_id', $snapshot['snapshot_id'])
            ->update(['status' => 'EXPIRED']);

        $this->expectException(InvalidArgumentException::class);
        $snapshotService->approveSnapshot(
            $snapshot['snapshot_id'],
            1,
            'OP-INV-2',
            10,
            [1],
            1,
            'AUTH-HASH'
        );
    }

    public function test_invariant_3_action_idempotency_key_uniqueness(): void
    {
        DB::table('action_executions')->insert([
            'idempotency_key' => 'IDEM-INV-3',
            'logical_operation_id' => 'OP-INV-3',
            'attempt_number' => 1,
            'command_envelope_hash' => 'HASH-123',
            'safety_epoch' => 1,
            'command_type' => 'ALLOCATE_BLOOD',
            'status' => 'COMPLETED',
            'created_at' => now(),
        ]);

        // Duplicate idempotency_key triggers QueryException
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('action_executions')->insert([
            'idempotency_key' => 'IDEM-INV-3',
            'logical_operation_id' => 'OP-INV-3',
            'attempt_number' => 2,
            'command_envelope_hash' => 'HASH-123',
            'safety_epoch' => 1,
            'command_type' => 'ALLOCATE_BLOOD',
            'status' => 'EXECUTING',
            'created_at' => now(),
        ]);
    }

    public function test_invariant_4_safety_epoch_mismatch_aborts_action(): void
    {
        $aiStateService = new AIStateService();

        // Increment epoch to 2 (e.g. RECOVERY_MODE activated)
        $aiStateService->incrementEpoch(['recovery_mode' => true]);

        $confidenceEngine = new ConfidenceEngine();
        $snapshotService = new PredictionSnapshotService($confidenceEngine, $aiStateService);

        // Approval attempt rejected due to active recovery mode
        $snapshot = $snapshotService->createSnapshot('FACILITY', 1, 'O-', 2);

        $this->expectException(InvalidArgumentException::class);
        $snapshotService->approveSnapshot(
            $snapshot['snapshot_id'],
            1,
            'OP-INV-4',
            10,
            [1],
            1,
            'AUTH-HASH'
        );
    }

    public function test_invariant_8_notification_intent_id_deduplication(): void
    {
        $intentId = hash('sha256', 'ACTION_COMPLETED:IDEM-NOTIF-1');

        DB::table('outbox_events')->insert([
            'notification_intent_id' => $intentId,
            'idempotency_key' => 'IDEM-NOTIF-1',
            'event_type' => 'ACTION_COMPLETED',
            'payload' => json_encode(['unit_id' => 1]),
            'status' => 'QUEUED',
            'created_at' => now(),
        ]);

        // Duplicate intent insert fails UNIQUE constraint
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('outbox_events')->insert([
            'notification_intent_id' => $intentId,
            'idempotency_key' => 'IDEM-NOTIF-1',
            'event_type' => 'ACTION_COMPLETED',
            'payload' => json_encode(['unit_id' => 1]),
            'status' => 'QUEUED',
            'created_at' => now(),
        ]);
    }
}
