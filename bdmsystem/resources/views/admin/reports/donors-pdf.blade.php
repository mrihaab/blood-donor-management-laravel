<!DOCTYPE html>
<html>
<head>
    <title>Donors Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .active { color: green; }
        .inactive { color: red; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Donors Report</h1>
        <p>Generated on: {{ now()->format('M d, Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Blood Group</th>
                <th>City</th>
                <th>Status</th>
                <th>Registered Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donors as $donor)
                <tr>
                    <td>{{ $donor->name }}</td>
                    <td>{{ $donor->email }}</td>
                    <td>{{ $donor->donor->contact_number ?? 'N/A' }}</td>
                    <td>{{ $donor->donor->bloodGroup->name ?? 'N/A' }}</td>
                    <td>{{ $donor->donor->city ?? 'N/A' }}</td>
                    <td class="{{ $donor->status }}">{{ ucfirst($donor->status) }}</td>
                    <td>{{ $donor->created_at->format('M d, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
