@extends('admin.layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Inventory Report</h5>
                    <div>
                        <a href="{{ route('admin.reports.inventory', ['format' => 'pdf']) }}" class="btn btn-sm btn-light">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Low Stock Alert -->
                    @if($lowStockItems->count() > 0)
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h6>
                            <p>{{ $lowStockItems->count() }} blood groups are below the threshold of {{ $lowStockThreshold }} units.</p>
                        </div>
                    @endif

                    <!-- Inventory Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Blood Group</th>
                                    <th>Units Available</th>
                                    <th>Units Requested</th>
                                    <th>Last Updated</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inventory as $item)
                                    <tr class="{{ $item->units_available < $lowStockThreshold ? 'table-warning' : '' }}">
                                        <td>{{ $item->bloodGroup->name }}</td>
                                        <td>{{ $item->units_available }}</td>
                                        <td>{{ $item->units_requested }}</td>
                                        <td>{{ $item->updated_at->format('M d, Y') }}</td>
                                        <td>
                                            @if($item->units_available < $lowStockThreshold)
                                                <span class="badge bg-danger">Low Stock</span>
                                            @elseif($item->units_available < ($lowStockThreshold * 2))
                                                <span class="badge bg-warning">Medium Stock</span>
                                            @else
                                                <span class="badge bg-success">Good Stock</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
