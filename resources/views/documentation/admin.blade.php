<div class="guide-context"><strong>আপনার কাজ অনুযায়ী শুরু করুন</strong><p>প্রতিটি topic-এ কাজের ক্রম, সংশ্লিষ্ট menu-এর ব্যবহার এবং management shortcut পাবেন। নিজের ব্যবসার তথ্য ব্যবহার করুন। Page দেখার permission ও create/edit permission আলাদা; আপনার account অনুযায়ী shortcut দেখানো হয়।</p></div>
<div class="guide-layout">
    <nav class="guide-nav" aria-label="Admin guide sections">
        <strong>এই গাইডে যা আছে</strong>
        @include('documentation.search')
        <a href="#admin-start">শুরু করার ক্রম</a>
        @foreach($adminSections as $section)
            <a href="#{{ $section['id'] }}">{{ $loop->iteration }}. {{ $section['title'] }}</a>
        @endforeach
    </nav>
    <div class="guide-content">
        <section class="guide-card" id="admin-start">
            <h2>Admin Setup — শুরু করার ক্রম</h2>
            <ol>
                <li>Store ও নিজের account context নিশ্চিত করুন; প্রয়োজনীয় staff/role প্রস্তুত করুন।</li>
                <li>Catalog reference data → Products/Variants → Inventory Locations ও Stock প্রস্তুত করুন।</li>
                <li>Supplier/Purchase এবং Cash & Bank-এর তথ্য প্রয়োজন অনুযায়ী পূরণ করুন।</li>
                <li>Delivery Zone/Driver ও POS Register/Shift প্রস্তুত করুন।</li>
                <li>Frontend Guide ধরে storefront content সাজিয়ে order ও checkout পরীক্ষা করুন।</li>
                <li>প্রতিদিন Orders, Payments, Shipments এবং Reports মিলিয়ে অসম্পূর্ণ কাজ শেষ করুন।</li>
            </ol>
            <p class="guide-note"><strong>কাজ শুরুর আগে</strong>সঠিক store ও account নির্বাচন করুন। Admin Guide-এ dashboard-এর কাজ এবং Frontend Guide-এ storefront-এর ছবিসহ নির্দেশনা পাবেন। কোনো menu না দেখলে আপনার account-এর access যাচাই করতে platform admin-এর সঙ্গে যোগাযোগ করুন।</p>
            <a class="guide-edit" href="{{ route('documentation.index') }}">Frontend Guide ও screenshot দেখুন &rarr;</a>
        </section>
        @foreach($adminSections as $section)
            <section class="guide-card" id="{{ $section['id'] }}">
                <span class="guide-eyebrow">TOPIC {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <h2>{{ $loop->iteration }}. {{ $section['title'] }}</h2>
                <p>{{ $section['intro'] }}</p>
                <h3 class="guide-subheading">কাজের ক্রম</h3>
                <ol>
                    @foreach($section['steps'] as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
                @if(count($section['items']))<h3 class="guide-subheading">Menu ও ব্যবহার</h3>@endif
                @foreach($section['items'] as $item)
                    @php
                        [$label, $routeName, $permission, $instructions] = $item;
                        $allowedRoles = $item[4] ?? null;
                        $user = auth()->user();
                        $canOpen = $user && (!$permission || $user->hasPermission($permission))
                            && (!$allowedRoles || $user->hasAnyRole($allowedRoles));
                    @endphp
                    <article class="guide-option">
                        <div class="guide-section-heading">
                            <h3>{{ $label }}</h3>
                            @if($canOpen)
                                <a class="guide-edit" href="{{ route($routeName) }}">Open {{ $label }} &rarr;</a>
                            @elseif(auth()->guest())
                                <a class="guide-edit" href="{{ route('login') }}">Login to manage &rarr;</a>
                            @else
                                <span class="guide-edit-note">আপনার role/permission-এ এই page খোলা নেই।</span>
                            @endif
                        </div>
                        <p>{{ $instructions }}</p>
                    </article>
                @endforeach
                <div class="guide-article-footer"><a href="#admin-start">↑ শুরুর নির্দেশনা</a>
                    @if(!$loop->last)<a href="#{{ $adminSections[$loop->index + 1]['id'] }}">পরবর্তী topic →</a>@else<a href="{{ route('documentation.index') }}">Frontend Guide →</a>@endif
                </div>
            </section>
        @endforeach
    </div>
</div>
