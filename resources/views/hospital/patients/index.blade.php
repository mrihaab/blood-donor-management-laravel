@extends('layouts.hospital')

@section('title', 'Patient Directory')
@section('page_title', 'Hospital Patients')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-gray-900">Hospital Patient Directory</h2>
        <a href="{{ route('hospital.patients.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm transition">
            + Register New Patient
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <form method="GET" action="{{ route('hospital.patients.index') }}" class="mb-6 flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by patient name or MRN..." class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium rounded-lg text-sm transition">
                Search
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">MRN</th>
                        <th class="px-4 py-3">Patient Name</th>
                        <th class="px-4 py-3">Gender</th>
                        <th class="px-4 py-3">Date of Birth</th>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($patients as $patient)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-bold text-gray-900">{{ $patient->mrn }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $patient->name }}</td>
                            <td class="px-4 py-3 capitalize">{{ $patient->gender }}</td>
                            <td class="px-4 py-3">{{ $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-md">
                                    {{ $patient->bloodGroup->name ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('hospital.patients.show', $patient->id) }}" class="text-blue-600 hover:underline text-xs font-semibold">View</a>
                                <a href="{{ route('hospital.patients.edit', $patient->id) }}" class="text-gray-600 hover:underline text-xs font-semibold">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 italic">No patients registered for this hospital yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $patients->links() }}
        </div>
    </div>
</div>
@endsection
