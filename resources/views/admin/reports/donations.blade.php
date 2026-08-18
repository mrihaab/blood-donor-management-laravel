@extends('layouts.admin')

@section('title', 'Donations Report')
@section('page_title', 'Donations Report')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Back to Reports</a>
        <a href="{{ route('admin.reports.donations', ['format' => 'pdf']) }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">Export PDF</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-900 text-lg mb-4">Blood Donation Logs</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Donor Name</th>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Units</th>
                        <th class="px-4 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($donations as $donation)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $donation->donor->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-bold rounded">{{ $donation->bloodGroup->name ?? 'N/A' }}</span></td>
                            <td class="px-4 py-3 font-bold">{{ $donation->quantity }}</td>
                            <td class="px-4 py-3">{{ $donation->donation_date ?? $donation->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
