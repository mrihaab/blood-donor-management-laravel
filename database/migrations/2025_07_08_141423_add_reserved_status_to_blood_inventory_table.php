<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blood_inventory', function (Blueprint $table) {
            // Modify the status enum to include 'reserved'
            DB::statement("ALTER TABLE blood_inventory MODIFY COLUMN status ENUM('available', 'expired', 'used', 'reserved') DEFAULT 'available'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blood_inventory', function (Blueprint $table) {
            // Revert status enum back to original values
            DB::statement("ALTER TABLE blood_inventory MODIFY COLUMN status ENUM('available', 'expired', 'used') DEFAULT 'available'");
        });
    }
};
