<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('blood_requests')) {
            Schema::table('blood_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('blood_requests', 'attendant_name')) {
                    $table->string('attendant_name')->nullable()->after('patient_name');
                }
                if (!Schema::hasColumn('blood_requests', 'attendant_phone')) {
                    $table->string('attendant_phone')->nullable()->after('attendant_name');
                }
            });
        }

        if (Schema::hasTable('blood_units')) {
            Schema::table('blood_units', function (Blueprint $table) {
                if (!Schema::hasColumn('blood_units', 'donation_type')) {
                    $table->string('donation_type')->default('voluntary')->after('status');
                }
                if (!Schema::hasColumn('blood_units', 'donor_relation')) {
                    $table->string('donor_relation')->nullable()->after('donation_type');
                }
                if (!Schema::hasColumn('blood_units', 'replacement_patient_id')) {
                    $table->unsignedBigInteger('replacement_patient_id')->nullable()->after('donor_relation');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('blood_requests')) {
            Schema::table('blood_requests', function (Blueprint $table) {
                $table->dropColumn(['attendant_name', 'attendant_phone']);
            });
        }

        if (Schema::hasTable('blood_units')) {
            Schema::table('blood_units', function (Blueprint $table) {
                $table->dropColumn(['donation_type', 'donor_relation', 'replacement_patient_id']);
            });
        }
    }
};
