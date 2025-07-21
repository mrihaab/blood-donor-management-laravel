@extends('donor.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit"></i> Edit My Profile
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('donor.profile.update') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <!-- Personal Information -->
                        <h6 class="text-muted mb-3">Personal Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" name="name" id="name" class="form-control" 
                                           value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" name="email" id="email" class="form-control" 
                                           value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Medical Information -->
                        <hr>
                        <h6 class="text-muted mb-3">Medical Information</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="blood_group_id" class="form-label">Blood Group *</label>
                                    <select name="blood_group_id" id="blood_group_id" class="form-control" required>
                                        <option value="">Select Blood Group...</option>
                                        @foreach($bloodGroups as $bloodGroup)
                                            <option value="{{ $bloodGroup->id }}" 
                                                {{ old('blood_group_id', optional($donor)->blood_group_id) == $bloodGroup->id ? 'selected' : '' }}>
                                                {{ $bloodGroup->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('blood_group_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">Gender *</label>
                                    <select name="gender" id="gender" class="form-control" required>
                                        <option value="">Select Gender...</option>
                                        <option value="male" {{ old('gender', optional($donor)->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', optional($donor)->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', optional($donor)->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth *</label>
                                    <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" 
                                           max="{{ date('Y-m-d', strtotime('-18 years')) }}" 
                                           value="{{ old('date_of_birth', optional($donor)->date_of_birth) }}" required>
                                    @error('date_of_birth')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <hr>
                        <h6 class="text-muted mb-3">Contact Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_number" class="form-label">Contact Number *</label>
                                    <input type="tel" name="contact_number" id="contact_number" class="form-control" 
                                           value="{{ old('contact_number', optional($donor)->contact_number) }}" required>
                                    @error('contact_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City *</label>
                                    <input type="text" name="city" id="city" class="form-control" 
                                           value="{{ old('city', optional($donor)->city) }}" required>
                                    @error('city')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="state" class="form-label">State *</label>
                                    <input type="text" name="state" id="state" class="form-control" 
                                           value="{{ old('state', optional($donor)->state) }}" required>
                                    @error('state')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="zip_code" class="form-label">ZIP Code *</label>
                                    <input type="text" name="zip_code" id="zip_code" class="form-control" 
                                           value="{{ old('zip_code', optional($donor)->zip_code) }}" required>
                                    @error('zip_code')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address *</label>
                                    <textarea name="address" id="address" class="form-control" rows="2" required>{{ old('address', optional($donor)->address) }}</textarea>
                                    @error('address')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Health Information -->
                        <hr>
                        <h6 class="text-muted mb-3">Health Information</h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="health_info" class="form-label">Health Information</label>
                                    <textarea name="health_info" id="health_info" class="form-control" rows="3" 
                                              placeholder="Any medical conditions, allergies, or health notes...">{{ old('health_info', optional($donor)->health_info) }}</textarea>
                                    @error('health_info')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_available" id="is_available" class="form-check-input" 
                                           value="1" {{ old('is_available', optional($donor)->is_available ?? true) ? 'checked' : '' }}>
                                    <label for="is_available" class="form-check-label">
                                        I am available for blood donation
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('donor.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
