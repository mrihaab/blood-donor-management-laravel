@props([
    'title',
    'value',
    'icon' => 'activity',
    'color' => 'red', // red, blue, green, amber, slate
    'subtext' => null,
])

@php
    $colorMap = [
        'red' => 'bg-red-50 dark:bg-rose-950/80 text-red-700 dark:text-rose-300 border border-red-200 dark:border-rose-800/80',
        'blue' => 'bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/80',
        'green' => 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80',
        'amber' => 'bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/80',
        'slate' => 'bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700',
    ];
    $badgeStyle = $colorMap[$color] ?? $colorMap['slate'];
@endphp

<div class="bg-white dark:bg-[#0c1427] overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800/80 p-5 shadow-sm transition hover:shadow-md">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $title }}</p>
            <h3 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $value }}</h3>
            @if($subtext)
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $subtext }}</p>
            @endif
        </div>
        <div class="rounded-lg p-3 {{ $badgeStyle }}">
            <svg class="h-6 w-6 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                @if($icon === 'droplet')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25c-5.25 6-9 10.5-9 14.25a9 9 0 0018 0c0-3.75-3.75-8.25-9-14.25z" />
                @elseif($icon === 'alert-triangle')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                @elseif($icon === 'clock')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                @elseif($icon === 'users')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                @endif
            </svg>
        </div>
    </div>
</div>
