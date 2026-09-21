<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Verify your account</title></head>
<body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#1f2937;">
<div style="max-width:620px;margin:32px auto;padding:0 16px;">
<div style="background:#1e3a8a;padding:28px 32px;border-radius:14px 14px 0 0;color:#fff;"><img src="{{ $logoUrl }}" alt="AFT SOFT" style="display:block;max-width:220px;max-height:58px;width:auto;height:auto;"><div style="margin-top:14px;font-size:14px;opacity:.9;">Secure your new store account</div></div>
<div style="background:#fff;padding:32px;border-radius:0 0 14px 14px;box-shadow:0 8px 24px rgba(15,23,42,.06);">
<h1 style="margin:0 0 12px;font-size:24px;color:#10245a;">Verify your email address</h1>
<p style="font-size:15px;line-height:1.7;color:#64748b;">Hi {{ $user->name }}, your store has been created. Please verify this email address to activate dashboard access.</p>
<div style="margin:26px 0;text-align:center;"><a href="{{ $verificationUrl }}" style="display:inline-block;padding:14px 26px;border-radius:8px;background:#0db89b;color:#fff;text-decoration:none;font-weight:700;">Verify my email</a></div>
<p style="font-size:13px;line-height:1.6;color:#94a3b8;">This secure link expires in 24 hours. If you did not create this account, you can safely ignore this email.</p>
<p style="margin:24px 0 0;padding-top:20px;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;">AFT SOFT · Build. Sell. Grow. Together.</p>
</div></div></body></html>
