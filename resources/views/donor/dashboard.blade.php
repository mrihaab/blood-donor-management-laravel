@extends('layouts.donor')

@section('title', 'Donor Dashboard')
@section('page_title', 'Donor Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Eligibility Status Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="p-3 bg-red-100 text-red-600 rounded-full text-3xl">🩸</div>
            <div>
                <h3 class="font-bold text-gray-900 text-lg">Welcome back, {{ Auth::user()->name }}!</h3>
                <p class="text-sm text-gray-600">Blood Group: <span class="font-bold text-red-600">{{ $donor->bloodGroup->name ?? 'Not Set' }}</span></p>
                <div class="mt-1">
                    @if($eligibilityStatus['eligible'] ?? true)
                        <span class="px-2.5 py-0.5 bg-green-100 text-green-800 text-xs font-bold rounded-full">Eligible to Donate</span>
                    @else
                        <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">Next Eligible Date: {{ $eligibilityStatus['next_eligible_date'] ?? 'N/A' }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('donor.appointments.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">+ Schedule Donation</a>
            <a href="{{ route('donor.blood_requests.create') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium rounded-lg text-sm transition">Request Blood</a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Donations</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalDonations ?? count($donations ?? []) }}</p>
            <p class="text-xs text-gray-400 mt-1">Lifesaving contributions</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Upcoming Appointments</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ count($upcomingAppointments ?? []) }}</p>
            <p class="text-xs text-gray-400 mt-1">Scheduled sessions</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <p class="text-sm font-medium text-gray-500">My Requests</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ count($myRequests ?? []) }}</p>
            <p class="text-xs text-gray-400 mt-1">Submitted blood requests</p>
        </div>
    </div>

    <!-- Recent History & Upcoming Appointments -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Appointments -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900 text-lg">Upcoming Appointments</h3>
                <a href="{{ route('donor.appointments.index') }}" class="text-sm text-red-600 hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                @forelse($upcomingAppointments as $app)
                    <div class="p-3 bg-gray-50 rounded-lg flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $app->appointment_date }} at {{ $app->appointment_time ?? '09:00 AM' }}</p>
                            <p class="text-xs text-gray-500">{{ $app->location ?? 'Main Blood Bank Center' }}</p>
                        </div>
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">{{ ucfirst($app->status) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 italic">No upcoming appointments scheduled.</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Donations -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900 text-lg">Recent Donation History</h3>
                <a href="{{ route('donor.history') }}" class="text-sm text-red-600 hover:underline">View Full History</a>
            </div>
            <div class="space-y-3">
                @forelse($donations as $donation)
                    <div class="p-3 bg-gray-50 rounded-lg flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">Donated {{ $donation->quantity }} Unit(s)</p>
                            <p class="text-xs text-gray-500">{{ $donation->donation_date ?? $donation->created_at->format('Y-m-d') }}</p>
                        </div>
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded">Completed</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 italic">No previous donation records found.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
