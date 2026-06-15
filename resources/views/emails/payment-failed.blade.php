<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Failed – Ticketly</title>
<style>
  * { margin:0;padding:0;box-sizing:border-box; }
  body { font-family: Inter,'Helvetica Neue',Arial,sans-serif; background:#0f0f1a; color:#f9fafb; }
  .wrapper { max-width:600px; margin:0 auto; }
  .header { background:linear-gradient(135deg,#dc2626,#b91c1c); padding:40px 32px; text-align:center; }
  .header h1 { font-size:26px; font-weight:900; color:#fff; margin-bottom:4px; }
  .header p  { color:rgba(255,255,255,0.75); font-size:15px; }
  .body { background:#111827; padding:32px; }
  .section { background:#1f2937; border-radius:12px; padding:24px; margin-bottom:16px; }
  .section-title { font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#f87171;margin-bottom:12px; }
  .event-name { font-size:18px;font-weight:800;color:#f9fafb;margin-bottom:8px; }
  .meta { display:flex;align-items:center;gap:8px;color:#9ca3af;font-size:14px;margin-bottom:6px; }
  .meta span { color:#d1d5db; }
  .error-box { background:#2d1a1a;border:1px solid #7f1d1d;border-radius:10px;padding:16px 20px;margin-bottom:16px; }
  .error-box p { color:#fca5a5;font-size:14px;line-height:1.6; }
  .steps { list-style:none;counter-reset:step; }
  .steps li { counter-increment:step;display:flex;gap:12px;padding:10px 0;border-bottom:1px solid #374151;color:#d1d5db;font-size:14px; }
  .steps li:last-child { border-bottom:none; }
  .steps li::before { content:counter(step);background:#4f46e5;color:#fff;font-weight:800;font-size:12px;border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px; }
  .cta { text-align:center;padding:24px 0; }
  .cta a { background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 32px;border-radius:10px;display:inline-block; }
  .timer-note { background:#1e2a3a;border:1px solid #1e40af;border-radius:10px;padding:14px 20px;text-align:center; }
  .timer-note p { color:#93c5fd;font-size:13px; }
  .footer { background:#0f172a;text-align:center;padding:24px 32px; }
  .footer p { color:#4b5563;font-size:12px;line-height:1.8; }
  .footer a { color:#6366f1;text-decoration:none; }
</style>
</head>
<body>
<div class="wrapper">

  <div class="header">
    <h1>⚠️ Payment Not Completed</h1>
    <p>Don't worry – your tickets may still be held</p>
  </div>

  <div class="body">

    <p style="color:#9ca3af;font-size:15px;margin-bottom:24px;">Hi {{ $reservation->customer_name ?? 'there' }},<br><br>Your payment for <strong style="color:#f9fafb">{{ $reservation->event->title }}</strong> was not completed. No money has been taken from your account.</p>

    <!-- Error Details -->
    <div class="error-box">
      <p>❌ <strong>Reason:</strong> {{ $errorMessage }}</p>
    </div>

    <!-- Event Info -->
    <div class="section">
      <div class="section-title">Event</div>
      <div class="event-name">{{ $reservation->event->title }}</div>
      <div class="meta">📅 <span>{{ ticketly_format_datetime($reservation->event->starts_at) }}</span></div>
      <div class="meta">📍 <span>{{ $reservation->event->venue_name }}, {{ $reservation->event->city }}</span></div>
    </div>

    <!-- What to do next -->
    <div class="section">
      <div class="section-title">What to do next</div>
      <ol class="steps">
        <li>Check that your card details are entered correctly (card number, expiry, CVC).</li>
        <li>Ensure your card has sufficient funds or credit available.</li>
        <li>Try a different card (Visa or Mastercard accepted).</li>
        <li>Contact your bank if the issue persists – they may be blocking online transactions.</li>
      </ol>
    </div>

    @if($reservation->isActive())
    <div class="timer-note" style="margin-bottom:20px">
      <p>⏱ <strong>Good news:</strong> Your ticket hold is still active for {{ ceil($reservation->secondsRemaining() / 60) }} more minute(s).<br>Return to the checkout to try again before your hold expires.</p>
    </div>
    @endif

    <div class="cta">
      <a href="{{ route('checkout.show', $reservation->token) }}">Retry Checkout →</a>
    </div>

  </div>

  <div class="footer">
    <p>This email was sent to {{ $reservation->customer_email }}<br>
    No payment was taken · Questions? <a href="mailto:support@ticketly.com">support@ticketly.com</a><br>
    © {{ date('Y') }} Ticketly. All rights reserved.</p>
  </div>

</div>
</body>
</html>
