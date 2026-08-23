<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blood_requests', function (Blueprint $table) {
            $table->foreignId('hospital_id')->nullable()->after('hospital')->constrained('hospitals')->onDelete('set null');
            $table->foreignId('patient_id')->nullable()->after('patient_name')->constrained('patients')->onDelete('set null');
        });

        // Seed default hospital for existing request backfilling
        $hospitalId = DB::table('hospitals')->insertGetId([
            'name' => 'General City Hospital',
            'city' => 'Metropolis',
            'contact_phone' => '555-0100',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('blood_requests')->update(['hospital_id' => $hospitalId]);
    }

    public function down(): void
    {
        Schema::table('blood_requests', function (Blueprint $table) {
            $table->dropForeign(['hospital_id']);
            $table->dropForeign(['patient_id']);
            $table->dropColumn(['hospital_id', 'patient_id']);
        });
    }
};
