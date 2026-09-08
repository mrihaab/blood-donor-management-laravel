@extends('layouts.admin')

@section('title', 'Operations Dashboard')

@section('content')
<div class="space-y-8 pb-12">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Blood Bank Operations Center</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-0.5">Current inventory overview, critical requisition triage, and active donor statistics.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.donations.create') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-extrabold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-rose-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Intake Donation</span>
            </a>
            <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs border border-slate-300 dark:border-slate-700 transition">
                View Barcode Bags
            </a>
        </div>
    </div>

    @php
        $oNegBagsCount = \App\Models\BloodUnit::where('status', 'available')
            ->where(function($q) {
                $q->where('blood_group', 'O-')->orWhere('blood_group_id', 8);
            })
            ->where('expiry_date', '>=', now()->format('Y-m-d'))
            ->count();
    @endphp

    <!-- Enterprise Clinical Blood Requisition Triage Stream -->
    <div x-data="{ openCommandCenter: true }" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1427] p-6 shadow-sm space-y-6 transition-colors">
        <!-- Header Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <span>Clinical Triage Command Center</span>
                        <x-status-badge status="emergency" label="Live Triage" />
                    </h2>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">FEFO stock allocation, Universal O- fallback, and emergency donor dispatching.</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button @click="openCommandCenter = !openCommandCenter" type="button" class="rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    <span x-text="openCommandCenter ? 'Hide Requisitions ▲' : 'Show Requisitions ▼'">Hide Requisitions ▲</span>
                </button>
            </div>
        </div>

        <div x-show="openCommandCenter" class="space-y-6">
            @if(isset($activeEmergencyRequests) && $activeEmergencyRequests->count() > 0)
                <div class="space-y-5">
                    @foreach($activeEmergencyRequests as $req)
                        @php
                            $bgName = $req->blood_group ?? $req->bloodGroup->name ?? 'Unspecified';
                            $fefoUnit = app(\App\Services\BloodRequestService::class)->getRecommendedFefoUnit($req);
                            $isExactGroup = $fefoUnit ? (strtolower(trim($fefoUnit->blood_group ?? $fefoUnit->bloodGroup->name ?? '')) === strtolower(trim($bgName))) : false;
                        @endphp

                        <!-- Emergency STAT Requisition Box -->
                        <div class="rounded-2xl border-2 border-rose-500/80 dark:border-rose-800 bg-rose-50/30 dark:bg-[#080d1a] p-5 md:p-6 shadow-sm space-y-4">
                            <!-- Header Row: STAT Emergency Pill + Req ID + Units Needed -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-rose-200 dark:border-rose-900/60 pb-3">
                                <div class="flex items-center space-x-2">
                                    <x-status-badge status="emergency" label="STAT Emergency Requisition" />
                                    <span class="font-mono font-bold text-slate-900 dark:text-white text-xs md:text-sm bg-white dark:bg-slate-900 px-2.5 py-1 rounded-md border border-rose-300 dark:border-rose-800">
                                        #REQ-{{ $req->id }}
                                    </span>
                                </div>
                                <span class="font-black text-rose-700 dark:text-rose-300 text-xs md:text-sm bg-rose-100 dark:bg-rose-950/80 px-3 py-1 rounded-full border border-rose-300 dark:border-rose-800">
                                    {{ $req->units_needed }} Bag(s) of {{ $bgName }} Needed
                                </span>
                            </div>

                            <!-- Details Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs md:text-sm text-slate-700 dark:text-slate-300">
                                <div class="space-y-1.5">
                                    <p><span class="font-bold text-slate-900 dark:text-white">Hospital Partner:</span> {{ $req->hospitalEntity->name ?? $req->hospital }} ({{ $req->city ?? 'Central' }})</p>
                                    <p><span class="font-bold text-slate-900 dark:text-white">Patient Name:</span> <strong class="font-extrabold text-slate-900 dark:text-white text-sm md:text-base">{{ $req->patient->name ?? $req->patient_name }}</strong></p>
                                    <p><span class="font-bold text-slate-900 dark:text-white">Bedside Location:</span> <span class="font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">{{ $req->ward_name ?? 'General Ward' }} {{ $req->bed_number ? '• Bed '.$req->bed_number : '' }}</span></p>
                                    <p><span class="font-bold text-slate-900 dark:text-white">Family Attendant:</span> <span class="font-bold text-blue-800 dark:text-blue-300 bg-blue-50 dark:bg-blue-950 px-2 py-0.5 rounded border border-blue-200 dark:border-blue-800">{{ $req->attendant_name ?? ($req->patient->contact_number ? 'Patient Contact: '.$req->patient->contact_number : 'Attendant Info Pending') }} {{ $req->attendant_phone ? '('.$req->attendant_phone.')' : '' }}</span></p>
                                </div>

                                <!-- Candidate Unit Panel -->
                                <div class="p-3.5 rounded-xl border space-y-1.5 text-xs shadow-sm {{ $fefoUnit ? 'bg-emerald-50/80 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-300' : 'bg-rose-50/80 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-300' }}">
                                    @if($fefoUnit)
                                        @php
                                            $fefoGroup = $fefoUnit->blood_group ?? $fefoUnit->bloodGroup->name ?? 'O-';
                                        @endphp

                                        <p class="font-bold text-slate-900 dark:text-white text-xs uppercase flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Current FEFO Candidate:</span>
                                        </p>
                                        <p class="font-mono font-extrabold text-slate-900 dark:text-white">Barcode: #{{ $fefoUnit->unit_number }} (Group: <strong class="text-rose-600 dark:text-rose-400">{{ $fefoGroup }}</strong>)</p>
                                        <p class="font-bold text-slate-700 dark:text-slate-300">Storage: {{ $fefoUnit->storage_location ?? 'Main Refrigerator - Shelf A' }}</p>
                                        <p class="text-slate-600 dark:text-slate-400 text-[11px]">Expiry: {{ \Carbon\Carbon::parse($fefoUnit->expiry_date)->format('M d, Y') }} ({{ \Carbon\Carbon::parse($fefoUnit->expiry_date)->diffForHumans() }})</p>
                                    @else
                                        <p class="font-bold text-rose-700 dark:text-rose-400 text-xs uppercase flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>Exact Stock Depleted In Vault:</span>
                                        </p>
                                        <p class="text-slate-700 dark:text-slate-300">Central vault does not have {{ $bgName }} blood bags available right now.</p>
                                        @if($oNegBagsCount > 0)
                                            <div class="p-2 bg-emerald-100 dark:bg-emerald-950/80 rounded-lg border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-300 font-bold text-[11px]">
                                                Universal O- Fallback Active: <strong>{{ $oNegBagsCount }} Bags Available</strong> in Main Refrigerator
                                            </div>
                                        @else
                                            <div class="p-2 bg-rose-100 dark:bg-rose-950/80 rounded-lg border border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-300 font-bold text-[11px]">
                                                Both {{ $bgName }} and Universal O- Stocks are DEPLETED!
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- Sleek Single-Line Action Toolbar -->
                            <div class="pt-3 flex flex-wrap items-center justify-between gap-3 border-t border-rose-200 dark:border-rose-900/60">
                                <span class="text-xs text-slate-600 dark:text-slate-400 font-medium">
                                    Select triage action to issue unit or dispatch donor call:
                                </span>
                                
                                <div class="flex items-center gap-2 whitespace-nowrap">
                                    @if($fefoUnit)
                                        <form method="POST" action="{{ route('admin.blood_requests.instant_dispense', $req->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                                <span>Issue FEFO Unit (#{{ $fefoUnit->unit_number }}) &rarr;</span>
                                            </button>
                                        </form>
                                    @else
                                        @if($oNegBagsCount > 0)
                                            <form method="POST" action="{{ route('admin.blood_requests.dispense_universal_fallback', $req->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-amber-500">
                                                    <span>Issue O- Fallback ({{ $oNegBagsCount }})</span>
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('admin.blood_requests.notify_donors', $req->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-rose-500">
                                                <span>Cascade Donors</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state title="No Urgent Triage Requisitions" message="All clinical blood component orders are fulfilled or up-to-date." />
            @endif
        </div>
    </div>
</div>
@endsection
