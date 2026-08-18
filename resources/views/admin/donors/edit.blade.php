@extends('layouts.admin')

@section('title', 'Edit Donor')
@section('page_title', 'Edit Donor Profile')

@section('content')
<div class="max-w-3xl bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <form method="POST" action="{{ route('admin.donors.update', $donor->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" value="{{ old('name', $donor->user->name ?? '') }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Blood Group</label>
                <select name="blood_group_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    @foreach($bloodGroups as $group)
                        <option value="{{ $group->id }}" {{ $donor->blood_group_id == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Gender</label>
                <select name="gender" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    <option value="male" {{ $donor->gender == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $donor->gender == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ $donor->gender == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                <input type="text" name="contact_number" value="{{ old('contact_number', $donor->contact_number) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">City</label>
                <input type="text" name="city" value="{{ old('city', $donor->city) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">State</label>
                <input type="text" name="state" value="{{ old('state', $donor->state) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Address</label>
            <textarea name="address" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">{{ old('address', $donor->address) }}</textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.donors.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg text-sm">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg text-sm hover:bg-red-700">Update Donor</button>
        </div>
    </form>
</div>
@endsection
