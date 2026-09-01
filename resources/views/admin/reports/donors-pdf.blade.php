<!DOCTYPE html>
<html>
<head>
    <title>Donor Report PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Blood Donation Management System - Donor Directory Report</h2>
    <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Blood Group</th>
                <th>Contact</th>
                <th>City</th>
                <th>Registered Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donors as $donor)
                <tr>
                    <td>#{{ $donor->id }}</td>
                    <td><strong>{{ $donor->user->name ?? 'N/A' }}</strong></td>
                    <td>{{ $donor->bloodGroup->name ?? 'N/A' }}</td>
                    <td>{{ $donor->contact_number ?? 'N/A' }}</td>
                    <td>{{ $donor->city ?? 'N/A' }}</td>
                    <td>{{ $donor->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
