@extends('layouts.admin')

@section('title', 'Manage Service Cities')
@section('page_title', 'Service Cities')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="flex space-x-4 border-b pb-3">
        <a href="{{ route('admin.settings.index') }}" class="text-gray-500 hover:text-gray-900 pb-2">General Settings</a>
        <a href="{{ route('admin.settings.blood_groups') }}" class="text-gray-500 hover:text-gray-900 pb-2">Blood Groups</a>
        <a href="{{ route('admin.settings.cities') }}" class="font-bold text-red-600 border-b-2 border-red-600 pb-2">Cities</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
        <h3 class="font-bold text-gray-900 text-lg">Active Operational Cities</h3>
        <ul class="divide-y divide-gray-100 text-sm text-gray-700">
            @forelse($cities as $city)
                <li class="py-2.5 flex items-center justify-between">
                    <span>📍 {{ $city }}</span>
                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-800 rounded font-semibold">Active</span>
                </li>
            @empty
                <li class="py-4 text-center italic text-gray-500">No active operational cities configured.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
