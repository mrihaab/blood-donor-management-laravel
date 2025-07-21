@extends('donor.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <!-- Donor User Guide -->
    <x-user-guide title="Welcome to Blood Donation Portal - Donor Dashboard" type="success" :steps="[
        'Complete your profile with accurate medical information',
        'Check your donation eligibility (must wait 56 days between donations)',
        'Book donation appointments at convenient times and locations',
        'Submit blood requests for patients in need',
        'Track your donation history and impact on saving lives',
        'Stay updated with your next eligible donation date'
    ]">
        <strong>Getting Started:</strong> If this is your first time, please update your profile first by clicking "My Profile" in the menu above.
        Make sure your blood group and contact information are accurate for emergency situations.
    </x-user-guide>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>🩸 Welcome, {{ auth()->user()->name }}!</h2>
                <span class="badge bg-danger fs-6">Blood Type: {{ auth()->user()->donor->bloodGroup->name ?? 'Not Set' }}</span>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    ✅ {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-plus" style="font-size: 2rem; color: #0d6efd;"></i>
                            <h5 class="mt-2">📅 Schedule Appointment</h5>
                            <a href="{{ route('donor.appointments.create') }}" class="btn btn-primary btn-sm">Book Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-person-gear" style="font-size: 2rem; color: #ffc107;"></i>
                            <h5 class="mt-2">👤 Update Profile</h5>
                            <a href="{{ route('donor.profile.edit') }}" class="btn btn-warning btn-sm">Edit Profile</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <i class="bi bi-droplet" style="font-size: 2rem; color: #dc3545;"></i>
                            <h5 class="mt-2">🆘 Blood Request</h5>
                            <a href="{{ route('donor.blood_requests.create') }}" class="btn btn-danger btn-sm">Submit Request</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <i class="bi bi-clock-history" style="font-size: 2rem; color: #0dcaf0;"></i>
                            <h5 class="mt-2">📜 View History</h5>
                            <a href="{{ route('donor.history') }}" class="btn btn-info btn-sm">View History</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donation Status -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">💉 Donation Status</h5>
                        </div>
                        <div class="card-body">
                            @if($latestDonation)
                                <p><strong>Last Donation:</strong> {{ $latestDonation->created_at->format('M d, Y') }}</p>
                                @if($nextEligibleDate)
                                    @if(\Carbon\Carbon::parse($nextEligibleDate)->isPast())
                                        <div class="alert alert-success">
                                            <strong>✅ You are eligible to donate now!</strong>
                                        </div>
                                        <a href="{{ route('donor.appointments.create') }}" class="btn btn-success">Schedule Donation</a>
                                    @else
                                        <div class="alert alert-warning">
                                            <strong>⏳ Next eligible date:</strong> {{ \Carbon\Carbon::parse($nextEligibleDate)->format('M d, Y') }}
                                            <br><small>{{ \Carbon\Carbon::parse($nextEligibleDate)->diffForHumans() }}</small>
                                        </div>
                                    @endif
                                @endif
                            @else
                                <div class="alert alert-info">
                                    <strong>🌟 Welcome! You haven't donated yet.</strong>
                                    <br>You are eligible to make your first donation.
                                </div>
                                <a href="{{ route('donor.appointments.create') }}" class="btn btn-primary">Schedule First Donation</a>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">📊 Your Statistics</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $donorProfile = auth()->user()->donor;
                                $totalDonations = $donorProfile ? $donorProfile->donations()->where('status', 'completed')->count() : 0;
                                $totalVolume = $donorProfile ? $donorProfile->donations()->where('status', 'completed')->sum('quantity') : 0;
                            @endphp
                            
                            <div class="row text-center">
                                <div class="col-6">
                                    <h3 class="text-primary">{{ $totalDonations }}</h3>
                                    <p class="text-muted">Total Donations</p>
                                </div>
                                <div class="col-6">
                                    <h3 class="text-danger">{{ $totalVolume }} mL</h3>
                                    <p class="text-muted">Blood Donated</p>
                                </div>
                            </div>
                            
                            @if($totalDonations > 0)
                                <div class="mt-3">
                                    <small class="text-success">🎉 Thank you for your {{ $totalDonations }} donation(s)!</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Blood Requests -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">🆘 Urgent Blood Requests - Your Blood Group Needed!</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $donorBloodGroup = auth()->user()->donor->bloodGroup->name ?? null;
                                $urgentRequests = collect();
                                if($donorBloodGroup) {
                                    $urgentRequests = \App\Models\BloodRequest::where('blood_group', $donorBloodGroup)
                                        ->where('status', 'pending')
                                        ->latest()
                                        ->take(5)
                                        ->get();
                                }
                            @endphp
                            
                            @if($urgentRequests->count() > 0)
                                <div class="alert alert-warning">
                                    <strong>⚡ {{ $urgentRequests->count() }} urgent requests for {{ $donorBloodGroup }} blood!</strong>
                                    Consider donating to help save lives.
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Patient</th>
                                                <th>Hospital</th>
                                                <th>City</th>
                                                <th>Reason</th>
                                                <th>Requested</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($urgentRequests as $request)
                                                <tr>
                                                    <td><strong>{{ $request->patient_name }}</strong></td>
                                                    <td>{{ $request->hospital }}</td>
                                                    <td>{{ $request->city }}</td>
                                                    <td><small>{{ Str::limit($request->reason ?? 'Emergency', 30) }}</small></td>
                                                    <td><small>{{ $request->created_at->diffForHumans() }}</small></td>
                                                    <td>
                                                        <a href="{{ route('donor.appointments.create') }}" 
                                                           class="btn btn-sm btn-success">
                                                            🩸 Donate Now
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="{{ route('donor.blood_requests.index') }}" class="btn btn-outline-danger">
                                        View All Blood Requests
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    @if($donorBloodGroup)
                                        <p class="text-muted">✅ No urgent requests for {{ $donorBloodGroup }} blood at the moment.</p>
                                        <small class="text-success">Thank you for being ready to help!</small>
                                    @else
                                        <p class="text-warning">⚠️ Please update your profile with your blood group to see relevant requests.</p>
                                        <a href="{{ route('donor.profile.edit') }}" class="btn btn-warning btn-sm">Update Profile</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">📅 Recent Activities</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $recentActivities = collect();
                                if($donorProfile) {
                                    $recentDonations = $donorProfile->donations()->latest()->limit(3)->get();
                                    $recentAppointments = $donorProfile->appointments()->latest()->limit(3)->get();
                                    
                                    foreach($recentDonations as $donation) {
                                        $recentActivities->push([
                                            'type' => 'donation',
                                            'message' => 'Blood donation completed (' . $donation->quantity . 'mL)',
                                            'date' => $donation->created_at,
                                            'icon' => '💉',
                                            'class' => 'text-success'
                                        ]);
                                    }
                                    
                                    foreach($recentAppointments as $appointment) {
                                        $recentActivities->push([
                                            'type' => 'appointment',
                                            'message' => 'Appointment ' . $appointment->status . ' for ' . $appointment->appointment_date->format('M d'),
                                            'date' => $appointment->created_at,
                                            'icon' => '📅',
                                            'class' => $appointment->status === 'completed' ? 'text-success' : 'text-primary'
                                        ]);
                                    }
                                }
                                
                                $recentActivities = $recentActivities->sortByDesc('date')->take(5);
                            @endphp
                            
                            @if($recentActivities->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($recentActivities as $activity)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="{{ $activity['class'] }}">{{ $activity['icon'] }}</span>
                                                {{ $activity['message'] }}
                                            </div>
                                            <small class="text-muted">{{ $activity['date']->diffForHumans() }}</small>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-center py-3">
                                    <p class="text-muted">No recent activities found.</p>
                                    <a href="{{ route('donor.appointments.create') }}" class="btn btn-primary">Schedule Your First Appointment</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
