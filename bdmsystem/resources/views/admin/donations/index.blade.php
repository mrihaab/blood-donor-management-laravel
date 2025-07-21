@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>💉 Donations Management</h2>
                <a href="{{ route('admin.donations.create') }}" class="btn btn-primary">➕ Record New Donation</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    ✅ {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ❌ {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📋 Donations History</h5>
                </div>
                <div class="card-body">
                    @if($donations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>👤 Donor</th>
                                        <th>🩸 Blood Group</th>
                                        <th>💧 Units</th>
                                        <th>📅 Donation Date</th>
                                        <th>📊 Status</th>
                                        <th>📝 Notes</th>
                                        <th>🔧 Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($donations as $index => $donation)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $donation->donor->user->name }}</strong><br>
                                                <small class="text-muted">{{ $donation->donor->user->email }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">{{ $donation->donor->bloodGroup->name }}</span>
                                            </td>
                                            <td><strong>{{ $donation->units ?? 1 }} Unit(s)</strong></td>
                                            <td>{{ $donation->donation_date->format('M d, Y') }}</td>
                                            <td>
                                                @switch($donation->status)
                                                    @case('pending')
                                                        <span class="badge bg-warning">⏳ Pending</span>
                                                        @break
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
                                            <td>
                                                @if($donation->notes)
                                                    <span title="{{ $donation->notes }}">
                                                        {{ Str::limit($donation->notes, 30) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">No notes</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.donations.show', $donation->id) }}" class="btn btn-sm btn-info">👁️ View</a>
                                                    <a href="{{ route('admin.donations.edit', $donation->id) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
                                                    <form method="POST" action="{{ route('admin.donations.destroy', $donation->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure?')">
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
                            <h4 class="text-muted">🚫 No donations found</h4>
                            <p class="text-muted">Record the first donation!</p>
                            <a href="{{ route('admin.donations.create') }}" class="btn btn-primary">➕ Record First Donation</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3>{{ $donations->where('status', 'completed')->count() }}</h3>
                            <p>Completed Donations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3>{{ $donations->where('status', 'pending')->count() }}</h3>
                            <p>Pending Donations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3>{{ $donations->where('status', 'completed')->sum('units') }} Units</h3>
                            <p>Total Blood Collected</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3>{{ $donations->where('donation_date', '>=', now()->startOfMonth())->count() }}</h3>
                            <p>This Month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
