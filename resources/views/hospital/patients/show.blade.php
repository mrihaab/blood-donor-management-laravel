@extends('layouts.hospital')

@section('title', 'Patient Details')
@section('page_title', $patient->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $patient->name }}</h2>
            <p class="text-xs text-gray-500 mt-1">MRN: <span class="font-bold text-gray-800">{{ $patient->mrn }}</span></p>
        </div>
        <a href="{{ route('hospital.patients.edit', $patient->id) }}" class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 font-semibold rounded-lg text-sm transition">
            Edit Patient Info
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold uppercase text-gray-500 tracking-wider">Clinical Demographic Profile</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-xs text-gray-500 block">Gender</span>
                    <span class="font-semibold text-gray-900 capitalize">{{ $patient->gender }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Date of Birth</span>
                    <span class="font-semibold text-gray-900">{{ $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Blood Group</span>
                    <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-md inline-block mt-0.5">
                        {{ $patient->bloodGroup->name ?? 'Unspecified' }}
                    </span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Contact Number</span>
                    <span class="font-semibold text-gray-900">{{ $patient->contact_number ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-sm font-bold uppercase text-gray-500 tracking-wider mb-4">Patient Requisition History</h3>
            <div class="space-y-3">
                @forelse($patient->bloodRequests as $req)
                    <div class="p-3 bg-gray-50 rounded-lg flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-gray-900">#REQ-{{ $req->id }}</span> &bull; {{ $req->units_needed }} bag(s) {{ $req->blood_group }}
                        </div>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $req->status === 'approved' ? 'bg-blue-100 text-blue-800' : ($req->status === 'dispensed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($req->status) }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-gray-500 italic">No blood requisitions recorded for this patient.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
