@extends('layouts.admin')

@section('title', 'Inventory Stock Details')
@section('page_title', 'Inventory Stock Details')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
    <div class="flex items-center justify-between border-b pb-4">
        <span class="px-3 py-1 bg-red-600 text-white font-extrabold text-lg rounded-md">
            {{ $inventory->bloodGroup->name ?? 'N/A' }}
        </span>
        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $inventory->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
            {{ ucfirst($inventory->status) }}
        </span>
    </div>

    <div class="space-y-2 text-sm">
        <p><span class="text-gray-500">Available Quantity:</span> <span class="font-bold text-gray-900">{{ $inventory->units_available ?? $inventory->quantity }} units</span></p>
        <p><span class="text-gray-500">Requested Quantity:</span> <span class="font-medium text-gray-900">{{ $inventory->units_requested ?? 0 }} units</span></p>
        <p><span class="text-gray-500">Expiry Date:</span> <span class="font-medium text-gray-900">{{ $inventory->expiry_date ?? 'N/A' }}</span></p>
    </div>

    <div class="pt-4 flex justify-end space-x-3">
        <a href="{{ route('admin.inventory.edit', $inventory->id) }}" class="px-4 py-2 bg-amber-500 text-white font-medium rounded-lg text-sm">Edit</a>
        <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg text-sm">Back</a>
    </div>
</div>
@endsection
