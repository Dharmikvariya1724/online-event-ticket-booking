<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Your Password – Ticketly</title>
<style>
  * { margin:0;padding:0;box-sizing:border-box; }
  body { font-family:Inter,'Helvetica Neue',Arial,sans-serif;background:#0f0f1a;color:#f9fafb; }
  .wrapper { max-width:560px;margin:0 auto; }
  .header { background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:40px 32px;text-align:center; }
  .header h1 { font-size:24px;font-weight:900;color:#fff;margin-bottom:4px; }
  .header p  { color:rgba(255,255,255,0.75);font-size:14px; }
  .body { background:#111827;padding:40px 32px; }
  .cta { text-align:center;margin:32px 0; }
  .cta a { display:inline-block;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 36px;border-radius:10px; }
  .note { background:#1e293b;border:1px solid #334155;border-radius:10px;padding:14px 18px;margin-top:24px; }
  .note p { color:#94a3b8;font-size:12px;line-height:1.8; }
  .footer { background:#0f172a;text-align:center;padding:20px 32px; }
  .footer p { color:#4b5563;font-size:11px;line-height:1.8; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>🔐 Password Reset</h1>
    <p>Ticketly Organiser Portal</p>
  </div>
  <div class="body">
    <p style="color:#d1d5db;font-size:15px;margin-bottom:16px;">Hi {{ $organiser->name }},</p>
    <p style="color:#9ca3af;font-size:14px;line-height:1.7;">We received a request to reset the password for your Ticketly organiser account (<strong style="color:#e5e7eb">{{ $organiser->email }}</strong>). Click the button below to set a new password.</p>
    <div class="cta">
      <a href="{{ $resetLink }}">Reset My Password →</a>
    </div>
    <div class="note">
      <p>⏱ This link expires in <strong>24 hours</strong>.<br>
      🔒 If you didn't request this, you can safely ignore this email – your password won't change.<br>
      ❓ Need help? Email <a href="mailto:support@ticketly.com" style="color:#818cf8">support@ticketly.com</a></p>
    </div>
  </div>
  <div class="footer">
    <p>© {{ date('Y') }} Ticketly. All rights reserved.<br>This email was sent to {{ $organiser->email }}</p>
  </div>
</div>
</body>
</html>
