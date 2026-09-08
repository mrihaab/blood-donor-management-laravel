<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_unit_allocations', function (Blueprint $table) {
            $table->id();
            $table->string('logical_operation_id');
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('blood_unit_id');
            $table->unsignedBigInteger('safety_epoch');
            $table->string('approval_nonce')->nullable();
            $table->timestamp('allocated_at')->useCurrent();

            $table->unique(['logical_operation_id', 'blood_unit_id']);
            $table->index('logical_operation_id');
            $table->index('blood_unit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_unit_allocations');
    }
};
