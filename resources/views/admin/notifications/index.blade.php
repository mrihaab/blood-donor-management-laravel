@extends('layouts.admin')

@section('title', 'Notifications')
@section('page_title', 'System Notifications')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-900">Broadcast Alerts & Notifications</h3>
        <a href="{{ route('admin.notifications.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">
            + Send Notification
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Title / Subject</th>
                        <th class="px-4 py-3">Recipient Type</th>
                        <th class="px-4 py-3">Sent At</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($notifications as $notif)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $notif->title ?? $notif->subject ?? 'Notification #'.$notif->id }}</td>
                            <td class="px-4 py-3">{{ ucfirst($notif->type ?? 'broadcast') }}</td>
                            <td class="px-4 py-3">{{ $notif->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.notifications.show', $notif->id) }}" class="text-blue-600 hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 italic">No notifications sent yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
