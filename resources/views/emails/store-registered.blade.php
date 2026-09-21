<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Your store is ready</title></head>
<body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:620px;margin:32px auto;padding:0 16px;">
        <div style="background:#1e3a8a;padding:28px 32px;border-radius:14px 14px 0 0;color:#fff;"><img src="{{ $logoUrl }}" alt="AFT SOFT" style="display:block;max-width:220px;max-height:58px;width:auto;height:auto;object-fit:contain;"><div style="margin-top:14px;font-size:14px;opacity:.9;">Your free commerce store is ready</div></div>
        <div style="background:#fff;padding:32px;border-radius:0 0 14px 14px;box-shadow:0 8px 24px rgba(15,23,42,.06);">
            <h1 style="margin:0 0 12px;font-size:24px;color:#10245a;">Welcome, {{ $user->name }}!</h1>
            <p style="font-size:15px;line-height:1.7;color:#64748b;">Your store <strong>{{ $store->name }}</strong> has been created successfully.</p>
            <p style="font-size:15px;line-height:1.7;color:#64748b;">You are all set to start building your online business. Add products, customize your storefront and begin selling from one simple dashboard.</p>
            <div style="margin:24px 0;padding:18px;border:1px solid #dbeafe;border-radius:10px;background:#f8fbff;"><div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#64748b;">Your storefront URL</div><a href="{{ $storeUrl }}" style="display:block;margin-top:8px;color:#0d9488;font-weight:700;word-break:break-all;">{{ $storeUrl }}</a></div>
            <a href="{{ $loginUrl }}" style="display:inline-block;padding:13px 22px;border-radius:8px;background:#0db89b;color:#fff;text-decoration:none;font-weight:700;">Open dashboard</a>
            <div style="margin-top:30px;padding-top:24px;border-top:1px solid #e2e8f0;">
                <div style="font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#64748b;">Next steps</div>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:14px;border-collapse:separate;border-spacing:0 9px;">
                    <tr>
                        <td width="38" valign="top"><div style="width:30px;height:30px;line-height:30px;border-radius:50%;background:#e0f7f3;color:#087f72;text-align:center;font-weight:700;font-size:13px;">1</div></td>
                        <td valign="top"><div style="font-size:14px;font-weight:700;color:#10245a;">Log in to your dashboard</div><div style="margin-top:2px;font-size:12px;color:#64748b;">Access your store and explore the setup options.</div></td>
                    </tr>
                    <tr>
                        <td width="38" valign="top"><div style="width:30px;height:30px;line-height:30px;border-radius:50%;background:#e0f7f3;color:#087f72;text-align:center;font-weight:700;font-size:13px;">2</div></td>
                        <td valign="top"><div style="font-size:14px;font-weight:700;color:#10245a;">Add your first product</div><div style="margin-top:2px;font-size:12px;color:#64748b;">Build your catalog and start bringing your ideas to life.</div></td>
                    </tr>
                    <tr>
                        <td width="38" valign="top"><div style="width:30px;height:30px;line-height:30px;border-radius:50%;background:#e0f7f3;color:#087f72;text-align:center;font-weight:700;font-size:13px;">3</div></td>
                        <td valign="top"><div style="font-size:14px;font-weight:700;color:#10245a;">Connect a custom domain</div><div style="margin-top:2px;font-size:12px;color:#64748b;">Use your own domain whenever you are ready to grow.</div></td>
                    </tr>
                </table>
            </div>
            <div style="margin-top:24px;padding:16px;border-radius:9px;background:#f8fafc;color:#64748b;font-size:13px;line-height:1.6;">
                Need help getting started? Visit your dashboard to manage your store, products, orders and storefront settings. Your free store URL is available immediately.
            </div>
        </div>
        <p style="padding:16px;text-align:center;font-size:12px;color:#94a3b8;">© {{ date('Y') }} AFT SOFT</p>
    </div>
</body>
</html>
