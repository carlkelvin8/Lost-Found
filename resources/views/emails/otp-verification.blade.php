<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; margin: 0; padding: 20px; }
        .container { max-width: 480px; margin: 0 auto; background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .logo { text-align: center; margin-bottom: 24px; }
        .logo img { width: 64px; height: 64px; border-radius: 50%; }
        h1 { font-size: 1.5rem; color: #111827; text-align: center; margin-bottom: 8px; }
        .subtitle { color: #6b7280; text-align: center; font-size: 0.9375rem; margin-bottom: 32px; }
        .otp-box { background: #f3f4f6; border: 2px dashed #d1d5db; border-radius: 12px; padding: 24px; text-align: center; margin-bottom: 24px; }
        .otp-code { font-size: 2.5rem; font-weight: 800; letter-spacing: 0.5rem; color: #0041C7; font-family: 'SF Mono', Consolas, monospace; }
        .note { font-size: 0.8125rem; color: #9ca3af; text-align: center; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 0.75rem; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="{{ asset('image.png') }}" alt="NAAP">
        </div>
        <h1>Verify Your Email</h1>
        <p class="subtitle">Hi {{ $userName }}, use the code below to verify your email address.</p>
        
        <div class="otp-box">
            <div class="otp-code">{{ $otp }}</div>
        </div>
        
        <p class="note">This code expires in 10 minutes. If you didn't create an account, please ignore this email.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} NAAP Lost & Found &mdash; National Aviation Academy of the Philippines
        </div>
    </div>
</body>
</html>
