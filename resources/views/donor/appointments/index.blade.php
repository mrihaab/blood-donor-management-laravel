@extends('layouts.donor')

@section('title', 'My Appointments')
@section('page_title', 'My Scheduled Appointments')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-900">Donation Appointments</h3>
        <a href="{{ route('donor.appointments.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">
            + Schedule Appointment
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Time</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($appointments as $app)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $app->appointment_date }}</td>
                            <td class="px-4 py-3">{{ $app->appointment_time ?? '09:00 AM' }}</td>
                            <td class="px-4 py-3">{{ $app->location ?? 'Main Blood Bank Center' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $app->status === 'completed' ? 'bg-green-100 text-green-800' : ($app->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ ucfirst($app->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('donor.appointments.show', $app->id) }}" class="text-blue-600 hover:underline text-xs">View</a>
                                @if($app->status === 'scheduled')
                                    <form method="POST" action="{{ route('donor.appointments.cancel', $app->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:underline text-xs">Cancel</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 italic">No scheduled appointments.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
