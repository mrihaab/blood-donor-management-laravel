@extends('layouts.admin')

@section('title', 'Admin Notifications')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">System Notification Feed</h1>
            <p class="text-xs text-slate-500 mt-1">Operational alerts, emergency requisition notices, and system events.</p>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('admin.notifications_feed.read_all') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white font-semibold text-xs rounded-lg hover:bg-slate-900 transition">
                    Mark All as Read ({{ $unreadCount }})
                </button>
            </form>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
        @forelse($notifications as $notif)
            <div class="p-4 rounded-lg border {{ $notif->isRead() ? 'bg-white border-slate-200' : 'bg-blue-50/60 border-blue-200' }} flex items-start justify-between">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-0.5 text-xs font-bold uppercase rounded-md {{ $notif->type === 'emergency' ? 'bg-red-600 text-white' : 'bg-blue-600 text-white' }}">
                            {{ $notif->type }}
                        </span>
                        <h3 class="text-sm font-bold text-slate-900">{{ $notif->title }}</h3>
                        <span class="text-xs text-slate-400">&bull; {{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-slate-700 mt-2">{{ $notif->message }}</p>
                </div>

                @if(!$notif->isRead())
                    <form method="POST" action="{{ route('admin.notifications_feed.read', $notif->id) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 font-semibold text-xs rounded transition">
                            Mark Read
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <p class="text-center text-slate-500 italic py-8">No notifications in feed.</p>
        @endforelse

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
