@php
  $portalFeePercentage = ticketly_format_percentage(ticketly_setting('portal_fee_percentage', config('ticketly.portal_fee_percentage', 10)));
  $serviceFeePercentage = ticketly_format_percentage(ticketly_setting('service_fee_percentage', config('ticketly.service_fee_percentage', 5)));
  $eventStartsAt = $booking->event->starts_at;
  $eventEndsAt = $booking->event->ends_at;
  $isMultiDayEvent = $eventEndsAt && ! $eventStartsAt->isSameDay($eventEndsAt);
  $eventStartDisplay = ticketly_format_compact_datetime($eventStartsAt);
  $eventEndDisplay = ticketly_format_compact_datetime($eventEndsAt);
  $eventDateLine = ticketly_format_date($eventStartsAt);
  $eventTimeLine = $isMultiDayEvent
    ? $eventStartDisplay . ' - ' . $eventEndDisplay
    : ticketly_format_time($eventStartsAt) . ' - ' . ticketly_format_time($eventEndsAt);
  $venueDisplay = collect([
    $booking->event->venue_name,
    $booking->event->venue_address,
    $booking->event->city,
    $booking->event->postcode,
  ])->filter()->implode(', ');
  $heroVenue = \Illuminate\Support\Str::limit($venueDisplay, 92);
  $ticketCount = (int) $booking->items->sum('quantity');
  $promoDiscountLabel = 'Discount';
  if ($booking->promoCode) {
    $promoValue = $booking->promoCode->type === 'percentage'
      ? ticketly_format_percentage($booking->promoCode->value) . '%'
      : ticketly_money($booking->promoCode->value);
    $promoDiscountLabel .= ' (' . $booking->promoCode->code . ' - ' . $promoValue . ')';
  }
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Ticket - {{ $booking->reference }}</title>
<style>
@page {
  size: A4;
  margin: 7mm 6mm;
}

body {
  margin: 0;
  padding: 0;
  color: #0f172a;
  background: #f8fafc;
  font-size: 11px;
  font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
}

table {
  width: 100%;
  border-collapse: collapse;
}

.sheet {
  width: 100%;
}

.hero {
  margin-bottom: 8px;
  border-radius: 14px;
  overflow: hidden;
  background: #0f172a;
  color: #ffffff;
  page-break-inside: avoid;
  break-inside: avoid;
}

.hero-bar {
  height: 5px;
  background: #6366f1;
}

.hero-body {
  padding: 12px 14px 13px;
}

.hero-left,
.hero-right {
  vertical-align: top;
}

.hero-left {
  width: 64%;
  padding-right: 12px;
}

.hero-right {
  width: 36%;
  text-align: right;
}

.hero-eyebrow {
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #cbd5e1;
}

.hero-title {
  margin: 6px 0 8px;
  font-size: 19px;
  font-weight: 700;
  line-height: 1.18;
  color: #ffffff;
}

.hero-meta {
  font-size: 10px;
  line-height: 1.45;
  color: #e2e8f0;
}

.status-chip {
  display: inline-block;
  padding: 5px 10px;
  border-radius: 999px;
  border: 1px solid rgba(110, 231, 183, 0.45);
  background: rgba(16, 185, 129, 0.16);
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #d1fae5;
}

.ref-label {
  margin-top: 10px;
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #94a3b8;
}

.ref-value {
  margin-top: 6px;
  font-size: 18px;
  font-weight: 700;
  letter-spacing: 0.12em;
  color: #ffffff;
}

.hero-total {
  margin-top: 8px;
  font-size: 9px;
  color: #cbd5e1;
}

.hero-total strong {
  display: block;
  margin-top: 2px;
  font-size: 15px;
  font-weight: 700;
  color: #ffffff;
}

.card {
  margin-bottom: 8px;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  background: #ffffff;
  page-break-inside: avoid;
  break-inside: avoid;
}

.card-title {
  padding: 8px 12px;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #475569;
}

.card-body {
  padding: 10px 12px;
}

.detail-grid {
  border-collapse: separate;
  border-spacing: 0 7px;
}

.detail-grid td {
  width: 50%;
  vertical-align: top;
}

.detail-item {
  padding-right: 12px;
}

.detail-label {
  margin-bottom: 3px;
  font-size: 8px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #64748b;
}

.detail-value {
  font-size: 11px;
  font-weight: 700;
  line-height: 1.45;
  color: #0f172a;
}

.detail-muted {
  font-size: 10px;
  font-weight: 500;
  line-height: 1.45;
  color: #475569;
}

.tickets {
  table-layout: fixed;
}

.tickets thead {
  display: table-header-group;
}

.tickets tr {
  page-break-inside: avoid;
  break-inside: avoid;
}

.tickets th {
  padding: 0 6px 6px;
  border-bottom: 1px solid #e2e8f0;
  font-size: 8px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #64748b;
}

.tickets td {
  padding: 8px 6px;
  border-bottom: 1px solid #eef2f7;
  font-size: 10px;
  vertical-align: top;
  color: #0f172a;
}

.tickets tbody tr:last-child td {
  border-bottom: none;
}

.tickets th:nth-child(1),
.tickets td:nth-child(1) {
  width: 42%;
  text-align: left;
  overflow-wrap: anywhere;
}

.tickets th:nth-child(2),
.tickets td:nth-child(2) {
  width: 9%;
  text-align: center;
  white-space: nowrap;
}

.tickets th:nth-child(3),
.tickets td:nth-child(3) {
  width: 18%;
  text-align: right;
  white-space: nowrap;
}

.tickets th:nth-child(4),
.tickets td:nth-child(4) {
  width: 31%;
  text-align: right;
  white-space: nowrap;
}

.ticket-name {
  font-size: 10px;
  font-weight: 700;
  line-height: 1.35;
  color: #0f172a;
}

.summary-wrap {
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid #e2e8f0;
  page-break-inside: avoid;
  break-inside: avoid;
}

.summary td {
  padding: 4px 0;
  font-size: 10px;
  color: #475569;
}

.summary td:last-child {
  text-align: right;
  white-space: nowrap;
}

.summary .discount td {
  color: #059669;
}

.summary .total td {
  padding-top: 6px;
  border-top: 1px solid #e2e8f0;
  font-size: 12px;
  font-weight: 700;
  color: #0f172a;
}

.summary .total td:last-child {
  color: #4338ca;
}

.kv td {
  padding: 5px 0;
  vertical-align: top;
}

.kv-key {
  width: 82px;
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #64748b;
}

.kv-value {
  font-size: 10px;
  font-weight: 600;
  line-height: 1.45;
  color: #0f172a;
  overflow-wrap: anywhere;
}

.qr-box {
  display: inline-block;
  padding: 8px;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  background: #ffffff;
}

.qr-img {
  width: 96px;
  height: 96px;
}

.scan-ref {
  margin-top: 8px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: #4338ca;
}

.note {
  margin-top: 6px;
  font-size: 9px;
  line-height: 1.4;
  color: #64748b;
}

.entry-grid td {
  width: 50%;
  vertical-align: top;
}

.entry-left {
  padding-right: 10px;
}

.entry-right {
  padding-left: 10px;
  text-align: center;
}

.footer {
  margin-top: 6px;
  padding: 4px 2px 0;
  text-align: center;
  font-size: 9px;
  line-height: 1.35;
  color: #64748b;
}

.footer strong {
  color: #334155;
}
</style>
</head>
<body>

<div class="sheet">
  <div class="hero">
    <div class="hero-bar"></div>
    <div class="hero-body">
      <table>
        <tr>
          <td class="hero-left">
            <div class="hero-eyebrow">Ticketly E-Ticket</div>
            <div class="hero-title">{{ $booking->event->title }}</div>
            <div class="hero-meta">
              <div>{{ $eventDateLine }}</div>
              <div>{{ $eventTimeLine }}</div>
              <div>{{ $heroVenue }}</div>
            </div>
          </td>
          <td class="hero-right">
            <div class="status-chip">Booking Confirmed</div>
            <div class="ref-label">Booking Reference</div>
            <div class="ref-value">{{ $booking->reference }}</div>
            <div class="hero-total">
              Total Paid
              <strong>{{ ticketly_money($booking->total) }}</strong>
            </div>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Event Details</div>
    <div class="card-body">
      <table class="detail-grid">
        <tr>
          <td>
            <div class="detail-item">
              <div class="detail-label">Event</div>
              <div class="detail-value">{{ $booking->event->title }}</div>
            </div>
          </td>
          <td>
            <div class="detail-item">
              <div class="detail-label">Date</div>
              <div class="detail-value">{{ $eventDateLine }}</div>
            </div>
          </td>
        </tr>
        <tr>
          <td>
            <div class="detail-item">
              <div class="detail-label">Time</div>
              <div class="detail-muted">{{ $eventTimeLine }}</div>
            </div>
          </td>
          <td>
            <div class="detail-item">
              <div class="detail-label">Tickets</div>
              <div class="detail-value">{{ number_format($ticketCount) }}</div>
            </div>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <div class="detail-item" style="padding-right: 0;">
              <div class="detail-label">Venue</div>
              <div class="detail-muted">{{ $venueDisplay }}</div>
            </div>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Ticket Summary</div>
    <div class="card-body">
      <table class="tickets">
        <thead>
          <tr>
            <th>Type</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>
          @foreach($booking->items as $item)
          <tr>
            <td>
              <div class="ticket-name">{{ $item->ticketTier->name }}</div>
            </td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->unit_price == 0 ? 'Free' : ticketly_money($item->unit_price) }}</td>
            <td>{{ $item->unit_price == 0 ? 'Free' : ticketly_money($item->subtotal) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>

      <div class="summary-wrap">
        <table class="summary">
          <tr>
            <td>Subtotal</td>
            <td>{{ ticketly_money($booking->subtotal) }}</td>
          </tr>
          <tr>
            <td>Portal Fee ({{ $portalFeePercentage }}%)</td>
            <td>{{ ticketly_money($booking->portal_fee ?? 0) }}</td>
          </tr>
          <tr>
            <td>Service Fee ({{ $serviceFeePercentage }}%)</td>
            <td>{{ ticketly_money($booking->service_fee ?? 0) }}</td>
          </tr>
          @if(($booking->discount_amount ?? 0) > 0)
          <tr class="discount">
            <td>{{ $promoDiscountLabel }}</td>
            <td>-{{ ticketly_money($booking->discount_amount) }}</td>
          </tr>
          @endif
          <tr class="total">
            <td>Total Paid</td>
            <td>{{ ticketly_money($booking->total) }}</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Attendee and Entry Scan</div>
    <div class="card-body">
      <table class="entry-grid">
        <tr>
          <td class="entry-left">
            <table class="kv">
              <tr>
                <td class="kv-key">Name</td>
                <td class="kv-value">{{ $booking->customer_name }}</td>
              </tr>
              <tr>
                <td class="kv-key">Email</td>
                <td class="kv-value">{{ $booking->customer_email }}</td>
              </tr>
              @if($booking->customer_phone)
              <tr>
                <td class="kv-key">Phone</td>
                <td class="kv-value">{{ $booking->customer_phone }}</td>
              </tr>
              @endif
              <tr>
                <td class="kv-key">Status</td>
                <td class="kv-value">{{ strtoupper($booking->status) }}</td>
              </tr>
            </table>
          </td>
          <td class="entry-right">
            <div class="qr-box">
              <img class="qr-img" src="{{ $qrImageSrc }}" alt="QR code for {{ $booking->reference }}">
            </div>
            <div class="scan-ref">{{ $booking->reference }}</div>
            <div class="note">Show this QR code or booking reference at the gate.</div>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="footer">
    Ticketly | <strong>{{ $booking->reference }}</strong>
  </div>
</div>

</body>
</html>
