<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Statistics Report PDF</title>
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
            font-size: 9.5pt;
            margin-bottom: 16px;
        }
        .kpi-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .kpi-cell {
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 10px;
            border-radius: 4px;
            font-size: 9pt;
        }
        .kpi-label {
            font-size: 8pt;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 14pt;
            font-weight: bold;
            color: #0f172a;
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
        <h1>LifeBlood Platform — Monthly Executive Operational Summary</h1>
        <p>Central Blood Bank Performance & Intake Metrics</p>
    </div>

    <div class="meta-bar">
        <strong>Reporting Period:</strong> {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }} &bull;
        <strong>Generated:</strong> {{ date('Y-m-d H:i:s') }} UTC
    </div>

    <table class="kpi-grid">
        <tr>
            <td class="kpi-cell" style="width: 20%;">
                <div class="kpi-label">Collected Units</div>
                <div class="kpi-value">{{ $stats['donations'] ?? 0 }}</div>
            </td>
            <td class="kpi-cell" style="width: 20%;">
                <div class="kpi-label">New Donors</div>
                <div class="kpi-value">{{ $stats['new_donors'] ?? 0 }}</div>
            </td>
            <td class="kpi-cell" style="width: 20%;">
                <div class="kpi-label">Requisitions</div>
                <div class="kpi-value">{{ $stats['blood_requests'] ?? 0 }}</div>
            </td>
            <td class="kpi-cell" style="width: 20%;">
                <div class="kpi-label">Approved</div>
                <div class="kpi-value">{{ $stats['approved_requests'] ?? 0 }}</div>
            </td>
            <td class="kpi-cell" style="width: 20%;">
                <div class="kpi-label">Dispensed</div>
                <div class="kpi-value">{{ $stats['dispensed_units'] ?? 0 }}</div>
            </td>
        </tr>
    </table>

    @if(isset($bloodGroupStats) && count($bloodGroupStats) > 0)
        <h3 style="font-size: 11pt; font-weight: bold; margin-bottom: 8px; color: #334155;">Blood Group Intake Breakdown</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Blood Group</th>
                    <th style="width: 60%;">Units Intaken</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bloodGroupStats as $bgStat)
                    <tr style="page-break-inside: avoid;">
                        <td><span class="badge">{{ $bgStat->name }}</span></td>
                        <td><strong>{{ $bgStat->total }} Bag(s)</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Confidential — Internal Central Blood Bank Document &bull; LifeBlood Platform
    </div>
</body>
</html>
