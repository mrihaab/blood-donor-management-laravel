<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::with('creator')
            ->latest()
            ->paginate(15);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        $donors = User::where('role', 'donor')->get();
        return view('admin.notifications.create', compact('donors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:email,sms,system,announcement',
            'recipients' => 'required|array',
            'recipients.*' => 'exists:users,id'
        ]);

        $notification = Notification::create([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'recipients' => $request->recipients,
            'created_by' => auth()->id()
        ]);

        // Send notification immediately
        $this->sendNotification($notification);

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification sent successfully!');
    }

    public function show(Notification $notification)
    {
        $notification->load('creator');
        return view('admin.notifications.show', compact('notification'));
    }

    public function sendBulk(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:email,sms,system,announcement',
            'send_to' => 'required|in:all_donors,all_admins,all_users'
        ]);

        $recipients = $this->getRecipientsByType($request->send_to);

        $notification = Notification::create([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'recipients' => $recipients->pluck('id')->toArray(),
            'created_by' => auth()->id()
        ]);

        $this->sendNotification($notification);

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Bulk notification sent to ' . $recipients->count() . ' users!');
    }

    private function sendNotification(Notification $notification)
    {
        try {
            $recipients = User::whereIn('id', $notification->recipients)->get();
            $successCount = 0;
            $failureCount = 0;

            foreach ($recipients as $recipient) {
                try {
                    if ($notification->type === 'email') {
                        // Send email notification
                        Mail::to($recipient->email)->send(new \App\Mail\NotificationMail($notification));
                    } elseif ($notification->type === 'sms') {
                        // SMS notification would be implemented here
                        // This is a placeholder for SMS integration
                        Log::info("SMS notification sent to {$recipient->phone}");
                    }
                    $successCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to send notification to user {$recipient->id}: " . $e->getMessage());
                    $failureCount++;
                }
            }

            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
                'delivery_stats' => [
                    'success' => $successCount,
                    'failed' => $failureCount,
                    'total' => $successCount + $failureCount
                ]
            ]);

        } catch (\Exception $e) {
            $notification->update(['status' => 'failed']);
            Log::error('Notification sending failed: ' . $e->getMessage());
        }
    }

    private function getRecipientsByType($type)
    {
        switch ($type) {
            case 'all_donors':
                return User::where('role', 'donor')->get();
            case 'all_admins':
                return User::where('role', 'admin')->get();
            case 'all_users':
                return User::all();
            default:
                return collect();
        }
    }
}
