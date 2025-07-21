@extends('donor.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> My Appointments
                    </h5>
                    <a href="{{ route('donor.appointments.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Book Appointment
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($appointments) && $appointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Appointment ID</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Location</th>
                                        <th>Units to Donate</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($appointments as $appointment)
                                        <tr>
                                            <td><strong>#{{ $appointment->id }}</strong></td>
                                            <td>{{ $appointment->appointment_date }}</td>
                                            <td>{{ $appointment->appointment_time ?? 'N/A' }}</td>
                                            <td>{{ $appointment->location ?? 'Main Center' }}</td>
                                            <td><span class="badge bg-info">{{ $appointment->units_to_donate ?? 1 }} Unit(s)</span></td>
                                            <td>
                                                @php
                                                    $statusClass = [
                                                        'scheduled' => 'primary',
                                                        'confirmed' => 'success',
                                                        'completed' => 'info',
                                                        'cancelled' => 'danger',
                                                        'no_show' => 'warning'
                                                    ][$appointment->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">
                                                    {{ ucfirst($appointment->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($appointment->status === 'scheduled')
                                                    <a href="{{ route('donor.appointments.edit', $appointment->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                @else
                                                    <small class="text-muted">No actions</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 text-muted">No Appointments</h4>
                            <p class="text-muted">You haven't scheduled any appointments yet.</p>
                            <a href="{{ route('donor.appointments.create') }}" class="btn btn-primary">
                                <i class="fas fa-calendar-plus"></i> Book Your First Appointment
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
