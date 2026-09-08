<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escalation_events', function (Blueprint $table) {
            $table->id();
            $table->string('escalation_intent_id')->unique();
            $table->unsignedBigInteger('request_id')->nullable();
            $table->string('escalation_tier'); // LEVEL_1_WS, LEVEL_2_PRIMARY_SMS, LEVEL_3_SECONDARY_SMS, LEVEL_4_PHYSICAL_SOP
            $table->string('recipient_phone_or_channel');
            $table->string('status')->default('QUEUED'); // QUEUED, SENT, SENT_UNKNOWN, CONFIRMED, FAILED
            $table->text('message');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('delivered_at')->nullable();

            $table->index(['status', 'escalation_tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escalation_events');
    }
};
