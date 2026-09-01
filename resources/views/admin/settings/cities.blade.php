@extends('layouts.admin')

@section('title', 'Manage Service Cities')
@section('page_title', 'Service Cities')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="flex space-x-4 border-b border-gray-200 pb-3 text-sm">
        <a href="{{ route('admin.settings.index') }}" class="text-gray-500 hover:text-gray-900 pb-2">General Settings</a>
        <a href="{{ route('admin.settings.blood_groups') }}" class="text-gray-500 hover:text-gray-900 pb-2">Blood Groups</a>
        <a href="{{ route('admin.settings.cities') }}" class="font-bold text-red-600 border-b-2 border-red-600 pb-2">Operational Cities</a>
    </div>

    @if (session('success'))
        <div class="p-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200">
            <span class="font-bold">Success!</span> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-900 text-lg">Active Operational Cities</h3>
                <p class="text-xs text-gray-500">Configure regions where blood bank operations and donor matching are active.</p>
            </div>
            <button onclick="document.getElementById('add-city-modal').classList.remove('hidden')" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition shadow-sm">
                + Add New City
            </button>
        </div>

        <ul class="divide-y divide-gray-100 text-sm text-gray-700">
            @forelse($cities as $index => $city)
                <li class="py-3 flex items-center justify-between hover:bg-gray-50/50 px-2 rounded-lg transition">
                    <div class="flex items-center space-x-3">
                        <span class="text-base">📍</span>
                        <span class="font-semibold text-gray-900">{{ $city }}</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="text-xs px-2.5 py-0.5 bg-green-100 text-green-800 rounded-full font-bold">Active</span>
                        
                        <!-- Delete City Form -->
                        <form method="POST" action="{{ route('admin.settings.cities.destroy', $index) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to remove city {{ $city }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline font-semibold">Delete</button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="py-4 text-center italic text-gray-500">No active operational cities configured.</li>
            @endforelse
        </ul>
    </div>
</div>

<!-- Add City Modal -->
<div id="add-city-modal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900">Add Operational City</h3>
            <button onclick="document.getElementById('add-city-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.settings.cities.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-700 mb-1">City Name *</label>
                <input type="text" name="city_name" required placeholder="e.g. Sialkot" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('add-city-modal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow">Add City</button>
            </div>
        </form>
    </div>
</div>
@endsection
