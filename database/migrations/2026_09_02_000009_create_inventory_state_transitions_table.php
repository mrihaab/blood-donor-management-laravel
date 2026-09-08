<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_state_transitions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('blood_unit_id');
            $table->string('previous_availability_state');
            $table->string('new_availability_state');
            $table->string('triggered_by');
            $table->unsignedBigInteger('safety_epoch');
            $table->timestamp('recorded_at')->useCurrent();

            $table->index(['blood_unit_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_state_transitions');
    }
};
