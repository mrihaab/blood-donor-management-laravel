@extends('layouts.admin')

@section('title', 'Emergency Requisition Queue')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Emergency Operations & Priority Queue</h1>
            <p class="text-xs text-slate-500 mt-1">Priority-ranked clinical requisitions. Sorted server-side by Urgency & Required-By timeframe.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.emergency_requests.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Urgency Priority</label>
                <select name="urgency" onchange="this.form.submit()" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="">All Priorities</option>
                    <option value="emergency" {{ request('urgency') === 'emergency' ? 'selected' : '' }}>🚨 Emergency</option>
                    <option value="urgent" {{ request('urgency') === 'urgent' ? 'selected' : '' }}>⚡ Urgent</option>
                    <option value="routine" {{ request('urgency') === 'routine' ? 'selected' : '' }}>📋 Routine</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Blood Group</label>
                <select name="blood_group" onchange="this.form.submit()" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="">All Blood Groups</option>
                    @foreach($bloodGroups as $group)
                        <option value="{{ $group->name }}" {{ request('blood_group') === $group->name ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="dispensed" {{ request('status') === 'dispensed' ? 'selected' : '' }}>Dispensed</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Hospital</label>
                <select name="hospital_id" onchange="this.form.submit()" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="">All Hospitals</option>
                    @foreach($hospitals as $hosp)
                        <option value="{{ $hosp->id }}" {{ request('hospital_id') == $hosp->id ? 'selected' : '' }}>{{ $hosp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <a href="{{ route('admin.emergency_requests.index') }}" class="w-full text-center px-4 py-2 bg-slate-100 text-slate-700 font-semibold rounded-lg text-sm hover:bg-slate-200 transition">Reset Filters</a>
            </div>
        </form>
    </div>

    <!-- Priority Queue Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Priority</th>
                        <th class="px-4 py-3">Req #</th>
                        <th class="px-4 py-3">Hospital</th>
                        <th class="px-4 py-3">Patient</th>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Units</th>
                        <th class="px-4 py-3">Required By</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50/50 {{ $req->urgency_level === 'emergency' ? 'bg-red-50/30' : '' }}">
                            <td class="px-4 py-3">
                                @if($req->urgency_level === 'emergency')
                                    <span class="px-2.5 py-1 bg-red-600 text-white text-xs font-extrabold rounded-md uppercase tracking-wider inline-flex items-center gap-1">
                                        🚨 Emergency
                                    </span>
                                @elseif($req->urgency_level === 'urgent')
                                    <span class="px-2.5 py-1 bg-amber-500 text-white text-xs font-bold rounded-md uppercase tracking-wider inline-flex items-center gap-1">
                                        ⚡ Urgent
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-slate-200 text-slate-800 text-xs font-semibold rounded-md uppercase tracking-wider inline-flex items-center gap-1">
                                        📋 Routine
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-900">#REQ-{{ $req->id }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $req->hospitalEntity->name ?? $req->hospital }}</td>
                            <td class="px-4 py-3 text-slate-900">{{ $req->patient->name ?? $req->patient_name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-md">
                                    {{ $req->blood_group }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold">{{ $req->units_needed }} bag(s)</td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                {{ $req->required_by ? $req->required_by->format('M d, Y H:i') : 'ASAP' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $req->status === 'approved' ? 'bg-blue-100 text-blue-800' : ($req->status === 'dispensed' ? 'bg-green-100 text-green-800' : ($req->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <form method="POST" action="{{ route('admin.blood_requests.notify_donors', $req->id) }}" class="inline">
                                     @csrf
                                     <button type="submit" title="Dispatch In-App, Emergency Email, and WhatsApp/SMS alerts to eligible donors" class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded transition inline-flex items-center gap-1">
                                         📲 Broadcast Alert (App + Email + WhatsApp)
                                     </button>
                                 </form>

                                 @if($req->status === 'pending')
                                     <form method="POST" action="{{ route('admin.blood_requests.approve', $req->id) }}" class="inline">
                                         @csrf
                                         <button type="submit" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded transition">
                                             Approve & FEFO Allocate
                                         </button>
                                     </form>
                                 @elseif($req->status === 'approved')
                                     <form method="POST" action="{{ route('admin.blood_requests.dispense', $req->id) }}" class="inline">
                                         @csrf
                                         <button type="submit" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded transition">
                                             Dispense Stock
                                         </button>
                                     </form>
                                 @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-slate-500 italic">No requisitions currently in priority queue.</td>
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
