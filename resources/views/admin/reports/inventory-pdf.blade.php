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
    <h2>Blood Donation Management System - Inventory Report</h2>
    <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    <table>
        <thead>
            <tr>
                <th>Blood Group</th>
                <th>Units Available</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventory as $item)
                <tr>
                    <td>{{ $item->bloodGroup->name ?? 'N/A' }}</td>
                    <td>{{ $item->units_available ?? $item->quantity }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
