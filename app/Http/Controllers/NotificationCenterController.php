<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationCenterController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $notifications = UserNotification::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        $unreadCount = UserNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        // Render appropriate layout view according to user role
        $viewName = match($user->role) {
            'admin' => 'admin.notifications.index',
            'hospital' => 'hospital.notifications.index',
            default => 'donor.notifications.index',
        };

        return view($viewName, compact('notifications', 'unreadCount'));
    }

    public function unreadFeed()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unreadCount' => 0, 'notifications' => []]);
        }

        $unreadCount = UserNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        $notifications = UserNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(UserNotification $notification)
    {
        $user = Auth::user();

        // Strict IDOR Protection: User can only mark their own notifications as read
        if ((int)$notification->user_id !== (int)$user->id) {
            abort(403, 'Unauthorized notification access.');
        }

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        $user = Auth::user();

        UserNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
