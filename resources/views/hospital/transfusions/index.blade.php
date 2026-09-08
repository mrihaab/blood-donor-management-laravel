@extends('layouts.hospital')

@section('title', 'Clinical Transfusions')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('hospital.dashboard')],
        ['label' => 'Clinical Transfusions Log']
    ]" />

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Clinical Transfusions Log</h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">Bedside transfusion administration, cross-match status, and adverse reaction logs.</p>
        </div>

        <a href="{{ route('hospital.transfusions.create') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white font-extrabold rounded-xl text-xs transition shadow-lg shadow-blue-600/30 flex items-center gap-2 self-start sm:self-auto">
            <span>+ Schedule Transfusion</span>
        </a>
    </div>

    <!-- Transfusions Table Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm transition-colors space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs md:text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 uppercase text-[11px] font-extrabold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 rounded-l-xl">TR #</th>
                        <th scope="col" class="px-4 py-3.5">Patient / MRN</th>
                        <th scope="col" class="px-4 py-3.5">Requisition</th>
                        <th scope="col" class="px-4 py-3.5">Transfusion Status</th>
                        <th scope="col" class="px-4 py-3.5">Units Assigned</th>
                        <th scope="col" class="px-4 py-3.5 text-right rounded-r-xl">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse($transfusions as $t)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="px-4 py-4 font-black text-blue-600 dark:text-blue-400 font-mono">#TR-{{ $t->id }}</td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $t->patient->name }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                                    MRN: {{ $t->patient->mrn }} ({{ $t->patient->bloodGroup->name ?? 'N/A' }})
                                </div>
                            </td>
                            <td class="px-4 py-4 font-mono font-bold text-slate-800 dark:text-slate-200">#REQ-{{ $t->blood_request_id }}</td>
                            <td class="px-4 py-4">
                                <x-status-badge :status="$t->status" />
                            </td>
                            <td class="px-4 py-4 font-bold text-slate-800 dark:text-slate-200">
                                {{ $t->transfusionUnits->count() }} unit(s)
                            </td>
                            <td class="px-4 py-4 text-right">
                                <a href="{{ route('hospital.transfusions.show', $t->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 dark:bg-blue-600/20 text-blue-600 dark:text-blue-400 hover:bg-blue-600 hover:text-white font-bold rounded-lg text-xs border border-blue-200 dark:border-blue-500/30 transition">
                                    <span>Manage</span>
                                    <span>&rarr;</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <x-empty-state 
                                    title="No Clinical Transfusions Logged" 
                                    description="Schedule a transfusion for your hospital's approved blood requisitions."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfusions->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                {{ $transfusions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
