@extends('admin.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Monthly Statistics Report</h5>
                    <div>
                        <a href="{{ route('admin.reports.monthly-stats', ['format' => 'pdf', 'year' => $year, 'month' => $month]) }}" class="btn btn-sm btn-light">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Month/Year Filter -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="year">Year</label>
                                <select name="year" id="year" class="form-control">
                                    @for($y = now()->year; $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="month">Month</label>
                                <select name="month" id="month" class="form-control">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5>Donations</h5>
                                    <h3>{{ $stats['donations'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5>Blood Requests</h5>
                                    <h3>{{ $stats['blood_requests'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5>New Donors</h5>
                                    <h3>{{ $stats['new_donors'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5>Approved Requests</h5>
                                    <h3>{{ $stats['approved_requests'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Blood Group Statistics -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6>Blood Group Donations</h6>
                                </div>
                                <div class="card-body">
                                    @if($bloodGroupStats->count() > 0)
                                        <canvas id="bloodGroupChart" height="200"></canvas>
                                    @else
                                        <p class="text-muted text-center">No data available for this period.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6>Daily Donations</h6>
                                </div>
                                <div class="card-body">
                                    @if($dailyDonations->count() > 0)
                                        <canvas id="dailyChart" height="200"></canvas>
                                    @else
                                        <p class="text-muted text-center">No data available for this period.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if($bloodGroupStats->count() > 0)
        // Blood Group Chart
        const bloodGroupCtx = document.getElementById('bloodGroupChart').getContext('2d');
        const bloodGroupChart = new Chart(bloodGroupCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($bloodGroupStats->pluck('blood_group')) !!},
                datasets: [{
                    data: {!! json_encode($bloodGroupStats->pluck('total')) !!},
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#FF6384', '#36A2EB'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    @endif

    @if($dailyDonations->count() > 0)
        // Daily Donations Chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const dailyChart = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dailyDonations->pluck('date')) !!},
                datasets: [{
                    label: 'Daily Donations',
                    data: {!! json_encode($dailyDonations->pluck('total')) !!},
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
    @endif
</script>
@endpush
