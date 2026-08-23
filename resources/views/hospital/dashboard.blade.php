@extends('layouts.hospital')

@section('title', 'Hospital Dashboard')
@section('page_title', $hospital->name . ' - Clinical Operations')

@section('content')
<div class="space-y-8">
    <!-- Top Action Banner -->
    <div class="flex items-center justify-between bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Hospital Blood Requisition Center</h2>
            <p class="text-xs text-gray-500 mt-1">Manage blood requests and patient records for {{ $hospital->name }}</p>
        </div>
        <a href="{{ route('hospital.requests.create') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition shadow-sm">
            + New Blood Requisition
        </a>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-bold uppercase text-amber-600 tracking-wider">Pending Approval</span>
            <div class="text-3xl font-extrabold text-gray-900 mt-2">{{ $pendingCount }}</div>
            <span class="text-xs text-gray-500 mt-1 block">Awaiting admin review</span>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-bold uppercase text-blue-600 tracking-wider">Approved & Allocated</span>
            <div class="text-3xl font-extrabold text-gray-900 mt-2">{{ $approvedCount }}</div>
            <span class="text-xs text-gray-500 mt-1 block">Allocated via FEFO</span>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs font-bold uppercase text-green-600 tracking-wider">Dispensed / Completed</span>
            <div class="text-3xl font-extrabold text-gray-900 mt-2">{{ $dispensedCount }}</div>
            <span class="text-xs text-gray-500 mt-1 block">Fulfilled requisitions</span>
        </div>
    </div>

    <!-- Recent Requisitions Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900">Recent Hospital Requisitions</h3>
            <a href="{{ route('hospital.requests.index') }}" class="text-xs text-blue-600 font-semibold hover:underline">View All Requisitions &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Req #</th>
                        <th class="px-4 py-3">Patient</th>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Units Needed</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentRequests as $req)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-semibold text-gray-900">#REQ-{{ $req->id }}</td>
                            <td class="px-4 py-3 text-gray-900 font-medium">{{ $req->patient->name ?? $req->patient_name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-md">
                                    {{ $req->blood_group }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold">{{ $req->units_needed }} bag(s)</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $req->status === 'approved' ? 'bg-blue-100 text-blue-800' : ($req->status === 'dispensed' ? 'bg-green-100 text-green-800' : ($req->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('hospital.requests.show', $req->id) }}" class="text-blue-600 hover:underline text-xs font-semibold">Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 italic">No requisitions submitted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
