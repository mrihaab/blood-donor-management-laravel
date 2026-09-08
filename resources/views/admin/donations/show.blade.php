@extends('layouts.admin')

@section('title', 'Donation Record Details')
@section('page_title', 'Donation Record Details')

@section('content')
<div class="max-w-4xl space-y-6 pb-12">
    <!-- Header & Action Controls -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $donation->donor->user->name ?? 'Donor #'.$donation->donor_id }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-mono font-extrabold bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                    {{ $donation->bloodGroup->name ?? 'N/A' }}
                </span>
            </div>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1 font-mono">Donation Log Record #DON-{{ str_pad($donation->id, 5, '0', STR_PAD_LEFT) }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.donations.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">
                Back to Directory
            </a>
            <a href="{{ route('admin.donations.edit', $donation->id) }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-sm transition">
                Edit Record
            </a>
        </div>
    </div>

    <!-- Donation Record Details Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 md:p-8 space-y-6">
        <div>
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-3">Clinical Collection Specifications</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs bg-slate-50 dark:bg-[#111c38] p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">Collected Quantity</span>
                    <span class="font-mono font-extrabold text-slate-900 dark:text-white text-sm">
                        {{ $donation->quantity > 50 ? $donation->quantity.' mL' : $donation->quantity.' Unit(s)' }}
                    </span>
                </div>
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">Donation Date</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $donation->donation_date ?? $donation->created_at->format('Y-m-d') }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">Collection Center / Facility</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $donation->collection_center ?? 'Central Blood Bank Lab' }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">Associated Donor ID</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">DNR-{{ str_pad($donation->donor_id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                @if($donation->notes)
                    <div class="sm:col-span-2 border-t border-slate-200 dark:border-slate-800/80 pt-2 mt-1">
                        <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">Clinical Notes</span>
                        <p class="text-slate-700 dark:text-slate-300 font-medium">{{ $donation->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
