<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'ward_name')) {
                $table->string('ward_name')->nullable()->after('status');
            }
            if (!Schema::hasColumn('patients', 'room_number')) {
                $table->string('room_number')->nullable()->after('ward_name');
            }
            if (!Schema::hasColumn('patients', 'bed_number')) {
                $table->string('bed_number')->nullable()->after('room_number');
            }
        });

        Schema::table('blood_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('blood_requests', 'ward_name')) {
                $table->string('ward_name')->nullable()->after('attendant_phone');
            }
            if (!Schema::hasColumn('blood_requests', 'room_number')) {
                $table->string('room_number')->nullable()->after('ward_name');
            }
            if (!Schema::hasColumn('blood_requests', 'bed_number')) {
                $table->string('bed_number')->nullable()->after('room_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['ward_name', 'room_number', 'bed_number']);
        });

        Schema::table('blood_requests', function (Blueprint $table) {
            $table->dropColumn(['ward_name', 'room_number', 'bed_number']);
        });
    }
};
