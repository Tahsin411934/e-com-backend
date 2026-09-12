import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

window.Crud = window.Crud || {
    callbacks: {},
    register(entity, action, callback) {
        this.callbacks[entity] = this.callbacks[entity] || {};
        this.callbacks[entity][action] = callback;
    },
    get(entity, action) {
        return this.callbacks[entity]?.[action] ?? null;
    },
};

// Global Drawer Handlers
window.openGlobalDrawer = function(drawerId, overlayId) {
    const $drawer = $(`#${drawerId}`);
    const $overlay = $(`#${overlayId}`);

    // ড্রয়ারের টাইটেল এবং বাটন টেক্সট ডাইনামিক করা (যদি ডেটা অ্যাট্রিবিউট থাকে)
    const mode = $drawer.data('mode') || 'add';
    
    $overlay.removeClass('opacity-0 pointer-events-none').addClass('opacity-100');
    $drawer.removeClass('translate-x-full');
    $('body').addClass('overflow-hidden');
};

window.closeGlobalDrawer = function(drawerId, overlayId) {
    // যদি নির্দিষ্ট ID না দেওয়া হয়, তবে পেজের সব ড্রয়ার ও ওভারলে বন্ধ করবে
    const $drawer = drawerId ? $(`#${drawerId}`) : $('[id$="-drawer"], [id$="Drawer"]');
    const $overlay = overlayId ? $(`#${overlayId}`) : $('[id$="-overlay"], [id$="Overlay"], #drawer-overlay');

    $drawer.addClass('translate-x-full');
    $overlay.removeClass('opacity-100').addClass('opacity-0 pointer-events-none');
    $('body').removeClass('overflow-hidden');
};

// গ্লোবাল ইভেন্ট লিসেনার (পেজ লোড হওয়ার পর একবারই কাজ করবে)
$(document).ready(function() {
    $(document).on('click', '.js-global-drawer-close', function() {
        closeGlobalDrawer($(this).data('drawer-id'), $(this).data('overlay-id'));
    });

    $(document).on('click', '.js-drawer-submit', function() {
        const button = $(this);
        const callback = window.Crud.get(button.data('crud-entity'), button.data('crud-action'));

        if (typeof callback === 'function') {
            callback();
        }
    });

    // ১. ওভারলে-তে ক্লিক করলে ড্রয়ার বন্ধ হবে
    $(document).on('click', '[id$="-overlay"], [id$="Overlay"], #drawer-overlay', function() {
        closeGlobalDrawer();
    });

    // ২. কিবোর্ডের Escape বাটন চাপলে ড্রয়ার বন্ধ হবে
    $(document).keydown(function(e) {
        if (e.key === 'Escape') closeGlobalDrawer();
    });
});
