@extends('layouts.admin')

@section('title', 'Donations Management')
@section('page_title', 'Donation Records')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-900">Recorded Donations</h3>
        <a href="{{ route('admin.donations.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">
            + Record New Donation
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Donor Name</th>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Quantity (Units)</th>
                        <th class="px-4 py-3">Donation Date</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($donations as $donation)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $donation->donor->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-md">
                                    {{ $donation->bloodGroup->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-gray-900">{{ $donation->quantity ?? 1 }}</td>
                            <td class="px-4 py-3">{{ $donation->donation_date ?? $donation->created_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.donations.show', $donation->id) }}" class="text-blue-600 hover:underline">View</a>
                                <a href="{{ route('admin.donations.edit', $donation->id) }}" class="text-amber-600 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 italic">No donations recorded in system yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
