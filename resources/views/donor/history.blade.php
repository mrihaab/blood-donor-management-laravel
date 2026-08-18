@extends('layouts.donor')

@section('title', 'Donation History')
@section('page_title', 'My Donation History')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Past Blood Donations</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Donation Date</th>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Quantity (Units)</th>
                        <th class="px-4 py-3">Collection Center</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($donations as $donation)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $donation->donation_date ?? $donation->created_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-md">
                                    {{ $donation->bloodGroup->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-gray-900">{{ $donation->quantity }}</td>
                            <td class="px-4 py-3">{{ $donation->collection_center ?? 'Main Blood Bank Center' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 italic">No past donation records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
