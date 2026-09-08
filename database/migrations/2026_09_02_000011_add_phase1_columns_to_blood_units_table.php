<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blood_units', function (Blueprint $table) {
            $table->unsignedBigInteger('blood_group_id')->nullable()->change();
            $table->unsignedBigInteger('component_id')->nullable()->change();
            $table->date('collection_date')->nullable()->change();

            if (!Schema::hasColumn('blood_units', 'blood_group')) {
                $table->string('blood_group')->nullable()->after('unit_number');
            }
            if (!Schema::hasColumn('blood_units', 'component_type')) {
                $table->string('component_type')->default('WHOLE_BLOOD')->after('blood_group');
            }
            if (!Schema::hasColumn('blood_units', 'lifecycle_state')) {
                $table->string('lifecycle_state')->default('AVAILABLE')->after('status');
            }
            if (!Schema::hasColumn('blood_units', 'availability_state')) {
                $table->string('availability_state')->default('IMMEDIATELY_USABLE')->after('lifecycle_state');
            }
            if (!Schema::hasColumn('blood_units', 'location_state')) {
                $table->string('location_state')->default('AT_ORIGIN')->after('availability_state');
            }
            if (!Schema::hasColumn('blood_units', 'testing_state')) {
                $table->string('testing_state')->default('PASSED')->after('location_state');
            }
            if (!Schema::hasColumn('blood_units', 'is_recalled')) {
                $table->boolean('is_recalled')->default(false)->after('testing_state');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blood_units', function (Blueprint $table) {
            $table->dropColumn(['blood_group', 'lifecycle_state', 'availability_state', 'location_state', 'testing_state', 'is_recalled']);
        });
    }
};
