@extends('layouts.admin')

@section('title', 'Admin Notifications')
@section('page_title', 'System Notification Feed')

@section('content')
<div class="space-y-6 pb-12" x-data="{ isSubmitting: false }">
    <!-- Header Row -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">System Notification Feed</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Operational alerts, emergency requisition notices, and system broadcast events.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.notifications.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-extrabold text-white hover:bg-rose-700 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-rose-500">
                <span>+ Broadcast Alert</span>
            </a>
            @if(isset($unreadCount) && $unreadCount > 0)
                <form method="POST" action="{{ route('admin.notifications_feed.read_all') }}" @submit="isSubmitting = true">
                    @csrf
                    <button type="submit" 
                            :disabled="isSubmitting"
                            class="px-4 py-2.5 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-white text-white dark:text-slate-900 font-extrabold text-xs rounded-xl shadow-sm transition disabled:opacity-50 inline-flex items-center gap-1.5">
                        <template x-if="isSubmitting">
                            <svg class="animate-spin h-3.5 w-3.5 text-white dark:text-slate-900" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </template>
                        <span x-text="isSubmitting ? 'Updating...' : 'Mark All Read ({{ $unreadCount }})'"></span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Notification Feed Container -->
    <div class="bg-white dark:bg-[#0c1427] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
        @forelse($notifications as $notif)
            <div class="p-4 rounded-xl border transition flex items-start justify-between gap-4 {{ method_exists($notif, 'isRead') && $notif->isRead() ? 'bg-white dark:bg-[#0c1427] border-slate-200 dark:border-slate-800' : 'bg-rose-50/40 dark:bg-rose-950/30 border-rose-200 dark:border-rose-900/60' }}">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <x-status-badge :status="($notif->type ?? 'system') === 'emergency' ? 'emergency' : 'approved'" :label="ucfirst($notif->type ?? 'system')" />
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">
                            <a href="{{ route('admin.notifications.show', $notif->id) }}" class="hover:text-rose-600 dark:hover:text-rose-400 transition">
                                {{ $notif->title }}
                            </a>
                        </h3>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">&bull; {{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{{ $notif->message }}</p>
                </div>

                @if(method_exists($notif, 'isRead') && !$notif->isRead())
                    <form method="POST" action="{{ route('admin.notifications_feed.read', $notif->id) }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-rose-100 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 hover:bg-rose-200 font-bold text-xs rounded-xl transition">
                            Mark Read
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="py-12 text-center text-slate-500 italic">No notifications found in system feed.</div>
        @endforelse

        @if(method_exists($notifications, 'links') && $notifications->hasPages())
            <div class="mt-4 border-t border-slate-200 dark:border-slate-800 pt-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
