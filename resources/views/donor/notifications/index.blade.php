@extends('layouts.donor')

@section('title', 'Emergency & Status Notifications')
@section('page_title', 'Emergency Appeals & Notifications')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Your Emergency Appeals & Alerts</h2>
            <p class="text-xs text-gray-500 mt-1">Urgent blood appeals matching your blood profile and account notifications.</p>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('donor.notifications.read_all') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 text-white font-semibold text-xs rounded-lg hover:bg-red-700 transition">
                    Mark All as Read ({{ $unreadCount }})
                </button>
            </form>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
        @forelse($notifications as $notif)
            <div class="p-4 rounded-lg border {{ $notif->isRead() ? 'bg-white border-gray-200' : 'bg-red-50/50 border-red-200' }} flex items-start justify-between">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-0.5 text-xs font-bold uppercase rounded-md {{ $notif->type === 'emergency' ? 'bg-red-600 text-white' : 'bg-blue-600 text-white' }}">
                            {{ $notif->type }}
                        </span>
                        <h3 class="text-sm font-bold text-gray-900">{{ $notif->title }}</h3>
                        <span class="text-xs text-gray-400">&bull; {{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-gray-700 mt-2">{{ $notif->message }}</p>
                </div>

                @if(!$notif->isRead())
                    <form method="POST" action="{{ route('donor.notifications.read', $notif->id) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 hover:bg-red-200 font-semibold text-xs rounded transition">
                            Mark Read
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <p class="text-center text-gray-500 italic py-8">No emergency alerts recorded.</p>
        @endforelse

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
