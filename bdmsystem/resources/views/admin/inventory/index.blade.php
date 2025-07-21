@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>🩸 Blood Inventory Management</h2>
                <a href="{{ route('admin.inventory.create') }}" class="btn btn-primary">➕ Add Blood Unit</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    ✅ {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Blood Availability Summary -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">📊 Blood Availability Summary</h5>
                        </div>
                        <div class="card-body">
                            @if($summary->count() > 0)
                                <div class="row">
                                    @foreach($summary as $bloodType)
                                        <div class="col-md-3 mb-3">
                                            <div class="card border-danger">
                                                <div class="card-body text-center">
                                                    <h4 class="text-danger">{{ $bloodType->blood_group }}</h4>
                                                    <h3 class="text-primary">{{ $bloodType->total_quantity }} mL</h3>
                                                    <small class="text-muted">Available</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <h5 class="text-muted">🚫 No blood units available</h5>
                                    <p class="text-muted">Start by adding blood units to the inventory</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📋 Blood Inventory Records</h5>
                </div>
                <div class="card-body">
                    @if($inventory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>🩸 Blood Group</th>
                                        <th>💧 Quantity</th>
                                        <th>👤 Donor</th>
                                        <th>📅 Collection Date</th>
                                        <th>⏰ Expiry Date</th>
                                        <th>📍 Location</th>
                                        <th>📊 Status</th>
                                        <th>🔧 Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inventory as $index => $item)
                                        <tr class="@if($item->expiry_date < now()) table-danger @endif">
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <span class="badge bg-danger">{{ $item->bloodGroup->name ?? 'Unknown' }}</span>
                                            </td>
                                            <td><strong>{{ $item->quantity }} mL</strong></td>
                                            <td>
                                                @if($item->donor && $item->donor->user)
                                                    {{ $item->donor->user->name }}
                                                @else
                                                    <span class="text-muted">Anonymous</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->collection_date->format('M d, Y') }}</td>
                                            <td>
                                                {{ $item->expiry_date->format('M d, Y') }}
                                                @if($item->expiry_date < now())
                                                    <span class="badge bg-danger ms-1">Expired</span>
                                                @elseif($item->expiry_date->diffInDays(now()) <= 7)
                                                    <span class="badge bg-warning ms-1">Expiring Soon</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->location }}</td>
                                            <td>
                                                @switch($item->status)
                                                    @case('available')
                                                        <span class="badge bg-success">✅ Available</span>
                                                        @break
                                                    @case('expired')
                                                        <span class="badge bg-danger">❌ Expired</span>
                                                        @break
                                                    @case('used')
                                                        <span class="badge bg-secondary">✔️ Used</span>
                                                        @break
                                                    @case('reserved')
                                                        <span class="badge bg-warning">🔒 Reserved</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.inventory.show', $item->id) }}" class="btn btn-sm btn-info">👁️ View</a>
                                                    <a href="{{ route('admin.inventory.edit', $item->id) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
                                                    <form method="POST" action="{{ route('admin.inventory.destroy', $item->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure?')">
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
                            <h4 class="text-muted">🚫 No inventory records found</h4>
                            <p class="text-muted">Start by adding blood units to the inventory!</p>
                            <a href="{{ route('admin.inventory.create') }}" class="btn btn-primary">➕ Add First Blood Unit</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3>{{ $inventory->where('status', 'available')->count() }}</h3>
                            <p>Available Units</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h3>{{ $inventory->where('status', 'expired')->count() }}</h3>
                            <p>Expired Units</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h3>{{ $inventory->where('status', 'used')->count() }}</h3>
                            <p>Used Units</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3>{{ $inventory->where('expiry_date', '<=', now()->addDays(7))->where('status', 'available')->count() }}</h3>
                            <p>Expiring Soon</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
