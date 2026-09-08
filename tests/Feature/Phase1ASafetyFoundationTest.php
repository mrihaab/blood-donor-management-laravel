<?php

namespace Tests\Feature;

use App\Enums\AISafetyState;
use App\Enums\BloodUnitAvailabilityState;
use App\Enums\BloodUnitLifecycleState;
use App\Enums\BloodUnitLocationState;
use App\Enums\BloodUnitTestingState;
use App\Services\AI\ActionExecutionService;
use App\Services\AI\AIStateService;
use App\Services\AI\CompatibilityEngine;
use App\Services\AI\ConfidenceEngine;
use App\Services\AI\InventorySnapshotService;
use App\Services\AI\PredictionSnapshotService;
use App\Services\AI\ValueObjects\CanonicalCommandEnvelope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class Phase1ASafetyFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_usable_predicate_evaluates_correctly_across_all_dimensions(): void
    {
        $usableUnit = [
            'lifecycle_state' => 'AVAILABLE',
            'availability_state' => 'IMMEDIATELY_USABLE',
            'testing_state' => 'PASSED',
            'location_state' => 'AT_ORIGIN',
            'expiry_date' => now()->addDays(10)->toDateTimeString(),
            'is_recalled' => false,
        ];

        $this->assertTrue(InventorySnapshotService::isUsable($usableUnit));

        // Test Recalled Unit
        $recalledUnit = array_merge($usableUnit, ['is_recalled' => true]);
        $this->assertFalse(InventorySnapshotService::isUsable($recalledUnit));

        // Test Dispatched Unit
        $dispatchedUnit = array_merge($usableUnit, ['lifecycle_state' => 'DISPATCHED']);
        $this->assertFalse(InventorySnapshotService::isUsable($dispatchedUnit));

        // Test Testing Pending Unit
        $pendingUnit = array_merge($usableUnit, ['testing_state' => 'TESTING_PENDING']);
        $this->assertFalse(InventorySnapshotService::isUsable($pendingUnit));

        // Test Expired Unit
        $expiredUnit = array_merge($usableUnit, ['expiry_date' => now()->subDay()->toDateTimeString()]);
        $this->assertFalse(InventorySnapshotService::isUsable($expiredUnit));
    }

    public function test_compatibility_engine_fail_hard_matrix(): void
    {
        $engine = new CompatibilityEngine();

        // Compatible cases
        $this->assertTrue($engine->verifyCompatibility('O-', 'O-'));
        $this->assertTrue($engine->verifyCompatibility('AB+', 'O-'));
        $this->assertTrue($engine->verifyCompatibility('A+', 'A-'));

        // Incompatible cases (FAIL-HARD)
        $this->assertFalse($engine->verifyCompatibility('O-', 'O+'));
        $this->assertFalse($engine->verifyCompatibility('A-', 'B-'));
        $this->assertFalse($engine->verifyCompatibility('O+', 'AB+'));
    }

    public function test_confidence_engine_state_machine_and_nullability(): void
    {
        $engine = new ConfidenceEngine();

        // Confident State
        $state = $engine->evaluateSafetyState(100, true, false, true);
        $this->assertEquals(AISafetyState::CONFIDENT, $state);
        $this->assertNotNull($engine->computeConfidenceScore($state, 0.95));

        // Degraded State
        $state = $engine->evaluateSafetyState(15, true, false, true);
        $this->assertEquals(AISafetyState::DEGRADED, $state);
        $this->assertNotNull($engine->computeConfidenceScore($state, 0.65));

        // Cold Start State (K = 0)
        $state = $engine->evaluateSafetyState(0, true, false, true);
        $this->assertEquals(AISafetyState::COLD_START, $state);
        $this->assertNull($engine->computeConfidenceScore($state, 0.95));

        // Model Drift State
        $state = $engine->evaluateSafetyState(100, true, true, true);
        $this->assertEquals(AISafetyState::MODEL_DRIFT, $state);
        $this->assertNull($engine->computeConfidenceScore($state, 0.95));

        // Data Untrustworthy State
        $state = $engine->evaluateSafetyState(100, false, false, true);
        $this->assertEquals(AISafetyState::DATA_UNTRUSTWORTHY, $state);
        $this->assertNull($engine->computeConfidenceScore($state, 0.95));
    }

    public function test_ai_state_service_epoch_and_capabilities(): void
    {
        $service = new AIStateService();
        $this->assertEquals(1, $service->getCurrentEpoch());

        $capabilities = $service->getEffectiveCapabilities();
        $this->assertTrue($capabilities['autonomous_ai_allowed']);
        $this->assertEquals('NORMAL', $capabilities['effective_mode']);

        // Enable RECOVERY_MODE
        $newEpoch = $service->incrementEpoch(['recovery_mode' => true]);
        $this->assertEquals(2, $newEpoch);

        $capabilities = $service->getEffectiveCapabilities();
        $this->assertFalse($capabilities['autonomous_ai_allowed']);
        $this->assertTrue($capabilities['manual_emergency_allowed']);
        $this->assertEquals('RECOVERY_MODE', $capabilities['effective_mode']);
    }

    public function test_canonical_command_envelope_hashing_and_validation(): void
    {
        $hash1 = CanonicalCommandEnvelope::computeHash(1, 10, 1, 'OP-100', [5, 2, 8], 3, 'AUTH-HASH');
        $hash2 = CanonicalCommandEnvelope::computeHash(1, 10, 1, 'OP-100', [2, 5, 8], 3, 'AUTH-HASH');

        // Ascending sorting guarantees identical hash for unsorted input
        $this->assertEquals($hash1, $hash2);

        // Expect exception on duplicate inventory IDs
        $this->expectException(InvalidArgumentException::class);
        new CanonicalCommandEnvelope(1, 10, 1, 'OP-100', [5, 5, 8], 3, 'AUTH-HASH');
    }

    public function test_prediction_snapshot_creation_lineage_and_approval(): void
    {
        $confidenceEngine = new ConfidenceEngine();
        $aiStateService = new AIStateService();
        $snapshotService = new PredictionSnapshotService($confidenceEngine, $aiStateService);

        $result = $snapshotService->createSnapshot('FACILITY', 1, 'O-', 5);
        $this->assertDatabaseHas('prediction_snapshots', ['id' => $result['snapshot_id']]);
        $this->assertDatabaseHas('prediction_snapshot_lineage', ['snapshot_id' => $result['snapshot_id'], 'is_leaf' => true, 'status' => 'PENDING']);

        $approval = $snapshotService->approveSnapshot(
            $result['snapshot_id'],
            1,
            'OP-200',
            100,
            [1, 2],
            2,
            'AUTH-HASH'
        );

        $this->assertEquals('APPROVED', $approval['status']);
        $this->assertDatabaseHas('prediction_snapshot_lineage', ['snapshot_id' => $result['snapshot_id'], 'status' => 'APPROVED']);
    }

    public function test_action_execution_pipeline_success(): void
    {
        // Seed blood unit in DB
        $unitId = DB::table('blood_units')->insertGetId([
            'unit_number' => 'UNIT-001',
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

        $confidenceEngine = new ConfidenceEngine();
        $aiStateService = new AIStateService();
        $snapshotService = new PredictionSnapshotService($confidenceEngine, $aiStateService);
        $actionService = new ActionExecutionService();

        $snapshot = $snapshotService->createSnapshot('FACILITY', 1, 'O-', 1);
        $approval = $snapshotService->approveSnapshot(
            $snapshot['snapshot_id'],
            1,
            'OP-300',
            50,
            [$unitId],
            1,
            'AUTH-HASH'
        );

        $actionResult = $actionService->executeAction(
            'IDEM-KEY-001',
            'OP-300',
            1,
            $snapshot['snapshot_id'],
            50,
            1,
            [$unitId],
            1,
            $approval['approval_nonce'],
            'AUTH-HASH'
        );

        $this->assertEquals('COMPLETED', $actionResult['status']);
        $this->assertDatabaseHas('blood_units', ['id' => $unitId, 'availability_state' => 'ALLOCATED']);
        $this->assertDatabaseHas('action_executions', ['idempotency_key' => 'IDEM-KEY-001', 'status' => 'COMPLETED']);
        $this->assertDatabaseHas('outbox_events', ['idempotency_key' => 'IDEM-KEY-001', 'event_type' => 'ACTION_COMPLETED']);
    }
}
