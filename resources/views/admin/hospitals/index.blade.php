@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Hospital Organizations Directory</h1>
            <p class="text-sm text-slate-500">Registered hospital institutions, licensing credentials, and blood request history.</p>
        </div>
        <a href="{{ route('admin.hospitals.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 shadow-sm transition">
            <span>🏥</span>
            <span>+ Register New Hospital</span>
        </a>
    </div>

    @if (session('success'))
        <div class="p-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200">
            <span class="font-bold">Success!</span> {{ session('success') }}
        </div>
    @endif

    <!-- Search Bar -->
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.hospitals.index') }}" class="flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by hospital name, city, or license number..." class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">Search</button>
        </form>
    </div>

    <!-- Hospitals Table -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 font-semibold text-slate-600">
                <tr>
                    <th class="px-6 py-3.5">Hospital Name</th>
                    <th class="px-6 py-3.5">License Number</th>
                    <th class="px-6 py-3.5">City</th>
                    <th class="px-6 py-3.5">Contact Phone</th>
                    <th class="px-6 py-3.5">Staff Users</th>
                    <th class="px-6 py-3.5">Patients</th>
                    <th class="px-6 py-3.5">Total Requisitions</th>
                    <th class="px-6 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-medium text-slate-800">
                @forelse($hospitals as $hospital)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $hospital->name }}</td>
                        <td class="px-6 py-4 font-mono text-slate-600">{{ $hospital->license_number ?? 'LIC-REG-99' }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $hospital->city }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $hospital->contact_phone }}</td>
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $hospital->users_count ?? 1 }}</td>
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $hospital->patients_count }}</td>
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $hospital->blood_requests_count }}</td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.hospitals.show', $hospital) }}" class="inline-flex items-center rounded-md border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                View Profile
                            </a>
                            <a href="{{ route('admin.hospitals.edit', $hospital) }}" class="inline-flex items-center rounded-md border border-slate-300 px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 transition">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.hospitals.destroy', $hospital) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete hospital {{ $hospital->name }} and all associated staff accounts?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 hover:bg-red-100 transition">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8">
                            <x-empty-state title="No registered hospitals found" description="Hospital records will appear here once registered by admin." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($hospitals->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $hospitals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
