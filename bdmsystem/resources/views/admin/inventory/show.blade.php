@extends('layouts.admin')

@section('title', 'Blood Inventory Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Blood Inventory Record Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.inventory.edit', $inventory->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit Record
                        </a>
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Inventory
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-red">
                                    <i class="fas fa-tint"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Blood Group</span>
                                    <span class="info-box-number">{{ $inventory->bloodGroup->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-blue">
                                    <i class="fas fa-cubes"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Quantity</span>
                                    <span class="info-box-number">{{ $inventory->quantity }} units</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-green">
                                    <i class="fas fa-calendar-plus"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Collection Date</span>
                                    <span class="info-box-number">{{ $inventory->collection_date ? $inventory->collection_date->format('M d, Y') : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-yellow">
                                    <i class="fas fa-calendar-times"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Expiry Date</span>
                                    <span class="info-box-number">{{ $inventory->expiry_date ? $inventory->expiry_date->format('M d, Y') : 'N/A' }}</span>
                                    @if($inventory->expiry_date && $inventory->expiry_date->isPast())
                                        <small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Expired</small>
                                    @elseif($inventory->expiry_date && $inventory->expiry_date->diffInDays(now()) <= 7)
                                        <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Expires Soon</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Additional Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-striped">
                                        <tr>
                                            <th style="width: 200px;">Storage Location:</th>
                                            <td>{{ $inventory->location ?? 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @if($inventory->status == 'available')
                                                    <span class="badge badge-success">Available</span>
                                                @elseif($inventory->status == 'expired')
                                                    <span class="badge badge-danger">Expired</span>
                                                @elseif($inventory->status == 'used')
                                                    <span class="badge badge-info">Used</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ ucfirst($inventory->status) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Associated Donor:</th>
                                            <td>
                                                @if($inventory->donor)
                                                    <a href="{{ route('admin.donors.show', $inventory->donor->id) }}" class="text-primary">
                                                        {{ $inventory->donor->user->name }}
                                                    </a>
                                                    <small class="text-muted">({{ $inventory->donor->user->email }})</small>
                                                @else
                                                    <span class="text-muted">No associated donor</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Record Created:</th>
                                            <td>{{ $inventory->created_at ? $inventory->created_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated:</th>
                                            <td>{{ $inventory->updated_at ? $inventory->updated_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.inventory.edit', $inventory->id) }}" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Edit Record
                                        </a>
                                        
                                        @if($inventory->status == 'available')
                                            <button type="button" class="btn btn-info" onclick="markAsUsed({{ $inventory->id }})">
                                                <i class="fas fa-check"></i> Mark as Used
                                            </button>
                                        @endif
                                        
                                        @if($inventory->status == 'available' && $inventory->expiry_date && $inventory->expiry_date->isPast())
                                            <button type="button" class="btn btn-danger" onclick="markAsExpired({{ $inventory->id }})">
                                                <i class="fas fa-times"></i> Mark as Expired
                                            </button>
                                        @endif
                                        
                                        <button type="button" class="btn btn-danger" onclick="deleteRecord({{ $inventory->id }})">
                                            <i class="fas fa-trash"></i> Delete Record
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden forms for actions -->
<form id="mark-used-form" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status" value="used">
</form>

<form id="mark-expired-form" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status" value="expired">
</form>

<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
function markAsUsed(inventoryId) {
    if (confirm('Are you sure you want to mark this blood inventory as used?')) {
        const form = document.getElementById('mark-used-form');
        form.action = `/admin/inventory/${inventoryId}`;
        form.submit();
    }
}

function markAsExpired(inventoryId) {
    if (confirm('Are you sure you want to mark this blood inventory as expired?')) {
        const form = document.getElementById('mark-expired-form');
        form.action = `/admin/inventory/${inventoryId}`;
        form.submit();
    }
}

function deleteRecord(inventoryId) {
    if (confirm('Are you sure you want to delete this blood inventory record? This action cannot be undone.')) {
        const form = document.getElementById('delete-form');
        form.action = `/admin/inventory/${inventoryId}`;
        form.submit();
    }
}
</script>
@endsection
