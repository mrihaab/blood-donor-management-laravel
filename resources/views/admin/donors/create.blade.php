@extends('layouts.admin')

@section('title', 'Add Donor')
@section('page_title', 'Register New Donor')

@section('content')
<div class="max-w-3xl bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <form method="POST" action="{{ route('admin.donors.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" value="{{ old('name') }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" value="{{ old('email') }}">
            </div>

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
                <label class="block text-sm font-medium text-gray-700">Gender</label>
                <select name="gender" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
                <input type="date" name="date_of_birth" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                <input type="text" name="contact_number" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">City</label>
                <input type="text" name="city" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">State / Province</label>
                <input type="text" name="state" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Address</label>
            <textarea name="address" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.donors.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg text-sm">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg text-sm hover:bg-red-700">Save Donor</button>
        </div>
    </form>
</div>
@endsection
