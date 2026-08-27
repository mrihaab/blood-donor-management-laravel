@extends('layouts.donor')

@section('title', 'My Blood Requests')
@section('page_title', 'My Blood Requests')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-900">Submitted Blood Requests</h3>
        <a href="{{ route('donor.blood_requests.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">
            + Submit Blood Request
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Patient Name</th>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Units Needed</th>
                        <th class="px-4 py-3">Hospital</th>
                        <th class="px-4 py-3">Urgency</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse(($requests ?? $bloodRequests ?? []) as $req)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $req->patient_name ?? $req->requester_name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-md">
                                    {{ $req->blood_group ?? $req->bloodGroup->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-gray-900">{{ $req->units_needed ?? $req->units ?? 1 }}</td>
                            <td class="px-4 py-3">{{ $req->hospital_name ?? $req->hospital ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ ($req->urgency ?? 'normal') === 'urgent' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($req->urgency ?? 'normal') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $req->status === 'approved' ? 'bg-green-100 text-green-800' : ($req->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 italic">No blood requests submitted.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
