<?php

namespace App\Services\AI;

use App\Enums\AISafetyState;
use App\Services\AI\ValueObjects\CanonicalCommandEnvelope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PredictionSnapshotService
{
    public function __construct(
        private readonly ConfidenceEngine $confidenceEngine,
        private readonly AIStateService $aiStateService
    ) {}

    /**
     * Create a new prediction snapshot and update lineage DAG.
     */
    public function createSnapshot(
        string $scopeType,
        ?int $scopeId,
        string $bloodGroup,
        int $predictedShortage,
        int $sampleSize = 100,
        bool $dataTrustworthy = true,
        bool $driftDetected = false,
        bool $modelAvailable = true
    ): array {
        $safetyState = $this->confidenceEngine->evaluateSafetyState(
            $sampleSize,
            $dataTrustworthy,
            $driftDetected,
            $modelAvailable
        );

        $confidenceScore = $this->confidenceEngine->computeConfidenceScore($safetyState, 0.92);

        $uuid = (string) Str::uuid();
        $decisionHash = hash('sha256', "{$scopeType}:{$scopeId}:{$bloodGroup}:{$predictedShortage}:{$safetyState->value}");

        $usabilityRuleHash = hash('sha256', 'InventorySnapshotService::isUsable::v1.0');
        $compatibilityRuleHash = hash('sha256', 'CompatibilityEngine::v1.0');
        $sopRuleHash = hash('sha256', 'SOP::StandardBloodMatchChecklist::v1.0');
        $modelArtifactHash = hash('sha256', 'ModelArtifact::LightGBM_Shortage_v1.0');

        return DB::transaction(function () use (
            $uuid, $scopeType, $scopeId, $bloodGroup, $predictedShortage,
            $confidenceScore, $safetyState, $decisionHash,
            $usabilityRuleHash, $compatibilityRuleHash, $sopRuleHash, $modelArtifactHash
        ) {
            $snapshotId = DB::table('prediction_snapshots')->insertGetId([
                'snapshot_uuid' => $uuid,
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
                'blood_group' => $bloodGroup,
                'predicted_shortage_units' => $predictedShortage,
                'confidence_score' => $confidenceScore,
                'ai_safety_state' => $safetyState->value,
                'decision_hash' => $decisionHash,
                'model_artifact_hash' => $modelArtifactHash,
                'usability_rule_hash' => $usabilityRuleHash,
                'compatibility_rule_hash' => $compatibilityRuleHash,
                'sop_rule_hash' => $sopRuleHash,
                'usability_rule_version' => '1.0',
                'compatibility_policy_version' => '1.0',
                'sop_version' => '1.0',
                'created_at' => now(),
            ]);

            // Insert into separate prediction_snapshot_lineage table
            DB::table('prediction_snapshot_lineage')->insert([
                'snapshot_id' => $snapshotId,
                'parent_snapshot_id' => null,
                'is_leaf' => true,
                'lineage_version' => 1,
                'status' => 'PENDING',
                'expires_at' => now()->addHours(6),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'snapshot_id' => $snapshotId,
                'snapshot_uuid' => $uuid,
                'decision_hash' => $decisionHash,
                'safety_state' => $safetyState->value,
                'confidence_score' => $confidenceScore,
            ];
        });
    }

    /**
     * Human Approval CAS. Converts PENDING leaf snapshot to APPROVED.
     */
    public function approveSnapshot(
        int $snapshotId,
        int $approvedByUserId,
        string $logicalOperationId,
        int $requestId,
        array $inventoryIds,
        int $quantity,
        string $authContextHash
    ): array {
        return DB::transaction(function () use (
            $snapshotId, $approvedByUserId, $logicalOperationId,
            $requestId, $inventoryIds, $quantity, $authContextHash
        ) {
            // Verify active leaf node in lineage table
            $lineage = DB::table('prediction_snapshot_lineage')
                ->where('snapshot_id', $snapshotId)
                ->lockForUpdate()
                ->first();

            if (!$lineage || !$lineage->is_leaf || $lineage->status !== 'PENDING') {
                throw new InvalidArgumentException("Snapshot #{$snapshotId} is not an active PENDING leaf in lineage DAG.");
            }

            if ($lineage->expires_at && now()->parse($lineage->expires_at)->isPast()) {
                throw new InvalidArgumentException("Snapshot #{$snapshotId} has expired.");
            }

            // Verify global safety state
            if ($this->aiStateService->isSafetyBlockerActive()) {
                throw new InvalidArgumentException("Approval rejected: Global safety blocker or recovery mode is active.");
            }

            $snapshot = DB::table('prediction_snapshots')->where('id', $snapshotId)->first();
            $nonce = (string) Str::uuid();

            // Compute canonical envelope hash
            $envelopeHash = CanonicalCommandEnvelope::computeHash(
                $snapshotId,
                $requestId,
                $snapshot->scope_id ?? 1,
                $logicalOperationId,
                $inventoryIds,
                $quantity,
                $authContextHash
            );

            // Update snapshot command_envelope_hash
            DB::table('prediction_snapshots')
                ->where('id', $snapshotId)
                ->update(['command_envelope_hash' => $envelopeHash]);

            // Update lineage table
            DB::table('prediction_snapshot_lineage')
                ->where('snapshot_id', $snapshotId)
                ->update([
                    'status' => 'APPROVED',
                    'approved_at' => now(),
                    'approved_by' => $approvedByUserId,
                    'approval_nonce' => $nonce,
                    'updated_at' => now(),
                ]);

            return [
                'snapshot_id' => $snapshotId,
                'status' => 'APPROVED',
                'approval_nonce' => $nonce,
                'command_envelope_hash' => $envelopeHash,
            ];
        });
    }
}
