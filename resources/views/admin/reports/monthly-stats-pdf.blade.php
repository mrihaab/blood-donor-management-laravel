<!DOCTYPE html>
<html>
<head>
    <title>Monthly Statistics Report PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .box { border: 1px solid #ddd; padding: 12px; margin-bottom: 10px; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Blood Donation Management System - Monthly Operational Executive Summary</h2>
    <p>Generated on: {{ date('Y-m-d H:i:s') }} &bull; Period: {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</p>

    <div class="box">
        <h3>Operational Key Performance Indicators (KPIs)</h3>
        <p><strong>Donation Units Collected:</strong> {{ $stats['donations'] ?? 0 }} Bags</p>
        <p><strong>New Donors Registered:</strong> {{ $stats['new_donors'] ?? 0 }}</p>
        <p><strong>Hospital Requisitions Filed:</strong> {{ $stats['blood_requests'] ?? 0 }}</p>
        <p><strong>Requisitions Approved:</strong> {{ $stats['approved_requests'] ?? 0 }}</p>
        <p><strong>Dispensed to Hospitals:</strong> {{ $stats['dispensed_units'] ?? 0 }} Bags</p>
    </div>

    @if(isset($bloodGroupStats) && count($bloodGroupStats) > 0)
        <h3>Blood Group Intake Breakdown</h3>
        <table>
            <thead>
                <tr>
                    <th>Blood Group</th>
                    <th>Units Intaken</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bloodGroupStats as $bgStat)
                    <tr>
                        <td><strong>{{ $bgStat->name }}</strong></td>
                        <td>{{ $bgStat->total }} Bag(s)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
