<header class="h-[60px] theme-navbar border-b border-primary flex items-center justify-between px-6 flex-shrink-0 z-20">
    <button id="collapseBtn"
        class="w-8 h-8 rounded-full border  theme-navbar-btn flex items-center justify-center text-gray-100 flex-shrink-0 shadow-sm">
        <i class="fas fa-chevron-left text-[15px]" id="collapseIcon"></i>
    </button>
    <!-- Search -->
    <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3  w-96">
        <i class="fas fa-search text-gray-400 text-xs"></i>
        <input type="text" placeholder="Search"
            class="bg-transparent border-none outline-none focus:outline-none focus:ring-0 text-sm text-gray-700 placeholder-gray-400 w-full" />
    </div>

    <!-- Right side -->
    <div class="flex items-center gap-3">

        <!-- Notification bell -->
        <div class="relative">
            <button id="notificationBellBtn" type="button"
                class="relative w-9 h-9 border border-gray-200 dark:border-gray-600 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <i class="fas fa-bell text-[15px]"></i>
                <span id="notificationBadge"
                    class="hidden absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full items-center justify-center border-2 border-white dark:border-gray-800">0</span>
            </button>

            {{-- Bell dropdown --}}
            <div id="notificationBellPanel"
                class="hidden absolute right-0 top-11 w-80 sm:w-96 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl z-50 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Notifications</span>
                    <button id="markAllReadBtn" type="button" class="text-xs text-primary hover:underline">Mark all read</button>
                </div>
                <div id="notificationList" class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700"></div>
                <a href="{{ route('notifications.index') }}"
                    class="block px-4 py-2.5 text-center text-xs font-semibold text-primary hover:bg-gray-50 dark:hover:bg-gray-700 border-t border-gray-200 dark:border-gray-700">
                    View all notifications
                </a>
            </div>
        </div>

        <!-- Settings gear -->
        <button
            class="w-9 h-9 border border-gray-200 dark:border-gray-600 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <i class="fas fa-cog text-[15px]"></i>
        </button>

        <!-- Divider -->
        <div class="w-px h-6 bg-gray-200 dark:bg-gray-600"></div>



        <!-- User chip -->
        <div class="hidden sm:flex sm:items-center sm:ms-6 relative group">

            <!-- Trigger -->
            <button type="button" class="flex items-center gap-2.5 cursor-pointer group focus:outline-none">

                <!-- Avatar -->
                <div
                    class="w-[34px] h-[34px] rounded-full overflow-hidden bg-gradient-to-br from-rose-400 to-pink-600 flex items-center justify-center flex-shrink-0 ring-2 ring-white shadow-sm">
                    <span class="text-white text-xs font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </span>
                </div>

                <!-- Name -->
                <div class="leading-tight text-left">

                    <p class="text-[13px] text-gray-800 dark:text-gray-200 font-semibold">
                        {{ Auth::user()->name }}
                    </p>
                </div>

                <!-- Icon -->
                <i
                    class="fas fa-chevron-down text-[10px] text-gray-400 group-hover:text-gray-600 transition-colors"></i>
            </button>

            <!-- Dropdown -->
            <div
                class="absolute right-0 top-12 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">

                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Profile
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Log Out
                    </button>
                </form>

            </div>
        </div>

    </div>
</header>

@push('scripts')
<script>
    (function () {
        const bellUrl = @json(route('notifications.bell'));
        const markAllUrl = @json(route('notifications.mark-all-read'));
        const typeIcons = {
            'review.created': 'fa-star text-amber-500',
            'review.approved': 'fa-circle-check text-green-500',
            'review.rejected': 'fa-circle-xmark text-red-500',
        };

        function esc(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
            });
        }

        function renderBell(data) {
            const badge = document.getElementById('notificationBadge');
            const list = document.getElementById('notificationList');
            if (!badge || !list) return;

            const count = data.unread_count || 0;
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }

            list.innerHTML = (data.items || []).length
                ? data.items.map(function (n) {
                    const icon = typeIcons[n.type] || 'fa-bell text-gray-400';
                    return '<button type="button" data-id="' + n.id + '" data-url="' + esc(n.url || '') + '" '
                        + 'class="notification-item w-full text-left px-4 py-3 flex gap-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition '
                        + (n.is_read ? 'opacity-60' : 'bg-amber-50/60 dark:bg-amber-900/10') + '">'
                        + '<i class="fas ' + icon + ' mt-0.5"></i>'
                        + '<span class="flex-1 min-w-0">'
                        + '<span class="block text-sm font-semibold text-gray-800 dark:text-gray-200">' + esc(n.subject) + '</span>'
                        + (n.body ? '<span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">' + esc(n.body) + '</span>' : '')
                        + '<span class="block text-[11px] text-gray-400 mt-1">' + esc(n.time || '') + '</span>'
                        + '</span>'
                        + (!n.is_read ? '<span class="w-2 h-2 bg-red-500 rounded-full mt-2 flex-shrink-0"></span>' : '')
                        + '</button>';
                }).join('')
                : '<div class="px-4 py-8 text-center text-sm text-gray-400">No notifications yet.</div>';
        }

        function loadBell() {
            fetch(bellUrl, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(renderBell)
                .catch(function () {});
        }

        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('notificationBellBtn');
            const panel = document.getElementById('notificationBellPanel');
            if (!btn || !panel) return;

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                panel.classList.toggle('hidden');
                if (!panel.classList.contains('hidden')) loadBell();
            });

            document.addEventListener('click', function (e) {
                if (!panel.contains(e.target) && !btn.contains(e.target)) panel.classList.add('hidden');
            });

            document.getElementById('markAllReadBtn').addEventListener('click', function () {
                $.post(markAllUrl, { _token: document.querySelector('meta[name="csrf-token"]').content }, function () {
                    loadBell();
                });
            });

            document.getElementById('notificationList').addEventListener('click', function (e) {
                const item = e.target.closest('.notification-item');
                if (!item) return;
                const url = item.dataset.url;
                $.post("{{ route('notifications.mark-read', ':id') }}".replace(':id', item.dataset.id),
                    { _token: '{{ csrf_token() }}' },
                    function () {
                        loadBell();
                        panel.classList.add('hidden');
                        if (url) window.location.href = url;
                    });
            });

            loadBell();
            setInterval(loadBell, 30000); // poll for new notifications every 30s
        });
    })();
</script>
@endpush
