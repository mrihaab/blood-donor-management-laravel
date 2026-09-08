<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donor Report PDF</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #0f172a;
            background-color: #ffffff;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            border-b: 2px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            color: #991b1b;
            margin: 0 0 4px 0;
        }
        .header p {
            font-size: 9pt;
            color: #64748b;
            margin: 0;
        }
        .meta-bar {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 9pt;
            margin-bottom: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.5px;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            text-align: left;
            vertical-align: middle;
        }
        tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8pt;
            font-weight: bold;
            border-radius: 3px;
            background-color: #fee2e2;
            color: #991b1b;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-t: 1px solid #e2e8f0;
            font-size: 8pt;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LifeBlood Platform — Registered Donor Directory Report</h1>
        <p>Central Blood Bank Administrative System Donor Registry</p>
    </div>

    <div class="meta-bar">
        <strong>Report Generated:</strong> {{ date('Y-m-d H:i:s') }} UTC &bull;
        <strong>Total Registered Donors:</strong> {{ count($donors) }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">ID</th>
                <th style="width: 25%;">Donor Name</th>
                <th style="width: 15%;">Blood Group</th>
                <th style="width: 20%;">Contact Number</th>
                <th style="width: 15%;">City</th>
                <th style="width: 15%;">Registered Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donors as $donor)
                <tr style="page-break-inside: avoid;">
                    <td>#{{ $donor->id }}</td>
                    <td><strong>{{ $donor->user->name ?? 'N/A' }}</strong></td>
                    <td><span class="badge">{{ $donor->bloodGroup->name ?? 'N/A' }}</span></td>
                    <td>{{ $donor->contact_number ?? 'N/A' }}</td>
                    <td>{{ $donor->city ?? 'N/A' }}</td>
                    <td>{{ $donor->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Confidential — Internal Central Blood Bank Document &bull; LifeBlood Platform
    </div>
</body>
</html>
