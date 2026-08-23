@extends('layouts.admin')

@section('content')
<div class="space-y-8 max-w-5xl mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.patients.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">&larr; Back to Patient Registry</a>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $patient->name }}</h1>
            <p class="text-xs text-slate-500 font-mono">Medical Record Number (MRN): {{ $patient->mrn }}</p>
        </div>
        <span class="inline-block rounded-lg bg-red-700 px-3 py-1 text-sm font-black text-white shadow-sm">
            {{ $patient->bloodGroup->name ?? 'O+' }}
        </span>
    </div>

    <!-- Patient Demographics & Hospital Assignment Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-2">
            <span class="text-xs font-semibold uppercase text-slate-400">Demographics</span>
            <p class="text-sm font-semibold text-slate-800">Gender: {{ ucfirst($patient->gender ?? 'Unspecified') }}</p>
            <p class="text-sm font-semibold text-slate-800">DOB: {{ $patient->date_of_birth ?? '1995-05-12' }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-2">
            <span class="text-xs font-semibold uppercase text-slate-400">Hospital Assignment</span>
            <p class="text-sm font-semibold text-slate-800">{{ $patient->hospital->name ?? 'General Hospital' }}</p>
            <p class="text-xs text-slate-500 font-mono">City: {{ $patient->hospital->city ?? 'Metropolis' }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-2">
            <span class="text-xs font-semibold uppercase text-slate-400">Requisitions Count</span>
            <p class="text-2xl font-bold text-slate-900">{{ $patient->bloodRequests->count() }}</p>
            <p class="text-xs text-slate-500">Total Blood Requests</p>
        </div>
    </div>

    <!-- Patient Blood Request History -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-200 pb-4">Blood Transfusion & Requisition History</h2>
        <div class="mt-4 divide-y divide-slate-100">
            @forelse($patient->bloodRequests as $req)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-sm text-slate-900">Request #{{ $req->id }} — {{ $req->blood_group }}</span>
                        <p class="text-xs text-slate-500 mt-0.5">Units Needed: {{ $req->units_needed }} | Submitted {{ $req->created_at->diffForHumans() }}</p>
                    </div>
                    <x-status-badge :status="$req->status" />
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-4">No requisitions logged for this patient.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
