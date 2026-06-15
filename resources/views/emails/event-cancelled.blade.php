<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Event Cancelled – Ticketly</title>
<style>
  * { margin:0;padding:0;box-sizing:border-box; }
  body { font-family:Inter,'Helvetica Neue',Arial,sans-serif;background:#0f0f1a;color:#f9fafb; }
  .wrapper { max-width:600px;margin:0 auto; }
  .header { background:linear-gradient(135deg,#b91c1c,#7f1d1d);padding:40px 32px;text-align:center; }
  .header h1 { font-size:24px;font-weight:900;color:#fff;margin-bottom:4px; }
  .body { background:#111827;padding:32px; }
  .section { background:#1f2937;border-radius:12px;padding:20px;margin-bottom:16px; }
  .section-title { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#f87171;margin-bottom:10px; }
  .reason-box { background:#2d1a1a;border:1px solid #7f1d1d;border-radius:10px;padding:16px 20px;margin-bottom:16px; }
  .refund-box { background:#064e3b;border:1px solid #065f46;border-radius:10px;padding:16px 20px;margin-bottom:16px; }
  .refund-box p { color:#6ee7b7;font-size:14px;line-height:1.7; }
  .footer { background:#0f172a;text-align:center;padding:20px 32px; }
  .footer p { color:#4b5563;font-size:11px;line-height:1.8; }
  .footer a { color:#6366f1;text-decoration:none; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>⚠️ Event Cancelled</h1>
    <p style="color:rgba(255,255,255,0.75);font-size:14px">Important information about your booking</p>
  </div>
  <div class="body">
    <p style="color:#9ca3af;font-size:15px;margin-bottom:20px">Hi {{ $booking->customer_name }},<br><br>We're sorry to inform you that <strong style="color:#f9fafb">{{ $booking->event->title }}</strong> has been cancelled by the organiser.</p>

    <div class="section">
      <div class="section-title">Your Booking</div>
      <div style="color:#d1d5db;font-size:14px;line-height:2">📋 Ref: <strong>{{ $booking->reference }}</strong><br>📅 Originally: {{ ticketly_format_datetime($booking->event->starts_at) }}<br>📍 {{ $booking->event->venue_name }}, {{ $booking->event->city }}</div>
    </div>

    <div class="reason-box">
      <p style="color:#fca5a5;font-size:13px;font-weight:700;margin-bottom:6px">Reason for cancellation:</p>
      <p style="color:#fecaca;font-size:13px;line-height:1.6">{{ $reason }}</p>
    </div>

    <div class="refund-box">
      <p>💰 <strong>Full refund of {{ ticketly_money($booking->total) }} has been initiated.</strong><br>
      Your refund will appear on your original payment method within 5–10 working days.<br>
      Booking reference {{ $booking->reference }} will be used to process your refund.</p>
    </div>

    <div style="background:#1e293b;border:1px solid #334155;border-radius:10px;padding:14px 18px">
      <p style="color:#94a3b8;font-size:12px;line-height:1.8">If you have any questions, please contact us at <a href="mailto:support@ticketly.com" style="color:#818cf8">support@ticketly.com</a> quoting your booking reference.</p>
    </div>
  </div>
  <div class="footer">
    <p>© {{ date('Y') }} Ticketly · <a href="{{ route('events.index') }}">Browse other events</a></p>
  </div>
</div>
</body>
</html>
