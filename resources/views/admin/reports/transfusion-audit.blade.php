@extends('layouts.admin')

@section('title', 'Transfusion Safety Audit Matrix')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'System Reports', 'url' => route('admin.reports.index')],
        ['label' => 'Transfusion Audit Matrix']
    ]" />

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Transfusion Safety & Expiry Audit</h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">Operational audit log for dispensed blood units, safety validation, and clinical location tracking.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.transfusion_audit', ['format' => 'csv']) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold rounded-xl text-xs shadow-sm transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl text-xs transition">
                &larr; Back to Reports
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
        <form method="GET" action="{{ route('admin.reports.transfusion_audit') }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-medium focus:border-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-medium focus:border-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Blood Group</label>
                <select name="blood_group" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-semibold focus:border-indigo-500 focus:outline-none">
                    <option value="">All Blood Groups</option>
                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                        <option value="{{ $bg }}" {{ request('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" :disabled="isSubmitting" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-sm transition disabled:opacity-50 inline-flex items-center justify-center gap-1">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Filtering...' : 'Filter'">Filter</span>
                </button>
                <a href="{{ route('admin.reports.transfusion_audit') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold rounded-xl text-xs transition text-center">Reset</a>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <h2 class="font-extrabold text-slate-900 dark:text-white text-base">Dispensed Unit Safety Audit Registry</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs md:text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3.5">Req #</th>
                        <th scope="col" class="px-6 py-3.5">Hospital & Ward Location</th>
                        <th scope="col" class="px-6 py-3.5">Patient & MRN</th>
                        <th scope="col" class="px-6 py-3.5">Dispensed Barcode & DIN</th>
                        <th scope="col" class="px-6 py-3.5">Blood Group</th>
                        <th scope="col" class="px-6 py-3.5">Expiry Audit</th>
                        <th scope="col" class="px-6 py-3.5">Safety Verification</th>
                        <th scope="col" class="px-6 py-3.5">Dispensing Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse($transfusions as $t)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900 dark:text-slate-100">#REQ-{{ $t->id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $t->hospitalEntity->name ?? $t->hospital }}</div>
                                <div class="text-[11px] text-emerald-700 dark:text-emerald-400 font-semibold">
                                    📍 {{ $t->ward_name ?? ($t->patient->ward_name ?? 'ICU Ward 3') }}
                                    @if($t->room_number || $t->patient->room_number) | R: {{ $t->room_number ?? $t->patient->room_number }} @endif
                                    @if($t->bed_number || $t->patient->bed_number) | B: {{ $t->bed_number ?? $t->patient->bed_number }} @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $t->patient->name ?? $t->patient_name }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">MRN: {{ $t->patient->mrn ?? 'MRN-94821' }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs">
                                <div class="font-bold text-indigo-900 dark:text-indigo-300">{{ $t->dispensed_bag_barcode }}</div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400">DIN: {{ $t->din_number }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 text-xs font-black rounded-lg border border-rose-200 dark:border-rose-800/60">
                                    {{ $t->blood_group }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $t->expiry_date_val->format('M d, Y') }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Dispensed: {{ $t->updated_at->format('M d, Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($t->safety_verified)
                                    <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-950/80 text-emerald-900 dark:text-emerald-300 text-[11px] font-extrabold rounded-full border border-emerald-300 dark:border-emerald-800/60 inline-flex items-center gap-1">
                                        ✓ Verification Validated
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-amber-100 dark:bg-amber-950/80 text-amber-900 dark:text-amber-300 text-[11px] font-extrabold rounded-full border border-amber-300 dark:border-amber-800/60 inline-flex items-center gap-1">
                                        ⚠️ Expiry Check Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                {{ $t->approver->name ?? 'Central Blood Bank Admin' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <x-empty-state 
                                    title="No Audit Records Found" 
                                    description="No completed transfusion records match the specified search parameters."
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
