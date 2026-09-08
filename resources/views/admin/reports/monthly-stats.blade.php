@extends('layouts.admin')

@section('title', 'Monthly Statistics Report')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'System Reports', 'url' => route('admin.reports.index')],
        ['label' => 'Monthly Statistics']
    ]" />

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Monthly Operational Summary</h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">Overview for {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</p>
        </div>
        <div>
            <a href="{{ route('admin.reports.monthly-stats', ['format' => 'pdf', 'month' => $month, 'year' => $year]) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow-sm transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Export Executive Summary PDF</span>
            </a>
        </div>
    </div>

    <!-- Filter & Summary Container Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-6">
        <!-- Month / Year Selector Filter -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-5 gap-4">
            <div>
                <h2 class="font-extrabold text-slate-900 dark:text-white text-lg">Select Reporting Period</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Filter statistics by month and calendar year.</p>
            </div>

            <form method="GET" action="{{ route('admin.reports.monthly-stats') }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true" class="flex items-center gap-2">
                <select name="month" class="rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 px-3 py-2 text-xs font-semibold focus:border-rose-500 focus:outline-none">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
                <select name="year" class="rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 px-3 py-2 text-xs font-semibold focus:border-rose-500 focus:outline-none">
                    @for($y = now()->year - 2; $y <= now()->year; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" :disabled="isSubmitting" class="rounded-xl bg-slate-900 dark:bg-slate-700 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800 dark:hover:bg-slate-600 transition disabled:opacity-50 inline-flex items-center gap-1">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Loading...' : 'Filter'">Filter</span>
                </button>
            </form>
        </div>

        <!-- KPI Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="p-4 bg-rose-50 dark:bg-rose-950/40 rounded-xl border border-rose-100 dark:border-rose-900/50">
                <p class="text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400">Donation Units Collected</p>
                <p class="text-3xl font-extrabold text-rose-900 dark:text-rose-100 mt-1">{{ $stats['donations'] }}</p>
            </div>
            <div class="p-4 bg-blue-50 dark:bg-blue-950/40 rounded-xl border border-blue-100 dark:border-blue-900/50">
                <p class="text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400">New Donors Registered</p>
                <p class="text-3xl font-extrabold text-blue-900 dark:text-blue-100 mt-1">{{ $stats['new_donors'] }}</p>
            </div>
            <div class="p-4 bg-purple-50 dark:bg-purple-950/40 rounded-xl border border-purple-100 dark:border-purple-900/50">
                <p class="text-xs font-bold uppercase tracking-wider text-purple-700 dark:text-purple-400">Requisitions Filed</p>
                <p class="text-3xl font-extrabold text-purple-900 dark:text-purple-100 mt-1">{{ $stats['blood_requests'] }}</p>
            </div>
            <div class="p-4 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-100 dark:border-amber-900/50">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Requisitions Approved</p>
                <p class="text-3xl font-extrabold text-amber-900 dark:text-amber-100 mt-1">{{ $stats['approved_requests'] }}</p>
            </div>
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-100 dark:border-emerald-900/50">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Dispensed to Hospitals</p>
                <p class="text-3xl font-extrabold text-emerald-900 dark:text-emerald-100 mt-1">{{ $stats['dispensed_units'] }}</p>
            </div>
        </div>

        <!-- Blood Group Intake Distribution -->
        @if(count($bloodGroupStats) > 0)
            <div class="pt-5 border-t border-slate-100 dark:border-slate-800 space-y-4">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Blood Group Intake Distribution for {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($bloodGroupStats as $bgStat)
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700/60 flex items-center justify-between">
                            <span class="font-bold text-xs rounded-md bg-rose-700 text-white px-2 py-0.5">{{ $bgStat->name }}</span>
                            <span class="font-extrabold text-slate-900 dark:text-slate-100 text-sm">{{ $bgStat->total }} Unit(s)</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
