<x-dynamic-component :component="auth()->user()?->hasAdminAccess() ? 'app-layout' : 'documentation-layout'">
    @php
        $sections = require resource_path('views/documentation/sections.php');
        $isAdminGuide = request()->routeIs('documentation.admin');
        $adminSections = require resource_path('views/documentation/admin-sections.php');
        $sectionActions = [
            'announcement' => ['frontend.announcement-bars.index', 'frontend.announcements.view', 'Add or Update Announcement Bar'],
            'header' => ['frontend.site-settings.index', 'frontend.settings.view', 'Update Logo & Site Settings'],
            'navigation' => ['frontend.nav-items.index', 'frontend.navbar.view', 'Add or Update Navigation'],
            'hero' => ['frontend.banners.index', 'frontend.banners.view', 'Add or Update Banner'],
            'categories' => ['categories.index', 'categories.view', 'Manage Categories'],
            'flash-sale' => ['campaigns.index', 'campaigns.view', 'Add or Update Campaign'],
            'promotions' => ['frontend.ctas.index', 'frontend.ctas.view', 'Add or Update Homepage Action'],
            'electronics' => ['products.index', 'products.view', 'Add or Update Products'],
            'fashion' => ['products.index', 'products.view', 'Add or Update Products'],
            'home-living' => ['products.index', 'products.view', 'Add or Update Products'],
            'footer' => ['frontend.site-settings.index', 'frontend.settings.view', 'Update Contact & Social Links'],
        ];
    @endphp
    @push('head')
        <style>
            .guide { max-width:1280px; margin:auto; padding:28px; color:#1e293b; }
            .guide h1 { font-size:30px; font-weight:800; margin-bottom:10px; }
            .guide p { line-height:1.9; margin:10px 0; }
            .guide-layout { display:grid; grid-template-columns:230px minmax(0,1fr); gap:28px; margin-top:26px; align-items:start; }
            .guide-nav { position:sticky; top:20px; max-height:calc(100vh - 100px); overflow-y:auto; padding:18px; border:1px solid #e2e8f0; border-radius:14px; background:#fff; }
            .guide-nav a { display:block; padding:8px 0; color:#0f766e; font-size:14px; }
            .guide-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; margin-bottom:24px; scroll-margin-top:24px; }
            .guide-card h2 { font-size:22px; font-weight:700; margin-bottom:16px; }
            .guide-section-heading { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px; }
            .guide-section-heading h2 { margin-bottom:0; }
            .guide-edit { display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:8px; background:#0f766e; color:#fff; font-size:13px; font-weight:600; }
            .guide-edit:hover { background:#115e59; }
            .guide-edit:focus-visible { outline:3px solid #14b8a6; outline-offset:3px; }
            .guide-edit-note { font-size:13px; color:#64748b; }
            .dark .guide-edit-note { color:#cbd5e1; }
            @media print { .guide-edit,.guide-edit-note { display:none!important; } }
            .guide-preview { position:relative; overflow:hidden; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; }
            .guide-preview img { position:absolute; top:0; left:0; width:100%; max-width:none; height:auto; }
            .guide-menu { padding:10px 14px; background:#ecfdf5; color:#065f46; border-radius:8px; margin:18px 0; }
            .guide ol, .guide ul { padding-left:24px; line-height:1.9; }
            .guide ol { list-style:decimal; } .guide ul { list-style:disc; }
            .guide li { padding:5px 0; }
            .guide-step { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; }
            .guide-step-text { flex:1; min-width:0; }
            .guide-preview-button { flex-shrink:0; border:1px solid #0f766e; color:#0f766e; background:#f0fdfa; border-radius:7px; padding:5px 12px; font:inherit; font-size:12px; cursor:pointer; }
            .guide-preview-button:focus-visible { outline:3px solid #14b8a6; outline-offset:2px; }
            .guide-dialog { width:min(1100px,calc(100vw - 32px)); max-height:85vh; overflow:auto; margin:auto; border:1px solid #cbd5e1; border-radius:16px; padding:24px; background:#fff; color:#1e293b; }
            .guide-dialog::backdrop { background:rgba(15,23,42,.65); }
            .guide-dialog-heading { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:14px; }
            .guide-dialog-heading h3 { font-size:20px; font-weight:700; }
            .guide-dialog-step { padding:12px 16px; background:#f1f5f9; border-radius:8px; }
            .guide-announcement-scroll { overflow-x:auto; margin:18px 0; }
            .guide-announcement-labels,.guide-announcement-example { display:grid; grid-template-columns:1fr 1fr 1fr; min-width:900px; gap:16px; padding:10px 16px; font-size:12px; }
            .guide-announcement-labels { color:#64748b; background:#f8fafc; }
            .guide-announcement-example { background:#004d40; color:#fff; }
            .guide-announcement-labels span:nth-child(2),.guide-announcement-example span:nth-child(2) { text-align:center; }
            .guide-announcement-labels span:last-child,.guide-announcement-example span:last-child { text-align:right; }
            .dark .guide-dialog { background:#1f2937; color:#e2e8f0; border-color:#475569; }
            .dark .guide-dialog-step { background:#334155; }
            @media(max-width:600px) { .guide-step { flex-direction:column; gap:6px; } .guide-dialog { padding:16px; } }
            @media print { .guide-preview-button,.guide-dialog { display:none!important; } }
            .guide-note { border-left:3px solid #eab308; background:#fffbeb; padding:12px 16px; border-radius:6px; font-size:14px; }
            .guide-action { display:inline-block; padding:9px 16px; border:1px solid #cbd5e1; border-radius:8px; margin-right:8px; color:#0f766e; }
            .guide-map-scroll { overflow-x:auto; border:1px solid #cbd5e1; border-radius:12px; margin:18px 0; }
            .guide-map { position:relative; min-width:680px; background:#fff; }
            .guide-map > img { display:block; width:100%; height:auto; }
            .guide-hotspot { position:absolute; left:0; right:0; border:2px solid #0f766e; pointer-events:none; }
            .guide-hotspot:focus-within,.guide-hotspot:hover { border-color:#f97316; background:rgba(20,184,166,.08); }
            .guide-hotspot-tools { position:absolute; right:2px; top:0; display:flex; align-items:center; gap:4px; pointer-events:auto; font-size:11px; line-height:18px; }
            .guide-hotspot-tools a,.guide-hotspot-tools span { display:inline-block; padding:0 7px; border-radius:3px; background:#0f766e; color:#fff; box-shadow:0 1px 4px #0005; }
            .guide-hotspot-tools a:last-child { background:#fff; color:#0f766e; border:1px solid #0f766e; }
            .guide-hotspot-tools a:focus-visible { outline:3px solid #f97316; outline-offset:2px; }
            .guide-shortcuts { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:12px; }
            .guide-shortcut { border:1px solid #cbd5e1; border-radius:10px; padding:14px; }
            .guide-shortcut strong { display:block; margin-bottom:10px; }
            .guide-shortcut a { margin-top:6px; }
            .dark .guide-shortcut { border-color:#475569; }
            .guide-tabs { display:flex; gap:10px; flex-wrap:wrap; margin:20px 0; }
            .guide-tabs a { padding:12px 20px; border:1px solid #cbd5e1; border-radius:10px; font-weight:700; }
            .guide-tabs a[aria-current="page"] { background:#0f766e; border-color:#0f766e; color:#fff; }
            .guide-content { min-width:0; }
            .guide-option { padding:18px 0; border-top:1px solid #e2e8f0; }
            .guide-option h3 { font-size:17px; font-weight:700; }
            .guide-option p { font-size:14px; }
            .guide-option .guide-section-heading { margin-bottom:6px; }
            @media print { .guide-map-scroll { overflow:visible; } .guide-map { min-width:0; } .guide-hotspot-tools { display:none; } .guide-shortcuts { display:none; } }
            .dark .guide { color:#e2e8f0; } .dark .guide-card,.dark .guide-nav { background:#1f2937; border-color:#475569; }
            .dark .guide-nav a { color:#5eead4; } .dark .guide-note { background:#422006; color:#fef3c7; }
            @media(max-width:900px) { .guide-layout { grid-template-columns:1fr; } .guide-nav { position:static; max-height:300px; } .guide { padding:16px; } .guide-card { padding:16px; } }
            @media print { #sidebar,header,.guide-nav,.guide-actions { display:none!important; } .guide-layout { display:block; } .guide { padding:0; color:#000; } .guide-card { break-inside:avoid; box-shadow:none; } }
        </style>
    @endpush
    <main class="guide" lang="bn">
        <h1>Documentation · {{ $isAdminGuide ? 'Admin Guide' : 'Frontend Guide' }}</h1>
        <nav class="guide-tabs" aria-label="Documentation parts">
            <a href="{{ route('documentation.admin') }}" @if($isAdminGuide) aria-current="page" @endif>01 · Admin Guide</a>
            <a href="{{ route('documentation.index') }}" @if(!$isAdminGuide) aria-current="page" @endif>02 · Frontend Guide</a>
        </nav>
        @if($isAdminGuide)
            @include('documentation.admin')
        @else
        <p>OneHaat.bd-এর দেওয়া homepage screenshot অনুসারে কোন অংশ কোথা থেকে সাজাবেন—ছবিসহ ধাপে ধাপে নির্দেশনা।</p>
        <p>ছবিটি design reference। নিচের নাম, message, রং, category, offer ও button text-গুলো শুধু উদাহরণ—নিজের store-এর জন্য নিজের পছন্দমতো content দিন। ছবি বা উদাহরণের লেখা হুবহু ব্যবহার করা বাধ্যতামূলক নয়। Upload limit, প্রয়োজনীয় field ও প্রকাশের status-এর নিয়মগুলো প্রযোজ্য থাকবে। Menu দেখার ও পরিবর্তনের permission আলাদা; কোনো menu না পেলে platform admin-এর সঙ্গে যোগাযোগ করুন। Store Owner নিজের store-এর content পরিচালনা করবেন; platform admin হলে সঠিক store নির্বাচন করুন।</p>
        <div class="guide-actions">
            <a class="guide-action" href="{{ asset('images/documentation/homepage.png') }}" target="_blank" rel="noopener">সম্পূর্ণ screenshot দেখুন</a>
            <button type="button" class="guide-action" onclick="window.print()">Print / Save as PDF</button>
        </div>
        <div class="guide-layout">
            <nav class="guide-nav" aria-label="Documentation sections">
                <strong>এই গাইডে যা আছে</strong>
                <a href="#homepage-map">পুরো ছবিতে shortcuts</a>
                <a href="#before-start">শুরু করার আগে</a>
                @foreach($sections as $section)
                    <a href="#{{ $section['id'] }}">{{ $loop->iteration }}. {{ $section['title'] }}</a>
                @endforeach
                <a href="#verification">শেষে যা পরীক্ষা করবেন</a>
            </nav>
            <div class="guide-content">
        <section class="guide-card" id="homepage-map">
            <h2>পুরো Homepage — চিহ্নিত অংশ থেকে সরাসরি কাজ করুন</h2>
            <p>প্রতিটি সবুজ বক্স একটি section। <strong>Add / Update</strong> দিয়ে management page খুলুন, সেখান থেকে নতুন content যোগ বা পুরোনোটি edit করুন। <strong>Details</strong> দিয়ে নিচের ছবিসহ সম্পূর্ণ নির্দেশনা পড়ুন। ছোট screen-এ ছবিটি ডানে-বামে scroll করুন; নিচের shortcut list-ও ব্যবহার করতে পারবেন।</p>
            <div class="guide-map-scroll" tabindex="0" role="region" aria-label="Marked homepage screenshot; scroll horizontally on small screens">
                <div class="guide-map">
                    <img src="{{ asset('images/documentation/homepage.png') }}" width="896" height="1754" alt="OneHaat.bd সম্পূর্ণ homepage; প্রতিটি section numbered box দিয়ে চিহ্নিত">
                    @foreach($sections as $section)
                        @php
                            [$start, $end] = $section['area'];
                            $action = $sectionActions[$section['id']] ?? null;
                        @endphp
                        <div class="guide-hotspot" style="top:{{ $start / 1754 * 100 }}%;height:{{ ($end - $start) / 1754 * 100 }}%">
                            <div class="guide-hotspot-tools">
                                <a href="#{{ $section['id'] }}" aria-label="{{ $loop->iteration }}. {{ $section['title'] }}">{{ $loop->iteration }}. {{ explode(' — ', $section['title'])[0] }}</a>
                                @if($action && auth()->user()?->hasPermission($action[1]))
                                    <a href="{{ route($action[0]) }}" aria-label="{{ $action[2] }}">Add / Update &rarr;</a>
                                @elseif($action && auth()->guest())
                                    <a href="{{ route('login') }}">Login to manage &rarr;</a>
                                @endif
                                <a href="#{{ $section['id'] }}" aria-label="Details: {{ $section['title'] }}">Details &darr;</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="guide-shortcuts" aria-label="Homepage management shortcuts">
                @foreach($sections as $section)
                    @php
                        $action = $sectionActions[$section['id']] ?? null;
                    @endphp
                    <div class="guide-shortcut">
                        <strong>{{ $loop->iteration }}. {{ $section['title'] }}</strong>
                        @if($action && auth()->user()?->hasPermission($action[1]))
                            <a class="guide-edit" href="{{ route($action[0]) }}">{{ $action[2] }} &rarr;</a>
                        @elseif($action && auth()->guest())
                            <a class="guide-edit" href="{{ route('login') }}">Login to manage &rarr;</a>
                        @elseif($action)
                            <p class="guide-edit-note">Management access পেতে admin-এর সঙ্গে যোগাযোগ করুন।</p>
                        @else
                            <p class="guide-edit-note">Dashboard editor নেই; নিচে frontend setup নির্দেশনা দেখুন।</p>
                        @endif
                        <a class="guide-action" href="#{{ $section['id'] }}">বিস্তারিত নির্দেশনা &darr;</a>
                    </div>
                @endforeach
            </div>
        </section>
                <section class="guide-card" id="before-start">
                    <h2>শুরু করার আগে</h2>
                    <ol>
                        <li>নিজের Store Owner account-এ login করুন। Store ও domain ঠিক আছে কি না নিশ্চিত করুন।</li>
                        <li>আগে Site Settings → Categories → Products তৈরি করুন। তারপর Navigation, Banners, Campaigns ও Homepage CTAs সাজান।</li>
                        <li>নিজের original image ও সঠিক destination link প্রস্তুত রাখুন। এই screenshot-এর ছোট অংশগুলো নির্দেশনার জন্য; এগুলো upload করার artwork নয়।</li>
                        <li>Save করার পর নিজের store-এর domain খুলে desktop ও mobile দুই জায়গায় দেখুন। এই backend-এর settings দেখানোর জন্য frontend-এ সংশ্লিষ্ট integration থাকতে হবে।</li>
                    </ol>
                </section>
                @foreach($sections as $section)
                    @php
                        [$start, $end] = $section['area'];
                    @endphp
                    <section class="guide-card" id="{{ $section['id'] }}">
                        <div class="guide-section-heading">
                            <h2>{{ $loop->iteration }}. {{ $section['title'] }}</h2>
                            @php
                                $action = $sectionActions[$section['id']] ?? null;
                            @endphp
                            @if($action && auth()->user()?->hasPermission($action[1]))
                                <a class="guide-edit" href="{{ route($action[0]) }}">
                                    {{ $action[2] }} <span aria-hidden="true">&rarr;</span>
                                </a>
                            @elseif($action && auth()->guest())
                                <a class="guide-edit" href="{{ route('login') }}">Login to manage &rarr;</a>
                            @elseif($action)
                                <span class="guide-edit-note">এই settings খুলতে admin-এর কাছে access চাইতে হবে।</span>
                            @else
                                <span class="guide-edit-note">এই অংশ পরিবর্তনে frontend developer-এর সহায়তা প্রয়োজন।</span>
                            @endif
                        </div>
                        <figure>
                            <div class="guide-preview" style="aspect-ratio:896 / {{ $end - $start }}">
                                <img src="{{ asset('images/documentation/homepage.png') }}"
                                    width="896" height="1754" alt="Reference: {{ $section['title'] }}"
                                    style="transform:translateY(-{{ $start / 1754 * 100 }}%)">
                            </div>
                            <figcaption class="text-xs mt-2 text-gray-500">দেওয়া homepage screenshot-এর সংশ্লিষ্ট অংশ</figcaption>
                        </figure>
                        <div class="guide-menu"><strong>কোথায় যাবেন:</strong> {{ $section['menu'] }}</div>
                        <ol>
                            @foreach($section['steps'] as $step)
                                <li>
                                    <div class="guide-step">
                                        <span class="guide-step-text">{{ $step }}</span>
                                        <button type="button" class="guide-preview-button"
                                            data-guide-preview="preview-{{ $section['id'] }}" data-preview-step="{{ $step }}"
                                            aria-label="Preview {{ $section['title'] }}, step {{ $loop->iteration }}"
                                            aria-haspopup="dialog" aria-controls="preview-{{ $section['id'] }}">Preview</button>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                        <dialog class="guide-dialog" id="preview-{{ $section['id'] }}" aria-labelledby="preview-title-{{ $section['id'] }}">
                            <div class="guide-dialog-heading">
                                <h3 id="preview-title-{{ $section['id'] }}">Frontend Preview · {{ $section['title'] }}</h3>
                                <form method="dialog"><button class="guide-preview-button" type="submit" autofocus>Close ×</button></form>
                            </div>
                            <p class="guide-dialog-step" data-preview-instruction></p>
                            @if($section['id'] === 'announcement')
                                <p>এই উদাহরণে desktop-এর বাম, মাঝ ও ডান অংশে লেখার অবস্থান দেখানো হয়েছে। নিজের store-এর message ব্যবহার করুন।</p>
                                <div class="guide-announcement-scroll" tabindex="0" role="region" aria-label="Announcement text placement preview">
                                    <div class="guide-announcement-labels"><span>Left Text · ৬–৮ শব্দ</span><span>Center Text · ৬–৮ শব্দ</span><span>Right Text · ৫–৭ শব্দ</span></div>
                                    <div class="guide-announcement-example"><span>Free Shipping on Orders Over ৳500</span><span>Cash on Delivery Available Across Bangladesh</span><span>Need Help Call Our Support Team</span></div>
                                </div>
                                <p class="text-sm text-gray-500">ছোট mobile screen-এ শুধু মাঝের message দেখাবে। Preview-টি sample; এটি আপনার saved announcement নয়।</p>
                            @endif
                            <figure>
                                <div class="guide-preview" style="aspect-ratio:896 / {{ $end - $start }}">
                                    <img src="{{ asset('images/documentation/homepage.png') }}" width="896" height="1754"
                                        alt="Frontend section preview: {{ $section['title'] }}"
                                        style="transform:translateY(-{{ $start / 1754 * 100 }}%)">
                                </div>
                                <figcaption class="text-xs mt-2 text-gray-500">Reference homepage-এর এই অংশে আপনার content দেখাবে। এটি screenshot preview; পরিবর্তন save বা publish করে না।</figcaption>
                            </figure>
                        </dialog>
                        <p class="guide-note"><strong>মনে রাখুন:</strong> {{ $section['note'] }}</p>
                        <a class="guide-action" href="#homepage-map">↑ পুরো ছবির shortcuts</a>
                        <a class="text-sm text-teal-700" href="#before-start">↑ শুরুতে যান</a>
                    </section>
                @endforeach
                <section class="guide-card" id="verification">
                    <h2>শেষে যা পরীক্ষা করবেন</h2>
                    <ul>
                        <li>কোনো content না দেখালে সঠিক store, active status, product-এর public visibility ও category assignment পরীক্ষা করুন।</li>
                        <li>Homepage product না মিললে Is Homepage এবং product/category Sort Order পরীক্ষা করুন।</li>
                        <li>ছবি না এলে file upload ও public storage link platform admin দিয়ে পরীক্ষা করান।</li>
                        <li>প্রতিটি menu, Shop Now, View All, search, cart ও wishlist link পরীক্ষা করুন।</li>
                        <li>Flash Sale-এর সময় ও checkout price মিলিয়ে নিন। Shipping banner-এর প্রতিশ্রুতি checkout rules-এর সঙ্গে মেলে কি না দেখুন।</li>
                        <li>দুইটি আলাদা store খুলে নিশ্চিত করুন এক store-এর logo বা product অন্য store-এ দেখাচ্ছে না।</li>
                        <li>Mobile-এ লেখা কাটা যাচ্ছে কি না, category scrolling ও banner button কাজ করছে কি না দেখুন।</li>
                    </ul>
                </section>
            </div>
        </div>
        @endif
    </main>
    @push('scripts')
        <script>
            document.addEventListener('click', function (event) {
                if (!(event.target instanceof Element)) return;
                const button = event.target.closest('[data-guide-preview]');
                if (!button) return;
                const dialog = document.getElementById(button.dataset.guidePreview);
                if (!(dialog instanceof HTMLDialogElement)) return;
                dialog.querySelector('[data-preview-instruction]').textContent = button.dataset.previewStep;
                dialog.showModal();
            });
        </script>
    @endpush
</x-dynamic-component>
