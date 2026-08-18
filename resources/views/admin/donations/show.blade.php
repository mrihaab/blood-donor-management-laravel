@extends('layouts.admin')

@section('title', 'Donation Record Details')
@section('page_title', 'Donation Details')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
    <div class="flex items-center justify-between border-b pb-4">
        <h3 class="text-xl font-bold text-gray-900">{{ $donation->donor->user->name ?? 'Donor #'.$donation->donor_id }}</h3>
        <span class="px-3 py-1 bg-red-600 text-white font-extrabold text-sm rounded-md">
            {{ $donation->bloodGroup->name ?? 'N/A' }}
        </span>
    </div>

    <div class="space-y-2 text-sm">
        <p><span class="text-gray-500">Donated Quantity:</span> <span class="font-bold text-gray-900">{{ $donation->quantity }} units</span></p>
        <p><span class="text-gray-500">Donation Date:</span> <span class="font-medium text-gray-900">{{ $donation->donation_date ?? $donation->created_at->format('Y-m-d') }}</span></p>
        <p><span class="text-gray-500">Collection Center:</span> <span class="font-medium text-gray-900">{{ $donation->collection_center ?? 'Main Center' }}</span></p>
    </div>

    <div class="pt-4 flex justify-end space-x-3">
        <a href="{{ route('admin.donations.edit', $donation->id) }}" class="px-4 py-2 bg-amber-500 text-white font-medium rounded-lg text-sm">Edit</a>
        <a href="{{ route('admin.donations.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg text-sm">Back</a>
    </div>
</div>
@endsection
