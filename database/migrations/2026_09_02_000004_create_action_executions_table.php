<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_executions', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key')->unique();
            $table->string('logical_operation_id');
            $table->unsignedInteger('attempt_number')->default(1);
            $table->string('approval_nonce')->nullable();
            $table->string('command_envelope_hash');
            $table->unsignedBigInteger('safety_epoch');
            $table->string('command_type');
            $table->unsignedBigInteger('facility_id')->nullable();
            $table->string('status')->default('EXECUTING'); // EXECUTING, COMPLETED, EXECUTION_UNKNOWN, RECONCILIATION_NOT_FOUND, FAILED_RETRYABLE, FAILED_FINAL, QUARANTINED
            $table->string('failure_reason')->nullable();
            $table->json('result_payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->unique(['logical_operation_id', 'attempt_number']);
            $table->index('logical_operation_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_executions');
    }
};
