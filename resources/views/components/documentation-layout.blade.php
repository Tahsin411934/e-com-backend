<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="AFT Soft ecommerce platform-এর Admin ও Frontend ব্যবহারের ছবিসহ নির্দেশনা।">
    <title>Documentation · AFT Soft</title>
    <style>
        *,*::before,*::after { box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body { margin:0; background:#f1f5f9; color:#1e293b; font-family:system-ui,-apple-system,"Segoe UI",sans-serif; }
        a { color:inherit; text-decoration:none; }
        button { font:inherit; background:transparent; cursor:pointer; }
        img { max-width:100%; }
        h1,h2,h3,p,figure { margin:0; }
        .public-doc-header { background:#fff; border-bottom:1px solid #e2e8f0; padding:16px 28px; }
        .public-doc-header nav { max-width:1224px; margin:auto; display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .public-doc-brand { font-size:18px; font-weight:800; color:#0f766e; }
        .public-doc-login { padding:8px 16px; border:1px solid #0f766e; border-radius:8px; color:#0f766e; }
        .text-xs { font-size:12px; } .text-sm { font-size:14px; } .text-gray-500 { color:#64748b; }
        .text-teal-700 { color:#0f766e; } .mt-2 { margin-top:8px; }
        @media(prefers-reduced-motion:reduce) { html { scroll-behavior:auto; } }
        @media print { .public-doc-header { display:none; } body { background:#fff; } }
    </style>
    @stack('head')
</head>
<body>
    <header class="public-doc-header">
        <nav aria-label="Public documentation">
            <a class="public-doc-brand" href="{{ route('documentation.index') }}">AFT Soft · Documentation</a>
            <a class="public-doc-login" href="{{ route('login') }}">Login</a>
        </nav>
    </header>
    {{ $slot }}
    @stack('scripts')
</body>
</html>
