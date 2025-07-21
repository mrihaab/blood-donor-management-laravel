@extends('admin.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tint"></i> Record New Donation
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.donations.store') }}" method="POST" id="donationForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="donor_id" class="form-label">Select Donor *</label>
                                    <select name="donor_id" id="donor_id" class="form-control" required>
                                        <option value="">Choose a donor...</option>
                                        @foreach($donors as $donor)
                                            <option value="{{ $donor->id }}" data-blood-group="{{ $donor->bloodGroup->name ?? 'Unknown' }}">
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
                                           max="{{ date('Y-m-d') }}" value="{{ old('donation_date', date('Y-m-d')) }}" required>
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
                                        <option value="1" {{ old('units') == 1 ? 'selected' : '' }}>1 Unit (450ml)</option>
                                        <option value="2" {{ old('units') == 2 ? 'selected' : '' }}>2 Units (900ml)</option>
                                    </select>
                                    @error('units')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Donor Eligibility</label>
                                    <div id="eligibilityStatus" class="p-2 border rounded bg-light">
                                        <span class="text-muted">Please select a donor to check eligibility</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3" 
                                              placeholder="Any special notes about this donation...">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Info Alert -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Recording a donation will automatically update the blood inventory for the donor's blood group.
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.donations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Donations
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save"></i> Record Donation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('donor_id').addEventListener('change', function() {
    const donorId = this.value;
    const statusDiv = document.getElementById('eligibilityStatus');
    const submitBtn = document.getElementById('submitBtn');
    
    if (!donorId) {
        statusDiv.innerHTML = '<span class="text-muted">Please select a donor to check eligibility</span>';
        submitBtn.disabled = false;
        return;
    }
    
    // Show loading
    statusDiv.innerHTML = '<span class="text-info"><i class="fas fa-spinner fa-spin"></i> Checking eligibility...</span>';
    
    // Check eligibility via AJAX
    fetch('{{ route("admin.donations.check_eligibility") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ donor_id: donorId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.eligible) {
            statusDiv.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> ' + data.message + '</span>';
            submitBtn.disabled = false;
        } else {
            statusDiv.innerHTML = '<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> ' + data.message + '</span>';
            submitBtn.disabled = true;
        }
        
        if (data.last_donation) {
            statusDiv.innerHTML += '<br><small class="text-muted">Last donation: ' + data.last_donation + '</small>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        statusDiv.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Error checking eligibility</span>';
        submitBtn.disabled = false; // Allow submission on error
    });
});
</script>
@endpush
