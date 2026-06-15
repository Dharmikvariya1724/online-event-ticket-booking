<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Organiser Approved - Ticketly</title>
  <style>
    body { margin: 0; padding: 0; background: #0b1220; color: #e5e7eb; font-family: Arial, sans-serif; }
    .wrapper { max-width: 620px; margin: 0 auto; padding: 24px; }
    .card { background: #111827; border: 1px solid #1f2937; border-radius: 12px; overflow: hidden; }
    .head { background: linear-gradient(135deg, #4f46e5, #7c3aed); padding: 22px 24px; color: #ffffff; }
    .head h1 { margin: 0; font-size: 22px; }
    .body { padding: 24px; }
    .body p { margin: 0 0 14px; line-height: 1.65; color: #d1d5db; }
    .note { margin-top: 16px; padding: 14px; border-radius: 10px; background: #0f1f15; border: 1px solid #14532d; color: #bbf7d0; }
    .foot { padding: 18px 24px; border-top: 1px solid #1f2937; color: #9ca3af; font-size: 12px; }
    .foot a { color: #818cf8; text-decoration: none; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="card">
      <div class="head">
        <h1>Registration Approved</h1>
      </div>
      <div class="body">
        <p>Hi {{ $organiser->name }},</p>
        <p>Great news! Your Ticketly organiser account has been approved.<strong>You can now sign in and start publishing events.</strong></p>
        <div class="note">
          Account email: <strong>{{ $organiser->email }}</strong>
        </div>
        <a style="display: inline-block; margin-top: 16px; padding: 11px 16px; border-radius: 8px; color: #fff; background: #4f46e5; text-decoration: none; font-weight: 700;" href="{{ route('organiser.login') }}">Go to Organiser Login</a>
      </div>
      <div class="foot">
        Ticketly Team<br>
        Need help? <a href="mailto:support@ticketly.com">support@ticketly.com</a>
      </div>
    </div>
  </div>
</body>
</html>

