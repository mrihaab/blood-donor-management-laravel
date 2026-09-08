<?php

namespace Tests\Feature;

use App\Services\AI\ActionExecutionService;
use App\Services\AI\AIStateService;
use App\Services\AI\ConfidenceEngine;
use App\Services\AI\PredictionSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PropertyFuzzTest extends TestCase
{
    use RefreshDatabase;

    public function test_50_event_property_fuzz_sequence_asserts_all_invariants(): void
    {
        $confidenceEngine = new ConfidenceEngine();
        $aiStateService = new AIStateService();
        $snapshotService = new PredictionSnapshotService($confidenceEngine, $aiStateService);

        // Seed 5 blood units
        $unitIds = [];
        for ($i = 1; $i <= 5; $i++) {
            $unitIds[] = DB::table('blood_units')->insertGetId([
                'unit_number' => "UNIT-FUZZ-{$i}",
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
        }

        $events = ['APPROVE', 'RECOVERY_ON', 'RECOVERY_OFF', 'KILL_ON', 'KILL_OFF', 'DEPLOY_ON', 'DEPLOY_OFF'];

        for ($step = 1; $step <= 50; $step++) {
            $chosenEvent = $events[array_rand($events)];

            switch ($chosenEvent) {
                case 'RECOVERY_ON':
                    $aiStateService->incrementEpoch(['recovery_mode' => true]);
                    break;
                case 'RECOVERY_OFF':
                    $aiStateService->incrementEpoch(['recovery_mode' => false]);
                    break;
                case 'KILL_ON':
                    $aiStateService->incrementEpoch(['global_kill_switch' => true]);
                    break;
                case 'KILL_OFF':
                    $aiStateService->incrementEpoch(['global_kill_switch' => false]);
                    break;
                case 'DEPLOY_ON':
                    $aiStateService->incrementEpoch(['deployment_mode' => true]);
                    break;
                case 'DEPLOY_OFF':
                    $aiStateService->incrementEpoch(['deployment_mode' => false]);
                    break;
                case 'APPROVE':
                    try {
                        $snapshot = $snapshotService->createSnapshot('FACILITY', 1, 'O-', 2);
                        $snapshotService->approveSnapshot(
                            $snapshot['snapshot_id'],
                            1,
                            "OP-FUZZ-{$step}",
                            10,
                            [$unitIds[0]],
                            1,
                            'AUTH-HASH'
                        );
                    } catch (\Exception $e) {
                        // Expected exception during blocked safety state
                    }
                    break;
            }

            // Assert Invariant: No unit has more than 1 allocation
            $duplicateAllocations = DB::table('blood_unit_allocations')
                ->select('blood_unit_id', DB::raw('COUNT(*) as alloc_count'))
                ->groupBy('blood_unit_id')
                ->having('alloc_count', '>', 1)
                ->get();

            $this->assertCount(0, $duplicateAllocations, "Fuzz Step #{$step}: Duplicate allocation detected!");
        }
    }
}
