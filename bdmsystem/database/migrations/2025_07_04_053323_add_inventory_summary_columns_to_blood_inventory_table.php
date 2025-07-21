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
            $table->integer('units_available')->default(0)->after('quantity');
            $table->integer('units_requested')->default(0)->after('units_available');
        });
        
        // Update existing records: set units_available = quantity for available blood
        DB::statement("
            UPDATE blood_inventory 
            SET units_available = CASE 
                WHEN status = 'available' AND expiry_date > NOW() THEN 1
                ELSE 0 
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blood_inventory', function (Blueprint $table) {
            $table->dropColumn(['units_available', 'units_requested']);
        });
    }
};
