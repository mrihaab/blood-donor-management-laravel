@extends('layouts.admin')

@section('title', 'Donations Report')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'System Reports', 'url' => route('admin.reports.index')],
        ['label' => 'Donations Report']
    ]" />

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Donations Report</h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">Physical blood unit intake logs, donor sources, and expiration timelines.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.donations', ['format' => 'csv']) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold rounded-xl text-xs shadow-sm transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('admin.reports.donations', ['format' => 'pdf']) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow-sm transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Export PDF</span>
            </a>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <h2 class="font-extrabold text-slate-900 dark:text-white text-base">Blood Bag Ingestion Registry</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs md:text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3.5">Unit Serial #</th>
                        <th scope="col" class="px-6 py-3.5">Donor / Source</th>
                        <th scope="col" class="px-6 py-3.5">Blood Group</th>
                        <th scope="col" class="px-6 py-3.5">Component</th>
                        <th scope="col" class="px-6 py-3.5">Collection Date</th>
                        <th scope="col" class="px-6 py-3.5">Expiry Date</th>
                        <th scope="col" class="px-6 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse($donations as $unit)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900 dark:text-slate-100">
                                {{ $unit->unit_number }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $unit->donor->user->name ?? 'Direct Admin Stock Intake' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 text-xs font-bold rounded-lg border border-rose-200 dark:border-rose-800/60">
                                    {{ $unit->bloodGroup->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                {{ $unit->component->name ?? 'Whole Blood' }}
                            </td>
                            <td class="px-6 py-4 text-xs font-mono text-slate-500 dark:text-slate-400">
                                {{ $unit->collection_date }}
                            </td>
                            <td class="px-6 py-4 text-xs font-mono text-slate-900 dark:text-slate-100 font-bold">
                                {{ $unit->expiry_date }}
                            </td>
                            <td class="px-6 py-4">
                                <x-status-badge :status="$unit->status" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <x-empty-state 
                                    title="No Donation Units Found" 
                                    description="No blood bag donation logs exist in the repository."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
