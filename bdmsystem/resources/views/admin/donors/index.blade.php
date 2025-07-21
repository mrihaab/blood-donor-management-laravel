@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>👥 Manage Donors</h2>
                <a href="{{ route('admin.donors.create') }}" class="btn btn-primary">➕ Add New Donor</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    ✅ {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📋 Donors List</h5>
                </div>
                <div class="card-body">
                    @if($donors->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>👤 Name</th>
                                        <th>📧 Email</th>
                                        <th>🩸 Blood Group</th>
                                        <th>📱 Contact</th>
                                        <th>🏙️ City</th>
                                        <th>📅 Last Donation</th>
                                        <th>🔧 Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($donors as $index => $donor)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $donor->name }}</strong>
                                                @if($donor->donor && $donor->donor->is_available)
                                                    <span class="badge bg-success ms-1">Available</span>
                                                @else
                                                    <span class="badge bg-secondary ms-1">Unavailable</span>
                                                @endif
                                            </td>
                                            <td>{{ $donor->email }}</td>
                                            <td>
                                                @if($donor->donor && $donor->donor->bloodGroup)
                                                    <span class="badge bg-danger">{{ $donor->donor->bloodGroup->name }}</span>
                                                @else
                                                    <span class="text-muted">Not set</span>
                                                @endif
                                            </td>
                                            <td>{{ $donor->donor->contact_number ?? 'N/A' }}</td>
                                            <td>{{ $donor->donor->city ?? 'N/A' }}</td>
                                            <td>
                                                @if($donor->donor && $donor->donor->last_donation_date)
                                                    @php
                                                        $lastDonationDate = $donor->donor->last_donation_date;
                                                        if (is_string($lastDonationDate)) {
                                                            $lastDonationDate = \Carbon\Carbon::parse($lastDonationDate);
                                                        }
                                                    @endphp
                                                    {{ $lastDonationDate->format('M d, Y') }}
                                                @else
                                                    <span class="text-muted">Never</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.donors.show', $donor->id) }}" class="btn btn-sm btn-info">👁️ View</a>
                                                    <a href="{{ route('admin.donors.edit', $donor->id) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
                                                    <form method="POST" action="{{ route('admin.donors.destroy', $donor->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this donor?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">🗑️ Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <h4 class="text-muted">🚫 No donors found</h4>
                            <p class="text-muted">Start by adding your first donor!</p>
                            <a href="{{ route('admin.donors.create') }}" class="btn btn-primary">➕ Add First Donor</a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3>{{ $donors->count() }}</h3>
                            <p>Total Donors</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3>{{ $donors->where('donor.is_available', true)->count() }}</h3>
                            <p>Available Donors</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3>{{ $donors->whereNotNull('donor.last_donation_date')->count() }}</h3>
                            <p>Previous Donors</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
