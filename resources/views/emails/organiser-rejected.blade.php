<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Organiser Application - Ticketly</title>
</head>
<body style="font-family:Inter,Arial,sans-serif;background:#0f172a;color:#e2e8f0;padding:24px;">
  <div style="max-width:600px;margin:0 auto;background:#111827;border-radius:12px;padding:24px;">
    <h2 style="color:#ffffff;margin-bottom:12px;">Organiser application update</h2>
    <p style="color:#cbd5f5;font-size:14px;line-height:1.6;">
      Hi {{ $organiser->name }},<br><br>
      Thank you for applying to become a Ticketly organiser. Unfortunately, we were unable to approve your application at this time.
    </p>
    <p style="color:#cbd5f5;font-size:14px;line-height:1.6;">
      Reason: <strong>{{ $reason }}</strong>
    </p>
    <p style="margin-top:20px;color:#94a3b8;font-size:13px;">
      If you have questions, please contact our support team.
    </p>
  </div>
</body>
</html>
