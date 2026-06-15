<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Refund Confirmed – Ticketly</title>
<style>
  * { margin:0;padding:0;box-sizing:border-box; }
  body { font-family:Inter,'Helvetica Neue',Arial,sans-serif;background:#0f0f1a;color:#f9fafb; }
  .wrapper { max-width:600px;margin:0 auto; }
  .header { background:linear-gradient(135deg,#059669,#047857);padding:40px 32px;text-align:center; }
  .header h1 { font-size:26px;font-weight:900;color:#fff;margin-bottom:4px; }
  .header p  { color:rgba(255,255,255,0.8);font-size:15px; }
  .body { background:#111827;padding:32px; }
  .ref-card { background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:12px;padding:20px;text-align:center;margin-bottom:20px; }
  .ref-card p { color:rgba(255,255,255,0.7);font-size:12px;margin-bottom:4px; }
  .ref-card span { color:#fff;font-size:24px;font-weight:900;letter-spacing:0.15em; }
  .section { background:#1f2937;border-radius:12px;padding:24px;margin-bottom:16px; }
  .section-title { font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#10b981;margin-bottom:12px; }
  .amount-box { background:#064e3b;border:1px solid #065f46;border-radius:10px;padding:16px;text-align:center;margin-bottom:16px; }
  .amount-box p { color:#6ee7b7;font-size:13px;margin-bottom:4px; }
  .amount-box span { color:#34d399;font-size:36px;font-weight:900; }
  .timeline li { display:flex;gap:12px;padding:10px 0;border-bottom:1px solid #374151;color:#d1d5db;font-size:14px; }
  .timeline li:last-child { border-bottom:none; }
  .timeline li span.dot { width:10px;height:10px;background:#10b981;border-radius:50%;flex-shrink:0;margin-top:4px; }
  .info-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
  .info-cell { background:#111827;border-radius:8px;padding:12px; }
  .info-cell .label { font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:700;margin-bottom:2px; }
  .info-cell .value { font-size:14px;color:#f9fafb;font-weight:600; }
  .footer { background:#0f172a;text-align:center;padding:24px 32px; }
  .footer p { color:#4b5563;font-size:12px;line-height:1.8; }
  .footer a { color:#6366f1;text-decoration:none; }
</style>
</head>
<body>
<div class="wrapper">

  <div class="header">
    <h1>💰 Refund Confirmed</h1>
    <p>Your refund has been processed successfully</p>
  </div>

  <div class="body">
    @php
      $isPartial = $booking->status === 'partially_refunded';
      $refundedTotal = (float) ($booking->refund_amount ?? 0);
      $currentTotal = (float) ($booking->total ?? 0);
      $originalTotal = $refundedTotal >= $currentTotal
          ? $refundedTotal
          : $refundedTotal + $currentTotal;
    @endphp

    <p style="color:#9ca3af;font-size:15px;margin-bottom:24px;">Hi {{ $booking->customer_name }},<br><br>We've confirmed your refund for <strong style="color:#f9fafb">{{ $booking->event->title }}</strong>. The details are below.</p>
    @if($isPartial)
      <p style="color:#9ca3af;font-size:14px;margin-bottom:16px;">Your remaining tickets are still valid and can be used for entry.</p>
    @endif

    <!-- Reference -->
    <div class="ref-card">
      <p>Original Booking Reference</p>
      <span>{{ $booking->reference }}</span>
    </div>

    <!-- Refund Amount -->
    <div class="amount-box">
      <p>Refund Amount</p>
      <span>{{ ticketly_money($refundAmount) }}</span>
    </div>

    <!-- Timeline -->
    <div class="section">
      <div class="section-title">Refund Timeline</div>
      <ul class="timeline" style="list-style:none">
        <li><span class="dot"></span><div><strong style="color:#f9fafb">Today</strong> – Refund initiated by Ticketly via Stripe</div></li>
        <li><span class="dot"></span><div><strong style="color:#f9fafb">1–3 business days</strong> – Refund appears in your Stripe/bank statement</div></li>
        <li><span class="dot"></span><div><strong style="color:#f9fafb">Up to 10 business days</strong> – Maximum time for refund to reach your account</div></li>
      </ul>
    </div>

    <!-- Original Booking Summary -->
    <div class="section">
      <div class="section-title">Original Booking</div>
      <div class="info-grid">
        <div class="info-cell"><div class="label">Event</div><div class="value">{{ \Illuminate\Support\Str::limit($booking->event->title, 30) }}</div></div>
        <div class="info-cell"><div class="label">Date</div><div class="value">{{ ticketly_format_date($booking->event->starts_at) }}</div></div>
        <div class="info-cell"><div class="label">Original Total</div><div class="value">{{ ticketly_money($originalTotal) }}</div></div>
        <div class="info-cell"><div class="label">Refund</div><div class="value" style="color:#34d399">{{ ticketly_money($refundAmount) }}</div></div>
      </div>
    </div>

    <!-- Note -->
    <div style="background:#1e293b;border:1px solid #334155;border-radius:10px;padding:16px 20px;margin-bottom:20px">
      <p style="color:#94a3b8;font-size:13px;line-height:1.7">💡 <strong style="color:#e2e8f0">Please note:</strong> Refund timelines depend on your bank or card issuer. If you haven't received your refund within 10 business days, please contact us at <a href="mailto:support@ticketly.com" style="color:#818cf8">support@ticketly.com</a> with your booking reference.</p>
    </div>

  </div>

  <div class="footer">
    <p>This email was sent to {{ $booking->customer_email }}<br>
    Booking ref: <strong>{{ $booking->reference }}</strong><br>
    © {{ date('Y') }} Ticketly. All rights reserved.<br>
    <a href="{{ route('events.index') }}">Browse Events</a> · <a href="mailto:support@ticketly.com">Contact Support</a></p>
  </div>

</div>
</body>
</html>
