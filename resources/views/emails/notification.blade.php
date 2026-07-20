<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.05);">
                    
                    {{-- Header --}}
                    <tr>
                        <td style="background:#0041C7;padding:32px 40px;text-align:center;">
                            <h1 style="color:#ffffff;font-size:20px;font-weight:700;margin:0;letter-spacing:-0.02em;">
                                NAAP Lost & Found
                            </h1>
                            <p style="color:rgba(255,255,255,0.8);font-size:13px;margin:6px 0 0;">
                                National Aviation Academy of the Philippines
                            </p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:40px;">
                            {{-- Notification type badge --}}
                            <table cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
                                <tr>
                                    <td style="background:rgba(0,65,199,0.08);color:#0041C7;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;padding:6px 12px;border-radius:20px;">
                                        {{ strtoupper($notifType) }}
                                    </td>
                                </tr>
                            </table>

                            <h2 style="color:#0f172a;font-size:22px;font-weight:700;margin:0 0 12px;letter-spacing:-0.02em;">
                                {{ $notifTitle }}
                            </h2>
                            
                            <p style="color:#475569;font-size:15px;line-height:1.7;margin:0 0 28px;">
                                {{ $notifBody }}
                            </p>

                            {{-- CTA Button --}}
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background:#0041C7;border-radius:10px;">
                                        <a href="{{ url('/notifications') }}" style="display:inline-block;padding:14px 28px;color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;">
                                            View in App →
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#f8fafc;padding:24px 40px;border-top:1px solid #e2e8f0;">
                            <p style="color:#94a3b8;font-size:12px;margin:0;text-align:center;">
                                This is an automated notification from NAAP Lost & Found System.<br>
                                Please do not reply to this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
