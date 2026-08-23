<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    /**
     * Log an activity into the unified Spatie activity_log table.
     */
    public static function logActivity($message, $userId = null)
    {
        $causer = $userId ? User::find($userId) : auth()->user();
        
        $activity = activity();
        if ($causer) {
            $activity->causedBy($causer);
        }

        return $activity->log($message);
    }
}
