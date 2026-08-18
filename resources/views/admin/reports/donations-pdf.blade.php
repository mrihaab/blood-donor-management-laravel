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
    <h2>Blood Donation Management System - Donations Report</h2>
    <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    <table>
        <thead>
            <tr>
                <th>Donor Name</th>
                <th>Blood Group</th>
                <th>Units</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donations as $donation)
                <tr>
                    <td>{{ $donation->donor->user->name ?? 'N/A' }}</td>
                    <td>{{ $donation->bloodGroup->name ?? 'N/A' }}</td>
                    <td>{{ $donation->quantity }}</td>
                    <td>{{ $donation->donation_date ?? $donation->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
