@extends('admin.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-hand-holding-medical"></i> Blood Requests Management
                    </h5>
                    <span class="badge bg-light text-dark">{{ $requests->count() }} Total Requests</span>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by city, patient, reason..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="blood_group" class="form-control">
                                    <option value="">All Blood Groups</option>
                                    <option value="A+" {{ request('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                                    <option value="A-" {{ request('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                                    <option value="B+" {{ request('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                                    <option value="B-" {{ request('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                                    <option value="AB+" {{ request('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                    <option value="AB-" {{ request('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                    <option value="O+" {{ request('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                                    <option value="O-" {{ request('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="fulfilled" {{ request('status') == 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.blood_requests.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-refresh"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Requests Table -->
                    @if($requests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Patient Name</th>
                                        <th>Blood Group</th>
                                        <th>Hospital</th>
                                        <th>City</th>
                                        <th>Units Needed</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Requested Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $request)
                                        <tr>
                                            <td>
                                                <strong>#{{ $request->id }}</strong>
                                            </td>
                                            <td>{{ $request->patient_name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $request->blood_group }}</span>
                                            </td>
                                            <td>{{ $request->hospital ?? 'N/A' }}</td>
                                            <td>{{ $request->city }}</td>
                                            <td>{{ $request->units_needed ?? 'N/A' }}</td>
                                            <td>
                                                <small>{{ Str::limit($request->reason ?? 'Emergency', 50) }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = [
                                                        'pending' => 'warning',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        'fulfilled' => 'info'
                                                    ][$request->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $request->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                @if($request->status === 'pending')
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <form method="POST" action="{{ route('admin.blood_requests.approve', $request->id) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm" 
                                                                    onclick="return confirm('Approve this blood request? This will update the inventory.')">
                                                                <i class="fas fa-check"></i> Approve
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.blood_requests.reject', $request->id) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                                    onclick="return confirm('Reject this blood request?')">
                                                                <i class="fas fa-times"></i> Reject
                                                            </button>
                                                        </form>
                                                        <button type="button" class="btn btn-warning btn-sm" 
                                                                onclick="showAssignDonorModal({{ $request->id }}, '{{ $request->blood_group }}')">
                                                            <i class="fas fa-user-plus"></i> Assign Donor
                                                        </button>
                                                        <form method="POST" action="{{ route('admin.blood_requests.notify_donors', $request->id) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-info btn-sm" 
                                                                    onclick="return confirm('Notify all eligible donors in {{ $request->city }}?')">
                                                                <i class="fas fa-bell"></i> Notify Donors
                                                            </button>
                                                        </form>
                                                    </div>
                                                @elseif($request->status === 'approved')
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <span class="badge bg-success">Approved - Ready for Processing</span>
                                                    </div>
                                                @else
                                                    <small class="text-muted">No actions available</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 text-muted">No Blood Requests Found</h4>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'blood_group', 'status']))
                                    No requests match your current filters. Try adjusting your search criteria.
                                @else
                                    No blood requests have been submitted yet.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Donor Modal -->
<div class="modal fade" id="assignDonorModal" tabindex="-1" aria-labelledby="assignDonorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignDonorModalLabel">Assign Donor to Blood Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignDonorForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="donor_id" class="form-label">Select Donor</label>
                        <select name="donor_id" id="donor_id" class="form-control" required>
                            <option value="">Choose a donor...</option>
                            @foreach(App\Models\Donor::with(['user', 'bloodGroup'])->where('status', 'active')->get() as $donor)
                                <option value="{{ $donor->id }}" data-blood-group="{{ $donor->bloodGroup->name ?? 'Unknown' }}">
                                    {{ $donor->user->name ?? 'Unknown' }} - {{ $donor->bloodGroup->name ?? 'Unknown' }} ({{ $donor->city }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Only donors with matching blood group will be shown after filtering.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Assign Donor</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showAssignDonorModal(requestId, bloodGroup) {
    const modal = new bootstrap.Modal(document.getElementById('assignDonorModal'));
    const form = document.getElementById('assignDonorForm');
    const donorSelect = document.getElementById('donor_id');
    
    // Set form action
    form.action = `/admin/blood-requests/${requestId}/assign-donor`;
    
    // Filter donors by blood group
    Array.from(donorSelect.options).forEach(option => {
        if (option.value === '') return; // Keep the default option
        
        const donorBloodGroup = option.getAttribute('data-blood-group');
        if (donorBloodGroup === bloodGroup) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Reset selection
    donorSelect.value = '';
    
    // Show modal
    modal.show();
}

</script>
@endpush
