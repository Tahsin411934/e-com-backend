<div class="nav-tooltip" id="navTooltip"></div>

<!-- ========== SIDEBAR ========== -->
<aside id="sidebar" aria-label="Main navigation" aria-hidden="false" class="w-52 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col flex-shrink-0 z-30 overflow-hidden">

    <!-- Logo -->
    <div class="h-[60px] theme-navbar text-white flex items-center justify-between px-3.5 border-b border-primary flex-shrink-0">
        <div class="flex items-center gap-2 overflow-hidden">
            <!-- A icon circle matching Ecommerce -->
            <div
                class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-primary-hover flex items-center justify-center flex-shrink-0 shadow-sm">
                <span class="text-white font-bold text-sm leading-none">A</span>
            </div>
            <span class="logo-text font-semibold text-white text-[20px] whitespace-nowrap">Ecommerce</span>
        </div>
        <button id="mobileSidebarClose" type="button" aria-label="Close navigation menu"
            class="mobile-sidebar-close w-8 h-8 rounded-lg items-center justify-center text-white/80 hover:text-white hover:bg-white/10 transition-colors">
            <i class="fas fa-xmark text-lg"></i>
        </button>

    </div>

    <!-- Search Box -->
    <div id="searchBox" class="px-2.5 py-2.5 border-b border-gray-100 flex-shrink-0">
        <div
            class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3  focus-within:border-primary focus-within:ring-1 focus-within:ring-primary-light transition-all">
            <i class="fas fa-search text-gray-400 text-xs flex-shrink-0"></i>
            <input id="sidebarSearch" type="text" placeholder="Search menus..."
                class="bg-transparent border-0 outline-none focus:outline-none focus:ring-0 text-[13px] text-gray-700 placeholder-gray-400 w-full" />
            <button id="searchClear" class="hidden text-gray-300 hover:text-gray-500 transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <p id="searchEmptyMsg" class="hidden text-[11px] text-gray-400 mt-1.5 px-1">No matching items found</p>
    </div>

    <!-- Navigation -->
    <nav id="sideNav" class="flex-1 overflow-y-auto overflow-x-hidden py-2.5 px-2">

        <x-sidebar-link href="{{ route('documentation.index') }}"
            class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors mb-0.5 {{ request()->routeIs('documentation.*') ? 'text-primary bg-primary-light' : '' }}"
            data-label="Documentation">
            <i class="fas fa-book-open w-4 text-center flex-shrink-0 text-base"></i>
            <span class="nav-label">Documentation</span>
        </x-sidebar-link>

    <!-- Dashboard -->
    <x-sidebar-link href="{{ route('dashboard') }}"
            class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 mb-0.5
    {{ request()->routeIs('dashboard') ? 'text-white active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' }}"
            data-label="Dashboard">
            <i class="fas fa-th-large w-4 text-center flex-shrink-0 text-base"></i>
            <span class="nav-label font-bold">Dashboard</span>
        </x-sidebar-link>

        @if(auth()->user()->hasAnyRole(['Store Owner', 'Store Staff']))
        <!-- Custom Domain -->
        <x-sidebar-link href="{{ route('custom-domain.index') }}" class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 mb-0.5 {{ request()->routeIs('custom-domain.*') ? 'text-white active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' }}" data-label="Custom Domain">
            <i class="fas fa-globe w-4 text-center flex-shrink-0 text-base"></i><span class="nav-label">Custom Domain</span>
        </x-sidebar-link>
        @endif

        <!-- Identity & Access (permission-driven) -->
        @if(auth()->user()->hasAnyPermission(['users.view','customers.view','roles.view','permissions.view']))
        <x-sidebar-group id="sub-identity" title="Users & Access" :active="request()->routeIs('customers.*', 'users.*', 'roles.*', 'permissions.*')">
            <x-slot name="icon"><i class="fas fa-id-card w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                @if(auth()->user()->hasPermission('customers.view'))
                <x-sidebar-link href="{{ route('customers.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('customers.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-user-group w-3.5 text-center"></i><span>Customers</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('users.view'))
                <x-sidebar-link href="{{ route('users.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('users.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-users w-3.5 text-center"></i><span>Users</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('roles.view'))
                <x-sidebar-link href="{{ route('roles.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('roles.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-shield-halved w-3.5 text-center"></i><span>User Roles</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('permissions.view'))
                <x-sidebar-link href="{{ route('permissions.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('permissions.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-key w-3.5 text-center"></i><span>Access Permissions</span>
                </x-sidebar-link>
                @endif

        </x-sidebar-group>
        @endif

        @if(auth()->user()->hasAnyPermission(['products.view','categories.view','brands.view','units.view','sizes.view','size-groups.view','tax-rates.view','barcode-print.view','product-requests.view']))
        <!-- Catalog -->
        <x-sidebar-group id="sub-cat" title="Products" :active="request()->routeIs('products.*', 'categories.*', 'brands.*', 'units.*', 'sizes.*', 'size-groups.*', 'barcode-print.*', 'tax-rates.*', 'product-requests.*')">
            <x-slot name="icon"><i class="fas fa-boxes w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                    @if(auth()->user()->hasPermission('products.view'))
                    <x-sidebar-link href="{{ route('products.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('products.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-box w-3.5 text-center"></i><span>Products</span>
                    </x-sidebar-link>
                    @endif
                    @if(auth()->user()->hasPermission('categories.view'))
                    <x-sidebar-link href="{{ route('categories.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('categories.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-tags w-3.5 text-center"></i><span>Categories</span>
                    </x-sidebar-link>
                    @endif
                    @if(auth()->user()->hasPermission('brands.view'))
                    <x-sidebar-link href="{{ route('brands.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('brands.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-star w-3.5 text-center"></i><span>Brands</span>
                    </x-sidebar-link>
                    @endif
                    @if(auth()->user()->hasPermission('units.view'))
                    <x-sidebar-link href="{{ route('units.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('units.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-ruler-combined w-3.5 text-center"></i><span>Measurement Units</span>
                    </x-sidebar-link>
                    @endif
                    @if(auth()->user()->hasPermission('sizes.view'))
                    <x-sidebar-link href="{{ route('sizes.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('sizes.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-ruler w-3.5 text-center"></i><span>Sizes</span>
                    </x-sidebar-link>
                    @endif
                    @if(auth()->user()->hasPermission('size-groups.view'))
                    <x-sidebar-link href="{{ route('size-groups.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('size-groups.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-layer-group w-3.5 text-center"></i><span>Size Groups</span>
                    </x-sidebar-link>
                    @endif
                    @if(auth()->user()->hasPermission('barcode-print.view'))
                    <x-sidebar-link href="{{ route('barcode-print.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('barcode-print.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-qrcode w-3.5 text-center"></i><span>Barcode Printing</span>
                    </x-sidebar-link>
                    @endif
                    @if(auth()->user()->hasPermission('tax-rates.view'))
                    <x-sidebar-link href="{{ route('tax-rates.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('tax-rates.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-percent w-3.5 text-center"></i><span>Tax Rates</span>
                    </x-sidebar-link>
                    @endif
                    @if(auth()->user()->hasPermission('product-requests.view'))
                    <x-sidebar-link href="{{ route('product-requests.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('product-requests.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-clipboard-list w-3.5 text-center"></i><span>Product Requests</span>
                    </x-sidebar-link>
                    @endif

        </x-sidebar-group>

        @endif

        @if(auth()->user()->hasAnyPermission(['stores.view','store-staff.view','store-roles.view','countries.view','addresses.view','app-settings.view','plans.view','features.view']))
        <!-- Store & Location -->
        <x-sidebar-group id="sub-store" title="Store Management" :active="request()->routeIs('stores.*', 'store-staff.*', 'store-roles.*', 'countries.*', 'addresses.*', 'app-settings.*', 'plans.*', 'features.*')">
            <x-slot name="icon"><i class="fas fa-store w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                @if(auth()->user()->hasPermission('stores.view'))
                <x-sidebar-link href="{{ route('stores.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('stores.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-store-alt w-3.5 text-center"></i><span>Stores</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('store-staff.view'))
                <x-sidebar-link href="{{ route('store-staff.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('store-staff.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-users w-3.5 text-center"></i><span>Staff</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('store-roles.view'))
                <x-sidebar-link href="{{ route('store-roles.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('store-roles.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-user-shield w-3.5 text-center"></i><span>Store Roles</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('countries.view'))
                <x-sidebar-link href="{{ route('countries.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('countries.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-globe w-3.5 text-center"></i><span>Countries</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('addresses.view'))
                <x-sidebar-link href="{{ route('addresses.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('addresses.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-address-book w-3.5 text-center"></i><span>Addresses</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('app-settings.view'))
                <x-sidebar-link href="{{ route('app-settings.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('app-settings.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-cogs w-3.5 text-center"></i><span>Store Settings</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('plans.view'))
                <x-sidebar-link href="{{ route('plans.index') }}" class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('plans.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-tags w-3.5 text-center"></i><span>Plans & Billing</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('features.view'))
                <x-sidebar-link href="{{ route('features.index') }}" class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('features.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-puzzle-piece w-3.5 text-center"></i><span>Plan Features</span>
                </x-sidebar-link>
                @endif

        </x-sidebar-group>

        @endif

        @if(auth()->user()->hasAnyPermission(['inventory.view','inventory-stock.view','inventory-locations.view','inventory-movements.view']))
        <!-- Inventory -->
        <x-sidebar-group id="sub-inv" title="Inventory" :active="request()->routeIs('inventory-stock.*', 'inventory-locations.*', 'inventory-movements.*')">
            <x-slot name="icon"><i class="fas fa-warehouse w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                @if(auth()->user()->hasPermission('inventory-stock.view'))
                <x-sidebar-link href="{{ route('inventory-stock.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('inventory-stock.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-boxes w-3.5 text-center"></i><span>Stock Overview</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('inventory-locations.view'))
                <x-sidebar-link href="{{ route('inventory-locations.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('inventory-locations.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-map-marker-alt w-3.5 text-center"></i><span>Locations</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('inventory-movements.view'))
                <x-sidebar-link href="{{ route('inventory-movements.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('inventory-movements.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-exchange-alt w-3.5 text-center"></i><span>Stock Movements</span>
                </x-sidebar-link>
                @endif

        </x-sidebar-group>

        @endif

        @if(auth()->user()->hasAnyPermission(['carts.view','coupons.view','campaigns.view','wishlists.view']))
        <!-- Cart & Wishlist -->
        <x-sidebar-group id="sub-cart" title="Marketing & Promotions" :active="request()->routeIs('cart.*', 'coupons.*', 'campaigns.*', 'wishlists.*')">
            <x-slot name="icon"><i class="fas fa-shopping-cart w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                @if(auth()->user()->hasPermission('carts.view'))
                <x-sidebar-link href="{{ route('cart.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('cart.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-shopping-cart w-3.5 text-center"></i><span>Abandoned Carts</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('coupons.view'))
                <x-sidebar-link href="{{ route('coupons.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('coupons.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-ticket-alt w-3.5 text-center"></i><span>Discount Coupons</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('campaigns.view'))
                <x-sidebar-link href="{{ route('campaigns.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('campaigns.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-bullhorn w-3.5 text-center"></i><span>Marketing Campaigns</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('wishlists.view'))
                <x-sidebar-link href="{{ route('wishlists.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('wishlists.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-heart w-3.5 text-center"></i><span>Wishlists</span>
                </x-sidebar-link>
                @endif

        </x-sidebar-group>

        @endif

        @if(auth()->user()->hasAnyPermission(['suppliers.view','purchase-orders.view','purchase-returns.view','supplier-payments.view']))
        <!-- Purchases -->
        <x-sidebar-group id="sub-purchases" title="Purchasing" :active="request()->routeIs('suppliers.*', 'purchase-orders.*', 'purchase-returns.*', 'supplier-payments.*')">
            <x-slot name="icon"><i class="fas fa-file-invoice w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                @if(auth()->user()->hasPermission('suppliers.view'))
                <x-sidebar-link href="{{ route('suppliers.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('suppliers.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-truck w-3.5 text-center"></i><span>Suppliers</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('purchase-orders.view'))
                <x-sidebar-link href="{{ route('purchase-orders.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('purchase-orders.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-file-invoice w-3.5 text-center"></i><span>Purchase Orders</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('purchase-returns.view'))
                <x-sidebar-link href="{{ route('purchase-returns.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('purchase-returns.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-undo-alt w-3.5 text-center"></i><span>Purchase Returns</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('supplier-payments.view'))
                <x-sidebar-link href="{{ route('supplier-payments.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('supplier-payments.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-money-bill-transfer w-3.5 text-center"></i><span>Supplier Payments</span>
                </x-sidebar-link>
                @endif

        </x-sidebar-group>

        @endif

        @if(auth()->user()->hasAnyPermission(['orders.view','payments.view','refunds.view']))
        <!-- Orders -->
        <x-sidebar-group id="sub-orders" title="Orders & Payments" :active="request()->routeIs('orders.*', 'payments.*', 'refunds.*')">
            <x-slot name="icon"><i class="fas fa-shopping-cart w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                @if(auth()->user()->hasPermission('orders.view'))
                <x-sidebar-link href="{{ route('orders.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('orders.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-receipt w-3.5 text-center"></i><span>Orders</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('payments.view'))
                <x-sidebar-link href="{{ route('payments.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('payments.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-credit-card w-3.5 text-center"></i><span>Payments</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('refunds.view'))
                <x-sidebar-link href="{{ route('refunds.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('refunds.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-undo-alt w-3.5 text-center"></i><span>Refunds</span>
                </x-sidebar-link>
                @endif

        </x-sidebar-group>

        @endif

        @if(auth()->user()->hasAnyPermission(['accounts.view','account-categories.view','account-expenses.view','account-transfers.view','account-reports.view']))
        <!-- Account -->
        <x-sidebar-group id="sub-account" title="Finance" :active="request()->routeIs('account.dashboard', 'account-accounts.*', 'account-expenses.*', 'account-transfers.*', 'account-categories.*', 'account-transactions.*', 'account-product-profits.*')">
            <x-slot name="icon"><i class="fas fa-wallet w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                @if(auth()->user()->hasPermission('account-reports.view'))
                <x-sidebar-link href="{{ route('account.dashboard') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account.dashboard') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-chart-pie w-3.5 text-center"></i><span>Dashboard</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('accounts.view'))
                <x-sidebar-link href="{{ route('account-accounts.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-accounts.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-building-columns w-3.5 text-center"></i><span>Cash & Bank</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('account-expenses.view'))
                <x-sidebar-link href="{{ route('account-expenses.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-expenses.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-money-bill-wave w-3.5 text-center"></i><span>Expenses</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('account-transfers.view'))
                <x-sidebar-link href="{{ route('account-transfers.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-transfers.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-right-left w-3.5 text-center"></i><span>Transfers</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('account-categories.view'))
                <x-sidebar-link href="{{ route('account-categories.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-categories.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-tags w-3.5 text-center"></i><span>Categories</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('account-reports.view'))
                <x-sidebar-link href="{{ route('account-transactions.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-transactions.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-book w-3.5 text-center"></i><span>Transaction Ledger</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('account-reports.view'))
                <x-sidebar-link href="{{ route('account-product-profits.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-product-profits.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-chart-simple w-3.5 text-center"></i><span>Product Profit</span>
                </x-sidebar-link>
                @endif

        </x-sidebar-group>
        @endif

        @if(auth()->user()->hasAnyPermission(['account-investments.view','account-categories.view','account-reports.view']))
         <x-sidebar-group id="sub-investments" title="Investments & Assets" :active="request()->routeIs('account-investments.*')">
            <x-slot name="icon"><i class="fas fa-chart-line w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                @if(auth()->user()->hasPermission('account-investments.view'))
                <x-sidebar-link href="{{ route('account-investments.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-investments.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-chart-bar w-3.5 text-center"></i><span>Investments</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('account-categories.view'))
                <x-sidebar-link href="{{ route('account-categories.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-categories.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-tags w-3.5 text-center"></i><span>Investment Types</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('account-reports.view'))
                <x-sidebar-link href="{{ route('account-transactions.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-transactions.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-book w-3.5 text-center"></i><span>Ledger</span>
                </x-sidebar-link>
                @endif

        </x-sidebar-group>
        @endif

        @if(auth()->user()->hasAnyPermission(['shipments.view','shipment-events.view','delivery-drivers.view','delivery-zones.view','deliveries.view']))
        <!-- Delivery -->
        <x-sidebar-group id="sub-delivery" title="Shipping & Delivery" :active="request()->routeIs('shipments.*', 'shipment-events.*', 'delivery-drivers.*', 'delivery-zones.*')">
            <x-slot name="icon"><i class="fas fa-shipping-fast w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                @if(auth()->user()->hasPermission('shipments.view'))
                <x-sidebar-link href="{{ route('shipments.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('shipments.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-truck w-3.5 text-center"></i><span>Shipments</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('shipment-events.view'))
                <x-sidebar-link href="{{ route('shipment-events.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('shipment-events.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-timeline w-3.5 text-center"></i><span>Delivery Tracking</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('delivery-drivers.view'))
                <x-sidebar-link href="{{ route('delivery-drivers.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('delivery-drivers.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-user-tie w-3.5 text-center"></i><span>Drivers</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('delivery-zones.view'))
                <x-sidebar-link href="{{ route('delivery-zones.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('delivery-zones.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-map-location-dot w-3.5 text-center"></i><span>Delivery Zones</span>
                </x-sidebar-link>
                @endif

        </x-sidebar-group>

        @endif

        @if(auth()->user()->hasAnyPermission(['pos.view','pos.sell','pos-registers.view','pos-shifts.view','pos-sales.view']))
        <!-- POS -->
        <x-sidebar-group id="sub-pos" title="Point of Sale" :active="request()->routeIs('pos-registers.*', 'pos-shifts.*', 'pos.sell.*', 'pos-sales.*')">
            <x-slot name="icon"><i class="fas fa-cash-register w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                @if(auth()->user()->hasPermission('pos-registers.view'))
                <x-sidebar-link href="{{ route('pos-registers.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('pos-registers.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-desktop w-3.5 text-center"></i><span>Cash Registers</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('pos-shifts.view'))
                <x-sidebar-link href="{{ route('pos-shifts.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('pos-shifts.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-clock w-3.5 text-center"></i><span>Shifts</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('pos.sell'))
                <x-sidebar-link href="{{ route('pos.sell.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('pos.sell.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-cash-register w-3.5 text-center"></i><span>New Sale</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('pos-sales.view'))
                <x-sidebar-link href="{{ route('pos-sales.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('pos-sales.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-receipt w-3.5 text-center"></i><span>Sales</span>
                </x-sidebar-link>
                @endif

        </x-sidebar-group>

        @endif

        @if(auth()->user()->hasAnyPermission(['reviews.view','notifications.view','histories.view','webhooks.view','webhook-deliveries.view']))
        <!-- Reviews & Notifications -->
        <x-sidebar-group id="sub-reviews" title="Customer Engagement" :active="request()->routeIs('product-reviews.*', 'notifications.*', 'history.*', 'webhooks.*', 'webhook-deliveries.*')">
            <x-slot name="icon"><i class="fas fa-star w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                @if(auth()->user()->hasPermission('reviews.view'))
                <x-sidebar-link href="{{ route('product-reviews.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('product-reviews.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-star-half-stroke w-3.5 text-center"></i><span>Product Reviews</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('notifications.view'))
                <x-sidebar-link href="{{ route('notifications.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('notifications.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-bell w-3.5 text-center"></i><span>Notifications</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('histories.view'))
                <x-sidebar-link href="{{ route('history.page') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('history.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-history w-3.5 text-center"></i><span>Activity History</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('webhooks.view'))
                <x-sidebar-link href="{{ route('webhooks.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('webhooks.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-plug w-3.5 text-center"></i><span>Webhooks</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('webhook-deliveries.view'))
                <x-sidebar-link href="{{ route('webhook-deliveries.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('webhook-deliveries.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-paper-plane w-3.5 text-center"></i><span>Webhook Deliveries</span>
                </x-sidebar-link>
                @endif

        </x-sidebar-group>
        @endif

        <!-- History -->
        @if(auth()->user()->hasPermission('histories.view'))
        <div class="mb-0.5">
            <x-sidebar-link href="{{ route('history.page') }}"
                class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 mb-0.5 {{ request()->routeIs('history.*') ? 'text-white active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' }}"
                data-label="Activity Log">
                <i class="fas fa-clock-rotate-left w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label">Activity Log</span>
            </x-sidebar-link>
        </div>
        @endif

        @if(auth()->user()->hasAnyPermission(['frontend.view','frontend.banners.view','frontend.sliders.view','frontend.navbar.view','frontend.announcements.view','frontend.ctas.view','frontend.service-benefits.view','frontend.pages.view','frontend.testimonials.view','frontend.faqs.view','frontend.footers.view','frontend.settings.view']))
        <!-- Frontend -->
        <x-sidebar-group id="sub-frontend" title="Online Store" :active="request()->routeIs('frontend.service-benefits.*', 'store-domains.*', 'frontend.banners.*', 'frontend.sliders.*', 'frontend.nav-items.index', 'frontend.announcement-bars.*', 'frontend.site-settings.*', 'frontend.ctas.*', 'frontend.nav-items.subnavbar.*', 'frontend.pages.*', 'frontend.testimonials.*', 'frontend.faqs.*', 'frontend.footers.*')">
            <x-slot name="icon"><i class="fas fa-paint-brush w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                @if(auth()->user()->hasPermission('frontend.service-benefits.view'))
                <x-sidebar-link href="{{ route('frontend.service-benefits.index') }}" class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.service-benefits.*') ? 'text-primary bg-primary-light font-medium' : '' }}"><i class="fas fa-shield-halved w-3.5 text-center"></i><span>Service Benefits</span></x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('stores.view'))
                <x-sidebar-link href="{{ route('store-domains.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('store-domains.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-globe w-3.5 text-center"></i><span>Domain Registry</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('frontend.banners.view'))
                <x-sidebar-link href="{{ route('frontend.banners.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.banners.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-images w-3.5 text-center"></i><span>Banners</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('frontend.sliders.view'))
                <x-sidebar-link href="#"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.sliders.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-sliders-h w-3.5 text-center"></i><span>Sliders</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('frontend.navbar.view'))
                <x-sidebar-link href="{{ route('frontend.nav-items.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.nav-items.index') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-bars w-3.5 text-center"></i><span>Primary Navigation</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('frontend.announcements.view'))
                <x-sidebar-link href="{{ route('frontend.announcement-bars.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.announcement-bars.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-rectangle-ad w-3.5 text-center"></i><span>Announcement Bars</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('frontend.settings.view'))
                <x-sidebar-link href="{{ route('frontend.site-settings.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.site-settings.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-cog w-3.5 text-center"></i><span>Site Settings</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('frontend.ctas.view'))
                <x-sidebar-link href="{{ route('frontend.ctas.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.ctas.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-bullhorn w-3.5 text-center"></i><span>Homepage Actions</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('frontend.navbar.view'))
                <x-sidebar-link href="{{ route('frontend.nav-items.subnavbar.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.nav-items.subnavbar.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-list w-3.5 text-center"></i><span>Secondary Navigation</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('frontend.pages.view'))
                <x-sidebar-link href="#"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.pages.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-file w-3.5 text-center"></i><span>Pages</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('frontend.testimonials.view'))
                <x-sidebar-link href="#"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.testimonials.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-comment-dots w-3.5 text-center"></i><span>Testimonials</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('frontend.faqs.view'))
                <x-sidebar-link href="#"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.faqs.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-question-circle w-3.5 text-center"></i><span>FAQs</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('frontend.footers.view'))
                <x-sidebar-link href="#"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.footers.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-shoe-prints w-3.5 text-center"></i><span>Footer</span>
                </x-sidebar-link>
                @endif

        </x-sidebar-group>

        @endif

        @if(auth()->user()->hasAnyPermission(['marketing.view','marketing.gtm.view']))
        <!-- Marketing -->
        <x-sidebar-group id="sub-marketing" title="Integrations" :active="request()->routeIs('frontend.marketing.gtm.*')">
            <x-slot name="icon"><i class="fas fa-bullhorn w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                @if(auth()->user()->hasPermission('marketing.gtm.view'))
                <x-sidebar-link href="{{ route('frontend.marketing.gtm.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.marketing.gtm.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-tags w-3.5 text-center"></i><span>Google Tag Manager</span>
                </x-sidebar-link>
                @endif

        </x-sidebar-group>

        @endif

        @if(auth()->user()->hasAnyPermission(['reports.view','reports.dashboard','reports.sales','reports.products','reports.inventory','reports.orders','reports.shipping','reports.customers','reports.campaigns','reports.finance','reports.refunds','reports.purchases','reports.staff']))
        <!-- Reports -->
        <x-sidebar-group id="sub-reports" title="Reports" :active="request()->routeIs('reports.dashboard', 'reports.sales', 'reports.products', 'reports.inventory', 'reports.orders', 'reports.shipping', 'reports.customers', 'reports.campaigns', 'reports.finance', 'reports.refunds', 'reports.purchases', 'reports.staff')">
            <x-slot name="icon"><i class="fas fa-chart-line w-4 text-center flex-shrink-0 text-base"></i></x-slot>

                @if(auth()->user()->hasPermission('reports.dashboard'))
                <x-sidebar-link href="{{ route('reports.dashboard') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('reports.dashboard') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-gauge-high w-3.5 text-center"></i><span>Executive Dashboard</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('reports.sales'))
                <x-sidebar-link href="{{ route('reports.sales') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('reports.sales') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-coins w-3.5 text-center"></i><span>Sales Reports</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('reports.products'))
                <x-sidebar-link href="{{ route('reports.products') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('reports.products') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-box w-3.5 text-center"></i><span>Product Reports</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('reports.inventory'))
                <x-sidebar-link href="{{ route('reports.inventory') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('reports.inventory') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-warehouse w-3.5 text-center"></i><span>Inventory Reports</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('reports.orders'))
                <x-sidebar-link href="{{ route('reports.orders') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('reports.orders') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-truck-fast w-3.5 text-center"></i><span>Order & Fulfilment Reports</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('reports.shipping'))
                <x-sidebar-link href="{{ route('reports.shipping') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('reports.shipping') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-ship w-3.5 text-center"></i><span>Shipping & Delivery Reports</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('reports.customers'))
                <x-sidebar-link href="{{ route('reports.customers') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('reports.customers') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-users w-3.5 text-center"></i><span>Customer Reports</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('reports.campaigns'))
                <x-sidebar-link href="{{ route('reports.campaigns') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('reports.campaigns') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-bullhorn w-3.5 text-center"></i><span>Campaign & Coupon Reports</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('reports.finance'))
                <x-sidebar-link href="{{ route('reports.finance') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('reports.finance') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-file-invoice-dollar w-3.5 text-center"></i><span>Finance Reports</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('reports.refunds'))
                <x-sidebar-link href="{{ route('reports.refunds') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('reports.refunds') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-rotate-left w-3.5 text-center"></i><span>Refund & Return Reports</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('reports.purchases'))
                <x-sidebar-link href="{{ route('reports.purchases') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('reports.purchases') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-cart-plus w-3.5 text-center"></i><span>Purchase & Supplier Reports</span>
                </x-sidebar-link>
                @endif
                @if(auth()->user()->hasPermission('reports.staff'))
                <x-sidebar-link href="{{ route('reports.staff') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('reports.staff') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-user-tie w-3.5 text-center"></i><span>Staff & Access Reports</span>
                </x-sidebar-link>
                @endif

        </x-sidebar-group>

        @endif

        @if(auth()->user()->hasPermission('settings.view'))
        <!-- Settings -->
        <x-sidebar-link href="#"
            class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150 mb-0.5"
            data-label="Settings">
            <i class="fas fa-cog w-4 text-center flex-shrink-0 text-base"></i>
            <span class="nav-label">Settings</span>
        </x-sidebar-link>
        @endif

    </nav>
</aside>
