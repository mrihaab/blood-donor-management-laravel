@extends('layouts.donor')

@section('title', 'Reschedule Appointment')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm flex items-center justify-between transition-colors">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Reschedule Appointment</h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Update your scheduled donation date and time.</p>
        </div>
        <a href="{{ route('donor.appointments.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition border border-slate-200 dark:border-slate-700">
            &larr; Back
        </a>
    </div>

    <!-- Form Container -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 md:p-8 shadow-sm transition-colors">
        <form method="POST" action="{{ route('donor.appointments.update', $appointment->id) }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Appointment Date -->
            <div>
                <label for="appointment_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    New Appointment Date <span class="text-rose-600 font-bold">*</span>
                </label>
                <input id="appointment_date" type="date" name="appointment_date" value="{{ old('appointment_date', $appointment->appointment_date) }}" min="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm font-mono">
                @error('appointment_date') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Preferred Time -->
            <div>
                <label for="appointment_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    New Preferred Time <span class="text-rose-600 font-bold">*</span>
                </label>
                <input id="appointment_time" type="time" name="appointment_time" value="{{ old('appointment_time', $appointment->appointment_time) }}" required class="w-full px-4 py-2.5 rounded-xl text-xs md:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:ring-2 focus:ring-rose-500 focus:outline-none transition shadow-sm font-mono">
                @error('appointment_time') <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('donor.appointments.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" :disabled="isSubmitting" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs md:text-sm font-extrabold rounded-xl shadow-md transition flex items-center gap-2">
                    <span x-text="isSubmitting ? 'Saving...' : 'Update Appointment'">Update Appointment</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
