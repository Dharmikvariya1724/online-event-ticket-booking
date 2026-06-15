<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>New Organiser Registration - Ticketly</title>
</head>
<body style="font-family:Inter,Arial,sans-serif;background:#0f172a;color:#e2e8f0;padding:24px;">
  <div style="max-width:600px;margin:0 auto;background:#111827;border-radius:12px;padding:24px;">
    <h2 style="color:#ffffff;margin-bottom:12px;">New organiser registration</h2>
    <p style="color:#cbd5f5;font-size:14px;line-height:1.6;">
      A new organiser has registered and is awaiting approval.
    </p>
    <ul style="color:#cbd5f5;font-size:14px;line-height:1.6;">
      <li>Name: {{ $organiser->name }}</li>
      <li>Company: {{ $organiser->company_name }}</li>
      <li>Email: {{ $organiser->email }}</li>
      <li>Phone: {{ $organiser->phone ?? 'N/A' }}</li>
    </ul>
    <p style="margin-top:20px;">
      <a href="{{ route('admin.organisers.show', $organiser->id) }}" style="color:#10b981;text-decoration:none;">Review organiser</a>
    </p>
  </div>
</body>
</html>
