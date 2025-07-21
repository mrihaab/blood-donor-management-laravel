@extends('admin.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle"></i> Add Blood Inventory
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.inventory.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="blood_group_id" class="form-label">Blood Group *</label>
                                    <select name="blood_group_id" id="blood_group_id" class="form-control" required>
                                        <option value="">Select Blood Group...</option>
                                        @foreach(\App\Models\BloodGroup::all() as $bloodGroup)
                                            <option value="{{ $bloodGroup->id }}" {{ old('blood_group_id') == $bloodGroup->id ? 'selected' : '' }}>
                                                {{ $bloodGroup->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('blood_group_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="units_available" class="form-label">Units Available *</label>
                                    <input type="number" name="units_available" id="units_available" class="form-control" 
                                           min="0" value="{{ old('units_available', 0) }}" required>
                                    @error('units_available')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="units_requested" class="form-label">Units Requested</label>
                                    <input type="number" name="units_requested" id="units_requested" class="form-control" 
                                           min="0" value="{{ old('units_requested', 0) }}">
                                    @error('units_requested')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                    <input type="date" name="expiry_date" id="expiry_date" class="form-control" 
                                           min="{{ date('Y-m-d') }}" value="{{ old('expiry_date') }}">
                                    @error('expiry_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Storage Location</label>
                                    <input type="text" name="location" id="location" class="form-control" 
                                           placeholder="Storage location (e.g., Refrigerator A, Freezer B)" value="{{ old('location', 'Main Storage') }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3" 
                                              placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Info Alert -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Blood units are typically collected from donations. Consider linking this inventory to actual donation records for better tracking.
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Inventory
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add to Inventory
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
