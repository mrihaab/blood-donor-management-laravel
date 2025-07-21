@extends('admin.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tint"></i> Donation Details #{{ $donation->id }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Donor Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $donation->donor->user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $donation->donor->user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Blood Group:</strong></td>
                                    <td><span class="badge bg-danger">{{ $donation->donor->bloodGroup->name }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Contact:</strong></td>
                                    <td>{{ $donation->donor->contact_number ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Donation Details</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Donation Date:</strong></td>
                                    <td>{{ $donation->donation_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Units Donated:</strong></td>
                                    <td>{{ $donation->units ?? 1 }} Unit(s)</td>
                                </tr>
                                <tr>
                                    <td><strong>Quantity:</strong></td>
                                    <td>{{ $donation->quantity }}ml</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @switch($donation->status)
                                            @case('completed')
                                                <span class="badge bg-success">✅ Completed</span>
                                                @break
                                            @case('dispensed')
                                                <span class="badge bg-info">💉 Dispensed</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger">❌ Cancelled</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $donation->status }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Collection Center:</strong></td>
                                    <td>{{ $donation->collection_center ?? 'Main Blood Bank' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($donation->notes)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h6>Notes</h6>
                                <div class="alert alert-info">
                                    {{ $donation->notes }}
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.donations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Donations
                        </a>
                        <div>
                            <a href="{{ route('admin.donations.edit', $donation->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('admin.donations.destroy', $donation->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this donation?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
