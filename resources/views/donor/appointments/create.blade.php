@extends('layouts.donor')

@section('title', 'Book Appointment')
@section('page_title', 'Book Donation Appointment')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <form method="POST" action="{{ route('donor.appointments.store') }}" onsubmit="const b=this.querySelector('button[type=submit]'); if(b.disabled) return false; b.disabled=true; b.innerHTML='Submitting...';" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700">Appointment Date</label>
            <input type="date" name="appointment_date" min="{{ date('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Preferred Time</label>
            <input type="time" name="appointment_time" value="09:00" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Preferred Donation Center</label>
            <input type="text" name="location" value="Main Blood Bank Center" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('donor.appointments.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg text-sm">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg text-sm hover:bg-red-700">Confirm Booking</button>
        </div>
    </form>
</div>
@endsection
