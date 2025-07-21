@extends('admin.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Reports Dashboard</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Donor Report -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-users"></i> Donor Report
                                    </h5>
                                    <p class="card-text">Generate comprehensive donor reports with filtering options.</p>
                                    <a href="{{ route('admin.reports.donors') }}" class="btn btn-primary">
                                        View Report
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Donation Report -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-tint"></i> Donation Report
                                    </h5>
                                    <p class="card-text">Track donation history and statistics over time.</p>
                                    <a href="{{ route('admin.reports.donations') }}" class="btn btn-primary">
                                        View Report
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Inventory Report -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-box"></i> Inventory Report
                                    </h5>
                                    <p class="card-text">Monitor blood inventory levels and low stock alerts.</p>
                                    <a href="{{ route('admin.reports.inventory') }}" class="btn btn-primary">
                                        View Report
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Stats -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-bar"></i> Monthly Statistics
                                    </h5>
                                    <p class="card-text">View monthly performance metrics and trends.</p>
                                    <a href="{{ route('admin.reports.monthly-stats') }}" class="btn btn-primary">
                                        View Report
                                    </a>
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
