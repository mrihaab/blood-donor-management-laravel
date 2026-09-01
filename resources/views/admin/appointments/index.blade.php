@extends('layouts.admin')

@section('title', 'Appointments')
@section('page_title', 'Donation Appointments')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Scheduled Appointments</h3>
            <p class="text-xs text-slate-500">Clinical appointment management & 1-click blood donation intakes.</p>
        </div>
        <a href="{{ route('admin.appointments.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">
            + Schedule Appointment
        </a>
    </div>

    @if (session('success'))
        <div class="p-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200">
            <span class="font-bold">Success!</span> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
            <span class="font-bold">Error!</span> {{ session('error') }}
        </div>
    @endif

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
                            <td class="px-4 py-3 font-semibold text-gray-900">
                                <a href="{{ route('admin.appointments.show', $app->id) }}" class="hover:underline hover:text-red-600">
                                    {{ $app->donor->user->name ?? 'N/A' }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-md">
                                    {{ $app->donor->bloodGroup->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $app->appointment_date }}</td>
                            <td class="px-4 py-3">{{ $app->appointment_time ?? '09:00 AM' }}</td>
                            <td class="px-4 py-3">
                                <x-status-badge :status="$app->status" />
                            </td>
                            <td class="px-4 py-3 text-right space-x-1.5">
                                @if($app->status === 'scheduled')
                                    <a href="{{ route('admin.appointments.show', $app->id) }}" class="px-2.5 py-1 bg-red-600 text-white text-xs font-bold rounded hover:bg-red-700 transition inline-block">
                                        🩸 Intake Blood
                                    </a>
                                    <form method="POST" action="{{ route('admin.appointments.mark_completed', $app->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700">Complete</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.appointments.mark_cancelled', $app->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-slate-200 text-slate-700 text-xs font-semibold rounded hover:bg-slate-300">Cancel</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.appointments.show', $app->id) }}" class="text-blue-600 hover:underline text-xs font-medium">View Details</a>
                                @endif

                                <form method="POST" action="{{ route('admin.appointments.destroy', $app->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete appointment #{{ $app->id }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-xs font-bold ml-1">
                                        Delete
                                    </button>
                                </form>
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
