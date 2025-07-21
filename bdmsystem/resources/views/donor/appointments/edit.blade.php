@extends('donor.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-edit"></i> Edit Donation Appointment
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('donor.appointments.update', $appointment->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="appointment_date" class="form-label">Preferred Date *</label>
                                    <input type="date" name="appointment_date" id="appointment_date" class="form-control" 
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                                           value="{{ old('appointment_date', $appointment->appointment_date) }}" required>
                                    @error('appointment_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="appointment_time" class="form-label">Preferred Time *</label>
                                    <select name="appointment_time" id="appointment_time" class="form-control" required>
                                        <option value="">Select Time...</option>
                                        <option value="09:00" {{ old('appointment_time', $appointment->appointment_time) == '09:00' ? 'selected' : '' }}>9:00 AM</option>
                                        <option value="10:00" {{ old('appointment_time', $appointment->appointment_time) == '10:00' ? 'selected' : '' }}>10:00 AM</option>
                                        <option value="11:00" {{ old('appointment_time', $appointment->appointment_time) == '11:00' ? 'selected' : '' }}>11:00 AM</option>
                                        <option value="14:00" {{ old('appointment_time', $appointment->appointment_time) == '14:00' ? 'selected' : '' }}>2:00 PM</option>
                                        <option value="15:00" {{ old('appointment_time', $appointment->appointment_time) == '15:00' ? 'selected' : '' }}>3:00 PM</option>
                                        <option value="16:00" {{ old('appointment_time', $appointment->appointment_time) == '16:00' ? 'selected' : '' }}>4:00 PM</option>
                                    </select>
                                    @error('appointment_time')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Preferred Location</label>
                                    <select name="location" id="location" class="form-control">
                                        <option value="Main Blood Bank" {{ old('location', $appointment->location) == 'Main Blood Bank' ? 'selected' : '' }}>Main Blood Bank</option>
                                        <option value="City Center Branch" {{ old('location', $appointment->location) == 'City Center Branch' ? 'selected' : '' }}>City Center Branch</option>
                                        <option value="Hospital Wing" {{ old('location', $appointment->location) == 'Hospital Wing' ? 'selected' : '' }}>Hospital Wing</option>
                                        <option value="Mobile Unit" {{ old('location', $appointment->location) == 'Mobile Unit' ? 'selected' : '' }}>Mobile Unit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="units_to_donate" class="form-label">Units to Donate *</label>
                                    <select name="units_to_donate" id="units_to_donate" class="form-control" required>
                                        <option value="">Select Units...</option>
                                        <option value="1" {{ old('units_to_donate', $appointment->units_to_donate) == '1' ? 'selected' : '' }}>1 Unit (450ml)</option>
                                        <option value="2" {{ old('units_to_donate', $appointment->units_to_donate) == '2' ? 'selected' : '' }}>2 Units (900ml)</option>
                                    </select>
                                    @error('units_to_donate')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Special Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3" 
                                              placeholder="Any special requirements or notes...">{{ old('notes', $appointment->notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Info Alert -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Please Note:</strong>
                            <ul class="mb-0 mt-2">
                                <li>You must wait at least 56 days between blood donations</li>
                                <li>Please eat a good meal and stay hydrated before your appointment</li>
                                <li>Bring a valid ID and your donor card if you have one</li>
                                <li>Appointments are subject to confirmation by our staff</li>
                            </ul>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('donor.appointments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Appointments
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Update Appointment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
