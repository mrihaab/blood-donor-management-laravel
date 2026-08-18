@extends('layouts.admin')

@section('title', 'Blood Requests')
@section('page_title', 'Blood Requests Management')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Patient & Hospital Blood Requests</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Patient / Requester</th>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Units Needed</th>
                        <th class="px-4 py-3">Hospital / Location</th>
                        <th class="px-4 py-3">Urgency</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($requests as $req)
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
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ ($req->urgency ?? 'normal') === 'urgent' || ($req->urgency ?? '') === 'emergency' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($req->urgency ?? 'normal') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $req->status === 'approved' ? 'bg-green-100 text-green-800' : ($req->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                @if($req->status === 'pending')
                                    <form method="POST" action="{{ route('admin.blood_requests.approve', $req->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.blood_requests.reject', $req->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded">Reject</button>
                                    </form>
                                @endif
                                @if($req->status === 'approved')
                                    <form method="POST" action="{{ route('admin.blood_requests.fulfill', $req->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded">Fulfill</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 italic">No blood requests submitted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
