@extends('layouts.admin')

@section('content')
<div class="space-y-8 max-w-5xl mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.hospitals.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">&larr; Back to Hospitals</a>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $hospital->name }}</h1>
            <p class="text-xs text-slate-500 font-mono">License: {{ $hospital->license_number ?? 'LIC-REG-99' }} | City: {{ $hospital->city }}</p>
        </div>
        <x-status-badge status="active" label="Verified Hospital" />
    </div>

    <!-- Hospital Metadata Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-2">
            <span class="text-xs font-semibold uppercase text-slate-400">Contact Details</span>
            <p class="text-sm font-semibold text-slate-800">Phone: {{ $hospital->contact_phone }}</p>
            <p class="text-sm font-semibold text-slate-800">Email: {{ $hospital->email ?? 'N/A' }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-2">
            <span class="text-xs font-semibold uppercase text-slate-400">Location</span>
            <p class="text-sm font-semibold text-slate-800">{{ $hospital->address ?? 'Main Medical Boulevard' }}</p>
            <p class="text-sm font-semibold text-slate-800">{{ $hospital->city }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-2">
            <span class="text-xs font-semibold uppercase text-slate-400">Requisitions & Patients</span>
            <p class="text-sm font-semibold text-slate-800">Patients Enrolled: {{ $hospital->patients->count() }}</p>
            <p class="text-sm font-semibold text-slate-800">Total Blood Requests: {{ $hospital->bloodRequests->count() }}</p>
        </div>
    </div>

    <!-- Hospital Blood Requests Table -->
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-200 pb-4">Recent Hospital Requisitions</h2>
        <div class="mt-4 divide-y divide-slate-100">
            @forelse($hospital->bloodRequests as $req)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-sm text-slate-900">Request #{{ $req->id }} — {{ $req->patient_name }}</span>
                        <span class="ml-2 font-mono text-xs text-red-600 font-bold">({{ $req->blood_group }})</span>
                        <p class="text-xs text-slate-500 mt-0.5">Needed: {{ $req->units_needed }} unit(s) | Requested {{ $req->created_at->diffForHumans() }}</p>
                    </div>
                    <x-status-badge :status="$req->status" />
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-4">No requisitions submitted by this hospital yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
