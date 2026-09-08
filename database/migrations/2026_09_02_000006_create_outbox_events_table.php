<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbox_events', function (Blueprint $table) {
            $table->id();
            $table->string('notification_intent_id')->unique();
            $table->string('idempotency_key');
            $table->string('logical_operation_id')->nullable();
            $table->string('event_type');
            $table->json('payload');
            $table->string('status')->default('QUEUED'); // QUEUED, CLAIMED, SENT_UNKNOWN, RECONCILING, CONFIRMED, FAILED
            $table->unsignedInteger('retry_count')->default(0);
            $table->string('failure_reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_events');
    }
};
