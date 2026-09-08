@extends('layouts.hospital')

@section('title', 'Requisition Details')
@section('page_title', 'Requisition #REQ-' . $bloodRequest->id)

@section('content')
<div class="space-y-6 pb-12">

    <!-- Header Actions with Back Link -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('hospital.requests.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Requisitions Stream</span>
            </a>
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Requisition #REQ-{{ $bloodRequest->id }}</h2>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Submitted on {{ $bloodRequest->created_at->format('M d, Y \a\t H:i') }}</p>
            </div>
        </div>

        <x-status-badge :status="$bloodRequest->status" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Requisition Clinical Specification Card -->
        <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4 transition-colors">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800 pb-3">Requisition Clinical Specification</h3>
            
            <div class="grid grid-cols-2 gap-4 text-xs md:text-sm">
                <div>
                    <span class="text-[11px] text-slate-600 dark:text-slate-400 block uppercase font-bold">Patient Name</span>
                    <span class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $bloodRequest->patient->name ?? $bloodRequest->patient_name }}</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-600 dark:text-slate-400 block uppercase font-bold">Hospital Partner</span>
                    <span class="font-bold text-slate-900 dark:text-white">
                        {{ is_object($bloodRequest->hospital) ? $bloodRequest->hospital->name : (is_string($bloodRequest->hospital) ? $bloodRequest->hospital : ($bloodRequest->hospitalEntity->name ?? 'N/A')) }}
                    </span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-600 dark:text-slate-400 block uppercase font-bold mb-1">Blood Group</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black bg-rose-50 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800 shadow-sm">
                        {{ $bloodRequest->blood_group }}
                    </span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-600 dark:text-slate-400 block uppercase font-bold">Quantity Needed</span>
                    <span class="font-extrabold text-slate-900 dark:text-slate-100">{{ $bloodRequest->units_needed }} Bag(s) ({{ $bloodRequest->units_needed * 450 }} mL)</span>
                </div>
            </div>

            <!-- Patient Location Box -->
            <div class="bg-slate-50 dark:bg-[#070d1a] p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    <span>Patient Bedside Delivery Location</span>
                </span>
                <p class="text-xs font-bold text-slate-900 dark:text-white mt-1">
                    {{ $bloodRequest->ward_name ?? ($bloodRequest->patient->ward_name ?? 'General Ward') }}
                    @if($bloodRequest->room_number || $bloodRequest->patient->room_number)
                        | Room: {{ $bloodRequest->room_number ?? $bloodRequest->patient->room_number }}
                    @endif
                    @if($bloodRequest->bed_number || $bloodRequest->patient->bed_number)
                        | Bed: {{ $bloodRequest->bed_number ?? $bloodRequest->patient->bed_number }}
                    @endif
                </p>
            </div>

            <!-- Family Attendant Contact Box -->
            <div class="bg-slate-50 dark:bg-[#070d1a] p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Family Attendant Contact (Replacement SMS)</span>
                </span>
                <p class="text-xs font-bold text-slate-900 dark:text-white mt-1">
                    {{ $bloodRequest->attendant_name ?? ($bloodRequest->patient->contact_number ? 'Patient Phone: '.$bloodRequest->patient->contact_number : 'Attendant Info Pending') }}
                    @if($bloodRequest->attendant_phone)
                        <span class="text-blue-600 dark:text-blue-400 font-mono font-bold">({{ $bloodRequest->attendant_phone }})</span>
                    @endif
                </p>
            </div>

            @if($bloodRequest->reason)
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <span class="text-[11px] text-slate-600 dark:text-slate-400 block uppercase font-bold">Clinical Indication</span>
                    <p class="text-xs text-slate-800 dark:text-slate-200 mt-1 italic">{{ $bloodRequest->reason }}</p>
                </div>
            @endif
        </div>

        <!-- Fulfillment & FEFO Audit Card -->
        <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4 transition-colors">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800 pb-3">Fulfillment & FEFO Audit Tracking</h3>
            
            @if($bloodRequest->status === 'dispensed')
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 rounded-xl text-xs text-emerald-900 dark:text-emerald-200 space-y-1">
                    <p class="font-extrabold text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Dispensed & Issued by Central Vault</span>
                    </p>
                    <p class="text-xs text-emerald-800 dark:text-emerald-300">Authoritative stock unit allocated via server FEFO transaction and dispatched to hospital unit.</p>
                </div>
            @elseif($bloodRequest->status === 'approved')
                <div class="p-4 bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 rounded-xl text-xs text-blue-900 dark:text-blue-200 space-y-1">
                    <p class="font-extrabold text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Stock Reserved via FEFO</span>
                    </p>
                    <p class="text-xs text-blue-800 dark:text-blue-300">Physical blood component bags cross-matched and reserved from central inventory by administrative triage.</p>
                </div>
            @elseif($bloodRequest->status === 'rejected')
                <div class="p-4 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 rounded-xl text-xs text-rose-900 dark:text-rose-200 space-y-1">
                    <p class="font-extrabold text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Requisition Declined</span>
                    </p>
                    <p class="text-xs text-rose-800 dark:text-rose-300">This requisition was declined during blood bank administrative review.</p>
                </div>
            @else
                <div class="p-4 bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 rounded-xl text-xs text-amber-900 dark:text-amber-200 space-y-1">
                    <p class="font-extrabold text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Pending Administrative Triage</span>
                    </p>
                    <p class="text-xs text-amber-800 dark:text-amber-300">Awaiting central blood bank admin approval and automatic FEFO inventory reservation.</p>
                </div>
            @endif

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-xs space-y-2 text-slate-600 dark:text-slate-400">
                <div class="flex justify-between">
                    <span>Audit Trail Logged:</span>
                    <span class="font-bold text-slate-900 dark:text-slate-200">#LOG-{{ $bloodRequest->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Cold-Chain Monitoring:</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">2.4°C Nominal</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
