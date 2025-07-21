@extends('donor.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-hand-holding-medical"></i> My Blood Requests
                    </h5>
                    <a href="{{ route('donor.blood_requests.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> New Request
                    </a>
                </div>
                <div class="card-body">
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
                                        <th>Status</th>
                                        <th>Submitted Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $request)
                                        <tr>
                                            <td><strong>#{{ $request->id }}</strong></td>
                                            <td>{{ $request->patient_name }}</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $request->blood_group }}</span>
                                            </td>
                                            <td>{{ $request->hospital }}</td>
                                            <td>{{ $request->city }}</td>
                                            <td>
                                                @php
                                                    $statusClass = [
                                                        'pending' => 'warning',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        'fulfilled' => 'info'
                                                    ][$request->status] ?? 'secondary';
                                                    
                                                    $statusText = [
                                                        'pending' => 'Pending Review',
                                                        'approved' => 'Approved - Processing',
                                                        'rejected' => 'Rejected',
                                                        'fulfilled' => 'Blood Dispensed - Ready for Collection'
                                                    ][$request->status] ?? ucfirst($request->status);
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">
                                                    {{ $statusText }}
                                                </span>
                                            </td>
                                            <td>{{ $request->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 text-muted">No Blood Requests</h4>
                            <p class="text-muted">You haven't submitted any blood requests yet.</p>
                            <a href="{{ route('donor.blood_requests.create') }}" class="btn btn-danger">
                                <i class="fas fa-plus"></i> Submit Blood Request
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
