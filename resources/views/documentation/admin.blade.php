<p>প্রজেক্টের dashboard-এর menu অনুযায়ী কাজের নির্দেশনা। কোন option কী কাজে লাগে, আগে কী প্রস্তুত করতে হবে এবং কোথা থেকে কাজ করবেন তা নিচে দেওয়া আছে। Button শুধু আপনার role ও view permission মিললে দেখানো হয়; পরিবর্তনের জন্য আলাদা create/edit permission লাগতে পারে।</p>
<div class="guide-layout">
    <nav class="guide-nav" aria-label="Admin guide sections">
        <strong>এই গাইডে যা আছে</strong>
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
            <p class="guide-note">Admin screenshot দেওয়া হয়নি, তাই এখানে বাস্তব menu ও route অনুসারে text guide আছে। Frontend Guide-এ আপনার দেওয়া screenshot ও marked shortcuts আছে। সব module-এর data isolation একইভাবে implemented ধরে নেবেন না; অন্য store-এর record নিয়ে কাজের আগে context যাচাই করুন।</p>
            <a class="guide-edit" href="{{ route('documentation.index') }}">Frontend Guide ও screenshot দেখুন &rarr;</a>
        </section>
        @foreach($adminSections as $section)
            <section class="guide-card" id="{{ $section['id'] }}">
                <h2>{{ $loop->iteration }}. {{ $section['title'] }}</h2>
                <p>{{ $section['intro'] }}</p>
                <ol>
                    @foreach($section['steps'] as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
                @foreach($section['items'] as $item)
                    @php
                        [$label, $routeName, $permission, $instructions] = $item;
                        $allowedRoles = $item[4] ?? null;
                        $canOpen = (!$permission || auth()->user()->hasPermission($permission))
                            && (!$allowedRoles || auth()->user()->hasAnyRole($allowedRoles));
                    @endphp
                    <article class="guide-option">
                        <div class="guide-section-heading">
                            <h3>{{ $label }}</h3>
                            @if($canOpen)
                                <a class="guide-edit" href="{{ route($routeName) }}">Open {{ $label }} &rarr;</a>
                            @else
                                <span class="guide-edit-note">আপনার role/permission-এ এই page খোলা নেই।</span>
                            @endif
                        </div>
                        <p>{{ $instructions }}</p>
                    </article>
                @endforeach
                <a class="guide-action" href="#admin-start">↑ Admin setup-এর শুরুতে</a>
            </section>
        @endforeach
    </div>
</div>
