
(function () {
    const sidebar = document.getElementById('sidebar');
    const collapseBtn = document.getElementById('collapseBtn');
    const collapseIcon = document.getElementById('collapseIcon');
    const navTooltip = document.getElementById('navTooltip');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileSidebarClose = document.getElementById('mobileSidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    let lastFocusedElement = null;

    if (sidebar && window.innerWidth < 640) sidebar.setAttribute('aria-hidden', 'true');

    function closeMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('mobile-open');
        sidebarOverlay?.classList.remove('visible');
        mobileMenuBtn?.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('sidebar-open');
        sidebar.setAttribute('aria-hidden', 'true');
        if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
            lastFocusedElement.focus({ preventScroll: true });
            lastFocusedElement = null;
        }
    }

    function openMobileSidebar() {
        if (!sidebar) return;
        lastFocusedElement = document.activeElement;
        sidebar.classList.add('mobile-open');
        sidebarOverlay?.classList.add('visible');
        mobileMenuBtn?.setAttribute('aria-expanded', 'true');
        document.body.classList.add('sidebar-open');
        sidebar.setAttribute('aria-hidden', 'false');
        sidebar.querySelector('input, a, button')?.focus({ preventScroll: true });
    }

    mobileMenuBtn?.addEventListener('click', () => {
        sidebar.classList.contains('mobile-open') ? closeMobileSidebar() : openMobileSidebar();
    });
    sidebarOverlay?.addEventListener('click', closeMobileSidebar);
    mobileSidebarClose?.addEventListener('click', closeMobileSidebar);
    sidebar.addEventListener('click', (event) => {
        if (event.target.closest('a')) closeMobileSidebar();
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 640) closeMobileSidebar();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar?.classList.contains('mobile-open')) {
            closeMobileSidebar();
        }
    });
    
    // Restore collapsed state from localStorage
    let collapsed = localStorage.getItem('sidebarCollapsed') === 'true';

    // Apply initial collapsed state on page load
    if (collapsed) {
        sidebar.classList.add('collapsed');
        sidebar.style.width = '60px';
    }

    /* ---- Collapse toggle ---- */
    collapseBtn?.addEventListener('click', () => {
        collapsed = !collapsed;
        localStorage.setItem('sidebarCollapsed', collapsed);
        if (collapsed) {
            sidebar.classList.add('collapsed');
            sidebar.style.width = '60px';
        } else {
            sidebar.classList.remove('collapsed');
            sidebar.style.width = '224px';
        }
    });

    /* ---- Helper: save which parent submenus are open ---- */
    function saveOpenParents() {
        const openIds = [];
        document.querySelectorAll('.has-sub.open').forEach(btn => {
            if (btn.dataset.sub) openIds.push(btn.dataset.sub);
        });
        localStorage.setItem('sidebarOpenSubmenus', JSON.stringify(openIds));
    }

    /* ---- Helper: restore open parents from saved state ---- */
    function restoreOpenParents() {
        const saved = localStorage.getItem('sidebarOpenSubmenus');
        if (!saved) return;
        try {
            const openIds = JSON.parse(saved);
            openIds.forEach(subId => {
                const sub = document.getElementById(subId);
                const btn = document.querySelector(`[data-sub="${subId}"]`);
                if (sub && btn) {
                    sub.classList.add('open');
                    btn.classList.add('open');
                }
            });
        } catch(e) {}
    }

/* ---- Submenu toggles ---- */
document.querySelectorAll('.has-sub').forEach(btn => {
    btn.addEventListener('click', function () {
        if (collapsed) return;

        const subId = this.dataset.sub;
        const sub = document.getElementById(subId);

        const isOpen = sub.classList.contains('open');

        if (isOpen) {
            // Close current submenu
            sub.classList.remove('open');
            this.classList.remove('open');
        } else {
            // Close all others
            document.querySelectorAll('.submenu').forEach(s => {
                s.classList.remove('open');
            });

            document.querySelectorAll('.has-sub').forEach(b => {
                b.classList.remove('open');
            });

            // Open selected submenu
            sub.classList.add('open');
            this.classList.add('open');
        }

        saveOpenParents();
    });
});

    /* ---- On page load, open submenu: first from saved state, then from active item ---- */
    restoreOpenParents();

    // Also open any parent that contains an active child (ensures active page is always visible)
    document.querySelectorAll('.submenu').forEach(sub => {
        const hasActive = sub.querySelector('a[class*="text-blue-600 bg-blue-50"]');
        if (hasActive) {
            sub.classList.add('open');
            const parentBtn = document.querySelector(`[data-sub="${sub.id}"]`);
            if (parentBtn) parentBtn.classList.add('open');
            saveOpenParents(); // immediately save
        }
    });

    /* ---- Tooltip on collapsed ---- */
    document.querySelectorAll('.nav-item[data-label], .has-sub[data-label]').forEach(el => {
        el.addEventListener('mouseenter', function (e) {
            if (!collapsed) return;
            navTooltip.textContent = this.dataset.label;
            const rect = this.getBoundingClientRect();
            navTooltip.style.top = (rect.top + rect.height / 2 - 11) + 'px';
            navTooltip.classList.add('show');
        });
        el.addEventListener('mouseleave', () => navTooltip.classList.remove('show'));
    });

    /* ---- Sidebar Search ---- */
    const searchInput = document.getElementById('sidebarSearch');
    const searchClear = document.getElementById('searchClear');
    const searchEmptyMsg = document.getElementById('searchEmptyMsg');

    function getAllNavItems() {
        const items = [];
        document.querySelectorAll('#sideNav a.nav-item[data-label]').forEach(el => {
            items.push({ el, label: el.dataset.label, group: null });
        });
        document.querySelectorAll('#sideNav button.has-sub[data-label]').forEach(btn => {
            items.push({ el: btn, label: btn.dataset.label, group: null, subId: btn.dataset.sub });
        });
        document.querySelectorAll('#sideNav .submenu a').forEach(el => {
            const label = el.querySelector('span') ? el.querySelector('span').textContent.trim() : '';
            const subEl = el.closest('.submenu');
            const parentBtn = subEl ? document.querySelector(`[data-sub="${subEl.id}"]`) : null;
            items.push({ el, label, group: subEl, parentBtn });
        });
        return items;
    }

    let searchDebounce;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            const term = this.value.trim().toLowerCase();
            searchClear.classList.toggle('hidden', !term);

            if (!term) {
                resetSearchFilter();
                // After clearing search, restore the originally active submenus
                restoreOpenParents();
                // Also ensure any active child's parent is open
                document.querySelectorAll('.submenu').forEach(sub => {
                    const hasActive = sub.querySelector('a[class*="text-blue-600 bg-blue-50"]');
                    if (hasActive) {
                        sub.classList.add('open');
                        const parentBtn = document.querySelector(`[data-sub="${sub.id}"]`);
                        if (parentBtn) parentBtn.classList.add('open');
                    }
                });
                return;
            }

            const allItems = getAllNavItems();
            let anyMatch = false;

            document.querySelectorAll('#sideNav > *').forEach(el => el.style.display = 'none');
            document.querySelectorAll('#sideNav .submenu').forEach(s => {
                s.classList.remove('open');
                s.style.display = '';
            });

            allItems.forEach(item => {
                if (item.label.toLowerCase().includes(term)) {
                    anyMatch = true;
                    item.el.style.display = '';
                    if (item.group && item.parentBtn) {
                        const wrapper = item.parentBtn.closest('div');
                        if (wrapper) wrapper.style.display = '';
                        item.parentBtn.style.display = '';
                        item.group.style.display = '';
                        item.group.classList.add('open');
                    } else if (item.subId) {
                        const wrapper = item.el.closest('div');
                        if (wrapper) wrapper.style.display = '';
                        const sub = document.getElementById(item.subId);
                        if (sub) { sub.style.display = ''; sub.classList.add('open'); }
                    }
                }
            });
            searchEmptyMsg.classList.toggle('hidden', anyMatch);
        }, 140);
    });

    searchClear.addEventListener('click', () => {
        searchInput.value = '';
        searchClear.classList.add('hidden');
        searchEmptyMsg.classList.add('hidden');
        resetSearchFilter();
        restoreOpenParents();
        document.querySelectorAll('.submenu').forEach(sub => {
            const hasActive = sub.querySelector('a[class*="text-blue-600 bg-blue-50"]');
            if (hasActive) {
                sub.classList.add('open');
                const parentBtn = document.querySelector(`[data-sub="${sub.id}"]`);
                if (parentBtn) parentBtn.classList.add('open');
            }
        });
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && searchInput.value) {
            searchInput.value = '';
            searchClear.classList.add('hidden');
            searchEmptyMsg.classList.add('hidden');
            resetSearchFilter();
            restoreOpenParents();
        }
    });

    function resetSearchFilter() {
        document.querySelectorAll('#sideNav > *').forEach(el => el.style.display = '');
        document.querySelectorAll('#sideNav a, #sideNav button').forEach(el => el.style.display = '');
        document.querySelectorAll('.submenu').forEach(s => {
            s.classList.remove('open');
            s.style.display = '';
        });
        document.querySelectorAll('.has-sub').forEach(b => b.classList.remove('open'));
        searchEmptyMsg.classList.add('hidden');
    }
})();
