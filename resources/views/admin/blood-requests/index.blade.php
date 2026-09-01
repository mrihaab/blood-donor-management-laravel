@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <x-breadcrumbs :items="[['label' => 'Blood Requests']]" />

    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Hospital Blood Requisitions & Allocations</h1>
            <p class="text-sm text-slate-500">Manage patient requisitions, allocate matching barcode units, and authorize dispensing.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200">
            <span class="font-bold">Success!</span> {{ session('success') }}
        </div>
    @endif

    <!-- Requisitions Data Table -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 font-semibold text-slate-600">
                <tr>
                    <th class="px-6 py-3.5">Req ID</th>
                    <th class="px-6 py-3.5">Patient / MRN</th>
                    <th class="px-6 py-3.5">Hospital</th>
                    <th class="px-6 py-3.5">Blood Group</th>
                    <th class="px-6 py-3.5">Units</th>
                    <th class="px-6 py-3.5">Pipeline Status</th>
                    <th class="px-6 py-3.5 text-right">Workflow Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-medium text-slate-800">
                @forelse($bloodRequests as $req)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 font-mono font-bold text-slate-900">#{{ $req->id }}</td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-900 block">{{ $req->patient_name }}</span>
                            @if($req->patient_id)
                                <a href="{{ route('admin.patients.show', $req->patient_id) }}" class="text-xs text-red-600 hover:underline font-mono block">MRN Linked &rarr;</a>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-slate-700 block">{{ $req->hospital }}</span>
                            <span class="text-xs text-slate-400 block">{{ $req->city }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block rounded-md bg-red-700 px-2 py-0.5 text-xs font-bold text-white">
                                {{ $req->blood_group }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $req->units_needed }}</td>
                        <td class="px-6 py-4">
                            <x-status-badge :status="$req->status" />
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            @if($req->status === 'pending')
                                <form id="form-approve-{{ $req->id }}" action="{{ route('admin.blood_requests.approve', $req->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition">
                                        Approve & Allocate
                                    </button>
                                </form>

                                <form id="form-reject-{{ $req->id }}" action="{{ route('admin.blood_requests.reject', $req->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="button" @click="$dispatch('open-confirm', { modalId: 'confirm-reject', formId: 'form-reject-{{ $req->id }}' })" class="rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">
                                        Reject
                                    </button>
                                </form>
                            @elseif($req->status === 'approved')
                                <form id="form-dispense-{{ $req->id }}" action="{{ route('admin.blood_requests.dispense', $req->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="button" @click="$dispatch('open-confirm', { modalId: 'confirm-dispense', formId: 'form-dispense-{{ $req->id }}' })" class="rounded-lg bg-purple-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-purple-700 transition">
                                        Dispense Units
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400 font-semibold uppercase">Completed</span>
                            @endif

                            <form method="POST" action="{{ route('admin.blood_requests.destroy', $req->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete blood requisition #REQ-{{ $req->id }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-100 transition">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8">
                            <x-empty-state title="No requisitions found" description="Blood requisition requests from hospitals will appear here." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($bloodRequests->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $bloodRequests->links() }}
            </div>
        @endif
    </div>

    <!-- Modals for Rejection and Dispensing Confirmation -->
    <x-confirm-dialog id="confirm-reject" title="Reject Blood Requisition" message="Are you sure you want to reject this blood requisition request? This action will be logged in audit trails." confirmText="Reject Requisition" variant="danger" />

    <x-confirm-dialog id="confirm-dispense" title="Dispense Allocated Units" message="Are you sure you want to dispense physical blood units for this requisition? This will update unit statuses to dispensed." confirmText="Dispense Units" variant="primary" />
</div>
@endsection
