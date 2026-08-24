<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blood_units', function (Blueprint $table) {
            $table->index(['blood_group_id', 'status', 'expiry_date'], 'idx_blood_units_fefo_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('blood_units', function (Blueprint $table) {
            $table->dropIndex('idx_blood_units_fefo_lookup');
        });
    }
};
