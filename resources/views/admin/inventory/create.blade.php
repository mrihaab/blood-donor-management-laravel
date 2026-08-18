@extends('layouts.admin')

@section('title', 'Add Inventory Stock')
@section('page_title', 'Add Blood Inventory Stock')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <form method="POST" action="{{ route('admin.inventory.store') }}" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700">Blood Group</label>
            <select name="blood_group_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                <option value="">Select Blood Group</option>
                @foreach($bloodGroups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Quantity (Units)</label>
            <input type="number" name="quantity" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Expiry Date</label>
            <input type="date" name="expiry_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                <option value="available">Available</option>
                <option value="reserved">Reserved</option>
                <option value="used">Used</option>
                <option value="expired">Expired</option>
            </select>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg text-sm">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg text-sm hover:bg-red-700">Add Stock</button>
        </div>
    </form>
</div>
@endsection
