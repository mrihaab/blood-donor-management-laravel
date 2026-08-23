@extends('layouts.hospital')

@section('title', 'Blood Requisitions')
@section('page_title', 'Requisition History')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-gray-900">Hospital Requisition Log</h2>
        <a href="{{ route('hospital.requests.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm transition">
            + New Blood Requisition
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <form method="GET" action="{{ route('hospital.requests.index') }}" class="mb-6 flex gap-4">
            <select name="status" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="dispensed" {{ request('status') === 'dispensed' ? 'selected' : '' }}>Dispensed</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Req ID</th>
                        <th class="px-4 py-3">Patient</th>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Units Needed</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Created At</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($requests as $req)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-bold text-gray-900">#REQ-{{ $req->id }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $req->patient->name ?? $req->patient_name }}</td>
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
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $req->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('hospital.requests.show', $req->id) }}" class="text-blue-600 hover:underline text-xs font-semibold">View Requisition</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 italic">No blood requisitions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection
