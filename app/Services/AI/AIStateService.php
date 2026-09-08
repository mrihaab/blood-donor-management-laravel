<?php

namespace App\Services\AI;

use App\Enums\AISafetyState;
use Illuminate\Support\Facades\DB;

class AIStateService
{
    /**
     * Get current Safety Epoch Version.
     */
    public function getCurrentEpoch(): int
    {
        return (int) DB::table('ai_safety_epoch')->where('id', 1)->value('epoch_version');
    }

    /**
     * Atomically increment Safety Epoch Version and update mode.
     */
    public function incrementEpoch(array $stateUpdates = []): int
    {
        return DB::transaction(function () use ($stateUpdates) {
            $current = DB::table('ai_safety_epoch')->where('id', 1)->lockForUpdate()->first();
            $newEpoch = $current->epoch_version + 1;

            $updateData = array_merge($stateUpdates, [
                'epoch_version' => $newEpoch,
                'updated_at' => now(),
            ]);

            DB::table('ai_safety_epoch')->where('id', 1)->update($updateData);

            return $newEpoch;
        });
    }

    /**
     * Capabilities Engine Matrix. Returns exact boolean capabilities for current system state.
     */
    public function getEffectiveCapabilities(): array
    {
        $epochRow = DB::table('ai_safety_epoch')->where('id', 1)->first();

        $recovery = (bool) ($epochRow->recovery_mode ?? false);
        $deployment = (bool) ($epochRow->deployment_mode ?? false);
        $killSwitch = (bool) ($epochRow->global_kill_switch ?? false);

        if ($recovery || $killSwitch) {
            return [
                'autonomous_ai_allowed' => false,
                'manual_emergency_allowed' => true,
                'deployments_allowed' => !$recovery,
                'effective_mode' => $recovery ? 'RECOVERY_MODE' : 'GLOBAL_KILL_SWITCH',
            ];
        }

        if ($deployment) {
            return [
                'autonomous_ai_allowed' => false,
                'manual_emergency_allowed' => true,
                'deployments_allowed' => true,
                'effective_mode' => 'DEPLOYMENT_MODE',
            ];
        }

        return [
            'autonomous_ai_allowed' => true,
            'manual_emergency_allowed' => true,
            'deployments_allowed' => true,
            'effective_mode' => 'NORMAL',
        ];
    }

    /**
     * Check if a safety blocker is currently active.
     */
    public function isSafetyBlockerActive(): bool
    {
        $capabilities = $this->getEffectiveCapabilities();
        return !$capabilities['autonomous_ai_allowed'];
    }
}
