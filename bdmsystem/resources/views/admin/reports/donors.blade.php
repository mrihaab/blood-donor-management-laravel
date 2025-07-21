@extends('admin.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Donor Report</h5>
                    <div>
                        <a href="{{ route('admin.reports.donors', ['format' => 'pdf']) }}" class="btn btn-sm btn-light">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                        <a href="{{ route('admin.reports.donors', ['format' => 'csv']) }}" class="btn btn-sm btn-light">
                            <i class="fas fa-file-csv"></i> Export CSV
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="blood_group">Blood Group</label>
                                <select name="blood_group" id="blood_group" class="form-control">
                                    <option value="">All Blood Groups</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="city">City</label>
                                <input type="text" name="city" id="city" class="form-control" placeholder="Enter city">
                            </div>
                            <div class="col-md-3">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('admin.reports.donors') }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Donors Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Blood Group</th>
                                    <th>City</th>
                                    <th>Status</th>
                                    <th>Registered Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($donors as $donor)
                                    <tr>
                                        <td>{{ $donor->name }}</td>
                                        <td>{{ $donor->email }}</td>
                                        <td>{{ $donor->donor->contact_number ?? 'N/A' }}</td>
                                        <td>{{ $donor->donor->bloodGroup->name ?? 'N/A' }}</td>
                                        <td>{{ $donor->donor->city ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $donor->status == 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($donor->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $donor->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($donors->isEmpty())
                        <div class="text-center py-4">
                            <p class="text-muted">No donors found matching the criteria.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
