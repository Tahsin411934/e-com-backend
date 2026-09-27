<x-app-layout>
    <div class="mx-auto w-full max-w-6xl p-4 sm:p-6 lg:p-8">
        <header class="mb-7">
            <p class="mb-2 text-sm font-semibold text-primary">Storefront settings</p>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Connect your custom domain</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500 dark:text-gray-400">Use a domain you own to give your storefront a memorable, professional address.</p>
        </header>

        <div id="msg" class="mb-5 hidden rounded-xl border px-4 py-3 text-sm" role="status" aria-live="polite"></div>

        <section class="mb-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-7">
            <div class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-bold text-gray-900 dark:text-white">Your storefront domains</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your free subdomain is always available. Connect one custom domain and update it whenever you need.</p>
                </div>
                <span id="domain-count" class="text-xs font-medium text-gray-500 dark:text-gray-400"></span>
            </div>
            <div id="list" class="space-y-3">
                <div class="animate-pulse rounded-xl border border-gray-200 p-4 dark:border-gray-700"><div class="h-4 w-1/3 rounded bg-gray-200 dark:bg-gray-700"></div><div class="mt-3 h-3 w-1/4 rounded bg-gray-100 dark:bg-gray-700/60"></div></div>
            </div>
            <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs text-gray-500 dark:text-gray-400">
                <span><i class="fa-solid fa-check mr-1 text-green-600 dark:text-green-400"></i>1 free subdomain included</span>
                <span><i class="fa-solid fa-check mr-1 text-green-600 dark:text-green-400"></i>1 custom domain at a time</span>
            </div>
        </section>

        <div class="grid items-start gap-5 lg:grid-cols-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-7">
                <div class="mb-6 flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"><i class="fa-solid fa-globe"></i></span>
                    <div>
                        <h2 id="form-title" class="font-bold text-gray-900 dark:text-white">Connect a custom domain</h2>
                        <p id="form-description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">Use a domain you already own. You can edit it later.</p>
                    </div>
                </div>
                <form id="form" novalidate>
                    <label for="domain" class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">Domain name</label>
                    <div class="flex rounded-xl shadow-sm">
                        <span class="inline-flex items-center rounded-l-xl border border-r-0 border-gray-200 bg-gray-50 px-3 text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">https://</span>
                        <input id="domain" required autocomplete="url" spellcheck="false" placeholder="www.yourstore.com" class="min-w-0 flex-1 rounded-r-xl border-gray-200 bg-white text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500">
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Enter the hostname only, without a path.</p>
                    <button id="submit" type="submit" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        <i class="fa-solid fa-link"></i><span>Connect domain</span>
                    </button>
                    <button id="cancel-edit" type="button" class="mt-5 ml-2 hidden rounded-xl border border-gray-300 px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Cancel</button>
                </form>
                <div class="mt-7 flex gap-3 rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-blue-900 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-200">
                    <i class="fa-solid fa-circle-info mt-0.5 shrink-0 text-blue-600 dark:text-blue-300"></i>
                    <p>After adding the domain, update its DNS record at your domain provider. DNS changes can take a few minutes to propagate.</p>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-7">
                <div class="mb-6 flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"><i class="fa-solid fa-network-wired"></i></span>
                    <div>
                        <h2 class="font-bold text-gray-900 dark:text-white">DNS setup guide</h2>
                        <p class="mt-1 text-sm text-gray-300">Add this record in your domain provider’s DNS settings.</p>
                    </div>
                </div>
                <ol class="space-y-4 text-sm">
                    <li class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">1</span><div><p class="font-semibold text-gray-800 dark:text-gray-100">Add your domain here</p><p class="mt-0.5 text-xs leading-5 text-gray-500 dark:text-gray-400">Enter it in the form and select <strong class="text-gray-700 dark:text-gray-200">Connect domain</strong>.</p></div></li>
                    <li class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">2</span><div><p class="font-semibold text-gray-800 dark:text-gray-100">Open your domain provider</p><p class="mt-0.5 text-xs leading-5 text-gray-500 dark:text-gray-400">Sign in where you bought or manage the domain, then open its <strong class="text-gray-700 dark:text-gray-200">DNS settings</strong>.</p></div></li>
                    <li class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">3</span><div><p class="font-semibold text-gray-800 dark:text-gray-100">Create the DNS record shown below</p><p class="mt-0.5 text-xs leading-5 text-gray-500 dark:text-gray-400">Choose the record type, enter its name and value exactly as shown, then save. Update or remove a conflicting record for that name first.</p></div></li>
                    <li class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">4</span><div><p class="font-semibold text-gray-800 dark:text-gray-100">Return here and verify</p><p class="mt-0.5 text-xs leading-5 text-gray-500 dark:text-gray-400">DNS updates may take a few minutes or longer. When ready, select <strong class="text-gray-700 dark:text-gray-200">Verify DNS</strong> beside your custom domain.</p></div></li>
                </ol>
                <div class="mt-5 rounded-xl border border-blue-100 bg-blue-50 p-4 dark:border-blue-900/60 dark:bg-blue-950/30">
                    <div class="mb-3 flex items-center justify-between gap-2"><p class="text-xs font-bold uppercase tracking-wide text-blue-900 dark:text-blue-200">DNS record for your domain</p><button type="button" id="copy-dns" class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-blue-800 hover:bg-blue-100 dark:border-blue-800 dark:bg-gray-800 dark:text-blue-200 dark:hover:bg-gray-700"><i class="fa-regular fa-copy"></i>Copy</button></div>
                    <div id="dns" class="text-sm text-gray-700 dark:text-gray-200"><div class="animate-pulse space-y-3"><div class="h-4 w-2/3 rounded bg-blue-100 dark:bg-gray-700"></div><div class="h-20 rounded-xl bg-blue-100 dark:bg-gray-700"></div></div></div>
                </div>
                <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/60"><p class="text-xs font-bold text-gray-800 dark:text-gray-100">Example: records Vercel may request</p><p class="mt-1 text-[11px] leading-5 text-gray-500 dark:text-gray-400">Examples only. Your exact values appear in your own Vercel project under Settings → Domains.</p></div>
                    <div class="overflow-x-auto"><table class="w-full min-w-[28rem] text-left text-xs"><thead class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400"><tr><th class="px-3 py-2 font-semibold">Type</th><th class="px-3 py-2 font-semibold">Name</th><th class="px-3 py-2 font-semibold">Example value</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-700"><tr><td class="px-3 py-2 font-semibold text-gray-800 dark:text-gray-200">A</td><td class="px-3 py-2 font-mono text-gray-600 dark:text-gray-300">@</td><td class="px-3 py-2 font-mono text-gray-600 dark:text-gray-300">216.198.79.1</td></tr><tr><td class="px-3 py-2 font-semibold text-gray-800 dark:text-gray-200">CNAME</td><td class="px-3 py-2 font-mono text-gray-600 dark:text-gray-300">www</td><td class="px-3 py-2 font-mono text-gray-600 dark:text-gray-300">project-specific.vercel-dns-017.com</td></tr><tr><td class="px-3 py-2 font-semibold text-gray-800 dark:text-gray-200">TXT</td><td class="px-3 py-2 font-mono text-gray-600 dark:text-gray-300">_vercel</td><td class="px-3 py-2 font-mono text-gray-600 dark:text-gray-300">vc-domain-verify=your-domain.com,unique-token</td></tr></tbody></table></div>
                    <p class="border-t border-amber-200 bg-amber-50 px-4 py-3 text-[11px] leading-5 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Do not copy these examples as your real DNS values. The verification token is unique to your domain and Vercel project. This page currently verifies the platform DNS target shown above; it does not fetch Vercel's per-domain records.</p>
                </div>
                <p class="mt-5 flex items-center gap-2 text-xs text-gray-400"><i class="fa-regular fa-clock"></i>After DNS is ready, verify your domain below.</p>
            </section>
        </div>

    </div>

    @push('scripts')
    <script>
    (() => {
        const api = '/api/v1/store/domains';
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const msg = document.getElementById('msg');
        const list = document.getElementById('list');
        const dns = document.getElementById('dns');
        const domainCount = document.getElementById('domain-count');
        const domainInput = document.getElementById('domain');
        const submitButton = document.getElementById('submit');
        const cancelEditButton = document.getElementById('cancel-edit');
        let editingDomainId = null;
        let dnsCopyText = '';
        const escapeHtml = value => $('<div>').text(value ?? '').html();
        const req = async (url, options = {}) => {
            const response = await fetch(url, {
                credentials: 'same-origin',
                ...options,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    ...(options.headers || {}),
                },
            });
            const data = await response.json();
            if (!response.ok) throw data;
            return data;
        };
        const notice = (text, isError = false) => {
            msg.textContent = text;
            msg.className = 'mb-5 rounded-xl border px-4 py-3 text-sm ' + (isError
                ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200'
                : 'border-green-200 bg-green-50 text-green-800 dark:border-green-900/60 dark:bg-green-950/40 dark:text-green-200');
        };
        const errorText = error => error?.errors?.domain?.[0] || error?.message || error?.error || 'Something went wrong. Please try again.';

        const load = async () => {
            try {
                const response = await req(api);
                const data = response.data;
                const instructions = data.dns_instructions;
                const dnsRows = [{ label: 'Type', value: instructions.record }, { label: 'Name', value: instructions.name }, { label: 'Value', value: instructions.value }];
                if (instructions.alternative) dnsRows.push({ label: 'Alternative', value: instructions.alternative.record + ' / ' + instructions.alternative.name + ' / ' + instructions.alternative.value });
                dnsCopyText = dnsRows.map(row => row.label + ': ' + row.value).join('\n');
                dns.innerHTML = '<dl class="grid grid-cols-[5rem_minmax(0,1fr)] gap-x-3 gap-y-2.5 leading-5">'
                    + dnsRows.map(row => '<dt class="text-xs text-gray-500 dark:text-gray-400">' + escapeHtml(row.label) + '</dt><dd class="break-all font-mono text-xs font-semibold text-gray-900 dark:text-white">' + escapeHtml(row.value) + '</dd>').join('')
                    + '</dl>';

                domainCount.textContent = data.items.length + (data.items.length === 1 ? ' connected domain' : ' connected domains');
                if (!data.items.length) {
                    list.innerHTML = '<div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center dark:border-gray-600"><i class="fa-solid fa-globe mb-3 text-xl text-gray-400"></i><p class="text-sm font-medium text-gray-700 dark:text-gray-200">Your domains are being prepared</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Your free storefront address will appear here.</p></div>';
                    return;
                }

                list.innerHTML = data.items.map(domain => {
                    const isCustom = domain.type === 'custom';
                    const status = domain.is_primary ? 'Primary' : (isCustom ? (domain.ssl_status || 'Pending setup') : 'Active');
                    const badgeClass = domain.is_primary || !isCustom
                        ? 'border-green-200 bg-green-50 text-green-700 dark:border-green-900/60 dark:bg-green-950/40 dark:text-green-300'
                        : 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300';
                    const action = isCustom
                        ? '<div class="flex flex-wrap items-center justify-end gap-2"><button type="button" data-id="' + encodeURIComponent(domain.id) + '" data-domain="' + escapeHtml(domain.domain) + '" class="edit-domain inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary/40 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"><i class="fa-solid fa-pen"></i>Edit</button>'
                            + '<button type="button" data-id="' + encodeURIComponent(domain.id) + '" class="verify inline-flex items-center gap-2 rounded-lg border border-primary/30 px-3 py-2 text-xs font-semibold text-primary transition hover:bg-primary/5 focus:outline-none focus:ring-2 focus:ring-primary/40 dark:border-primary/50 dark:text-blue-300 dark:hover:bg-primary/10">'
                            + '<i class="fa-solid ' + (domain.verified_at ? 'fa-circle-check' : 'fa-rotate') + '"></i>' + (domain.verified_at ? 'Verified' : 'Verify DNS') + '</button></div>'
                        : '<span class="inline-flex items-center gap-1.5 text-xs font-semibold text-green-700 dark:text-green-300"><i class="fa-solid fa-circle-check"></i>Active</span>';
                    return '<article class="flex flex-col gap-4 rounded-xl border border-gray-200 p-4 transition hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600 sm:flex-row sm:items-center sm:justify-between">'
                        + '<div class="flex min-w-0 items-start gap-3"><span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300"><i class="fa-solid fa-globe"></i></span>'
                        + '<div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><p class="break-all text-sm font-semibold text-gray-900 dark:text-white">' + escapeHtml(domain.domain) + '</p>'
                        + '<span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-bold ' + badgeClass + '">' + escapeHtml(status) + '</span></div>'
                        + '<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">' + (isCustom ? 'Custom domain' : 'Free storefront domain') + '</p></div></div>'
                        + '<div class="flex items-center justify-end">' + action + '</div></article>';
                }).join('');

                list.querySelectorAll('.edit-domain').forEach(button => {
                    button.onclick = () => {
                        editingDomainId = button.dataset.id;
                        domainInput.value = button.dataset.domain;
                        document.getElementById('form-title').textContent = 'Edit custom domain';
                        document.getElementById('form-description').textContent = 'Changing the domain clears verification. Update DNS and verify the new address.';
                        submitButton.innerHTML = '<i class="fa-solid fa-floppy-disk"></i><span>Save domain</span>';
                        cancelEditButton.classList.remove('hidden');
                        domainInput.focus();
                        document.getElementById('form').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    };
                });

                list.querySelectorAll('.verify').forEach(button => {
                    button.onclick = async () => {
                        button.disabled = true;
                            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>Checking…';
                        try {
                            await req(api + '/' + encodeURIComponent(button.dataset.id) + '/verify', { method: 'POST' });
                            notice('DNS verified. SSL is being provisioned.');
                            await load();
                        } catch (error) {
                            button.disabled = false;
                            button.innerHTML = '<i class="fa-solid fa-rotate"></i>Verify DNS';
                            notice(errorText(error), true);
                        }
                    };
                });
            } catch (error) {
                notice(errorText(error), true);
                list.innerHTML = '<div class="rounded-xl border border-dashed border-gray-300 px-4 py-7 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">Unable to load domains. Refresh the page to try again.</div>';
            }
        };

        document.getElementById('form').onsubmit = async event => {
            event.preventDefault();
            const button = submitButton;
            const isEditing = editingDomainId !== null;
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>' + (isEditing ? 'Saving…' : 'Connecting…') + '</span>';
            try {
                const endpoint = isEditing ? api + '/' + encodeURIComponent(editingDomainId) : api;
                await req(endpoint, { method: isEditing ? 'PUT' : 'POST', body: JSON.stringify({ domain: domainInput.value.trim() }) });
                domainInput.value = '';
                notice(isEditing ? 'Custom domain updated. Update its DNS record, then verify it.' : 'Custom domain added. Update its DNS record, then verify it.');
                resetEditor();
                await load();
            } catch (error) {
                notice(errorText(error), true);
            } finally {
                button.disabled = false;
                if (editingDomainId === null) button.innerHTML = '<i class="fa-solid fa-link"></i><span>Connect domain</span>';
            }
        };

        function resetEditor() {
            editingDomainId = null;
            domainInput.value = '';
            document.getElementById('form-title').textContent = 'Connect a custom domain';
            document.getElementById('form-description').textContent = 'Use a domain you already own. You can edit it later.';
            submitButton.innerHTML = '<i class="fa-solid fa-link"></i><span>Connect domain</span>';
            cancelEditButton.classList.add('hidden');
        }
        cancelEditButton.onclick = resetEditor;

        document.getElementById('copy-dns').onclick = async event => {
            if (!dnsCopyText) return;
            try {
                await navigator.clipboard.writeText(dnsCopyText);
                event.currentTarget.innerHTML = '<i class="fa-solid fa-check"></i>Copied';
                setTimeout(() => { event.currentTarget.innerHTML = '<i class="fa-regular fa-copy"></i>Copy'; }, 1800);
            } catch (error) {
                notice('Could not copy automatically. Please copy the DNS values manually.', true);
            }
        };

        load();
    })();
    </script>
    @endpush
</x-app-layout>
