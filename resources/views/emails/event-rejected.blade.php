<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Event Rejected - Ticketly</title>
</head>
<body style="font-family:Inter,Arial,sans-serif;background:#0f172a;color:#e2e8f0;padding:24px;">
  <div style="max-width:600px;margin:0 auto;background:#111827;border-radius:12px;padding:24px;">
    <h2 style="color:#ffffff;margin-bottom:12px;">Event review update</h2>
    <p style="color:#cbd5f5;font-size:14px;line-height:1.6;">
      Your event <strong>{{ $event->title }}</strong> was not approved.
    </p>
    <p style="color:#cbd5f5;font-size:14px;line-height:1.6;">
      Reason: <strong>{{ $reason }}</strong>
    </p>
    <p style="margin-top:20px;">
      <a href="{{ route('organiser.events.edit', $event->id) }}" style="color:#10b981;text-decoration:none;">Edit event</a>
    </p>
  </div>
</body>
</html>
