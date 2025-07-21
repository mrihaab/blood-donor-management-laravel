@extends('donor.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus"></i> Submit Blood Request
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('donor.blood_requests.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="patient_name" class="form-label">Patient Name *</label>
                                    <input type="text" name="patient_name" id="patient_name" class="form-control" 
                                           value="{{ old('patient_name') }}" required>
                                    @error('patient_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="blood_group" class="form-label">Blood Group Needed *</label>
                                    <select name="blood_group" id="blood_group" class="form-control" required>
                                        <option value="">Select Blood Group...</option>
                                        <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                        <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                                    </select>
                                    @error('blood_group')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="hospital" class="form-label">Hospital Name *</label>
                                    <input type="text" name="hospital" id="hospital" class="form-control" 
                                           value="{{ old('hospital') }}" required>
                                    @error('hospital')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City *</label>
                                    <input type="text" name="city" id="city" class="form-control" 
                                           value="{{ old('city') }}" required>
                                    @error('city')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="units_needed" class="form-label">Units Required *</label>
                                    <select name="units_needed" id="units_needed" class="form-control" required>
                                        <option value="">Select Units...</option>
                                        <option value="1" {{ old('units_needed') == '1' ? 'selected' : '' }}>1 Unit (450ml)</option>
                                        <option value="2" {{ old('units_needed') == '2' ? 'selected' : '' }}>2 Units (900ml)</option>
                                        <option value="3" {{ old('units_needed') == '3' ? 'selected' : '' }}>3 Units (1350ml)</option>
                                        <option value="4" {{ old('units_needed') == '4' ? 'selected' : '' }}>4 Units (1800ml)</option>
                                    </select>
                                    @error('units_needed')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Reason for Blood Request</label>
                                    <textarea name="reason" id="reason" class="form-control" rows="4" 
                                              placeholder="Please describe the medical reason for this blood request...">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Info Alert -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Your blood request will be reviewed by our medical team. You will be notified once it's approved and donors are contacted.
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('donor.blood_requests.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Requests
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
