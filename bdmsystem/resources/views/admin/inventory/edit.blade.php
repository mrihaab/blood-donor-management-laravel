@extends('admin.layouts.app')

@section('title', 'Edit Blood Inventory')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Blood Inventory Record</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Inventory
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.inventory.update', $inventory->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="blood_group_id">Blood Group <span class="text-danger">*</span></label>
                                    <select name="blood_group_id" id="blood_group_id" class="form-control @error('blood_group_id') is-invalid @enderror" required>
                                        <option value="">Select Blood Group</option>
                                        @foreach($bloodGroups as $bloodGroup)
                                            <option value="{{ $bloodGroup->id }}" {{ old('blood_group_id', $inventory->blood_group_id) == $bloodGroup->id ? 'selected' : '' }}>
                                                {{ $bloodGroup->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('blood_group_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity (Units) <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                                           value="{{ old('quantity', $inventory->quantity) }}" min="1" required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="collection_date">Collection Date <span class="text-danger">*</span></label>
                                    <input type="date" name="collection_date" id="collection_date" class="form-control @error('collection_date') is-invalid @enderror" 
                                           value="{{ old('collection_date', $inventory->collection_date ? $inventory->collection_date->format('Y-m-d') : '') }}" required>
                                    @error('collection_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date <span class="text-danger">*</span></label>
                                    <input type="date" name="expiry_date" id="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                           value="{{ old('expiry_date', $inventory->expiry_date ? $inventory->expiry_date->format('Y-m-d') : '') }}" required>
                                    @error('expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location">Storage Location <span class="text-danger">*</span></label>
                                    <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" 
                                           value="{{ old('location', $inventory->location) }}" placeholder="e.g., Refrigerator A-1" required>
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="">Select Status</option>
                                        <option value="available" {{ old('status', $inventory->status) == 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="reserved" {{ old('status', $inventory->status) == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                        <option value="expired" {{ old('status', $inventory->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                                        <option value="used" {{ old('status', $inventory->status) == 'used' ? 'selected' : '' }}>Used</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="donor_id">Donor (Optional)</label>
                                    <select name="donor_id" id="donor_id" class="form-control @error('donor_id') is-invalid @enderror">
                                        <option value="">Select Donor (Optional)</option>
                                        @foreach($donors as $donor)
                                            <option value="{{ $donor->id }}" {{ old('donor_id', $inventory->donor_id) == $donor->id ? 'selected' : '' }}>
                                                {{ $donor->user->name }} ({{ $donor->bloodGroup->name ?? $donor->blood_group }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('donor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Inventory Record
                            </button>
                            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto-set expiry date based on collection date (blood typically expires after 42 days)
    document.getElementById('collection_date').addEventListener('change', function() {
        const collectionDate = new Date(this.value);
        if (collectionDate) {
            const expiryDate = new Date(collectionDate);
            expiryDate.setDate(expiryDate.getDate() + 42); // 42 days shelf life
            document.getElementById('expiry_date').value = expiryDate.toISOString().split('T')[0];
        }
    });
</script>
@endsection
