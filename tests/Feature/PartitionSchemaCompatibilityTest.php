<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PartitionSchemaCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_state_transitions_schema_structure(): void
    {
        $unitId = DB::table('blood_units')->insertGetId([
            'unit_number' => 'UNIT-PART-01',
            'blood_group_id' => null,
            'component_id' => null,
            'blood_group' => 'A+',
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

        $transitionId = DB::table('inventory_state_transitions')->insertGetId([
            'blood_unit_id' => $unitId,
            'previous_availability_state' => 'IMMEDIATELY_USABLE',
            'new_availability_state' => 'ALLOCATED',
            'triggered_by' => 'ActionExecutionService',
            'safety_epoch' => 1,
            'recorded_at' => now(),
        ]);

        $this->assertDatabaseHas('inventory_state_transitions', [
            'id' => $transitionId,
            'blood_unit_id' => $unitId,
            'new_availability_state' => 'ALLOCATED',
        ]);
    }
}
