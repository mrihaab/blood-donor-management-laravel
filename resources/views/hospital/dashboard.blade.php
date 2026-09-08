@extends('layouts.hospital')

@section('title', 'Clinical Operations & Blood Bank Command Center')
@section('page_title', ($hospital->name ?? 'Hospital') . ' — Clinical Command Center')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Emergency Universal Dispatch Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-rose-950/40 to-slate-900 border border-rose-900/40 p-6 shadow-2xl">
        <!-- Glow effects -->
        <div class="absolute -top-24 -right-24 h-48 w-48 rounded-full bg-rose-600/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 h-48 w-48 rounded-full bg-blue-600/10 blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2.5">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                    </span>
                    <span class="text-xs font-black text-rose-400 uppercase tracking-wider bg-rose-950/80 px-2.5 py-0.5 rounded-full border border-rose-800/60">
                        Emergency Request Service Active
                    </span>
                </div>
                <h1 class="text-xl md:text-2xl font-black text-white tracking-tight">
                    Clinical Operations & Blood Bank Command Center
                </h1>
                <p class="text-xs md:text-sm text-slate-400 max-w-2xl">
                    Current blood component allocation, bedside requisition triage, and FEFO inventory synchronization for <strong class="text-slate-200">{{ $hospital->name ?? 'Clinical Hospital Partner' }}</strong>.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('hospital.requests.create', ['urgency' => 'emergency']) }}" class="px-5 py-3 bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 active:scale-95 text-white font-extrabold rounded-xl text-xs md:text-sm shadow-xl shadow-rose-950/60 flex items-center gap-2 border border-rose-500/30 transition">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>+ New Emergency Requisition</span>
                </a>
                
                <a href="{{ route('hospital.patients.index') }}" class="px-4 py-3 bg-slate-800/80 hover:bg-slate-700 active:scale-95 text-slate-200 font-bold rounded-xl text-xs md:text-sm border border-slate-700 transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span>Inpatient Directory</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Stat Metrics Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        <!-- Card 1: Pending Requests -->
        <div class="relative overflow-hidden bg-white dark:bg-[#0b1329] p-6 rounded-2xl border border-amber-200 dark:border-amber-900/40 shadow-sm dark:shadow-xl group hover:border-amber-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase text-amber-800 dark:text-amber-400 tracking-wider bg-amber-50 dark:bg-amber-950/60 px-2.5 py-1 rounded-lg border border-amber-200 dark:border-amber-800/50">
                    Pending Admin Triage
                </span>
                <div class="h-9 w-9 rounded-xl bg-amber-100 dark:bg-amber-950/80 border border-amber-200 dark:border-amber-800/50 flex items-center justify-center text-amber-700 dark:text-amber-400 font-black text-sm">
                    ⏳
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <div>
                    <div class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white tracking-tight">{{ $pendingCount }}</div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Requisitions awaiting blood bank approval</p>
                </div>
                <!-- Sparkline SVG -->
                <div class="w-16 h-8 text-amber-500/80">
                    <svg viewBox="0 0 50 20" class="w-full h-full fill-none stroke-current stroke-2">
                        <path d="M0 15 Q 12 5, 25 12 T 50 4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px]">
                <span class="text-amber-700 dark:text-amber-400 font-bold">● Requires FEFO Matching</span>
                <a href="{{ route('hospital.requests.index') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white font-semibold flex items-center gap-1">
                    View list &rarr;
                </a>
            </div>
        </div>

        <!-- Card 2: Approved & Allocated -->
        <div class="relative overflow-hidden bg-white dark:bg-[#0b1329] p-6 rounded-2xl border border-blue-200 dark:border-blue-900/40 shadow-sm dark:shadow-xl group hover:border-blue-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase text-blue-800 dark:text-blue-400 tracking-wider bg-blue-50 dark:bg-blue-950/60 px-2.5 py-1 rounded-lg border border-blue-200 dark:border-blue-800/50">
                    Allocated & In-Transit
                </span>
                <div class="h-9 w-9 rounded-xl bg-blue-100 dark:bg-blue-950/80 border border-blue-200 dark:border-blue-800/50 flex items-center justify-center text-blue-700 dark:text-blue-400 font-black text-sm">
                    🧪
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <div>
                    <div class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white tracking-tight">{{ $approvedCount }}</div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Cross-matched & reserved for bedside</p>
                </div>
                <!-- Sparkline SVG -->
                <div class="w-16 h-8 text-blue-500/80">
                    <svg viewBox="0 0 50 20" class="w-full h-full fill-none stroke-current stroke-2">
                        <path d="M0 12 Q 15 18, 30 6 T 50 2"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px]">
                <span class="text-blue-700 dark:text-blue-400 font-bold">● Cold-Chain Secure</span>
                <a href="{{ route('hospital.requests.index') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white font-semibold flex items-center gap-1">
                    Track &rarr;
                </a>
            </div>
        </div>

        <!-- Card 3: Dispensed & Completed -->
        <div class="relative overflow-hidden bg-white dark:bg-[#0b1329] p-6 rounded-2xl border border-emerald-200 dark:border-emerald-900/40 shadow-sm dark:shadow-xl group hover:border-emerald-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase text-emerald-800 dark:text-emerald-400 tracking-wider bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-1 rounded-lg border border-emerald-200 dark:border-emerald-800/50">
                    Dispensed & Fulfilled
                </span>
                <div class="h-9 w-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-800/50 flex items-center justify-center text-emerald-700 dark:text-emerald-400 font-black text-sm">
                    ✅
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <div>
                    <div class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white tracking-tight">{{ $dispensedCount }}</div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Successfully transfused to patients</p>
                </div>
                <!-- Sparkline SVG -->
                <div class="w-16 h-8 text-emerald-500/80">
                    <svg viewBox="0 0 50 20" class="w-full h-full fill-none stroke-current stroke-2">
                        <path d="M0 18 Q 10 10, 25 15 T 50 2"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px]">
                <span class="text-emerald-700 dark:text-emerald-400 font-bold">● Transfusion Logged</span>
                <a href="{{ route('hospital.requests.index') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white font-semibold flex items-center gap-1">
                    History &rarr;
                </a>
            </div>
        </div>

    </div>

    <!-- Recent Requisitions Table Card -->
    <div class="bg-white dark:bg-[#0b1329] rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-sm dark:shadow-2xl p-5 md:p-6 overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800/80">
            <div>
                <h2 class="text-base font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span>Clinical Requisition Stream</span>
                    <span class="text-[10px] font-extrabold bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800 px-2 py-0.5 rounded-full uppercase">Operational Feed</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Recent hospital blood requests sorted by urgency and order timestamp</p>
            </div>
            <a href="{{ route('hospital.requests.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-bold rounded-xl text-xs border border-slate-200 dark:border-slate-800 transition flex items-center gap-1.5 self-start sm:self-auto">
                <span>View Full Log</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs md:text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/90 text-slate-700 dark:text-slate-400 uppercase text-[11px] font-extrabold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 rounded-l-xl">Req #</th>
                        <th scope="col" class="px-4 py-3.5">Patient Details</th>
                        <th scope="col" class="px-4 py-3.5">Blood Group</th>
                        <th scope="col" class="px-4 py-3.5">Units Required</th>
                        <th scope="col" class="px-4 py-3.5">Urgency</th>
                        <th scope="col" class="px-4 py-3.5">Status</th>
                        <th scope="col" class="px-4 py-3.5 text-right rounded-r-xl">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                    @forelse($recentRequests as $req)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/50 transition">
                            <td class="px-4 py-4 font-black text-blue-600 dark:text-blue-400">
                                #REQ-{{ $req->id }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-900 dark:text-white text-xs md:text-sm">
                                    {{ $req->patient->name ?? $req->patient_name }}
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1.5">
                                    <span>📍</span>
                                    <span>
                                        {{ $req->ward_name ? $req->ward_name : 'General Ward' }}
                                        @if($req->bed_number)
                                            • Bed {{ $req->bed_number }}
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black bg-rose-50 dark:bg-rose-950/80 text-rose-800 dark:text-rose-400 border border-rose-200 dark:border-rose-800/80 shadow-xs">
                                    🩸 {{ $req->blood_group }}
                                </span>
                            </td>
                            <td class="px-4 py-4 font-extrabold text-slate-800 dark:text-slate-200">
                                {{ $req->units_needed }} {{ Str::plural('Bag', $req->units_needed) }}
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-normal block">({{ $req->units_needed * 450 }} mL)</span>
                            </td>
                            <td class="px-4 py-4">
                                @if(strtolower($req->urgency_level ?? '') === 'emergency' || strtolower($req->urgency_level ?? '') === 'stat')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-400 border border-rose-300 dark:border-rose-700">
                                        🚨 STAT Emergency
                                    </span>
                                @elseif(strtolower($req->urgency_level ?? '') === 'urgent' || strtolower($req->urgency_level ?? '') === 'high')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-400 border border-amber-300 dark:border-amber-700">
                                        ⚡ High Priority
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700">
                                        📋 Routine Schedule
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <x-status-badge :status="$req->status" />
                            </td>
                            <td class="px-4 py-4 text-right">
                                <a href="{{ route('hospital.requests.show', $req->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 dark:bg-blue-600/20 text-blue-600 dark:text-blue-400 hover:bg-blue-600 hover:text-white font-bold rounded-xl text-xs border border-blue-200 dark:border-blue-500/30 transition shadow-xs">
                                    <span>Details</span>
                                    <span>&rarr;</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <x-empty-state 
                                    title="No Active Blood Requisitions" 
                                    description="Your hospital has no recent blood component requests."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- FEFO & Cold-Chain Protocol Status Footer Bar -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-semibold text-slate-600 dark:text-slate-400">
        <div class="p-4 bg-white dark:bg-[#0b1329] border border-slate-200 dark:border-slate-800 rounded-xl flex items-center gap-3">
            <span class="text-xl">🛡️</span>
            <div>
                <p class="text-slate-900 dark:text-slate-200 font-bold">FEFO Protocol Active</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Automatic first-expiring-first-out unit reservation.</p>
            </div>
        </div>

        <div class="p-4 bg-white dark:bg-[#0b1329] border border-slate-200 dark:border-slate-800 rounded-xl flex items-center gap-3">
            <span class="text-xl">❄️</span>
            <div>
                <p class="text-slate-900 dark:text-slate-200 font-bold">Cold-Chain Storage Safeguard</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Temperature monitoring across all central vaults.</p>
            </div>
        </div>

        <div class="p-4 bg-white dark:bg-[#0b1329] border border-slate-200 dark:border-slate-800 rounded-xl flex items-center gap-3">
            <span class="text-xl">⚡</span>
            <div>
                <p class="text-slate-900 dark:text-slate-200 font-bold">Clinical Operations Line</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Direct dispatch hotline for STAT emergency blood orders.</p>
            </div>
        </div>
    </div>

</div>
@endsection
