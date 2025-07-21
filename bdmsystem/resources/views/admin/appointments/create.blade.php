@extends('admin.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-plus"></i> Schedule New Appointment
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.appointments.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="donor_id" class="form-label">Select Donor *</label>
                                    <select name="donor_id" id="donor_id" class="form-control" required>
                                        <option value="">Choose a donor...</option>
                                        @foreach(\App\Models\Donor::with('user')->get() as $donor)
                                            <option value="{{ $donor->id }}">
                                                {{ $donor->user->name }} ({{ $donor->user->email }})
                                                @if($donor->bloodGroup)
                                                    - {{ $donor->bloodGroup->name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('donor_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="appointment_date" class="form-label">Appointment Date *</label>
                                    <input type="date" name="appointment_date" id="appointment_date" class="form-control" 
                                           min="{{ date('Y-m-d') }}" value="{{ old('appointment_date') }}" required>
                                    @error('appointment_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="appointment_time" class="form-label">Appointment Time *</label>
                                    <input type="time" name="appointment_time" id="appointment_time" class="form-control" 
                                           value="{{ old('appointment_time') }}" required>
                                    @error('appointment_time')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                        <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" name="location" id="location" class="form-control" 
                                           placeholder="Blood donation center location" value="{{ old('location', 'Main Blood Bank') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="units_to_donate" class="form-label">Units to Donate *</label>
                                    <select name="units_to_donate" id="units_to_donate" class="form-control" required>
                                        <option value="">Select Units...</option>
                                        <option value="1" {{ old('units_to_donate') == '1' ? 'selected' : '' }}>1 Unit (450ml)</option>
                                        <option value="2" {{ old('units_to_donate') == '2' ? 'selected' : '' }}>2 Units (900ml)</option>
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
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3" 
                                              placeholder="Any special instructions or notes...">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Appointments
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Schedule Appointment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
