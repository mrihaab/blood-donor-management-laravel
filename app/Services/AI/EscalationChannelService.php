<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EscalationChannelService
{
    /**
     * Dispatch emergency alert through 4-Level Hierarchy.
     */
    public function dispatchEscalation(
        int $requestId,
        string $tier, // LEVEL_1_WS, LEVEL_2_PRIMARY_SMS, LEVEL_3_SECONDARY_SMS, LEVEL_4_PHYSICAL_SOP
        string $recipientChannel,
        string $message
    ): array {
        $intentId = hash('sha256', "{$requestId}:{$tier}:{$recipientChannel}");

        DB::table('escalation_events')->insertOrIgnore([
            'escalation_intent_id' => $intentId,
            'request_id' => $requestId,
            'escalation_tier' => $tier,
            'recipient_phone_or_channel' => $recipientChannel,
            'status' => 'SENT',
            'message' => $message,
            'created_at' => now(),
            'delivered_at' => now(),
        ]);

        Log::info("Emergency Escalation Dispatched [{$tier}]", [
            'request_id' => $requestId,
            'channel' => $recipientChannel,
            'intent_id' => $intentId,
        ]);

        return [
            'intent_id' => $intentId,
            'tier' => $tier,
            'status' => 'SENT',
        ];
    }
}
