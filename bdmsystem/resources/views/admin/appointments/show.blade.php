@extends('layouts.admin')

@section('title', 'Appointment Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Appointment Details</h3>
                    <div>
                        <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Appointment Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $appointment->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('F j, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Time:</strong></td>
                                    <td>{{ $appointment->appointment_time }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Location:</strong></td>
                                    <td>{{ $appointment->location }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($appointment->status == 'scheduled')
                                            <span class="badge badge-info">Scheduled</span>
                                        @elseif($appointment->status == 'completed')
                                            <span class="badge badge-success">Completed</span>
                                        @elseif($appointment->status == 'cancelled')
                                            <span class="badge badge-danger">Cancelled</span>
                                        @elseif($appointment->status == 'no_show')
                                            <span class="badge badge-warning">No Show</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($appointment->status ?? 'Unknown') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $appointment->created_at->format('F j, Y g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Updated:</strong></td>
                                    <td>{{ $appointment->updated_at->format('F j, Y g:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Donor Information</h5>
                            @if($appointment->donor && $appointment->donor->user)
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $appointment->donor->user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $appointment->donor->user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $appointment->donor->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Blood Group:</strong></td>
                                    <td>
                                        @if($appointment->donor->bloodGroup)
                                            <span class="badge badge-primary">{{ $appointment->donor->bloodGroup->name }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $appointment->donor->blood_group ?? 'Not Set' }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Age:</strong></td>
                                    <td>{{ $appointment->donor->age ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Gender:</strong></td>
                                    <td>{{ ucfirst($appointment->donor->gender ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>City:</strong></td>
                                    <td>{{ $appointment->donor->city ?? 'N/A' }}</td>
                                </tr>
                            </table>
                            @else
                            <p class="text-muted">Donor information not available</p>
                            @endif
                        </div>
                    </div>
                    
                    @if($appointment->notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Notes</h5>
                            <div class="border p-3 bg-light">
                                {{ $appointment->notes }}
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Actions</h5>
                            <div class="btn-group" role="group">
                                @if($appointment->status != 'completed')
                                <button type="button" class="btn btn-success btn-sm" onclick="updateStatus('completed')">
                                    <i class="fas fa-check"></i> Mark as Completed
                                </button>
                                @endif
                                
                                @if($appointment->status != 'cancelled')
                                <button type="button" class="btn btn-danger btn-sm" onclick="updateStatus('cancelled')">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                @endif
                                
                                @if($appointment->status != 'no_show')
                                <button type="button" class="btn btn-warning btn-sm" onclick="updateStatus('no_show')">
                                    <i class="fas fa-user-times"></i> Mark as No Show
                                </button>
                                @endif
                                
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteAppointment()" style="margin-left: 10px;">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Form -->
<form id="statusForm" method="POST" action="{{ route('admin.appointments.update', $appointment->id) }}" style="display: none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="donor_id" value="{{ $appointment->donor_id }}">
    <input type="hidden" name="appointment_date" value="{{ $appointment->appointment_date }}">
    <input type="hidden" name="appointment_time" value="{{ $appointment->appointment_time }}">
    <input type="hidden" name="location" value="{{ $appointment->location }}">
    <input type="hidden" name="notes" value="{{ $appointment->notes }}">
    <input type="hidden" name="status" id="statusInput">
</form>

<!-- Delete Form -->
<form id="deleteForm" method="POST" action="{{ route('admin.appointments.destroy', $appointment->id) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@section('scripts')
<script>
function updateStatus(status) {
    if (confirm('Are you sure you want to update the appointment status to ' + status + '?')) {
        document.getElementById('statusInput').value = status;
        document.getElementById('statusForm').submit();
    }
}

function deleteAppointment() {
    if (confirm('Are you sure you want to delete this appointment? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endsection
