<x-dynamic-component :component="auth()->user()?->hasAdminAccess() ? 'app-layout' : 'documentation-layout'">
    @php
        $sections = require resource_path('views/documentation/sections.php');
        $isAdminGuide = request()->routeIs('documentation.admin');
        $adminSections = require resource_path('views/documentation/admin-sections.php');
        $frontendTopics = require resource_path('views/documentation/frontend-topics.php');
        $topicCount = $isAdminGuide ? count($adminSections) : count($sections) + count($frontendTopics);
        $optionCount = array_sum(array_map(fn ($section) => count($section['items']), $adminSections));
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
            'benefits' => ['frontend.service-benefits.index', 'frontend.service-benefits.view', 'Manage Service Benefits'],
        ];
    @endphp
    @push('head')
        <link rel="stylesheet" href="{{ asset('css/documentation.css') }}">
    @endpush
    <main class="guide" lang="bn" id="documentation-content">
        <div class="guide-hero">
            <div>
                <p class="guide-eyebrow">AFT SOFT / E-COMMERCE / KNOWLEDGE BASE</p>
                <h1>{{ $isAdminGuide ? 'আপনার store পরিচালনার গাইড' : 'আপনার storefront সাজানোর গাইড' }}</h1>
                <p class="guide-lead">{{ $isAdminGuide ? 'Product থেকে order, inventory থেকে reports—দৈনন্দিন কাজের জন্য প্রয়োজনীয় নির্দেশনা এক জায়গায়।' : 'প্রতিটি section চিনুন, নিজের content যোগ করুন এবং প্রকাশের আগে ফলাফল যাচাই করুন।' }}</p>
                <div class="guide-meta"><span>{{ $topicCount }}টি বিষয়</span><span>{{ $isAdminGuide ? $optionCount.'টি menu নির্দেশনা' : 'ছবিসহ section preview' }}</span><span>বাংলায় নির্দেশনা · Dashboard-এর আসল field name</span></div>
            </div>
            <div class="guide-actions"><button type="button" class="guide-action" onclick="window.print()">Print / Save PDF</button></div>
        </div>
        <nav class="guide-tabs" aria-label="Documentation parts">
            <a href="{{ route('documentation.admin') }}" @if($isAdminGuide) aria-current="page" @endif><span>01 · Admin Guide</span><small>Store ও দৈনন্দিন কাজ পরিচালনা</small></a>
            <a href="{{ route('documentation.index') }}" @if(!$isAdminGuide) aria-current="page" @endif><span>02 · Frontend Guide</span><small>Storefront-এর content ও appearance</small></a>
        </nav>
        @if($isAdminGuide)
            @include('documentation.admin')
        @else
        <div class="guide-context">
            <strong>নিজের brand, নিজের content</strong>
            <p>ছবির নাম, message, রং, category ও offer কেবল উদাহরণ। নিজের পছন্দের content ব্যবহার করুন; upload limit, required field ও প্রকাশের নিয়মগুলো অনুসরণ করুন। গাইড পড়তে login লাগে না। Management page ব্যবহার করতে account ও প্রয়োজনীয় permission লাগবে।</p>
        </div>
        <div class="guide-layout">
            <nav class="guide-nav" aria-label="Documentation sections">
                <strong>এই গাইডে যা আছে</strong>
                @include('documentation.search')
                <a href="#homepage-map">পুরো ছবিতে shortcuts</a>
                <a href="#before-start">শুরু করার আগে</a>
                @foreach($sections as $section)
                    <a href="#{{ $section['id'] }}">{{ $loop->iteration }}. {{ $section['title'] }}</a>
                @endforeach
                <strong class="guide-nav-group">Pages ও Customer Features</strong>
                @foreach($frontendTopics as $topic)
                    <a href="#{{ $topic['id'] }}">{{ count($sections) + $loop->iteration }}. {{ $topic['title'] }}</a>
                @endforeach
                <a href="#verification">শেষে যা পরীক্ষা করবেন</a>
            </nav>
            <div class="guide-content">
        <section class="guide-card" id="homepage-map">
            <div class="guide-section-heading"><div><span class="guide-eyebrow">VISUAL OVERVIEW</span><h2>আপনার homepage এক নজরে</h2></div><a class="guide-action" href="{{ asset('images/documentation/homepage.png') }}" target="_blank" rel="noopener">পূর্ণ ছবি ↗</a></div>
            <p>বড় screen-এ চিহ্নিত অংশ থেকে <strong>Add / Update</strong> বা <strong>Details</strong> খুলুন। ছোট screen-এ ছবির নিচের <strong>section shortcuts</strong> তালিকা ব্যবহার করুন। প্রতিটি section-এর শিরোনামের পাশে একটি <strong>Preview</strong> button আছে। <strong>পূর্ণ ছবি</strong> থেকে বড় করে screenshot দেখা যাবে।</p>
            <div class="guide-map-scroll" tabindex="0" role="region" aria-label="Marked homepage screenshot">
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
            <details class="guide-shortcut-disclosure"><summary>সব section-এর management shortcut দেখুন <span>{{ count($sections) }}টি section</span></summary>
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
            </details>
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
                            <div class="guide-topic-heading"><span class="guide-eyebrow">SECTION {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h2>{{ $loop->iteration }}. {{ $section['title'] }}</h2>
                            </div>
                            <div class="guide-heading-actions">
                            <button type="button" class="guide-preview-button"
                                data-guide-preview="preview-{{ $section['id'] }}"
                                aria-label="Preview {{ $section['title'] }}"
                                aria-haspopup="dialog" aria-controls="preview-{{ $section['id'] }}">Preview</button>
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
                        </div>
                        <figure>
                            <div class="guide-preview" style="aspect-ratio:896 / {{ $end - $start }}">
                                <img src="{{ asset('images/documentation/homepage.png') }}"
                                    width="896" height="1754" alt="Reference: {{ $section['title'] }}"
                                    style="transform:translateY(-{{ $start / 1754 * 100 }}%)">
                            </div>
                            <figcaption class="text-xs mt-2 text-gray-500">উদাহরণ storefront · এই অংশে আপনার নিজস্ব content দেখাবে</figcaption>
                        </figure>
                        <div class="guide-menu"><strong>কোথায় যাবেন:</strong> {{ $section['menu'] }}</div>
                        <h3 class="guide-subheading">ধাপে ধাপে সেটআপ</h3>
                        <ol>
                            @foreach($section['steps'] as $step)
                                <li>{{ $step }}</li>
                            @endforeach
                        </ol>
                        <dialog class="guide-dialog" id="preview-{{ $section['id'] }}" aria-labelledby="preview-title-{{ $section['id'] }}">
                            <div class="guide-dialog-heading">
                                <h3 id="preview-title-{{ $section['id'] }}">Frontend Preview · {{ $section['title'] }}</h3>
                                <form method="dialog"><button class="guide-preview-button" type="submit" autofocus>Close ×</button></form>
                            </div>
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
                        <p class="guide-note"><strong>সহায়ক তথ্য</strong>{{ $section['note'] }}</p>
                        <div class="guide-article-footer"><a href="#homepage-map">↑ Homepage overview</a>
                            @if(!$loop->last)
                                <a href="#{{ $sections[$loop->index + 1]['id'] }}">পরবর্তী: {{ $sections[$loop->index + 1]['title'] }} →</a>
                            @else
                                <a href="#{{ $frontendTopics[0]['id'] }}">Pages ও customer features →</a>
                            @endif
                        </div>
                    </section>
                @endforeach
                @include('documentation.frontend-features')
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
        <script src="{{ asset('js/documentation.js') }}" defer></script>
    @endpush
</x-dynamic-component>
