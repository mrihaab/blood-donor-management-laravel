@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Patients & Medical Records Registry</h1>
            <p class="text-sm text-slate-500">Patient Medical Record Numbers (MRN), hospital assignments, and requisition history.</p>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.patients.index') }}" class="flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by patient name or MRN number..." class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">Search</button>
        </form>
    </div>

    <!-- Patients Table -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 font-semibold text-slate-600">
                <tr>
                    <th class="px-6 py-3.5">Patient Name</th>
                    <th class="px-6 py-3.5">MRN Serial</th>
                    <th class="px-6 py-3.5">Blood Group</th>
                    <th class="px-6 py-3.5">Hospital Assignment</th>
                    <th class="px-6 py-3.5">Requisitions</th>
                    <th class="px-6 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-medium text-slate-800">
                @forelse($patients as $patient)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $patient->name }}</td>
                        <td class="px-6 py-4 font-mono font-semibold text-slate-600">{{ $patient->mrn }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block rounded-md bg-red-700 px-2 py-0.5 text-xs font-bold text-white">
                                {{ $patient->bloodGroup->name ?? 'O+' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $patient->hospital->name ?? 'General Hospital' }}</td>
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $patient->blood_requests_count }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.patients.show', $patient) }}" class="inline-flex items-center rounded-md border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                Medical History
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8">
                            <x-empty-state title="No patient medical records found" description="Patients enrolled by hospitals will appear here." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($patients->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
