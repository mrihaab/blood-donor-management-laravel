@props([
    'status',
    'label' => null,
])

@php
    $statusKey = strtolower($status);
    $displayLabel = $label ?? ucfirst($statusKey);

    $styles = [
        'available' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'active'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'approved'  => 'bg-blue-50 text-blue-700 border-blue-200',
        'allocated' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'reserved'  => 'bg-amber-50 text-amber-700 border-amber-200',
        'pending'   => 'bg-amber-50 text-amber-700 border-amber-200',
        'scheduled' => 'bg-blue-50 text-blue-700 border-blue-200',
        'dispensed' => 'bg-purple-50 text-purple-700 border-purple-200',
        'expired'   => 'bg-rose-50 text-rose-700 border-rose-200',
        'discarded' => 'bg-slate-100 text-slate-700 border-slate-300',
        'rejected'  => 'bg-rose-50 text-rose-700 border-rose-200',
        'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
    ];

    $badgeStyle = $styles[$statusKey] ?? 'bg-slate-50 text-slate-700 border-slate-200';
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $badgeStyle }}">
    <span class="h-1.5 w-1.5 rounded-full fill-current"></span>
    {{ $displayLabel }}
</span>
