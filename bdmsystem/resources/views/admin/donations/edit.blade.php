@extends('admin.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit"></i> Edit Donation #{{ $donation->id }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.donations.update', $donation->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="donor_id" class="form-label">Select Donor *</label>
                                    <select name="donor_id" id="donor_id" class="form-control" required>
                                        @foreach($donors as $donor)
                                            <option value="{{ $donor->id }}" {{ $donation->donor_id == $donor->id ? 'selected' : '' }}>
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
                                    <label for="donation_date" class="form-label">Donation Date *</label>
                                    <input type="date" name="donation_date" id="donation_date" class="form-control" 
                                           max="{{ date('Y-m-d') }}" value="{{ old('donation_date', $donation->donation_date) }}" required>
                                    @error('donation_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="units" class="form-label">Units Donated *</label>
                                    <select name="units" id="units" class="form-control" required>
                                        <option value="">Select units...</option>
                                        <option value="1" {{ old('units', $donation->units) == 1 ? 'selected' : '' }}>1 Unit (450ml)</option>
                                        <option value="2" {{ old('units', $donation->units) == 2 ? 'selected' : '' }}>2 Units (900ml)</option>
                                    </select>
                                    @error('units')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="completed" {{ old('status', $donation->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="dispensed" {{ old('status', $donation->status) == 'dispensed' ? 'selected' : '' }}>Dispensed</option>
                                        <option value="cancelled" {{ old('status', $donation->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
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
                                              placeholder="Any special notes about this donation...">{{ old('notes', $donation->notes) }}</textarea>
                                    @error('notes')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.donations.show', $donation->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Details
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Update Donation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
