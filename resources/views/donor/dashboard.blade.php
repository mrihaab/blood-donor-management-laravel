@extends('layouts.donor')

@section('title', 'Donor Dashboard')

@section('content')
<div class="space-y-8">

    <!-- Active Emergency Blood Appeal Banner (if matching donor blood group) -->
    @if(isset($activeEmergencyRequests) && count($activeEmergencyRequests) > 0)
        <div class="space-y-4">
            @foreach($activeEmergencyRequests as $req)
                <div class="rounded-2xl border border-rose-300 dark:border-rose-900/60 bg-rose-50/90 dark:bg-rose-950/40 p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 transition-colors">
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 bg-rose-600 text-white font-extrabold text-[11px] uppercase tracking-wider rounded-md shadow-sm">
                                Emergency Blood Appeal
                            </span>
                            <span class="text-xs font-bold text-rose-700 dark:text-rose-300">Matching Your Blood Group ({{ $req->blood_group }})</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Urgent Need at {{ $req->hospital }} ({{ $req->city }})</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 font-medium">Requisition #REQ-{{ $req->id }} &bull; Required: {{ $req->units_needed }} Bag(s)</p>
                    </div>

                    <div class="shrink-0">
                        <form method="POST" action="{{ route('donor.appointments.rsvp_emergency', $req->id) }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                            @csrf
                            <button type="submit" :disabled="isSubmitting" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs md:text-sm font-extrabold rounded-xl shadow-md transition flex items-center gap-2">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span x-text="isSubmitting ? 'Confirming...' : 'Confirm Availability'">Confirm Availability</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Hero Greeting & Blood Group Card -->
    <div class="rounded-2xl bg-gradient-to-r from-rose-700 via-rose-600 to-red-700 p-6 md:p-8 text-white shadow-md relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 relative z-10">
            <div class="space-y-2 max-w-xl">
                <span class="inline-block rounded-full bg-white/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white backdrop-blur-sm">
                    Lifesaving Partner
                </span>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                    Welcome back, {{ auth()->user()->name }}!
                </h1>
                <p class="text-xs md:text-sm text-rose-100 font-medium">
                    Thank you for being a vital part of the community blood network. Your voluntary donations help save lives in local hospitals.
                </p>
            </div>
            
            <div class="rounded-2xl bg-white/15 dark:bg-black/20 p-5 backdrop-blur-md text-center border border-white/20 shrink-0 min-w-[140px]">
                <span class="text-[11px] uppercase font-bold text-rose-100 tracking-wider block">Blood Group</span>
                <span class="text-3xl font-black text-white block mt-1 font-mono">{{ auth()->user()->donor->bloodGroup->name ?? 'O+' }}</span>
            </div>
        </div>
    </div>

    <!-- Eligibility & Primary Metrics Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Donation Eligibility -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1427] p-6 shadow-sm space-y-3 transition-colors">
            <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Donation Recovery Status</span>
            @if($eligibility['eligible'] ?? true)
                <div class="flex items-center space-x-2">
                    <span class="h-3 w-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-base font-extrabold text-emerald-700 dark:text-emerald-400">Eligible to Donate</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400">You have completed the recommended recovery period.</p>
            @else
                <div class="flex items-center space-x-2">
                    <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                    <span class="text-base font-extrabold text-amber-700 dark:text-amber-400">
                        Eligible {{ isset($eligibility['next_eligible_date']) && $eligibility['next_eligible_date'] ? $eligibility['next_eligible_date']->format('M d, Y') : 'Soon' }}
                    </span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    Next donation available in {{ $eligibility['days_remaining'] ?? $eligibility['days_until_eligible'] ?? 0 }} day(s).
                </p>
            @endif
        </div>

        <!-- Last Recorded Donation -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1427] p-6 shadow-sm space-y-3 transition-colors">
            <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Last Donation Date</span>
            <h3 class="text-xl font-black text-slate-900 dark:text-white">
                {{ auth()->user()->donor && auth()->user()->donor->last_donation_date ? auth()->user()->donor->last_donation_date->format('M d, Y') : 'No prior record' }}
            </h3>
            <p class="text-xs text-slate-600 dark:text-slate-400">Recorded in central inventory system</p>
        </div>

        <!-- Next Scheduled Visit -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1427] p-6 shadow-sm space-y-3 transition-colors">
            <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Next Scheduled Visit</span>
            @if($upcomingAppointment)
                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $upcomingAppointment->appointment_date }}</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400">Location: {{ $upcomingAppointment->location ?? 'Main Blood Bank Center' }}</p>
            @else
                <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400">No appointment scheduled</h3>
                <a href="{{ route('donor.appointments.create') }}" class="inline-block text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline">+ Schedule Appointment</a>
            @endif
        </div>
    </div>

    <!-- Quick Actions Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('donor.appointments.create') }}" class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1427] p-6 shadow-sm hover:border-rose-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition">Schedule Appointment</h3>
                <span class="text-rose-600 dark:text-rose-400 font-bold">&rarr;</span>
            </div>
            <p class="mt-2 text-xs text-slate-600 dark:text-slate-400">Book a visit at a regional blood center or mobile collection drive.</p>
        </a>

        <a href="{{ route('donor.blood_requests.create') }}" class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1427] p-6 shadow-sm hover:border-rose-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition">Request Blood</h3>
                <span class="text-rose-600 dark:text-rose-400 font-bold">&rarr;</span>
            </div>
            <p class="mt-2 text-xs text-slate-600 dark:text-slate-400">Submit a blood request for a family member or patient in need.</p>
        </a>

        <a href="{{ route('donor.history') }}" class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1427] p-6 shadow-sm hover:border-rose-500 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition">Donation History</h3>
                <span class="text-rose-600 dark:text-rose-400 font-bold">&rarr;</span>
            </div>
            <p class="mt-2 text-xs text-slate-600 dark:text-slate-400">Review past donations, collection dates, and unit records.</p>
        </a>
    </div>

</div>
@endsection
