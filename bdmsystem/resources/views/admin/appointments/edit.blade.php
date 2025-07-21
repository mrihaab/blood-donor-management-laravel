@extends('layouts.admin')

@section('title', 'Edit Appointment')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Edit Appointment</h3>
                    <div>
                        <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.appointments.update', $appointment->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="donor_id">Donor <span class="text-danger">*</span></label>
                                    <select name="donor_id" id="donor_id" class="form-control @error('donor_id') is-invalid @enderror" required>
                                        <option value="">Select Donor</option>
                                        @foreach($donors as $donor)
                                            <option value="{{ $donor->id }}" 
                                                {{ old('donor_id', $appointment->donor_id) == $donor->id ? 'selected' : '' }}>
                                                {{ $donor->user->name }} - {{ $donor->user->email }}
                                                @if($donor->bloodGroup) ({{ $donor->bloodGroup->name }}) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('donor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="appointment_date">Appointment Date <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           name="appointment_date" 
                                           id="appointment_date" 
                                           class="form-control @error('appointment_date') is-invalid @enderror" 
                                           value="{{ old('appointment_date', $appointment->appointment_date) }}" 
                                           required>
                                    @error('appointment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="appointment_time">Appointment Time <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           name="appointment_time" 
                                           id="appointment_time" 
                                           class="form-control @error('appointment_time') is-invalid @enderror" 
                                           value="{{ old('appointment_time', $appointment->appointment_time) }}" 
                                           required>
                                    @error('appointment_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location">Location <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="location" 
                                           id="location" 
                                           class="form-control @error('location') is-invalid @enderror" 
                                           value="{{ old('location', $appointment->location) }}" 
                                           placeholder="Enter appointment location"
                                           required>
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="">Select Status</option>
                                        <option value="scheduled" {{ old('status', $appointment->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                        <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        <option value="no_show" {{ old('status', $appointment->status) == 'no_show' ? 'selected' : '' }}>No Show</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="units_to_donate">Units to Donate <span class="text-danger">*</span></label>
                                    <select name="units_to_donate" id="units_to_donate" class="form-control @error('units_to_donate') is-invalid @enderror" required>
                                        <option value="">Select Units...</option>
                                        <option value="1" {{ old('units_to_donate', $appointment->units_to_donate) == '1' ? 'selected' : '' }}>1 Unit (450ml)</option>
                                        <option value="2" {{ old('units_to_donate', $appointment->units_to_donate) == '2' ? 'selected' : '' }}>2 Units (900ml)</option>
                                    </select>
                                    @error('units_to_donate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" 
                                              id="notes" 
                                              class="form-control @error('notes') is-invalid @enderror" 
                                              rows="4" 
                                              placeholder="Enter any additional notes or comments about the appointment">{{ old('notes', $appointment->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Appointment
                                    </button>
                                    <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <button type="button" class="btn btn-danger float-right" onclick="deleteAppointment()">
                                        <i class="fas fa-trash"></i> Delete Appointment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" action="{{ route('admin.appointments.destroy', $appointment->id) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@section('scripts')
<script>
function deleteAppointment() {
    if (confirm('Are you sure you want to delete this appointment? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}

// Set minimum date to today
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('appointment_date');
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);
});
</script>
@endsection
