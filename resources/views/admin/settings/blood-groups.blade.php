@extends('layouts.admin')

@section('title', 'Manage Blood Groups')
@section('page_title', 'Blood Groups Config')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="flex space-x-4 border-b pb-3">
        <a href="{{ route('admin.settings.index') }}" class="text-gray-500 hover:text-gray-900 pb-2">General Settings</a>
        <a href="{{ route('admin.settings.blood_groups') }}" class="font-bold text-red-600 border-b-2 border-red-600 pb-2">Blood Groups</a>
        <a href="{{ route('admin.settings.cities') }}" class="text-gray-500 hover:text-gray-900 pb-2">Cities</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
        <h3 class="font-bold text-gray-900 text-lg">Configured Blood Groups</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($bloodGroups as $group)
                        <tr>
                            <td class="px-4 py-3 font-bold text-red-600">{{ $group->name }}</td>
                            <td class="px-4 py-3">{{ $group->description ?? 'Standard Blood Group' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
