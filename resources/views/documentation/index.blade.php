<x-app-layout>
    @php($sections = require resource_path('views/documentation/sections.php'))
    @push('head')
        <style>
            .guide { max-width:1280px; margin:auto; padding:28px; color:#1e293b; }
            .guide h1 { font-size:30px; font-weight:800; margin-bottom:10px; }
            .guide p { line-height:1.9; margin:10px 0; }
            .guide-layout { display:grid; grid-template-columns:230px minmax(0,1fr); gap:28px; margin-top:26px; align-items:start; }
            .guide-nav { position:sticky; top:20px; padding:18px; border:1px solid #e2e8f0; border-radius:14px; background:#fff; }
            .guide-nav a { display:block; padding:8px 0; color:#0f766e; font-size:14px; }
            .guide-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; margin-bottom:24px; scroll-margin-top:24px; }
            .guide-card h2 { font-size:22px; font-weight:700; margin-bottom:16px; }
            .guide-preview { position:relative; overflow:hidden; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; }
            .guide-preview img { position:absolute; top:0; left:0; width:100%; max-width:none; height:auto; }
            .guide-menu { padding:10px 14px; background:#ecfdf5; color:#065f46; border-radius:8px; margin:18px 0; }
            .guide ol, .guide ul { padding-left:24px; line-height:1.9; }
            .guide ol { list-style:decimal; } .guide ul { list-style:disc; }
            .guide li { padding:5px 0; }
            .guide-note { border-left:3px solid #eab308; background:#fffbeb; padding:12px 16px; border-radius:6px; font-size:14px; }
            .guide-action { display:inline-block; padding:9px 16px; border:1px solid #cbd5e1; border-radius:8px; margin-right:8px; color:#0f766e; }
            .dark .guide { color:#e2e8f0; } .dark .guide-card,.dark .guide-nav { background:#1f2937; border-color:#475569; }
            .dark .guide-nav a { color:#5eead4; } .dark .guide-note { background:#422006; color:#fef3c7; }
            @media(max-width:900px) { .guide-layout { grid-template-columns:1fr; } .guide-nav { position:static; } .guide { padding:16px; } .guide-card { padding:16px; } }
            @media print { #sidebar,header,.guide-nav,.guide-actions { display:none!important; } .guide-layout { display:block; } .guide { padding:0; color:#000; } .guide-card { break-inside:avoid; box-shadow:none; } }
        </style>
    @endpush
    <main class="guide" lang="bn">
        <h1>Documentation · Homepage Setup</h1>
        <p>OneHaat.bd-এর দেওয়া homepage screenshot অনুসারে কোন অংশ কোথা থেকে সাজাবেন—ছবিসহ ধাপে ধাপে নির্দেশনা।</p>
        <p>ছবিটি design reference। নিজের store-এর logo, product, দাম ও policy ব্যবহার করুন। Menu দেখার ও পরিবর্তনের permission আলাদা; কোনো menu না পেলে platform admin-এর সঙ্গে যোগাযোগ করুন। Store Owner নিজের store-এর content পরিচালনা করবেন; platform admin হলে সঠিক store নির্বাচন করুন।</p>
        <div class="guide-actions">
            <a class="guide-action" href="{{ asset('images/documentation/homepage.png') }}" target="_blank" rel="noopener">সম্পূর্ণ screenshot দেখুন</a>
            <button type="button" class="guide-action" onclick="window.print()">Print / Save as PDF</button>
        </div>
        <div class="guide-layout">
            <nav class="guide-nav" aria-label="Documentation sections">
                <strong>এই গাইডে যা আছে</strong>
                <a href="#before-start">শুরু করার আগে</a>
                @foreach($sections as $section)
                    <a href="#{{ $section['id'] }}">{{ $loop->iteration }}. {{ $section['title'] }}</a>
                @endforeach
                <a href="#verification">শেষে যা পরীক্ষা করবেন</a>
            </nav>
            <div>
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
                    @php([$start, $end] = $section['area'])
                    <section class="guide-card" id="{{ $section['id'] }}">
                        <h2>{{ $loop->iteration }}. {{ $section['title'] }}</h2>
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
                                <li>{{ $step }}</li>
                            @endforeach
                        </ol>
                        <p class="guide-note"><strong>মনে রাখুন:</strong> {{ $section['note'] }}</p>
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
    </main>
</x-app-layout>
