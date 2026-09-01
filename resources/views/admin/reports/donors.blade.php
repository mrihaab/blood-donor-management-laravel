@extends('layouts.admin')

@section('title', 'Donor Report')
@section('page_title', 'Donor Report')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Back to Reports</a>
        <div class="flex space-x-2">
            <a href="{{ route('admin.reports.donors', ['format' => 'csv']) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-medium rounded-lg text-sm transition">Export CSV</a>
            <a href="{{ route('admin.reports.donors', ['format' => 'pdf']) }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">Export PDF</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-900 text-lg mb-4">Registered Donor List & Participation</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Donor ID</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3">City</th>
                        <th class="px-4 py-3">Total Donations</th>
                        <th class="px-4 py-3">Registered Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($donors as $donor)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 font-mono text-slate-500">#{{ $donor->id }}</td>
                            <td class="px-4 py-3 font-bold text-gray-900">{{ $donor->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-0.5 bg-red-100 text-red-800 text-xs font-bold rounded">
                                    {{ $donor->bloodGroup->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $donor->contact_number ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $donor->city ?? 'N/A' }}</td>
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $donor->appointments->where('status', 'completed')->count() }} Intake(s)</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $donor->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500 italic">No donor records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
