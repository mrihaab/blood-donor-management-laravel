@extends('layouts.admin')

@section('title', 'Blood Inventory')
@section('page_title', 'Blood Stock Inventory')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-900">Inventory Units</h3>
        <a href="{{ route('admin.inventory.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">
            + Add Stock Batch
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Units Available</th>
                        <th class="px-4 py-3">Units Requested</th>
                        <th class="px-4 py-3">Expiry Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($inventory as $item)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-md">
                                    {{ $item->bloodGroup->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-gray-900">{{ $item->units_available ?? $item->quantity }}</td>
                            <td class="px-4 py-3">{{ $item->units_requested ?? 0 }}</td>
                            <td class="px-4 py-3">{{ $item->expiry_date ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $item->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.inventory.show', $item->id) }}" class="text-blue-600 hover:underline">View</a>
                                <a href="{{ route('admin.inventory.edit', $item->id) }}" class="text-amber-600 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 italic">No inventory stock items recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
