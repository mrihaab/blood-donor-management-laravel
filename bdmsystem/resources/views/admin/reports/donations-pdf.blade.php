<!DOCTYPE html>
<html>
<head>
    <title>Donations Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .completed { color: green; }
        .pending { color: orange; }
        .cancelled { color: red; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Donations Report</h1>
        <p>Generated on: {{ now()->format('M d, Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Donor Name</th>
                <th>Blood Group</th>
                <th>Units</th>
                <th>Donation Date</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donations as $donation)
                <tr>
                    <td>{{ $donation->donor->user->name }}</td>
                    <td>{{ $donation->bloodGroup->name }}</td>
                    <td>{{ $donation->units }}</td>
                    <td>{{ $donation->donation_date }}</td>
                    <td class="{{ $donation->status }}">{{ ucfirst($donation->status) }}</td>
                    <td>{{ $donation->created_at->format('M d, Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
