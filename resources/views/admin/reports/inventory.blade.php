@extends('layouts.admin')

@section('title', 'Inventory Report')
@section('page_title', 'Blood Stock Inventory Report')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Back to Reports</a>
        <a href="{{ route('admin.reports.inventory', ['format' => 'pdf']) }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">Export Stock Certificate PDF</a>
    </div>

    <!-- Stock Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-5 bg-white rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-slate-500">Active Available Stock</p>
                <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $totalAvailable }} Bags</p>
            </div>
            <span class="p-3 bg-emerald-100 text-emerald-800 rounded-xl font-bold text-xl">🟢</span>
        </div>
        <div class="p-5 bg-white rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-slate-500">Total Dispensed Stock</p>
                <p class="text-3xl font-extrabold text-purple-600 mt-1">{{ $totalDispensed }} Bags</p>
            </div>
            <span class="p-3 bg-purple-100 text-purple-800 rounded-xl font-bold text-xl">🟣</span>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-900 text-lg mb-4">Real-Time Stock Availability by Blood Group</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Available Bags</th>
                        <th class="px-4 py-3">Donor Intake</th>
                        <th class="px-4 py-3">Direct Admin Intake</th>
                        <th class="px-4 py-3">Expiring Soon (7 Days)</th>
                        <th class="px-4 py-3">Health Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium">
                    @foreach($inventory as $item)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 font-bold text-gray-900">
                                <span class="px-2.5 py-1 bg-red-700 text-white text-xs font-bold rounded">
                                    {{ $item['blood_group'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-extrabold text-slate-900 text-base">{{ $item['units_available'] }} Bags</td>
                            <td class="px-4 py-3 text-xs font-semibold text-emerald-800">{{ $item['donor_intake_count'] }} Bags</td>
                            <td class="px-4 py-3 text-xs font-semibold text-purple-800">{{ $item['direct_intake_count'] }} Bags</td>
                            <td class="px-4 py-3">
                                @if($item['expiring_soon'] > 0)
                                    <span class="font-bold text-amber-700">⚠️ {{ $item['expiring_soon'] }} Bag(s)</span>
                                @else
                                    <span class="text-slate-400">0 Bags</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($item['is_low_stock'])
                                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-800">
                                        ⚠️ Low Stock Threshold
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                                        ✓ Optimal Stock
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
