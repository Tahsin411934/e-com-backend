@foreach($frontendTopics as $topic)
    <section class="guide-card" id="{{ $topic['id'] }}">
        <span class="guide-eyebrow">FRONTEND FEATURE {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
        <h2>{{ count($sections) + $loop->iteration }}. {{ $topic['title'] }}</h2>
        <p>{{ $topic['intro'] }}</p>
        <div class="guide-menu"><strong>কোথায় পাবেন:</strong> {{ $topic['menu'] }}</div>
        @if(count($topic['paths']))
            <div class="guide-route-list" aria-label="Storefront routes">
                <span>নিজের store-এর URL-এর পরে:</span>
                @foreach($topic['paths'] as $path)
                    <code>{{ $path }}</code>
                @endforeach
            </div>
        @endif
        <h3 class="guide-subheading">ব্যবহার ও যাচাই</h3>
        <ol>
            @foreach($topic['steps'] as $step)
                <li>{{ $step }}</li>
            @endforeach
        </ol>
        <p class="guide-note"><strong>বর্তমান আচরণ ও সীমাবদ্ধতা</strong>{{ $topic['note'] }}</p>
        <div class="guide-article-footer">
            <a href="#homepage-map">↑ Homepage overview</a>
            @if(!$loop->last)
                <a href="#{{ $frontendTopics[$loop->index + 1]['id'] }}">পরবর্তী বিষয় →</a>
            @else
                <a href="#verification">প্রকাশের আগে যাচাই করুন →</a>
            @endif
        </div>
    </section>
@endforeach
