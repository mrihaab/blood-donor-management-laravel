@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-4">
        @if(auth()->user()->role === 'admin')
            All Blood Requests
        @else
            Your Blood Requests
        @endif
    </h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-2 rounded mb-4">{{ session('error') }}</div>
    @endif

    @if($requests->isEmpty())
        <p class="text-gray-600">
            @if(auth()->user()->role === 'admin')
                No blood requests found.
            @else
                You have not submitted any blood requests yet.
            @endif
        </p>
    @else
    <form method="GET" class="mb-6 flex flex-wrap gap-4 items-center">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search city or reason"
        class="border rounded px-3 py-2 w-64">

    <select name="blood_group" class="border rounded px-3 py-2">
        <option value="">All Blood Groups</option>
        @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $group)
            <option value="{{ $group }}" {{ request('blood_group') == $group ? 'selected' : '' }}>{{ $group }}</option>
        @endforeach
    </select>

    <select name="status" class="border rounded px-3 py-2">
        <option value="">All Statuses</option>
        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
    </select>

    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Filter
    </button>
</form>

        <table class="w-full border table-auto">
            <thead class="bg-gray-100">
                <tr>
                    @if(auth()->user()->role === 'admin')
                        <th class="px-4 py-2">User</th>
                    @endif
                    <th class="px-4 py-2">Blood Group</th>
                    <th class="px-4 py-2">City</th>
                    <th class="px-4 py-2">Needed Date</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">
                        @if(auth()->user()->role === 'admin') Actions @else Submitted @endif
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $req)
                <tr class="border-t">
                    @if(auth()->user()->role === 'admin')
                        <td class="px-4 py-2">{{ $req->user->name ?? 'N/A' }}</td>
                    @endif
                    <td class="px-4 py-2">{{ $req->blood_group }}</td>
                    <td class="px-4 py-2">{{ $req->city }}</td>
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($req->needed_date)->format('d M Y') }}</td>
                    <td class="px-4 py-2 capitalize">{{ $req->status }}</td>
                    <td class="px-4 py-2">
                        @if(auth()->user()->role === 'admin')
                            @if($req->status === 'pending')
                                <div class="flex gap-2">
                                    <form action="{{ route('admin.blood_requests.approve', $req->id) }}" method="POST">
                                        @csrf
                                        <button class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.blood_requests.reject', $req->id) }}" method="POST">
                                        @csrf
                                        <button class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Reject</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-sm text-gray-500">No action</span>
                            @endif
                        @else
                            <span class="text-sm text-gray-500">{{ $req->created_at->diffForHumans() }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection
