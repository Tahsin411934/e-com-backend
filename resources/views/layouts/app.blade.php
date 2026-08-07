<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>E-Commerce Dashboard</title>

    <!-- Apply saved theme early (prevent flash) -->
    <script>
        (function() {
            try {
                var DEFAULT_PRIMARY = '#1e3a8a';
                var color = localStorage.getItem('theme-primary') || DEFAULT_PRIMARY;
                var savedDark = localStorage.getItem('theme-dark');
                var dark = savedDark === null
                    ? (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)
                    : savedDark === '1';
                var root = document.documentElement;
                if (dark) root.classList.add('dark');
                function shade(hex, percent) {
                    var num = parseInt(hex.replace('#', ''), 16);
                    if (isNaN(num)) return hex;
                    var amt = Math.round(2.55 * percent);
                    var r = Math.min(255, Math.max(0, (num >> 16) + amt));
                    var g = Math.min(255, Math.max(0, ((num >> 8) & 0x00FF) + amt));
                    var b = Math.min(255, Math.max(0, (num & 0x0000FF) + amt));
                    return '#' + (0x1000000 + (r << 16) + (g << 8) + b).toString(16).slice(1);
                }
                function hexToRgb(hex) {
                    var num = parseInt(hex.replace('#', ''), 16);
                    if (isNaN(num)) return '30, 58, 138';
                    return ((num >> 16) & 255) + ', ' + ((num >> 8) & 255) + ', ' + (num & 255);
                }
                root.style.setProperty('--primary-color', color);
                root.style.setProperty('--primary-hover', shade(color, -10));
                root.style.setProperty('--primary-light', shade(color, 80));
                root.style.setProperty('--primary-soft', shade(color, 90));
                root.style.setProperty('--primary-rgb', hexToRgb(color));
            } catch(e) {}
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200..800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />

    @stack('head')
</head>

<body class="bg-[#e4ebf1] dark:bg-gray-900 font-sans antialiased flex h-screen overflow-hidden">

    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Theme Customizer: Floating color picker + dark/light mode -->
    <x-theme-customizer />

    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Navigation -->
        @include('layouts.navigation')

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 dark:bg-gray-900 relative flex flex-col">
            @include('components.global-loader')
            <div class="flex-1 w-full">
                {{ $slot }}
            </div>

        </main>
    </div>



    <!-- JS LIBRARIES -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- DataTables Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Sidebar JS -->
    <script src="{{ asset('js/sidebar.js') }}"></script>
    <!-- Theme JS -->
    <script src="{{ asset('js/theme.js') }}"></script>

    <!-- CSRF Setup -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <!-- ✅ GLOBAL LOADER HANDLING - FINAL WORKING VERSION -->
    <script>
        $(document).ready(function() {
            window.isInitialPageLoad = true;

            window.isDataTableProcessing = function() {
                return $('.dataTables_processing:visible').length > 0 || $('body').hasClass(
                'dt-custom-loading');
            };

            // ১. AJAX start
            $(document).on('ajaxStart', function() {
                if (window.isInitialPageLoad && !window.isDataTableProcessing()) {
                    $('#global-loader').removeClass('hidden');
                }
            });

            // ২. AJAX শেষ হলে (টাইম কমিয়ে ১০০ms করা হয়েছে দ্রুত রেসপন্সের জন্য)
            $(document).on('ajaxStop', function() {
                setTimeout(function() {
                    if (!window.isDataTableProcessing()) {
                        $('#global-loader').addClass('hidden');
                        window.isInitialPageLoad = false;
                    }
                }, 100);
            });

            // ৩. স্ট্যাটিক পেজের জন্য (টাইম কমিয়ে ১০০ms করা হয়েছে)
            function handleStaticPageLoader() {
                setTimeout(function() {
                    if (!window.isDataTableProcessing()) {
                        $('#global-loader').addClass('hidden');
                        window.isInitialPageLoad = false;
                    }
                }, 100); // ১০০ms পলকের মতো কাজ করবে, ইউজার কোনো ল্যাগ বুঝবে না
            }

            // ব্রাউজার যদি অলরেডি সব লোড করে ফেলে, সাথে সাথে রান হবে
            if (document.readyState === 'complete') {
                handleStaticPageLoader();
            } else {
                $(window).on('load', function() {
                    handleStaticPageLoader();
                });
            }
        });
    </script>

    <!-- Theme Manager Init -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.ThemeManager) {
                window.ThemeManager.loadTheme();
                window.ThemeManager.initCustomizer();
            }
        });
    </script>

    @stack('scripts')

</body>

</html>