@extends('donor.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> My Donation History
                    </h5>
                </div>
                <div class="card-body">
                    @if($donations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Blood Group</th>
                                        <th>Units Donated</th>
                                        <th>Status</th>
                                        <th>Location</th>
                                        <th>Next Eligible Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($donations as $donation)
                                        <tr>
                                            <td>{{ $donation->donation_date ?? $donation->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    {{ $donation->bloodGroup->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>{{ $donation->units ?? 1 }} unit(s)</td>
                                            <td>
                                                @php
                                                    $status = $donation->status ?? 'completed';
                                                    $badgeClass = [
                                                        'completed' => 'success',
                                                        'dispensed' => 'info',
                                                        'pending' => 'warning',
                                                        'cancelled' => 'danger'
                                                    ][$status] ?? 'secondary';
                                                    
                                                    $statusText = [
                                                        'completed' => 'Donated',
                                                        'dispensed' => 'Dispensed to Patient',
                                                        'pending' => 'Pending',
                                                        'cancelled' => 'Cancelled'
                                                    ][$status] ?? ucfirst($status);
                                                @endphp
                                                <span class="badge bg-{{ $badgeClass }}">
                                                    {{ $statusText }}
                                                </span>
                                            </td>
                                            <td>{{ $donation->location ?? 'Blood Bank' }}</td>
                                            <td>
                                                @php
                                                    $donationDate = $donation->donation_date ?? $donation->created_at;
                                                    $nextDate = date('M d, Y', strtotime($donationDate . ' + 56 days'));
                                                @endphp
                                                {{ $nextDate }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> You can donate blood again after 56 days from your last donation as per WHO guidelines.
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-heart text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 text-muted">No Donation History</h4>
                            <p class="text-muted">You haven't made any blood donations yet. Start saving lives today!</p>
                            <a href="{{ route('donor.appointments.create') }}" class="btn btn-danger">
                                <i class="fas fa-calendar-plus"></i> Schedule Donation
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
