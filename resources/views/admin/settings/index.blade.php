@extends('layouts.admin')

@section('title', 'System Settings')
@section('page_title', 'System Settings')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="flex space-x-4 border-b pb-3">
        <a href="{{ route('admin.settings.index') }}" class="font-bold text-red-600 border-b-2 border-red-600 pb-2">General Settings</a>
        <a href="{{ route('admin.settings.blood_groups') }}" class="text-gray-500 hover:text-gray-900 pb-2">Blood Groups</a>
        <a href="{{ route('admin.settings.cities') }}" class="text-gray-500 hover:text-gray-900 pb-2">Cities</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700">Organization Name</label>
                <input type="text" name="organization_name" value="{{ old('organization_name', $settings['organization_name'] ?? 'LifeBlood Management System') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Organization Email</label>
                <input type="email" name="organization_email" value="{{ old('organization_email', $settings['organization_email'] ?? 'admin@blood.com') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Low Stock Alert Threshold (Units)</label>
                <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $settings['low_stock_threshold'] ?? 10) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg text-sm hover:bg-red-700">Save Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
