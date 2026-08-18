@extends('layouts.admin')

@section('title', 'Appointment Details')
@section('page_title', 'Appointment Details')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
    <div class="flex items-center justify-between border-b pb-4">
        <h3 class="text-xl font-bold text-gray-900">{{ $appointment->donor->user->name ?? 'Donor #'.$appointment->donor_id }}</h3>
        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $appointment->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
            {{ ucfirst($appointment->status) }}
        </span>
    </div>

    <div class="space-y-2 text-sm">
        <p><span class="text-gray-500">Blood Group:</span> <span class="font-bold text-red-600">{{ $appointment->donor->bloodGroup->name ?? 'N/A' }}</span></p>
        <p><span class="text-gray-500">Appointment Date:</span> <span class="font-medium text-gray-900">{{ $appointment->appointment_date }}</span></p>
        <p><span class="text-gray-500">Appointment Time:</span> <span class="font-medium text-gray-900">{{ $appointment->appointment_time ?? 'N/A' }}</span></p>
        <p><span class="text-gray-500">Location:</span> <span class="font-medium text-gray-900">{{ $appointment->location ?? 'Main Center' }}</span></p>
    </div>

    <div class="pt-4 flex justify-end space-x-3">
        <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="px-4 py-2 bg-amber-500 text-white font-medium rounded-lg text-sm">Edit</a>
        <a href="{{ route('admin.appointments.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg text-sm">Back</a>
    </div>
</div>
@endsection
