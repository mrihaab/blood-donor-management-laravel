@extends('layouts.admin')

@section('title', 'Manage Donors')
@section('page_title', 'Registered Donor Directory')

@section('content')
<div class="space-y-6 pb-12" x-data="{ isSubmitting: false }">
    <!-- Header & Action Row -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Registered Donor Directory</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Audit, inspect, and manage registered blood donors and eligibility profiles.</p>
        </div>
        <a href="{{ route('admin.donors.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-extrabold text-white hover:bg-rose-700 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-rose-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            <span>Register New Donor</span>
        </a>
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

    <!-- Search & Filter Bar -->
    <div class="bg-white dark:bg-[#0c1427] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.donors.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label for="search" class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Search Donor / Phone / City</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, phone or city..." class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none">
            </div>

            <div>
                <label for="blood_group_id" class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Blood Group Profile</label>
                <select id="blood_group_id" name="blood_group_id" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    <option value="">All Blood Groups</option>
                    @foreach(\App\Models\BloodGroup::all() as $bg)
                        <option value="{{ $bg->id }}" {{ request('blood_group_id') == $bg->id ? 'selected' : '' }}>{{ $bg->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-white text-white dark:text-slate-900 font-extrabold rounded-xl text-xs shadow-sm transition">
                    Filter Directory
                </button>
                @if(request()->hasAny(['search', 'blood_group_id']))
                    <a href="{{ route('admin.donors.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Donor Table Container -->
    <div class="bg-white dark:bg-[#0c1427] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100/80 dark:bg-[#111c38] text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3.5">Donor Name & Email</th>
                        <th class="px-4 py-3.5">ABO/Rh Profile</th>
                        <th class="px-4 py-3.5">Contact Number</th>
                        <th class="px-4 py-3.5">Location</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-semibold">
                    @forelse($donors as $donor)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-4 py-3.5">
                                <a href="{{ route('admin.donors.show', $donor->id) }}" class="font-extrabold text-slate-900 dark:text-white hover:text-rose-600 dark:hover:text-rose-400 transition">
                                    {{ $donor->user->name ?? 'Unregistered User' }}
                                </a>
                                <span class="block text-xs font-mono text-slate-500 dark:text-slate-400 font-normal">{{ $donor->user->email ?? 'No email assigned' }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-mono font-extrabold bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                    {{ $donor->bloodGroup->name ?? 'Unassigned' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-xs text-slate-800 dark:text-slate-200">{{ $donor->contact_number ?? 'Not Provided' }}</td>
                            <td class="px-4 py-3.5 text-xs text-slate-700 dark:text-slate-300">{{ $donor->city ?? 'N/A' }}{{ $donor->state ? ', '.$donor->state : '' }}</td>
                            <td class="px-4 py-3.5">
                                <x-status-badge :status="$donor->status === 'active' ? 'active' : 'expired'" :label="ucfirst($donor->status ?? 'active')" />
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                <a href="{{ route('admin.donors.show', $donor->id) }}" class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold rounded-xl transition">
                                    View
                                </a>
                                <a href="{{ route('admin.donors.edit', $donor->id) }}" class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold rounded-xl transition">
                                    Edit
                                </a>
                                <form id="form-delete-donor-{{ $donor->id }}" method="POST" action="{{ route('admin.donors.destroy', $donor->id) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" @click="$dispatch('open-confirm', { modalId: 'confirm-delete-donor-{{ $donor->id }}', formId: 'form-delete-donor-{{ $donor->id }}' })" class="px-2.5 py-1.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 hover:bg-rose-100 text-xs font-bold rounded-xl transition">
                                        Delete
                                    </button>
                                </form>
                                <x-confirm-dialog id="confirm-delete-donor-{{ $donor->id }}" title="Delete Donor Account" message="Are you sure you want to delete donor '{{ $donor->user->name ?? 'Donor' }}'? Associated donor profile record will be permanently deleted." confirmText="Delete Donor" variant="danger" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500 italic">No registered donors found matching criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($donors, 'links') && $donors->hasPages())
            <div class="mt-4 border-t border-slate-200 dark:border-slate-800 pt-4">
                {{ $donors->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
