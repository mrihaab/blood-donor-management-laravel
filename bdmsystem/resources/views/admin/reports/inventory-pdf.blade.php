<!DOCTYPE html>
<html>
<head>
    <title>Inventory Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .low-stock { background-color: #fff3cd; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Blood Inventory Report</h1>
        <p>Generated on: {{ now()->format('M d, Y H:i') }}</p>
    </div>

    @if($lowStockItems->count() > 0)
        <div style="background-color: #fff3cd; padding: 10px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
            <strong>Low Stock Alert:</strong> {{ $lowStockItems->count() }} blood groups are below the threshold.
        </div>
    @endif

    <table>
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
                <tr class="{{ $item->units_available < 10 ? 'low-stock' : '' }}">
                    <td>{{ $item->bloodGroup->name }}</td>
                    <td>{{ $item->units_available }}</td>
                    <td>{{ $item->units_requested }}</td>
                    <td>{{ $item->updated_at->format('M d, Y') }}</td>
                    <td>
                        @if($item->units_available < 10)
                            Low Stock
                        @elseif($item->units_available < 20)
                            Medium Stock
                        @else
                            Good Stock
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
