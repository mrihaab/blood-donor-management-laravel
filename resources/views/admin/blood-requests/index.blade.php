@extends('layouts.admin')

@section('title', 'Hospital Requisitions')

@section('content')
<div class="space-y-6 pb-12">
    <x-breadcrumbs :items="[['label' => 'Blood Requisitions']]" />

    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Hospital Blood Requisitions & Allocations</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Manage patient requisitions, allocate matching barcode units, and authorize dispensing.</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="p-4 text-xs md:text-sm text-emerald-900 dark:text-emerald-200 rounded-xl bg-emerald-50 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between shadow-sm" role="alert">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span><strong class="font-bold">Success!</strong> {{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 text-xs md:text-sm text-rose-900 dark:text-rose-200 rounded-xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 flex items-center justify-between shadow-sm" role="alert">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span><strong class="font-bold">Insufficient Stock Alert:</strong> {{ session('error') }}</span>
            </div>
            <a href="{{ route('admin.inventory.create') }}" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs transition shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                + Add Stock Now
            </a>
        </div>
    @endif

    <!-- Requisitions Data Table -->
    <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1427] shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100/80 dark:bg-[#111c38] text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
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
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-semibold">
                    @forelse($bloodRequests as $req)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900 dark:text-white">#{{ $req->id }}</td>
                            <td class="px-6 py-4">
                                <span class="font-extrabold text-slate-900 dark:text-white block">{{ $req->patient_name }}</span>
                                @if($req->patient_id)
                                    <a href="{{ route('admin.patients.show', $req->patient_id) }}" class="text-xs text-rose-600 dark:text-rose-400 hover:underline font-mono block mt-0.5">MRN Linked &rarr;</a>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-900 dark:text-slate-100 block">{{ $req->hospital }}</span>
                                <span class="text-xs text-slate-600 dark:text-slate-400 block">{{ $req->city }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block rounded-lg bg-rose-50 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800 px-2.5 py-1 text-xs font-black shadow-sm">
                                    {{ $req->blood_group }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-extrabold text-slate-900 dark:text-white">{{ $req->units_needed }} Bag(s)</td>
                            <td class="px-6 py-4">
                                <x-status-badge :status="$req->status" />
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                    @if($req->status === 'pending')
                                        <form id="form-approve-{{ $req->id }}" action="{{ route('admin.blood_requests.approve', $req->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 px-3.5 py-1.5 text-xs font-extrabold text-white transition shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                                Approve & Allocate
                                            </button>
                                        </form>

                                        <form id="form-reject-{{ $req->id }}" action="{{ route('admin.blood_requests.reject', $req->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="button" @click="$dispatch('open-confirm', { modalId: 'confirm-reject', formId: 'form-reject-{{ $req->id }}' })" class="rounded-xl border border-rose-300 dark:border-rose-800 bg-white dark:bg-slate-900 px-3.5 py-1.5 text-xs font-bold text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/60 transition focus:outline-none focus:ring-2 focus:ring-rose-500">
                                                Reject
                                            </button>
                                        </form>
                                    @elseif($req->status === 'approved')
                                        <form id="form-dispense-{{ $req->id }}" action="{{ route('admin.blood_requests.dispense', $req->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="button" @click="$dispatch('open-confirm', { modalId: 'confirm-dispense', formId: 'form-dispense-{{ $req->id }}' })" class="rounded-xl bg-blue-600 hover:bg-blue-700 px-3.5 py-1.5 text-xs font-extrabold text-white transition shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                Dispense Units
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-600 dark:text-slate-400 font-semibold uppercase">Completed</span>
                                    @endif

                                    <form id="form-delete-{{ $req->id }}" method="POST" action="{{ route('admin.blood_requests.destroy', $req->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="$dispatch('open-confirm', { modalId: 'confirm-delete-{{ $req->id }}', formId: 'form-delete-{{ $req->id }}' })" class="rounded-xl border border-rose-200 dark:border-rose-900 bg-rose-50 dark:bg-rose-950/40 px-2.5 py-1.5 text-xs font-bold text-rose-700 dark:text-rose-300 hover:bg-rose-100 transition focus:outline-none focus:ring-2 focus:ring-rose-500">
                                            Delete
                                        </button>
                                    </form>
                                    <x-confirm-dialog id="confirm-delete-{{ $req->id }}" title="Delete Requisition" message="Are you sure you want to delete requisition #REQ-{{ $req->id }}? This action cannot be undone." confirmText="Delete Requisition" variant="danger" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8">
                                <x-empty-state title="No requisitions found" message="Blood requisition requests from hospitals will appear here." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bloodRequests->hasPages())
            <div class="border-t border-slate-200 dark:border-slate-800 px-6 py-4">
                {{ $bloodRequests->links() }}
            </div>
        @endif
    </div>

    <!-- Modals for Rejection and Dispensing Confirmation -->
    <x-confirm-dialog id="confirm-reject" title="Reject Blood Requisition" message="Are you sure you want to reject this blood requisition request? This action will be logged in audit trails." confirmText="Reject Requisition" variant="danger" />
    <x-confirm-dialog id="confirm-dispense" title="Dispense Allocated Units" message="Are you sure you want to dispense physical blood units for this requisition? This will update unit statuses to dispensed." confirmText="Dispense Units" variant="primary" />
</div>
@endsection
