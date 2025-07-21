<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc2626;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .btn {
            display: inline-block;
            background-color: #dc2626;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $notification->title }}</h1>
        <p>Blood Donation Management System</p>
    </div>
    
    <div class="content">
        <div style="white-space: pre-line;">{{ $notification->message }}</div>
        
        @if($notification->type === 'announcement')
            <a href="{{ url('/login') }}" class="btn">Visit Dashboard</a>
        @endif
    </div>
    
    <div class="footer">
        <p>This is an automated message from the Blood Donation Management System.</p>
        <p>If you received this email in error, please contact your administrator.</p>
        <p>&copy; {{ date('Y') }} Blood Donation Management System. All rights reserved.</p>
    </div>
</body>
</html>
