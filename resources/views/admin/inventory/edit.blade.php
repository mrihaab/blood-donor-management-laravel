@extends('layouts.admin')

@section('title', 'Edit Inventory Stock')
@section('page_title', 'Edit Inventory Batch')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <form method="POST" action="{{ route('admin.inventory.update', $inventory->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700">Blood Group</label>
            <select name="blood_group_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                @foreach($bloodGroups as $group)
                    <option value="{{ $group->id }}" {{ $inventory->blood_group_id == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Quantity (Units)</label>
            <input type="number" name="quantity" min="0" value="{{ old('quantity', $inventory->quantity) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                <option value="available" {{ $inventory->status == 'available' ? 'selected' : '' }}>Available</option>
                <option value="reserved" {{ $inventory->status == 'reserved' ? 'selected' : '' }}>Reserved</option>
                <option value="used" {{ $inventory->status == 'used' ? 'selected' : '' }}>Used</option>
                <option value="expired" {{ $inventory->status == 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg text-sm">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg text-sm hover:bg-red-700">Update Stock</button>
        </div>
    </form>
</div>
@endsection
