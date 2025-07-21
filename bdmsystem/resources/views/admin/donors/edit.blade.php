@extends('admin.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit"></i> Edit Donor
                    </h5>
                    <a href="{{ route('admin.donors.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.donors.update', $donor->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Personal Information -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Personal Information</h6>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $donor->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $donor->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $donor->phone) }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                                   id="date_of_birth" name="date_of_birth" 
                                                   value="{{ old('date_of_birth', $donor->date_of_birth ? $donor->date_of_birth->format('Y-m-d') : '') }}" required>
                                            @error('date_of_birth')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                            <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                                <option value="">Select Gender</option>
                                                <option value="male" {{ old('gender', $donor->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ old('gender', $donor->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="other" {{ old('gender', $donor->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                            @error('gender')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Medical Information -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Medical Information</h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="blood_group" class="form-label">Blood Group <span class="text-danger">*</span></label>
                                            <select class="form-control @error('blood_group') is-invalid @enderror" id="blood_group" name="blood_group" required>
                                                <option value="">Select Blood Group</option>
                                                <option value="A+" {{ old('blood_group', $donor->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
                                                <option value="A-" {{ old('blood_group', $donor->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
                                                <option value="B+" {{ old('blood_group', $donor->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
                                                <option value="B-" {{ old('blood_group', $donor->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
                                                <option value="AB+" {{ old('blood_group', $donor->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                                <option value="AB-" {{ old('blood_group', $donor->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                                <option value="O+" {{ old('blood_group', $donor->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
                                                <option value="O-" {{ old('blood_group', $donor->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>
                                            </select>
                                            @error('blood_group')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="weight" class="form-label">Weight (kg)</label>
                                            <input type="number" class="form-control @error('weight') is-invalid @enderror" 
                                                   id="weight" name="weight" value="{{ old('weight', $donor->weight) }}" min="40">
                                            @error('weight')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="medical_conditions" class="form-label">Medical Conditions</label>
                                    <textarea class="form-control @error('medical_conditions') is-invalid @enderror" 
                                              id="medical_conditions" name="medical_conditions" rows="3" 
                                              placeholder="List any medical conditions, medications, or health concerns">{{ old('medical_conditions', $donor->medical_conditions) }}</textarea>
                                    @error('medical_conditions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="last_donation_date" class="form-label">Last Donation Date</label>
                                    <input type="date" class="form-control @error('last_donation_date') is-invalid @enderror" 
                                           id="last_donation_date" name="last_donation_date" 
                                           value="{{ old('last_donation_date', $donor->last_donation_date ? $donor->last_donation_date->format('Y-m-d') : '') }}">
                                    @error('last_donation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3">Location Information</h6>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city', $donor->city) }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                           id="state" name="state" value="{{ old('state', $donor->state) }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="zip_code" class="form-label">ZIP Code</label>
                                    <input type="text" class="form-control @error('zip_code') is-invalid @enderror" 
                                           id="zip_code" name="zip_code" value="{{ old('zip_code', $donor->zip_code) }}">
                                    @error('zip_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Full Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3" 
                                              placeholder="Enter complete address">{{ old('address', $donor->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Status and Notes -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="active" {{ old('status', $donor->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $donor->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="suspended" {{ old('status', $donor->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                        <option value="deferred" {{ old('status', $donor->status) == 'deferred' ? 'selected' : '' }}>Deferred</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="emergency_contact" class="form-label">Emergency Contact</label>
                                    <input type="tel" class="form-control @error('emergency_contact') is-invalid @enderror" 
                                           id="emergency_contact" name="emergency_contact" 
                                           value="{{ old('emergency_contact', $donor->emergency_contact) }}"
                                           placeholder="Emergency contact number">
                                    @error('emergency_contact')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Admin Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3" 
                                              placeholder="Internal notes about the donor (not visible to donor)">{{ old('notes', $donor->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Donor
                                        </button>
                                        <a href="{{ route('admin.donors.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </div>
                                    <div>
                                        <small class="text-muted">
                                            Last updated: {{ $donor->updated_at->format('M d, Y H:i') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Donation History -->
            @if($donor->donations->count() > 0)
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-history"></i> Donation History
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Blood Group</th>
                                    <th>Units</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($donor->donations->take(5) as $donation)
                                <tr>
                                    <td>{{ $donation->donation_date->format('M d, Y') }}</td>
                                    <td><span class="badge bg-primary">{{ $donation->blood_group }}</span></td>
                                    <td>{{ $donation->units }} unit(s)</td>
                                    <td>
                                        <span class="badge bg-{{ $donation->status == 'completed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($donation->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $donation->collection_center ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($donor->donations->count() > 5)
                        <div class="text-center">
                            <small class="text-muted">Showing 5 recent donations. Total: {{ $donor->donations->count() }}</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Calculate age from date of birth
    $('#date_of_birth').on('change', function() {
        const dob = new Date($(this).val());
        const today = new Date();
        const age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
        
        if (age < 18) {
            alert('Donor must be at least 18 years old.');
            $(this).val('');
        } else if (age > 65) {
            if (!confirm('Donor is over 65 years old. Are you sure you want to continue?')) {
                $(this).val('');
            }
        }
    });

    // Weight validation
    $('#weight').on('change', function() {
        const weight = parseInt($(this).val());
        if (weight && weight < 50) {
            alert('Minimum weight requirement is 50 kg for blood donation.');
        }
    });
});
</script>
@endpush
