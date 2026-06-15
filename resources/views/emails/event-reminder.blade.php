<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Event Reminder</title>
<style>
  body { font-family: Arial, sans-serif; background: #f6f8fb; color: #1f2937; margin: 0; padding: 24px; }
  .card { max-width: 620px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
  .head { background: #0f172a; color: #ffffff; padding: 20px 24px; }
  .head h1 { margin: 0; font-size: 20px; }
  .body { padding: 24px; line-height: 1.6; }
  .meta { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin: 16px 0; }
  .meta p { margin: 6px 0; }
  .cta { display: inline-block; background: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 8px; padding: 10px 16px; font-weight: 600; margin-top: 8px; }
  .foot { padding: 16px 24px 22px; color: #6b7280; font-size: 13px; border-top: 1px solid #e5e7eb; }
</style>
</head>
<body>
  <div class="card">
    <div class="head">
      <h1>Ticketly Event Reminder</h1>
    </div>

    <div class="body">
      <p>Hi {{ $booking->customer_name ?: 'there' }},</p>
      <p><strong>{{ $label }}</strong></p>
      <p>This is a reminder for your booking reference <strong>{{ $booking->reference }}</strong>.</p>

      <div class="meta">
        <p><strong>Event:</strong> {{ $booking->event->title }}</p>
        <p><strong>Date:</strong> {{ ticketly_format_date($booking->event->starts_at) }}</p>
        <p><strong>Time:</strong> {{ ticketly_format_time($booking->event->starts_at) }}</p>
        <p><strong>Venue:</strong> {{ $booking->event->venue_name }}, {{ $booking->event->city }}</p>
      </div>

      <a class="cta" href="{{ route('events.show', $booking->event->slug) }}">View Event</a>
    </div>

    <div class="foot">
      <div>This email was sent to {{ $booking->customer_email }}</div>
      <div>Booking ref: {{ $booking->reference }}</div>
    </div>
  </div>
</body>
</html>
