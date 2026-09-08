@extends('layouts.donor')

@section('title', 'Appointment Details')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Appointment Ticket Card -->
    <div class="bg-white dark:bg-[#0c1427] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 md:p-8 shadow-sm transition-colors space-y-6">
        
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 block">Donation Pass</span>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Appointment Details</h1>
            </div>
            <x-status-badge :status="$appointment->status" />
        </div>

        <div class="bg-slate-50 dark:bg-[#070d1a] p-5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs md:text-sm">
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Scheduled Date</span>
                    <span class="font-bold font-mono text-slate-900 dark:text-white text-base">{{ $appointment->appointment_date }}</span>
                </div>
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Preferred Time</span>
                    <span class="font-bold font-mono text-slate-900 dark:text-white text-base">{{ $appointment->appointment_time ?? '09:00 AM' }}</span>
                </div>
                <div class="sm:col-span-2 pt-2 border-t border-slate-200/60 dark:border-slate-800/80">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Collection Location</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $appointment->location ?? 'Main Blood Bank Center' }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('donor.appointments.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition">
                &larr; Back to Appointments
            </a>

            @if($appointment->status === 'scheduled')
                <a href="{{ route('donor.appointments.edit', $appointment->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-extrabold rounded-xl shadow-sm transition">
                    Reschedule Visit
                </a>
            @endif
        </div>

    </div>

</div>
@endsection
