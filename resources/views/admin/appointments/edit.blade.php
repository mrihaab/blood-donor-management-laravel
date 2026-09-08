@extends('layouts.admin')

@section('title', 'Edit Appointment')
@section('page_title', 'Edit Appointment Details')

@section('content')
<div class="max-w-3xl space-y-6 pb-12" x-data="{ isSubmitting: false }">
    <!-- Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Edit Appointment #{{ $appointment->id }}</h1>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Update scheduled date, time slot, or appointment status.</p>
        </div>
        <a href="{{ route('admin.appointments.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">
            Back to Directory
        </a>
    </div>

    <!-- Alert Messages -->
    @if ($errors->any())
        <div class="p-4 text-xs md:text-sm text-rose-900 dark:text-rose-200 rounded-xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 shadow-sm" role="alert">
            <div class="font-bold mb-1">Please correct the following errors:</div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Container -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 md:p-8 space-y-6">
        <form method="POST" action="{{ route('admin.appointments.update', $appointment->id) }}" class="space-y-6" @submit="isSubmitting = true">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 border-b border-slate-100 dark:border-slate-800 pb-2">Schedule & Status Update</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="appointment_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Appointment Date <span class="text-rose-600">*</span></label>
                        <input id="appointment_date" type="date" name="appointment_date" value="{{ old('appointment_date', $appointment->appointment_date) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="appointment_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Time Slot <span class="text-rose-600">*</span></label>
                        <input id="appointment_time" type="time" name="appointment_time" value="{{ old('appointment_time', $appointment->appointment_time) }}" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Appointment Status <span class="text-rose-600">*</span></label>
                    <select id="status" name="status" required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        <option value="scheduled" {{ old('status', $appointment->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="no_show" {{ old('status', $appointment->status) == 'no_show' ? 'selected' : '' }}>No Show</option>
                    </select>
                </div>
            </div>

            <!-- Form Action Footer -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.appointments.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 transition">Cancel</a>
                <button type="submit" 
                        :disabled="isSubmitting"
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-md transition focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50 inline-flex items-center gap-1.5">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Updating...' : 'Save Appointment Changes'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
