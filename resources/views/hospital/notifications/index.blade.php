@extends('layouts.hospital')

@section('title', 'Hospital Notifications')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('hospital.dashboard')],
        ['label' => 'Notifications Feed']
    ]" />

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Clinical Notifications Feed</h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">Requisition updates, blood allocation notices, and central vault alerts.</p>
        </div>
        
        @if(isset($unreadCount) && $unreadCount > 0)
            <form method="POST" action="{{ route('notifications.mark_all_read') }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                @csrf
                <button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl transition shadow-sm disabled:opacity-50 inline-flex items-center gap-1.5">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Updating...' : 'Mark All Read (' + {{ $unreadCount }} + ')'">Mark All Read ({{ $unreadCount }})</span>
                </button>
            </form>
        @endif
    </div>

    <!-- Notifications List Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm transition-colors space-y-4">
        @forelse($notifications as $notif)
            <div class="p-4 rounded-xl border {{ $notif->read_at ? 'bg-white dark:bg-slate-900/40 border-slate-200 dark:border-slate-800' : 'bg-blue-50/70 dark:bg-blue-950/40 border-blue-200 dark:border-blue-800/80' }} flex items-start justify-between gap-4 transition">
                <div class="space-y-1.5 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md {{ $notif->type === 'approved' ? 'bg-blue-600 text-white' : ($notif->type === 'dispensed' ? 'bg-emerald-600 text-white' : ($notif->type === 'rejected' ? 'bg-rose-600 text-white' : 'bg-slate-700 text-white')) }}">
                            {{ $notif->type ?? 'Alert' }}
                        </span>
                        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ $notif->title }}</h2>
                        <span class="text-xs text-slate-400 font-mono">&bull; {{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{{ $notif->message }}</p>
                </div>

                @if(!$notif->read_at)
                    <form method="POST" action="{{ route('notifications.mark_read', $notif->id) }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                        @csrf
                        <button type="submit" :disabled="isSubmitting" class="px-3 py-1.5 bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 hover:bg-blue-200 font-bold text-xs rounded-lg border border-blue-200 dark:border-blue-800 transition whitespace-nowrap">
                            Mark Read
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <x-empty-state 
                title="No Notifications Recorded" 
                description="Your hospital has no recent clinical alerts or requisition updates."
            />
        @endforelse

        @if($notifications->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
