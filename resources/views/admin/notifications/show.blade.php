@extends('layouts.admin')

@section('title', 'Notification Details')
@section('page_title', 'Notification Details')

@section('content')
<div class="max-w-3xl space-y-6 pb-12">
    <!-- Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $notification->title ?? $notification->subject ?? 'Notification Details' }}</h1>
            <p class="text-xs md:text-sm font-mono text-slate-600 dark:text-slate-400 mt-1">Broadcast on {{ $notification->created_at->format('F d, Y \a\t h:i A') }}</p>
        </div>
        <a href="{{ route('admin.notifications.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">
            Back to Feed
        </a>
    </div>

    <!-- Notification Details Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 md:p-8 space-y-4">
        <div class="flex items-center gap-2">
            <x-status-badge :status="($notification->type ?? 'system') === 'emergency' ? 'emergency' : 'approved'" :label="ucfirst($notification->type ?? 'system')" />
            <span class="text-xs font-mono text-slate-500 dark:text-slate-400">&bull; Sent by {{ optional($notification->creator)->name ?? 'System Admin' }}</span>
        </div>

        <div class="text-slate-800 dark:text-slate-200 text-sm leading-relaxed pt-2 border-t border-slate-100 dark:border-slate-800">
            {{ $notification->message ?? $notification->content ?? 'No content provided.' }}
        </div>
    </div>
</div>
@endsection
