@extends('layouts.admin')

@section('title', 'Donor Details')
@section('page_title', 'Donor Details')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between border-b pb-4 mb-4">
            <div>
                <h3 class="text-xl font-bold text-gray-900">{{ $donor->user->name ?? 'N/A' }}</h3>
                <p class="text-sm text-gray-500">{{ $donor->user->email ?? 'N/A' }}</p>
            </div>
            <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-extrabold rounded-lg">
                {{ $donor->bloodGroup->name ?? 'N/A' }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Contact Number:</span> <span class="font-medium text-gray-900">{{ $donor->contact_number }}</span></div>
            <div><span class="text-gray-500">Gender:</span> <span class="font-medium text-gray-900">{{ ucfirst($donor->gender) }}</span></div>
            <div><span class="text-gray-500">Date of Birth:</span> <span class="font-medium text-gray-900">{{ $donor->date_of_birth }}</span></div>
            <div><span class="text-gray-500">City / State:</span> <span class="font-medium text-gray-900">{{ $donor->city }}, {{ $donor->state }}</span></div>
            <div class="sm:col-span-2"><span class="text-gray-500">Address:</span> <span class="font-medium text-gray-900">{{ $donor->address }}</span></div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('admin.donors.edit', $donor->id) }}" class="px-4 py-2 bg-amber-500 text-white font-medium rounded-lg text-sm hover:bg-amber-600">Edit Profile</a>
            <a href="{{ route('admin.donors.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg text-sm">Back to List</a>
        </div>
    </div>
</div>
@endsection
