<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Urgent Emergency Blood Request Alert</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; color: #1e293b; padding: 20px; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: #dc2626; color: #ffffff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; tracking-wide; }
        .body { padding: 24px; }
        .badge { display: inline-block; background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 6px; font-weight: bold; font-size: 14px; }
        .details { background: #f1f5f9; padding: 16px; border-radius: 12px; margin: 16px 0; font-size: 14px; line-height: 1.6; }
        .btn { display: block; width: 100%; text-align: center; background: #dc2626; color: #ffffff; padding: 14px 0; text-decoration: none; border-radius: 10px; font-weight: bold; font-size: 16px; margin-top: 20px; }
        .footer { text-align: center; font-size: 12px; color: #64748b; padding: 16px; border-t: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>🚨 URGENT EMERGENCY BLOOD APPEAL</h1>
        </div>
        <div class="body">
            <p>Dear <strong>{{ $donorUser->name }}</strong>,</p>
            <p>An emergency patient at <strong>{{ $bloodRequest->hospital }}</strong> urgently requires blood donation that matches your blood group!</p>
            
            <div class="details">
                <p><strong>Required Blood Group:</strong> <span class="badge">{{ $bloodRequest->blood_group }}</span></p>
                <p><strong>Hospital / Location:</strong> {{ $bloodRequest->hospital }} ({{ $bloodRequest->city }})</p>
                <p><strong>Units Needed:</strong> {{ $bloodRequest->units_needed }} Unit(s)</p>
                <p><strong>Patient Reference:</strong> #REQ-{{ $bloodRequest->id }} ({{ $bloodRequest->patient_name }})</p>
            </div>

            <p>Your blood type can save a life today! If you are medically fit and available to donate, please click below to confirm your arrival RSVP:</p>

            <a href="{{ route('donor.appointments.index') }}" class="btn">🩸 I Can Donate Now (Confirm RSVP)</a>
        </div>
        <div class="footer">
            LifeBlood Emergency Dispatch System &bull; WHO & ISBT-128 Clinical Protocol
        </div>
    </div>
</body>
</html>
