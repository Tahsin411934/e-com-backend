
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
    
    // Keep collapse state in memory only; every page starts in the expanded
    // layout while route-active groups are rendered open by Blade.
    let collapsed = false;

    /* ---- Collapse toggle ---- */
    collapseBtn?.addEventListener('click', () => {
        collapsed = !collapsed;
        if (collapsed) {
            sidebar.classList.add('collapsed');
            sidebar.style.width = '60px';
        } else {
            sidebar.classList.remove('collapsed');
            sidebar.style.width = '224px';
        }
    });

    function setGroupOpen(button, isOpen) {
        const group = button.closest('[data-sidebar-group]');
        const submenu = document.getElementById(button.dataset.sub);
        if (!submenu) return;

        submenu.classList.toggle('open', isOpen);
        button.classList.toggle('open', isOpen);
        button.setAttribute('aria-expanded', String(isOpen));
        group?.setAttribute('data-open', String(isOpen));
    }

    function openActiveGroups() {
        document.querySelectorAll('[data-sidebar-group]').forEach(group => {
            const button = group.querySelector('[data-sidebar-group-toggle]');
            if (button) setGroupOpen(button, group.dataset.active === 'true');
        });
    }

    /* ---- Submenu toggles ---- */
    document.querySelectorAll('[data-sidebar-group-toggle]').forEach(button => {
        button.addEventListener('click', function () {
            // Keep submenu toggles available on mobile even when the desktop
            // sidebar is saved in its collapsed state.
            if (collapsed && window.innerWidth >= 640) return;

            const submenu = document.getElementById(this.dataset.sub);
            const isOpen = submenu?.classList.contains('open') ?? false;

            if (!isOpen) {
                document.querySelectorAll('[data-sidebar-group-toggle]').forEach(otherButton => {
                    setGroupOpen(otherButton, false);
                });
            }

            setGroupOpen(this, !isOpen);
        });
    });

    // The current route determines the open group in the server-rendered
    // sidebar. Do not restore an unrelated submenu from a previous page.
    openActiveGroups();

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
                openActiveGroups();
                return;
            }

            const allItems = getAllNavItems();
            let anyMatch = false;

            document.querySelectorAll('#sideNav > *').forEach(el => el.style.display = 'none');
            document.querySelectorAll('#sideNav .submenu').forEach(s => {
                s.classList.remove('open');
                s.style.display = '';
            });
            document.querySelectorAll('[data-sidebar-group-toggle]').forEach(button => {
                button.setAttribute('aria-expanded', 'false');
                button.closest('[data-sidebar-group]')?.setAttribute('data-open', 'false');
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
                        setGroupOpen(item.parentBtn, true);
                    } else if (item.subId) {
                        const wrapper = item.el.closest('div');
                        if (wrapper) wrapper.style.display = '';
                        const sub = document.getElementById(item.subId);
                        if (sub) {
                            sub.style.display = '';
                            setGroupOpen(item.el, true);
                        }
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
        openActiveGroups();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && searchInput.value) {
            searchInput.value = '';
            searchClear.classList.add('hidden');
            searchEmptyMsg.classList.add('hidden');
            resetSearchFilter();
            openActiveGroups();
        }
    });

    function resetSearchFilter() {
        document.querySelectorAll('#sideNav > *').forEach(el => el.style.display = '');
        document.querySelectorAll('#sideNav a, #sideNav button').forEach(el => el.style.display = '');
        document.querySelectorAll('.submenu').forEach(s => {
            s.style.display = '';
        });
        document.querySelectorAll('[data-sidebar-group-toggle]').forEach(button => setGroupOpen(button, false));
        searchEmptyMsg.classList.add('hidden');
    }
})();
