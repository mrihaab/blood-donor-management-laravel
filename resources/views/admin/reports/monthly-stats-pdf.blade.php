<!DOCTYPE html>
<html>
<head>
    <title>Monthly Statistics Report PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .box { border: 1px solid #ddd; padding: 12px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Blood Donation Management System - Monthly Statistics</h2>
    <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>

    <div class="box">
        <h3>This Month Summary</h3>
        <p>Donations: {{ $thisMonthStats['donations'] ?? 0 }}</p>
        <p>New Donors: {{ $thisMonthStats['new_donors'] ?? 0 }}</p>
        <p>Blood Requests: {{ $thisMonthStats['blood_requests'] ?? 0 }}</p>
        <p>Approved Requests: {{ $thisMonthStats['approved_requests'] ?? 0 }}</p>
    </div>
</body>
</html>
