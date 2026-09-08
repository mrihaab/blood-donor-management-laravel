@extends('layouts.hospital')

@section('title', 'Patient Record — ' . $patient->name)

@section('content')
<div class="space-y-6 max-w-5xl">

    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('hospital.dashboard')],
        ['label' => 'Inpatient Directory', 'url' => route('hospital.patients.index')],
        ['label' => $patient->name]
    ]" />

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $patient->name }}</h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Medical Record Number: <span class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ $patient->mrn }}</span></p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('hospital.requests.create') }}?patient_id={{ $patient->id }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow-sm transition inline-flex items-center gap-1.5">
                <span>🩸</span> New Requisition
            </a>
            <a href="{{ route('hospital.patients.edit', $patient->id) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs border border-slate-300 dark:border-slate-700 transition">
                Edit Record
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Demographic Card -->
        <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800 pb-3">Clinical Demographic Profile</h2>
            <div class="grid grid-cols-2 gap-4 text-xs md:text-sm">
                <div>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block uppercase font-bold">Gender</span>
                    <span class="font-bold text-slate-900 dark:text-white capitalize">{{ $patient->gender }}</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block uppercase font-bold">Date of Birth</span>
                    <span class="font-bold text-slate-900 dark:text-white font-mono">
                        {{ is_object($patient->date_of_birth) ? $patient->date_of_birth->format('M d, Y') : ($patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('M d, Y') : 'N/A') }}
                    </span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block uppercase font-bold mb-1">Blood Group</span>
                    <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 text-xs font-black rounded-lg border border-rose-200 dark:border-rose-800 inline-block">
                        🩸 {{ $patient->bloodGroup->name ?? 'Unspecified' }}
                    </span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block uppercase font-bold">Contact Phone</span>
                    <span class="font-bold text-slate-900 dark:text-white font-mono">{{ $patient->contact_number ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Inpatient Location Card -->
            <div class="p-4 bg-slate-50 dark:bg-[#070d1a] rounded-xl border border-slate-200 dark:border-slate-800 space-y-1 mt-4">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                    <span>📍 Bedside Location</span>
                </span>
                <p class="text-xs font-bold text-slate-900 dark:text-white">
                    {{ $patient->ward_name ?? 'General Ward' }}
                    @if($patient->room_number) | Room {{ $patient->room_number }} @endif
                    @if($patient->bed_number) | Bed {{ $patient->bed_number }} @endif
                </p>
            </div>
        </div>

        <!-- Requisition History Card -->
        <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800 pb-3">Requisition History Log</h2>
            <div class="space-y-3">
                @forelse($patient->bloodRequests as $req)
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700/60 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-slate-900 dark:text-white font-mono">#REQ-{{ $req->id }}</span>
                            <span class="text-slate-500 dark:text-slate-400"> &bull; {{ $req->units_needed }} bag(s) {{ $req->blood_group }}</span>
                        </div>
                        <x-status-badge :status="$req->status" />
                    </div>
                @empty
                    <x-empty-state 
                        title="No Requisitions Filed" 
                        description="No blood component requests exist for this inpatient."
                    />
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
