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
    <h2>Blood Donation Management System - Donor Report</h2>
    <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Blood Group</th>
                <th>Contact</th>
                <th>City</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donors as $donor)
                <tr>
                    <td>{{ $donor->user->name ?? 'N/A' }}</td>
                    <td>{{ $donor->bloodGroup->name ?? 'N/A' }}</td>
                    <td>{{ $donor->contact_number }}</td>
                    <td>{{ $donor->city }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
