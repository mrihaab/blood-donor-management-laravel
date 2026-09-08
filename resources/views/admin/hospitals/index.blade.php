@extends('layouts.admin')

@section('title', 'Hospital Directory')
@section('page_title', 'Hospital Directory')

@section('content')
<div class="space-y-6 pb-12" x-data="{ isSubmitting: false }">
    <!-- Page Header & Action Row -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Hospital Institutions Directory</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Manage accredited clinical institutions, licensing credentials, and clinician access.</p>
        </div>
        <a href="{{ route('admin.hospitals.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-extrabold text-white hover:bg-rose-700 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-rose-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Register New Hospital</span>
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

    <!-- Search Bar -->
    <div class="bg-white dark:bg-[#0c1427] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.hospitals.index') }}" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by hospital name, city, or license number..." class="flex-1 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none">
            <button type="submit" class="py-2 px-5 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-white text-white dark:text-slate-900 font-extrabold rounded-xl text-xs shadow-sm transition">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.hospitals.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs transition flex items-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Hospitals Table -->
    <div class="bg-white dark:bg-[#0c1427] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100/80 dark:bg-[#111c38] text-slate-700 dark:text-slate-300 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3.5">Hospital Name</th>
                        <th class="px-4 py-3.5">License Number</th>
                        <th class="px-4 py-3.5">City</th>
                        <th class="px-4 py-3.5">Contact Phone</th>
                        <th class="px-4 py-3.5">Requisitions</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-semibold">
                    @forelse($hospitals as $hospital)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-4 py-3.5">
                                <a href="{{ route('admin.hospitals.show', $hospital) }}" class="font-extrabold text-slate-900 dark:text-white hover:text-rose-600 dark:hover:text-rose-400 transition">
                                    {{ $hospital->name }}
                                </a>
                                <span class="block text-xs font-mono text-slate-500 dark:text-slate-400 font-normal">{{ $hospital->email }}</span>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-xs font-bold text-slate-800 dark:text-slate-200">{{ $hospital->license_number ?? 'N/A' }}</td>
                            <td class="px-4 py-3.5 text-xs text-slate-700 dark:text-slate-300">{{ $hospital->city }}</td>
                            <td class="px-4 py-3.5 font-mono text-xs text-slate-800 dark:text-slate-200">{{ $hospital->contact_phone }}</td>
                            <td class="px-4 py-3.5 font-mono font-bold text-slate-900 dark:text-white">{{ $hospital->blood_requests_count ?? 0 }}</td>
                            <td class="px-4 py-3.5">
                                <x-status-badge :status="$hospital->status === 'active' ? 'active' : 'expired'" :label="ucfirst($hospital->status ?? 'active')" />
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1.5">
                                <a href="{{ route('admin.hospitals.show', $hospital) }}" class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold rounded-xl transition">
                                    View Profile
                                </a>
                                <a href="{{ route('admin.hospitals.edit', $hospital) }}" class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold rounded-xl transition">
                                    Edit
                                </a>
                                <form id="form-delete-hospital-{{ $hospital->id }}" method="POST" action="{{ route('admin.hospitals.destroy', $hospital) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" @click="$dispatch('open-confirm', { modalId: 'confirm-delete-hospital-{{ $hospital->id }}', formId: 'form-delete-hospital-{{ $hospital->id }}' })" class="px-2.5 py-1.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 hover:bg-rose-100 text-xs font-bold rounded-xl transition">
                                        Delete
                                    </button>
                                </form>
                                <x-confirm-dialog id="confirm-delete-hospital-{{ $hospital->id }}" title="Delete Hospital Organization" message="Are you sure you want to delete hospital '{{ $hospital->name }}'? Deleting this hospital will also remove associated staff login accounts." confirmText="Delete Hospital" variant="danger" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-500 italic">No registered hospital institutions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($hospitals->hasPages())
            <div class="mt-4 border-t border-slate-200 dark:border-slate-800 pt-4">
                {{ $hospitals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
