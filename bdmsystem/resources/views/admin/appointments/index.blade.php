@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>📅 Manage Appointments</h2>
                <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">➕ Schedule New Appointment</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    ✅ {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📋 Appointments List</h5>
                </div>
                <div class="card-body">
                    @if($appointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>👤 Donor</th>
                                        <th>🩸 Blood Group</th>
                                        <th>📅 Date</th>
                                        <th>⏰ Time</th>
                                        <th>📍 Location</th>
                                        <th>💧 Units</th>
                                        <th>📊 Status</th>
                                        <th>🔧 Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($appointments as $index => $appointment)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $appointment->donor->user->name }}</strong><br>
                                                <small class="text-muted">{{ $appointment->donor->user->email }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">{{ $appointment->donor->bloodGroup->name }}</span>
                                            </td>
                                            <td>{{ $appointment->appointment_date->format('M d, Y') }}</td>
                                            <td>{{ $appointment->appointment_time->format('H:i A') }}</td>
                                            <td>{{ $appointment->location }}</td>
                                            <td><span class="badge bg-info">{{ $appointment->units_to_donate ?? 1 }} Unit(s)</span></td>
                                            <td>
                                                @switch($appointment->status)
                                                    @case('scheduled')
                                                        <span class="badge bg-info">🕐 Scheduled</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-success">✅ Completed</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge bg-danger">❌ Cancelled</span>
                                                        @break
                                                    @case('no_show')
                                                        <span class="badge bg-warning">🚫 No Show</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($appointment->status === 'scheduled')
                                                    <div class="btn-group" role="group">
                                                        <!-- Quick Actions for Scheduled Appointments -->
                                                        <form method="POST" action="{{ route('admin.appointments.mark_completed', $appointment->id) }}" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark this appointment as completed?')">
                                                                ✅ Done
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.appointments.mark_cancelled', $appointment->id) }}" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Cancel this appointment?')">
                                                                ❌ Cancel
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.appointments.mark_no_show', $appointment->id) }}" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Mark as no show?')">
                                                                🚫 No Show
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <div class="btn-group mt-1" role="group">
                                                        <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-sm btn-info">👁️ View</a>
                                                        <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn btn-sm btn-secondary">✏️ Edit</a>
                                                    </div>
                                                @else
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-sm btn-info">👁️ View</a>
                                                        <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
                                                        <form method="POST" action="{{ route('admin.appointments.destroy', $appointment->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">🗑️ Delete</button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <h4 class="text-muted">🚫 No appointments found</h4>
                            <p class="text-muted">Schedule the first appointment!</p>
                            <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">➕ Schedule First Appointment</a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3>{{ $appointments->where('status', 'scheduled')->count() }}</h3>
                            <p>Scheduled</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3>{{ $appointments->where('status', 'completed')->count() }}</h3>
                            <p>Completed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h3>{{ $appointments->where('status', 'cancelled')->count() }}</h3>
                            <p>Cancelled</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3>{{ $appointments->where('status', 'no_show')->count() }}</h3>
                            <p>No Show</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
