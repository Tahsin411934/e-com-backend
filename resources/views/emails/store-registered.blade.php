<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Your store is ready</title></head>
<body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:620px;margin:32px auto;padding:0 16px;">
        <div style="background:#1e3a8a;padding:28px 32px;border-radius:14px 14px 0 0;color:#fff;"><div style="display:inline-block;padding:7px 10px;border-radius:7px;background:#fff;"><img src="{{ $logoUrl }}" alt="AFT SOFT" style="display:block;max-width:180px;max-height:42px;width:auto;height:auto;object-fit:contain;"></div><div style="margin-top:12px;font-size:14px;opacity:.85;">Your free commerce store is ready</div></div>
        <div style="background:#fff;padding:32px;border-radius:0 0 14px 14px;box-shadow:0 8px 24px rgba(15,23,42,.06);">
            <h1 style="margin:0 0 12px;font-size:24px;color:#10245a;">Welcome, {{ $user->name }}!</h1>
            <p style="font-size:15px;line-height:1.7;color:#64748b;">Your store <strong>{{ $store->name }}</strong> has been created successfully.</p>
            <div style="margin:24px 0;padding:18px;border:1px solid #dbeafe;border-radius:10px;background:#f8fbff;"><div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#64748b;">Your storefront URL</div><a href="{{ $storeUrl }}" style="display:block;margin-top:8px;color:#0d9488;font-weight:700;word-break:break-all;">{{ $storeUrl }}</a></div>
            <a href="{{ $loginUrl }}" style="display:inline-block;padding:13px 22px;border-radius:8px;background:#0db89b;color:#fff;text-decoration:none;font-weight:700;">Open dashboard</a>
            <p style="margin:28px 0 0;font-size:13px;line-height:1.6;color:#94a3b8;">You can add products, customize your storefront and connect a custom domain from your dashboard.</p>
        </div>
        <p style="padding:16px;text-align:center;font-size:12px;color:#94a3b8;">© {{ date('Y') }} AFT SOFT</p>
    </div>
</body>
</html>
