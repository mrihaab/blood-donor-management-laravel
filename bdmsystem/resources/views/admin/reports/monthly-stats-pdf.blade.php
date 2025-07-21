<!DOCTYPE html>
<html>
<head>
    <title>Monthly Statistics Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .stats-grid { display: table; width: 100%; margin-bottom: 30px; }
        .stat-item { display: table-cell; text-align: center; padding: 15px; border: 1px solid #ddd; }
        .stat-number { font-size: 24px; font-weight: bold; color: #007bff; }
        .stat-label { font-size: 14px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Monthly Statistics Report</h1>
        <h2>{{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</h2>
        <p>Generated on: {{ now()->format('M d, Y H:i') }}</p>
    </div>

    <!-- Summary Statistics -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-number">{{ $stats['donations'] }}</div>
            <div class="stat-label">Donations</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $stats['blood_requests'] }}</div>
            <div class="stat-label">Blood Requests</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $stats['new_donors'] }}</div>
            <div class="stat-label">New Donors</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $stats['approved_requests'] }}</div>
            <div class="stat-label">Approved Requests</div>
        </div>
    </div>

    <!-- Blood Group Statistics -->
    @if($bloodGroupStats->count() > 0)
        <h3>Blood Group Donations</h3>
        <table>
            <thead>
                <tr>
                    <th>Blood Group</th>
                    <th>Total Donations</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bloodGroupStats as $stat)
                    <tr>
                        <td>{{ $stat->blood_group }}</td>
                        <td>{{ $stat->total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Daily Donations -->
    @if($dailyDonations->count() > 0)
        <h3>Daily Donations</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Donations</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dailyDonations as $daily)
                    <tr>
                        <td>{{ $daily->date }}</td>
                        <td>{{ $daily->total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
