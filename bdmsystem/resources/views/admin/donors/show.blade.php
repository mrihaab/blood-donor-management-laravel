@extends('admin.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user"></i> Donor Details: {{ $donor->name }}
                    </h5>
                    <div>
                        <a href="{{ route('admin.donors.edit', $donor->id) }}" class="btn btn-light btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.donors.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Personal Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Full Name:</strong></td>
                                    <td>{{ $donor->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $donor->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $donor->status == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($donor->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Registered:</strong></td>
                                    <td>{{ $donor->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Medical Information -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Medical Information</h6>
                            @if($donor->donor)
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Blood Group:</strong></td>
                                        <td>
                                            @if($donor->donor->bloodGroup)
                                                <span class="badge bg-danger">{{ $donor->donor->bloodGroup->name }}</span>
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Gender:</strong></td>
                                        <td>{{ ucfirst($donor->donor->gender ?? 'Not specified') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date of Birth:</strong></td>
                                        <td>{{ $donor->donor->date_of_birth ?? 'Not specified' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Available for Donation:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $donor->donor->is_available ? 'success' : 'warning' }}">
                                                {{ $donor->donor->is_available ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Donor profile not completed yet.
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($donor->donor)
                        <hr>
                        <div class="row">
                            <!-- Contact Information -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Contact Information</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td>{{ $donor->donor->contact_number ?? 'Not provided' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Address:</strong></td>
                                        <td>{{ $donor->donor->address ?? 'Not provided' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>City:</strong></td>
                                        <td>{{ $donor->donor->city ?? 'Not provided' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>State:</strong></td>
                                        <td>{{ $donor->donor->state ?? 'Not provided' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ZIP Code:</strong></td>
                                        <td>{{ $donor->donor->zip_code ?? 'Not provided' }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Health Information -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Health Information</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Health Notes:</strong></td>
                                        <td>{{ $donor->donor->health_info ?? 'No health notes provided' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Last Donation:</strong></td>
                                        <td>{{ $donor->donor->last_donation_date ?? 'No donations yet' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Donation History -->
                    <hr>
                    <h6 class="text-muted mb-3">Donation History</h6>
                    @if($donor->donor && $donor->donor->donations && $donor->donor->donations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Blood Group</th>
                                        <th>Units</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($donor->donor->donations as $donation)
                                        <tr>
                                            <td>{{ $donation->donation_date ?? $donation->created_at->format('M d, Y') }}</td>
                                            <td>
                                                @if($donation->bloodGroup)
                                                    <span class="badge bg-primary">{{ $donation->bloodGroup->name }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>{{ $donation->units ?? 1 }} unit(s)</td>
                                            <td>
                                                <span class="badge bg-{{ $donation->status == 'completed' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($donation->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            This donor has not made any donations yet.
                        </div>
                    @endif

                    <!-- Quick Actions -->
                    <hr>
                    <div class="d-flex gap-2 flex-wrap">
                        @if($donor->donor)
                            <a href="{{ route('admin.donations.create') }}?donor_id={{ $donor->donor->id }}" class="btn btn-success btn-sm">
                                <i class="fas fa-tint"></i> Record Donation
                            </a>
                            <a href="{{ route('admin.appointments.create') }}?donor_id={{ $donor->donor->id }}" class="btn btn-info btn-sm">
                                <i class="fas fa-calendar-plus"></i> Schedule Appointment
                            </a>
                        @endif
                        
                        <form method="POST" action="{{ route('admin.donors.toggle_status', $donor->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-{{ $donor->status == 'active' ? 'warning' : 'success' }} btn-sm">
                                <i class="fas fa-{{ $donor->status == 'active' ? 'pause' : 'play' }}"></i>
                                {{ $donor->status == 'active' ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
