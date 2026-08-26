@extends('layouts.donor')

@section('content')
<div class="space-y-8">
    <!-- Donor Hero Header -->
    <div class="rounded-2xl bg-gradient-to-r from-red-800 to-red-600 p-8 text-white shadow-lg">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="inline-block rounded-full bg-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white backdrop-blur-sm">Donor Portal</span>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight">Welcome back, {{ auth()->user()->name }}!</h1>
                <p class="mt-1 text-sm text-red-100">Thank you for being a lifesaving hero. Your blood donations save lives across hospitals.</p>
            </div>
            <div class="rounded-xl bg-white/10 p-4 backdrop-blur-sm text-center border border-white/20">
                <span class="text-xs uppercase font-bold text-red-200 block">Your Blood Group</span>
                <span class="text-3xl font-black text-white block mt-0.5">{{ auth()->user()->donor->bloodGroup->name ?? 'O+' }}</span>
            </div>
        </div>
    </div>

    <!-- Eligibility & Quick Metrics Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <!-- Medical Eligibility Status -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">56-Day Donation Guard</span>
            @if($eligibility['eligible'] ?? true)
                <div class="flex items-center space-x-2">
                    <span class="h-3 w-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-lg font-bold text-emerald-700">Eligible to Donate Today!</span>
                </div>
                <p class="text-xs text-slate-500">You have met the mandatory 56-day recovery interval.</p>
            @else
                <div class="flex items-center space-x-2">
                    <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                    <span class="text-lg font-bold text-amber-700">Deferred Until {{ isset($eligibility['next_eligible_date']) && $eligibility['next_eligible_date'] ? $eligibility['next_eligible_date']->format('M d, Y') : 'specified date' }}</span>
                </div>
                <p class="text-xs text-slate-500">Please wait {{ $eligibility['days_remaining'] ?? $eligibility['days_until_eligible'] ?? 0 }} more days before your next donation.</p>
            @endif
        </div>

        <!-- Last Donation -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Last Donation Date</span>
            <h3 class="text-2xl font-bold text-slate-900">{{ auth()->user()->donor && auth()->user()->donor->last_donation_date ? auth()->user()->donor->last_donation_date->format('M d, Y') : 'No prior record' }}</h3>
            <p class="text-xs text-slate-500">Recorded in LifeBlood platform</p>
        </div>

        <!-- Upcoming Appointment -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Next Scheduled Visit</span>
            @if($upcomingAppointment)
                <h3 class="text-lg font-bold text-slate-900">{{ $upcomingAppointment->appointment_date }}</h3>
                <p class="text-xs text-slate-500">Location: {{ $upcomingAppointment->location ?? 'Main Donation Center' }}</p>
            @else
                <h3 class="text-base font-bold text-slate-500">No upcoming appointment</h3>
                <a href="{{ route('donor.appointments.create') }}" class="inline-block text-xs font-semibold text-red-600 hover:underline">+ Book Appointment</a>
            @endif
        </div>
    </div>

    <!-- Quick Action Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('donor.appointments.create') }}" class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:border-red-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 group-hover:text-red-600 transition">Book Donation Appointment</h3>
                <span class="text-slate-400 group-hover:text-red-600 font-bold">&rarr;</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Schedule a visit at a convenient blood collection center.</p>
        </a>

        <a href="{{ route('donor.blood-requests.create') }}" class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:border-red-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 group-hover:text-red-600 transition">Request Emergency Blood</h3>
                <span class="text-slate-400 group-hover:text-red-600 font-bold">&rarr;</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Submit a requisition request for a family member or hospital patient.</p>
        </a>

        <a href="{{ route('donor.history') }}" class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:border-red-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 group-hover:text-red-600 transition">Donation Impact History</h3>
                <span class="text-slate-400 group-hover:text-red-600 font-bold">&rarr;</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">View past donation dates, volumes, and resulting barcode units.</p>
        </a>
    </div>
</div>
@endsection
