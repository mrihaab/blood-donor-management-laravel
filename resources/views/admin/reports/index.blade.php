@extends('layouts.admin')

@section('title', 'System Reports & Export')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'System Reports']
    ]" />

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">System Reports & Exports</h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">Generate operational summaries, export data registries, and audit clinical logs.</p>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Donor Reports -->
        <div class="bg-white dark:bg-[#0c1427] p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition">
            <div>
                <div class="w-12 h-12 bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 rounded-xl flex items-center justify-center font-bold text-2xl mb-4 shadow-xs">
                    🧍
                </div>
                <h2 class="font-extrabold text-slate-900 dark:text-white text-lg">Donor Reports</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Export donor directory, blood group distributions, contact registries, and participation counts.</p>
            </div>
            <div class="mt-6 flex flex-col gap-2">
                <a href="{{ route('admin.reports.donors') }}" class="w-full px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl text-center transition">View Report</a>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('admin.reports.donors', ['format' => 'csv']) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg text-center transition">CSV</a>
                    <a href="{{ route('admin.reports.donors', ['format' => 'pdf']) }}" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg text-center transition">PDF</a>
                </div>
            </div>
        </div>

        <!-- Donation Reports -->
        <div class="bg-white dark:bg-[#0c1427] p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition">
            <div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center font-bold text-2xl mb-4 shadow-xs">
                    💉
                </div>
                <h2 class="font-extrabold text-slate-900 dark:text-white text-lg">Donation Reports</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Review historical blood bag intake logs, component types, collection dates, and expiry dates.</p>
            </div>
            <div class="mt-6 flex flex-col gap-2">
                <a href="{{ route('admin.reports.donations') }}" class="w-full px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl text-center transition">View Report</a>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('admin.reports.donations', ['format' => 'csv']) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg text-center transition">CSV</a>
                    <a href="{{ route('admin.reports.donations', ['format' => 'pdf']) }}" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg text-center transition">PDF</a>
                </div>
            </div>
        </div>

        <!-- Inventory Reports -->
        <div class="bg-white dark:bg-[#0c1427] p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition">
            <div>
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center font-bold text-2xl mb-4 shadow-xs">
                    🗂️
                </div>
                <h2 class="font-extrabold text-slate-900 dark:text-white text-lg">Inventory Reports</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Export active available stock by blood group, intake source breakdowns, and low-stock alerts.</p>
            </div>
            <div class="mt-6 flex flex-col gap-2">
                <a href="{{ route('admin.reports.inventory') }}" class="w-full px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl text-center transition">View Report</a>
                <a href="{{ route('admin.reports.inventory', ['format' => 'pdf']) }}" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg text-center transition">Export PDF Certificate</a>
            </div>
        </div>

        <!-- Monthly Statistics -->
        <div class="bg-white dark:bg-[#0c1427] p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition">
            <div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 rounded-xl flex items-center justify-center font-bold text-2xl mb-4 shadow-xs">
                    📊
                </div>
                <h2 class="font-extrabold text-slate-900 dark:text-white text-lg">Monthly Statistics</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Monthly collection totals, hospital requisition fulfillments, and operational performance.</p>
            </div>
            <div class="mt-6 flex flex-col gap-2">
                <a href="{{ route('admin.reports.monthly-stats') }}" class="w-full px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl text-center transition">View Summary</a>
                <a href="{{ route('admin.reports.monthly-stats', ['format' => 'pdf']) }}" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg text-center transition">Executive PDF</a>
            </div>
        </div>
    </div>

    <!-- Transfusion Audit Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 text-white shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-bold mb-2 border border-indigo-500/30">
                🔒 Clinical Safety & Quality Control
            </div>
            <h2 class="text-xl font-extrabold">Transfusion Safety & Expiry Audit Matrix</h2>
            <p class="text-xs text-slate-300 mt-1 max-w-2xl">Trace dispensed blood units against hospital requisitions, patient MRNs, expiration dates, and dispensing authorization logs.</p>
        </div>
        <a href="{{ route('admin.reports.transfusion_audit') }}" class="shrink-0 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-sm transition inline-flex items-center gap-2">
            <span>📋</span> View Audit Matrix
        </a>
    </div>
</div>
@endsection
