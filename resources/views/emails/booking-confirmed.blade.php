<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking Confirmed – {{ $booking->reference }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: Inter, 'Helvetica Neue', Arial, sans-serif; background: #0f0f1a; color: #f9fafb; }
  .wrapper { max-width: 600px; margin: 0 auto; }
  .header { background: linear-gradient(135deg, #4f46e5, #7c3aed, #db2777); padding: 40px 32px; text-align: center; }
  .header h1 { font-size: 28px; font-weight: 900; color: #fff; margin-bottom: 4px; }
  .header p { color: rgba(255,255,255,0.75); font-size: 15px; }
  .badge { display: inline-block; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); border-radius: 50px; padding: 8px 24px; margin-top: 16px; }
  .badge span { color: #fff; font-size: 22px; font-weight: 900; letter-spacing: 0.15em; }
  .body { background: #111827; padding: 32px; }
  .section { background: #1f2937; border-radius: 12px; padding: 24px; margin-bottom: 16px; }
  .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #6366f1; margin-bottom: 12px; }
  .event-name { font-size: 20px; font-weight: 800; color: #f9fafb; margin-bottom: 8px; }
  .meta { display: flex; align-items: center; gap: 8px; color: #9ca3af; font-size: 14px; margin-bottom: 6px; }
  .meta span { color: #d1d5db; }
  .divider { height: 1px; background: #374151; margin: 16px 0; }
  .ticket-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; }
  .ticket-name { color: #d1d5db; font-size: 14px; }
  .ticket-qty { background: #4f46e5; color: #fff; font-weight: 700; font-size: 12px; border-radius: 6px; padding: 2px 8px; margin: 0 8px; }
  .ticket-price { color: #f9fafb; font-weight: 700; font-size: 14px; }
  .price-row { display: flex; justify-content: space-between; font-size: 13px; color: #9ca3af; padding: 4px 0; }
 
  .price-total { display: flex; justify-content: space-between; font-size: 18px; font-weight: 800; color: #f9fafb; padding-top: 12px; border-top: 1px solid #374151; margin-top: 8px; }
  .price-total span:last-child { color: #818cf8; }
  .discount-row { color: #34d399; }
  .cta { text-align: center; padding: 24px 0; }
  .cta a { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 14px 32px; border-radius: 10px; display: inline-block; }
  .notice { background: #1e3a2f; border: 1px solid #166534; border-radius: 10px; padding: 16px 20px; }
  .notice p { color: #86efac; font-size: 13px; line-height: 1.6; }
  .footer { background: #0f172a; text-align: center; padding: 24px 32px; }
  .footer p { color: #4b5563; font-size: 12px; line-height: 1.8; }
  .footer a { color: #6366f1; text-decoration: none; }
  .attach-note { background: #1e293b; border: 1px dashed #334155; border-radius: 10px; padding: 14px 20px; text-align: center; }
  .attach-note p { color: #94a3b8; font-size: 13px; }
  @media (prefers-color-scheme: light) {
    body { background: #f3f4f6; }
    .body { background: #ffffff; }
    .section { background: #f9fafb; }
    .event-name { color: #111827; }
    .price-total { color: #111827; }
  }
</style>
</head>
<body>
@php
  $portalFeePercentage = ticketly_format_percentage(ticketly_setting('portal_fee_percentage', config('ticketly.portal_fee_percentage', 10)));
  $serviceFeePercentage = ticketly_format_percentage(ticketly_setting('service_fee_percentage', config('ticketly.service_fee_percentage', 5)));
  $promoDiscountLabel = 'Discount';
  if ($booking->promoCode) {
    $promoValue = $booking->promoCode->type === 'percentage'
      ? ticketly_format_percentage($booking->promoCode->value) . '%'
      : ticketly_money($booking->promoCode->value);
    $promoDiscountLabel .= ' (' . $booking->promoCode->code . ' - ' . $promoValue . ')';
  }
@endphp
<div class="wrapper">

  <div class="header">
    <h1>🎉 You're going!</h1>
    <p>Your booking is confirmed. See you there!</p>
    <div class="badge"><span>{{ $booking->reference }}</span></div>
  </div>

  <div class="body">

    <p style="color:#9ca3af;font-size:15px;margin-bottom:24px;">Hi {{ $booking->customer_name }},<br><br>Great news – your booking for <strong style="color:#f9fafb">{{ $booking->event->title }}</strong> is confirmed and your tickets are secured. We've attached your printable ticket to this email.</p>

    <!-- Event Details -->
    <div class="section">
      <div class="section-title">Event Details</div>
      <div class="event-name">{{ $booking->event->title }}</div>
      <div class="meta">📅&nbsp;&nbsp; <span>{{ ticketly_format_date($booking->event->starts_at) }}</span></div>
      <div class="meta">🕐&nbsp;&nbsp; <span>{{ ticketly_format_time($booking->event->starts_at) }} – {{ ticketly_format_time($booking->event->ends_at) }}</span></div>
      <div class="meta">📍&nbsp;&nbsp; <span>{{ $booking->event->venue_name }}, {{ $booking->event->venue_address }}, {{ $booking->event->city }}</span></div>
    </div>

    <!-- Ticket Summary -->
    <div class="section">
      <div class="section-title">Ticket Summary</div>
      @foreach($booking->items as $item)
      <div class="ticket-row">
        <span class="ticket-name">{{ $item->ticketTier->name }}  {{ $item->unit_price == 0 ? 'Free' : ticketly_money($item->subtotal / $item->quantity) }}</span>
        <span class="ticket-qty">× {{ $item->quantity }}</span>
        <span class="ticket-price">{{ $item->unit_price == 0 ? 'Free' : ticketly_money($item->subtotal) }}</span>
      </div>
      @endforeach
      <div class="divider"></div>
      <div class="price-row"><span>Subtotal:</span> <span>&nbsp;{{ ticketly_money($booking->subtotal) }}</span></div>
      <div class="price-row"><span>Portal Fee ({{ $portalFeePercentage }}%): </span> 
      <span>&nbsp;{{ ticketly_money( $booking->portal_fee ?? 0) }}</span>
      </div>
      <div class="price-row">
      <span>Service Fee ({{ $serviceFeePercentage }}%): </span>
      <span>&nbsp;{{ ticketly_money( $booking->service_fee ?? 0) }}</span>
      </div>
      @if($booking->discount_amount > 0)
      <div class="price-row discount-row">
        <span>{{ $promoDiscountLabel }}</span>
        <span>-{{ ticketly_money($booking->discount_amount) }}</span>
      </div>
      @endif
      <div class="price-total">
      <span>Total Paid: </span> 
      <span>&nbsp;{{ ticketly_money( $booking->total) }}</span>
      </div>
    </div>

    <!-- Attached Ticket Notice -->
    <div class="attach-note" style="margin-bottom:16px">
      <p>📎 Your printable ticket is attached to this email.<br>Print it or save it on your phone – you'll need it at the door.</p>
    </div>

    <!-- At the Venue -->
    <div class="section">
      <div class="section-title">At the Venue</div>
      <div class="notice">
        <p>✅ Show your booking reference <strong>{{ $booking->reference }}</strong> at the entrance – on your phone or printed.<br><br>
        🅿️ {{ $booking->event->parking_info ?? 'Please check the event page for parking and transport info.' }}</p>
      </div>
    </div>

    <div class="cta">
      <a href="{{ route('events.show', $booking->event->slug) }}">View Event Details →</a>
    </div>

  </div>

  <div class="footer">
    <p>This email was sent to {{ $booking->customer_email }}<br>
    Booking ref: <strong>{{ $booking->reference }}</strong><br>
    <a href="{{ route('events.index') }}">Browse Events</a> · <a href="mailto:support@ticketly.com">Contact Support</a></p>
  </div>

</div>
</body>
</html>
