@extends('layouts.admin')

@section('title', 'Activity Audit Log')
@section('page_title', 'System Activity Audit Log')

@section('content')
<div class="space-y-6 pb-12" x-data="{ isSubmitting: false }">
    <!-- Header Row -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">System Activity & Security Audit Log</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Audit administrative operations, donor profile updates, and clinical transactions.</p>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white dark:bg-[#0c1427] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search audit log description..." class="flex-1 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none">
            <button type="submit" class="py-2 px-5 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-white text-white dark:text-slate-900 font-extrabold rounded-xl text-xs shadow-sm transition">
                Filter Logs
            </button>
            @if(request('search'))
                <a href="{{ route('admin.activity-logs.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs transition flex items-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Activity Log Table -->
    <div class="bg-white dark:bg-[#0c1427] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100/80 dark:bg-[#111c38] text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3.5">Timestamp</th>
                        <th class="px-4 py-3.5">User (Causer)</th>
                        <th class="px-4 py-3.5">Action & Event Description</th>
                        <th class="px-4 py-3.5">Subject Resource</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-semibold">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-xs text-slate-600 dark:text-slate-400">
                                {{ $log->created_at->format('M d, Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-extrabold text-slate-900 dark:text-white">
                                    {{ optional($log->causer)->name ?? 'System Event' }}
                                </span>
                                @if(optional($log->causer)->email)
                                    <span class="block text-xs font-mono text-slate-500 dark:text-slate-400 font-normal">
                                        {{ $log->causer->email }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-800 dark:text-slate-200">
                                <p class="font-bold text-slate-900 dark:text-white">{{ $log->description }}</p>
                                @if($log->properties && count($log->properties) > 0)
                                    <details class="mt-1">
                                        <summary class="text-[11px] text-rose-600 dark:text-rose-400 font-semibold cursor-pointer hover:underline">View Metadata Details</summary>
                                        <div class="mt-1.5 p-2 bg-slate-100 dark:bg-slate-900 rounded-lg text-[10px] font-mono text-slate-700 dark:text-slate-300 max-h-32 overflow-y-auto">
                                            @foreach($log->properties as $key => $val)
                                                <div><strong>{{ $key }}:</strong> {{ is_array($val) ? json_encode($val) : $val }}</div>
                                            @endforeach
                                        </div>
                                    </details>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($log->subject_type)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-600 font-normal italic">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-slate-500 italic">No activity log entries found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($logs, 'links') && $logs->hasPages())
            <div class="mt-4 border-t border-slate-200 dark:border-slate-800 pt-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
