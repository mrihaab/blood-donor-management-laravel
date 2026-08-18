@extends('layouts.admin')

@section('title', 'Manage Donors')
@section('page_title', 'Registered Donors')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <h3 class="text-lg font-bold text-gray-900">Donor Directory</h3>
        <a href="{{ route('admin.donors.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">
            + Register New Donor
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Blood Group</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($donors as $donor)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $donor->user->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-md">
                                {{ $donor->bloodGroup->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $donor->contact_number }}</td>
                        <td class="px-4 py-3">{{ $donor->city }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ ($donor->status ?? 'active') === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($donor->status ?? 'active') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.donors.show', $donor->id) }}" class="text-blue-600 hover:underline">View</a>
                            <a href="{{ route('admin.donors.edit', $donor->id) }}" class="text-amber-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 italic">No donors registered in system.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($donors, 'links'))
        <div class="mt-4">
            {{ $donors->links() }}
        </div>
    @endif
</div>
@endsection
