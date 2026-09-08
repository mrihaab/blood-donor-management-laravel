@extends('layouts.admin')

@section('title', 'Inventory Report')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'System Reports', 'url' => route('admin.reports.index')],
        ['label' => 'Inventory Report']
    ]" />

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Blood Stock Inventory Report</h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">Breakdown of blood unit inventory, intake origins, and stock status threshold.</p>
        </div>
        <div>
            <a href="{{ route('admin.reports.inventory', ['format' => 'pdf']) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow-sm transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Export Stock Report PDF</span>
            </a>
        </div>
    </div>

    <!-- Stock Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-6 bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Active Available Stock</p>
                <p class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">{{ $totalAvailable }} Units</p>
            </div>
            <div class="p-3 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl text-2xl font-bold shadow-xs">
                🟢
            </div>
        </div>
        <div class="p-6 bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Dispensed Stock</p>
                <p class="text-3xl font-extrabold text-purple-600 dark:text-purple-400 mt-1">{{ $totalDispensed }} Units</p>
            </div>
            <div class="p-3 bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 rounded-xl text-2xl font-bold shadow-xs">
                🟣
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <h2 class="font-extrabold text-slate-900 dark:text-white text-base">Current Stock Availability by Blood Group</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs md:text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3.5">Blood Group</th>
                        <th scope="col" class="px-6 py-3.5">Available Units</th>
                        <th scope="col" class="px-6 py-3.5">Donor Intake</th>
                        <th scope="col" class="px-6 py-3.5">Direct Admin Intake</th>
                        <th scope="col" class="px-6 py-3.5">Expiring Soon (7 Days)</th>
                        <th scope="col" class="px-6 py-3.5">Inventory Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @foreach($inventory as $item)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-slate-100">
                                <span class="px-2.5 py-1 bg-rose-700 text-white text-xs font-black rounded-md">
                                    {{ $item['blood_group'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-extrabold text-slate-900 dark:text-slate-100 text-base">
                                {{ $item['units_available'] }} Bags
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                                {{ $item['donor_intake_count'] }} Bags
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-purple-700 dark:text-purple-400">
                                {{ $item['direct_intake_count'] }} Bags
                            </td>
                            <td class="px-6 py-4">
                                @if($item['expiring_soon'] > 0)
                                    <span class="font-bold text-amber-600 dark:text-amber-400 inline-flex items-center gap-1">
                                        ⚠️ {{ $item['expiring_soon'] }} Bag(s)
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">0 Bags</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($item['is_low_stock'])
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 inline-flex items-center gap-1">
                                        ⚠️ Low Stock Threshold
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 inline-flex items-center gap-1">
                                        ✓ Optimal Stock
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
