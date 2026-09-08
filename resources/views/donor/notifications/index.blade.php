@extends('layouts.donor')

@section('title', 'Notifications')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Notifications & Appeals Stream</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Community emergency blood calls, appointment reminders, and account updates.</p>
        </div>
        @if(($unreadCount ?? 0) > 0)
            <form method="POST" action="{{ route('donor.notifications.read_all') }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                @csrf
                <button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5 self-start sm:self-auto">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="isSubmitting ? 'Updating...' : 'Mark All as Read ({{ $unreadCount }})'">Mark All as Read ({{ $unreadCount }})</span>
                </button>
            </form>
        @endif
    </div>

    <!-- Notifications List Container -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm transition-colors space-y-4">
        @forelse($notifications as $notif)
            <div class="p-5 rounded-2xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $notif->isRead() ? 'bg-white dark:bg-[#070d1a] border-slate-200 dark:border-slate-800' : 'bg-rose-50/60 dark:bg-rose-950/30 border-rose-200 dark:border-rose-900/60 shadow-sm' }}">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-md {{ $notif->type === 'emergency' ? 'bg-rose-600 text-white' : 'bg-blue-600 text-white' }}">
                            {{ $notif->type }}
                        </span>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ $notif->title }}</h3>
                        <span class="text-xs font-mono text-slate-400 dark:text-slate-500">&bull; {{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-slate-700 dark:text-slate-300">{{ $notif->message }}</p>
                </div>

                @if(!$notif->isRead())
                    <div class="shrink-0">
                        <form method="POST" action="{{ route('donor.notifications.read', $notif->id) }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                            @csrf
                            <button type="submit" :disabled="isSubmitting" class="px-3.5 py-1.5 bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/60 text-rose-700 dark:text-rose-300 font-bold text-xs rounded-xl border border-rose-200 dark:border-rose-900/60 shadow-sm transition">
                                <span x-text="isSubmitting ? '...' : 'Mark Read'">Mark Read</span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="py-12 text-center text-slate-500 dark:text-slate-400 italic">
                <div class="flex flex-col items-center space-y-2">
                    <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span>You're all caught up! No notifications recorded.</span>
                </div>
            </div>
        @endforelse

        @if(method_exists($notifications, 'links'))
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
