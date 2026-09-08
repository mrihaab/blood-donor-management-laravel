@extends('layouts.admin')

@section('title', 'Hospital Profile')
@section('page_title', 'Hospital Organization Profile')

@section('content')
<div class="max-w-4xl space-y-6 pb-12" x-data="{ isSubmitting: false }">
    <!-- Header & Action Controls -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $hospital->name }}</h1>
                <x-status-badge :status="$hospital->status === 'active' ? 'active' : 'expired'" :label="ucfirst($hospital->status ?? 'active')" />
            </div>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1 font-mono">License No: {{ $hospital->license_number ?? 'LIC-REG-99' }} | City: {{ $hospital->city }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.hospitals.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">
                Back to Directory
            </a>
            <a href="{{ route('admin.hospitals.edit', $hospital) }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-sm transition">
                Edit Details
            </a>
            <form id="form-delete-hospital-show" method="POST" action="{{ route('admin.hospitals.destroy', $hospital) }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="button" @click="$dispatch('open-confirm', { modalId: 'confirm-delete-hospital-show', formId: 'form-delete-hospital-show' })" class="px-3 py-2.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 hover:bg-rose-100 text-xs font-bold rounded-xl transition">
                    Delete
                </button>
            </form>
            <x-confirm-dialog id="confirm-delete-hospital-show" title="Delete Hospital Entity" message="Are you sure you want to delete hospital '{{ $hospital->name }}'? Deleting this hospital will also remove associated staff login accounts." confirmText="Delete Hospital" variant="danger" />
        </div>
    </div>

    <!-- Institutional Profile Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 md:p-8 space-y-6">
        <!-- Section 1: Contact & Physical Location -->
        <div>
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-3">Institutional Contact & Location</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs bg-slate-50 dark:bg-[#111c38] p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">Emergency Phone</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $hospital->contact_phone }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">Official Email</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $hospital->email ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">City & Province</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $hospital->city }}</span>
                </div>
                <div class="sm:col-span-3 border-t border-slate-200 dark:border-slate-800/80 pt-2 mt-1">
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-0.5">Full Physical Address</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $hospital->address ?? 'Main Medical Boulevard' }}</span>
                </div>
            </div>
        </div>

        <!-- Section 2: Associated Clinician Staff Accounts -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400">Authorized Clinician Staff Accounts</h3>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline">+ Manage Accounts</a>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-100/80 dark:bg-[#111c38] text-slate-700 dark:text-slate-300 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Staff Name</th>
                            <th class="px-4 py-3">Login Email</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Provisioned Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-semibold">
                        @forelse($hospital->users as $staff)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                <td class="px-4 py-3 font-extrabold text-slate-900 dark:text-white">{{ $staff->name }}</td>
                                <td class="px-4 py-3 font-mono text-slate-600 dark:text-slate-400">{{ $staff->email }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge status="approved" label="Hospital Staff" />
                                </td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$staff->status === 'active' ? 'active' : 'expired'" :label="ucfirst($staff->status)" />
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-500">{{ $staff->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500 italic">No staff user accounts associated with this hospital yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 3: Recent Hospital Blood Requisitions -->
        <div>
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-3">Recent Hospital Blood Requisitions</h3>
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-100/80 dark:bg-[#111c38] text-slate-700 dark:text-slate-300 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Requisition ID</th>
                            <th class="px-4 py-3">Patient Name</th>
                            <th class="px-4 py-3">Blood Group</th>
                            <th class="px-4 py-3">Units Needed</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-semibold">
                        @forelse($hospital->bloodRequests as $req)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-white">#REQ-{{ str_pad($req->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-4 py-3 font-extrabold text-slate-900 dark:text-white">{{ $req->patient_name }}</td>
                                <td class="px-4 py-3 font-mono font-extrabold text-rose-600 dark:text-rose-400">{{ $req->blood_group }}</td>
                                <td class="px-4 py-3 font-mono font-bold">{{ $req->units_needed }} Unit(s)</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$req->status" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500 italic">No requisitions submitted by this hospital yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
