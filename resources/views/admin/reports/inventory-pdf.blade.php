<!DOCTYPE html>
<html>
<head>
    <title>Inventory Report PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Blood Bank Central Stock Report Certificate</h2>
    <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    <p><strong>Active Available Stock:</strong> {{ $totalAvailable ?? 0 }} Bags &bull; <strong>Total Dispensed:</strong> {{ $totalDispensed ?? 0 }} Bags</p>
    <table>
        <thead>
            <tr>
                <th>Blood Group</th>
                <th>Available Units</th>
                <th>Donor Intake</th>
                <th>Direct Admin Intake</th>
                <th>Expiring Soon (7 Days)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventory as $item)
                <tr>
                    <td><strong>{{ $item['blood_group'] }}</strong></td>
                    <td>{{ $item['units_available'] }} Bags</td>
                    <td>{{ $item['donor_intake_count'] }} Bags</td>
                    <td>{{ $item['direct_intake_count'] }} Bags</td>
                    <td>{{ $item['expiring_soon'] > 0 ? $item['expiring_soon'] . ' Bag(s)' : '0 Bags' }}</td>
                    <td>{{ $item['is_low_stock'] ? 'Low Stock Threshold' : 'Optimal Stock' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
