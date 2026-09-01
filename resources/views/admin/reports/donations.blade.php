@extends('layouts.admin')

@section('title', 'Donations Report')
@section('page_title', 'Donations Report')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Back to Reports</a>
        <div class="flex space-x-2">
            <a href="{{ route('admin.reports.donations', ['format' => 'csv']) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-medium rounded-lg text-sm transition">Export CSV</a>
            <a href="{{ route('admin.reports.donations', ['format' => 'pdf']) }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">Export PDF</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-900 text-lg mb-4">Physical Blood Bag Donation Logs & Ingestion</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Unit Serial #</th>
                        <th class="px-4 py-3">Donor / Source</th>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Component</th>
                        <th class="px-4 py-3">Collection Date</th>
                        <th class="px-4 py-3">Expiry Date</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @forelse($donations as $unit)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 font-mono font-bold text-slate-900">{{ $unit->unit_number }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">
                                {{ $unit->donor->user->name ?? 'Direct Admin Stock Intake' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-0.5 bg-red-100 text-red-800 text-xs font-bold rounded">
                                    {{ $unit->bloodGroup->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold text-slate-700">{{ $unit->component->name ?? 'Whole Blood' }}</td>
                            <td class="px-4 py-3 text-xs font-mono text-slate-500">{{ $unit->collection_date }}</td>
                            <td class="px-4 py-3 text-xs font-mono text-slate-900 font-bold">{{ $unit->expiry_date }}</td>
                            <td class="px-4 py-3">
                                <x-status-badge :status="$unit->status" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500 italic">No donation unit logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
