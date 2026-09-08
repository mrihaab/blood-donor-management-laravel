<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blood_units', function (Blueprint $table) {
            if (!Schema::hasColumn('blood_units', 'din')) {
                $table->string('din')->nullable()->after('unit_number');
            }
            if (!Schema::hasColumn('blood_units', 'component_type')) {
                $table->string('component_type')->default('PRBC')->after('blood_group');
            }
            if (!Schema::hasColumn('blood_units', 'temperature_status')) {
                $table->string('temperature_status')->default('normal')->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blood_units', function (Blueprint $table) {
            $table->dropColumn(['din', 'component_type', 'temperature_status']);
        });
    }
};
