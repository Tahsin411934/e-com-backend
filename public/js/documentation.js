(function () {
    const guide = document.querySelector('.guide');
    if (!guide) return;

    guide.addEventListener('click', function (event) {
        if (!(event.target instanceof Element)) return;
        const button = event.target.closest('[data-guide-preview]');
        if (!button) return;
        const dialog = document.getElementById(button.dataset.guidePreview);
        if (dialog instanceof HTMLDialogElement && !dialog.open) dialog.showModal();
    });

    const navigation = guide.querySelector('.guide-nav');
    const search = guide.querySelector('#guide-search-input');
    const status = guide.querySelector('#guide-search-status');
    if (!navigation || !search || !status) return;

    const topics = Array.from(navigation.querySelectorAll('a[href^="#"]')).map(function (link) {
        const section = document.getElementById(link.getAttribute('href').slice(1));
        return { link: link, section: section, text: (link.textContent + ' ' + (section ? section.textContent : '')).toLocaleLowerCase() };
    });

    search.addEventListener('input', function () {
        const query = search.value.trim().toLocaleLowerCase();
        const terms = query.split(/\s+/).filter(Boolean);
        let matches = 0;
        topics.forEach(function (topic) {
            const visible = terms.every(function (term) { return topic.text.includes(term); });
            topic.link.hidden = !visible;
            if (visible) matches++;
        });
        status.hidden = !query;
        status.textContent = matches ? matches + 'টি বিষয় পাওয়া গেছে। তালিকা থেকে নির্বাচন করুন।' : 'মিল পাওয়া যায়নি। অন্য শব্দ দিয়ে খুঁজুন।';
    });

    document.addEventListener('keydown', function (event) {
        const target = event.target;
        const editing = target instanceof Element && (target.matches('input, textarea, select') || target.isContentEditable);
        if (event.key === '/' && !editing && !guide.querySelector('dialog[open]') && !event.ctrlKey && !event.metaKey && !event.altKey) {
            event.preventDefault();
            search.focus();
        }
    });

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries) {
            const visible = entries.filter(function (entry) { return entry.isIntersecting; });
            if (!visible.length) return;
            const current = visible.sort(function (a, b) { return a.boundingClientRect.top - b.boundingClientRect.top; })[0].target;
            topics.forEach(function (topic) {
                if (topic.section === current) topic.link.setAttribute('aria-current', 'location');
                else topic.link.removeAttribute('aria-current');
            });
        }, { rootMargin: '-8% 0px -65% 0px', threshold: 0 });
        topics.forEach(function (topic) { if (topic.section) observer.observe(topic.section); });
    }
})();
