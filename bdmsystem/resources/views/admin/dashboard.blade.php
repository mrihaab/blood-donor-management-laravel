@extends('admin.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <!-- Admin User Guide -->
    <x-user-guide title="Welcome to Blood Donation Management System - Admin Panel" type="primary" :steps="[
        'Monitor real-time blood inventory and low stock alerts',
        'Manage donor registrations and profile updates', 
        'Schedule and track donation appointments',
        'Record blood donations and update inventory automatically',
        'Process emergency blood requests from hospitals',
        'Generate comprehensive reports with PDF/CSV export',
        'Configure system settings and manage users'
    ]">
        <strong>Quick Start:</strong> Use the navigation menu above to access different sections. 
        The dashboard below shows your current system status at a glance.
    </x-user-guide>

    <!-- Summary Cards -->
    <div class="row">
        <!-- Total Donors -->
        <div class="col-md-6 mb-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Donors</h5>
                    <p class="card-text display-6">{{ $totalDonors }}</p>
                </div>
            </div>
        </div>

        <!-- Total Blood Requests -->
        <div class="col-md-6 mb-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Total Blood Requests</h5>
                    <p class="card-text display-6">{{ $totalRequests }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Blood Units by Group -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-2">
                <div class="card-header bg-primary text-white">
                    Available Blood Units by Group
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($bloodInventory as $inventory)
                            <div class="col-md-3 mb-2">
                                <div class="border p-3 text-center bg-light">
                                  <h6>{{ $inventory['blood_group'] }}</h6>
                                    <p class="h4">{{ $inventory['units_available'] }} units</p>
                                    @if($inventory['units_available'] < $lowStockThreshold)
                                        <span class="badge bg-danger">Low Stock</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Donation History Chart -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    Donation History (Last 6 Months)
                </div>
                <div class="card-body">
                    <canvas id="donationChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities / Logs -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-4">
                <div class="card-header bg-dark text-white">
                    Recent Activities / Logs
                </div>
                <div class="card-body">
                    @if(count($recentActivities))
                        <ul class="list-group">
                            @foreach($recentActivities as $activity)
                                <li class="list-group-item">
                                    <strong>{{ $activity['created_at'] }}</strong> - 
                                    <span class="text-primary">{{ $activity['user_name'] }}</span>: 
                                    {{ $activity['message'] }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No recent activities found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('donationChart').getContext('2d');
    const donationChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($donationsChart->pluck('month')) !!},
            datasets: [{
                label: 'Donations',
                data: {!! json_encode($donationsChart->pluck('count')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
@endpush
