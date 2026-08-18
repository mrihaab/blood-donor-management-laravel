@extends('layouts.admin')

@section('title', 'Donor Report')
@section('page_title', 'Donor Report')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Back to Reports</a>
        <a href="{{ route('admin.reports.donors', ['format' => 'pdf']) }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">Export PDF</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-900 text-lg mb-4">Registered Donor List</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3">City</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($donors as $donor)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $donor->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-bold rounded">{{ $donor->bloodGroup->name ?? 'N/A' }}</span></td>
                            <td class="px-4 py-3">{{ $donor->contact_number }}</td>
                            <td class="px-4 py-3">{{ $donor->city }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
