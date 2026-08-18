@extends('layouts.admin')

@section('title', 'Appointments')
@section('page_title', 'Donation Appointments')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-900">Scheduled Appointments</h3>
        <a href="{{ route('admin.appointments.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">
            + Schedule Appointment
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Donor</th>
                        <th class="px-4 py-3">Blood Group</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Time</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($appointments as $app)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $app->donor->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-md">
                                    {{ $app->donor->bloodGroup->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $app->appointment_date }}</td>
                            <td class="px-4 py-3">{{ $app->appointment_time ?? '09:00 AM' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $app->status === 'completed' ? 'bg-green-100 text-green-800' : ($app->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ ucfirst($app->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                @if($app->status === 'scheduled')
                                    <form method="POST" action="{{ route('admin.appointments.mark_completed', $app->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700">Complete</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.appointments.mark_cancelled', $app->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-red-600 text-white text-xs font-semibold rounded hover:bg-red-700">Cancel</button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.appointments.show', $app->id) }}" class="text-blue-600 hover:underline text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 italic">No appointments scheduled.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
