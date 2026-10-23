<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="AFT Soft ecommerce platform-এর Admin ও Frontend ব্যবহারের ছবিসহ নির্দেশনা।">
    <title>{{ request()->routeIs('documentation.admin') ? 'Admin Guide' : 'Frontend Guide' }} · AFT Soft Documentation</title>
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
        .public-doc-header-links { display:flex; gap:16px; align-items:center; font-size:13px; }
        .public-doc-skip { position:absolute; top:-100px; left:16px; background:#fff; padding:12px; z-index:100; border:2px solid #0f766e; border-radius:8px; }
        .public-doc-skip:focus { top:12px; }
        .public-doc-footer { max-width:1328px; margin:auto; border-top:1px solid #dce3e8; padding:24px 28px; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; color:#64748b; font-size:12px; }
        @media(max-width:600px) { .public-doc-header { padding:14px 16px; } .public-doc-brand { font-size:15px; } .public-doc-header-links>span { display:none; } }
        .text-xs { font-size:12px; } .text-sm { font-size:14px; } .text-gray-500 { color:#64748b; }
        .text-teal-700 { color:#0f766e; } .mt-2 { margin-top:8px; }
        @media(prefers-reduced-motion:reduce) { html { scroll-behavior:auto; } }
        @media print { .public-doc-header { display:none; } body { background:#fff; } }
    </style>
    @stack('head')
</head>
<body>
    <a class="public-doc-skip" href="#documentation-content">গাইডে যান</a>
    <header class="public-doc-header">
        <nav aria-label="Public documentation">
            <a class="public-doc-brand" href="{{ route('documentation.index') }}">AFT Soft · Documentation</a>
            <div class="public-doc-header-links"><span>E-commerce Help Center</span><a class="public-doc-login" href="{{ route('login') }}">Dashboard Login →</a></div>
        </nav>
    </header>
    {{ $slot }}
    <footer class="public-doc-footer"><span>© {{ now()->year }} AFT Soft · E-commerce Documentation</span><a href="https://aftsoftandlimited.com">AFT Soft website ↗</a></footer>
    @stack('scripts')
</body>
</html>
