<!DOCTYPE html>
<html>
<head>
    <title>Donations Report PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Blood Donation Management System - Physical Unit Ingestion Logs Report</h2>
    <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    <table>
        <thead>
            <tr>
                <th>Unit Serial #</th>
                <th>Donor / Source</th>
                <th>Blood Group</th>
                <th>Component</th>
                <th>Collection Date</th>
                <th>Expiry Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donations as $unit)
                <tr>
                    <td><strong>{{ $unit->unit_number }}</strong></td>
                    <td>{{ $unit->donor->user->name ?? 'Direct Admin Intake' }}</td>
                    <td>{{ $unit->bloodGroup->name ?? 'N/A' }}</td>
                    <td>{{ $unit->component->name ?? 'Whole Blood' }}</td>
                    <td>{{ $unit->collection_date }}</td>
                    <td>{{ $unit->expiry_date }}</td>
                    <td>{{ ucfirst($unit->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
