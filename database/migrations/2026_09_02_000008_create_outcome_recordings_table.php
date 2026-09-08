<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outcome_recordings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('snapshot_id');
            $table->string('scope_type')->default('FACILITY');
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->string('actual_inventory_status');
            $table->string('actual_fulfillment_status');
            $table->string('actual_time_status');
            $table->string('derived_ground_truth_state');
            $table->string('outcome_category')->default('AUTO_RECONCILED'); // AUTO_RECONCILED, NEEDS_REVIEW, ADJUDICATED_MODEL_ERROR
            $table->text('review_notes')->nullable();
            $table->unsignedBigInteger('adjudicated_by')->nullable();
            $table->timestamp('recorded_at')->useCurrent();

            $table->foreign('snapshot_id')->references('id')->on('prediction_snapshots')->onDelete('cascade');
            $table->index(['scope_type', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outcome_recordings');
    }
};
