<div class="nav-tooltip" id="navTooltip"></div>

<!-- ========== SIDEBAR ========== -->
<aside id="sidebar" class="w-52 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col flex-shrink-0 z-30 overflow-hidden">

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

    <!-- Dashboard -->
    <a href="{{ route('dashboard') }}"
            class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 mb-0.5 
    {{ request()->routeIs('dashboard') ? 'text-white active' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' }}"
            data-label="Dashboard">
            <i class="fas fa-th-large w-4 text-center flex-shrink-0 text-base"></i>
            <span class="nav-label font-bold">Dashboard</span>
        </a>

        <!-- Identity & Access - Only for Super Admin and Admin -->
        @if(auth()->check() && auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
        <div class="mb-0.5">
            <button
                class="nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150"
                data-label="Identity" data-sub="sub-identity">
                <i class="fas fa-id-card w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label flex-1 text-left">Identity & Access</span>
                <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
            </button>
            <div class="submenu sub-indent" id="sub-identity">
                <a href="{{ route('users.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('users.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-users w-3.5 text-center"></i><span>Users</span>
                </a>
                @if(auth()->user()->hasRole('Super Admin'))
                <a href="{{ route('roles.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('roles.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-shield-halved w-3.5 text-center"></i><span>Roles</span>
                </a>
                <a href="{{ route('permissions.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('permissions.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-key w-3.5 text-center"></i><span>Permissions</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Catalog -->
        <div class="mb-0.5">
            <button
                class="nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150"
                data-label="Catalog" data-sub="sub-cat">
                <i class="fas fa-boxes w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label flex-1 text-left">Catalog</span>
                <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
            </button>
                <div class="submenu sub-indent" id="sub-cat">
                    <a href="{{ route('products.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('products.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-box w-3.5 text-center"></i><span>Products</span>
                    </a>
                    <a href="{{ route('categories.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('categories.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-tags w-3.5 text-center"></i><span>Categories</span>
                    </a>
                    <a href="{{ route('brands.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('brands.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-star w-3.5 text-center"></i><span>Brands</span>
                    </a>
                    <a href="{{ route('units.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('units.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-ruler-combined w-3.5 text-center"></i><span>Units</span>
                    </a>
                    <a href="{{ route('sizes.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('sizes.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-ruler w-3.5 text-center"></i><span>Sizes</span>
                    </a>
                    <a href="{{ route('barcode-print.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('barcode-print.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-qrcode w-3.5 text-center"></i><span>Barcode Print</span>
                    </a>
                    <a href="{{ route('tax-rates.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('tax-rates.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-percent w-3.5 text-center"></i><span>Tax Rates</span>
                    </a>
                    <a href="{{ route('product-requests.index') }}"
                        class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('product-requests.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                        <i class="fas fa-clipboard-list w-3.5 text-center"></i><span>Product Requests</span>
                    </a>
                </div>
        </div>

        <!-- Store & Location -->
        <div class="mb-0.5">
            <button
                class="nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150"
                data-label="Store" data-sub="sub-store">
                <i class="fas fa-store w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label flex-1 text-left">Store & Location</span>
                <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
            </button>
            <div class="submenu sub-indent" id="sub-store">
                <a href="{{ route('stores.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('stores.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-store-alt w-3.5 text-center"></i><span>Stores</span>
                </a>
                <a href="{{ route('store-staff.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('store-staff.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-users w-3.5 text-center"></i><span>Staff</span>
                </a>
                <a href="{{ route('countries.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('countries.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-globe w-3.5 text-center"></i><span>Countries</span>
                </a>
                <a href="{{ route('addresses.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('addresses.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-address-book w-3.5 text-center"></i><span>Addresses</span>
                </a>
                <a href="{{ route('app-settings.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('app-settings.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-cogs w-3.5 text-center"></i><span>App Settings</span>
                </a>
            </div>
        </div>

        <!-- Inventory -->
        <div class="mb-0.5">
            <button
                class="nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150"
                data-label="Inventory" data-sub="sub-inv">
                <i class="fas fa-warehouse w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label flex-1 text-left">Inventory</span>
                <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
            </button>
            <div class="submenu sub-indent" id="sub-inv">
                <a href="{{ route('inventory-stock.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('inventory-stock.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-boxes w-3.5 text-center"></i><span>Stock</span>
                </a>
                <a href="{{ route('inventory-locations.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('inventory-locations.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-map-marker-alt w-3.5 text-center"></i><span>Locations</span>
                </a>
                <a href="{{ route('inventory-movements.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('inventory-movements.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-exchange-alt w-3.5 text-center"></i><span>Movements</span>
                </a>
            </div>
        </div>

        <!-- Cart & Wishlist -->
        <div class="mb-0.5">
            <button
                class="nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150"
                data-label="Cart" data-sub="sub-cart">
                <i class="fas fa-shopping-cart w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label flex-1 text-left">Cart & Wishlist</span>
                <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
            </button>
            <div class="submenu sub-indent" id="sub-cart">
                <a href="{{ route('cart.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('cart.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-shopping-cart w-3.5 text-center"></i><span>Carts</span>
                </a>
                <a href="{{ route('coupons.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('coupons.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-ticket-alt w-3.5 text-center"></i><span>Coupons</span>
                </a>
                <a href="{{ route('campaigns.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('campaigns.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-bullhorn w-3.5 text-center"></i><span>Campaigns</span>
                </a>
                <a href="{{ route('wishlists.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('wishlists.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-heart w-3.5 text-center"></i><span>Wishlists</span>
                </a>
            </div>
        </div>

        <!-- Purchases -->
        <div class="mb-0.5">
            <button
                class="nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150"
                data-label="Purchases" data-sub="sub-purchases">
                <i class="fas fa-file-invoice w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label flex-1 text-left">Purchases</span>
                <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
            </button>
            <div class="submenu sub-indent" id="sub-purchases">
                <a href="{{ route('suppliers.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('suppliers.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-truck w-3.5 text-center"></i><span>Suppliers</span>
                </a>
                <a href="{{ route('purchase-orders.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('purchase-orders.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-file-invoice w-3.5 text-center"></i><span>Purchase Orders</span>
                </a>
                <a href="{{ route('purchase-returns.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('purchase-returns.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-undo-alt w-3.5 text-center"></i><span>Purchase Returns</span>
                </a>
            </div>
        </div>

        <!-- Orders -->
        <div class="mb-0.5">
            <button
                class="nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150"
                data-label="Orders" data-sub="sub-orders">
                <i class="fas fa-shopping-cart w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label flex-1 text-left">Orders</span>
                <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
            </button>
            <div class="submenu sub-indent" id="sub-orders">
                <a href="{{ route('orders.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('orders.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-receipt w-3.5 text-center"></i><span>Orders</span>
                </a>
                <a href="{{ route('payments.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('payments.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-credit-card w-3.5 text-center"></i><span>Payments</span>
                </a>
                <a href="{{ route('refunds.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('refunds.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-undo-alt w-3.5 text-center"></i><span>Refunds</span>
                </a>
            </div>
        </div>

        <!-- Account -->
        <div class="mb-0.5">
            <button
                class="nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150"
                data-label="Account" data-sub="sub-account">
                <i class="fas fa-wallet w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label flex-1 text-left">Account</span>
                <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
            </button>
            <div class="submenu sub-indent" id="sub-account">
                <a href="{{ route('account.dashboard') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account.dashboard') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-chart-pie w-3.5 text-center"></i><span>Dashboard</span>
                </a>
                <a href="{{ route('account-accounts.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-accounts.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-building-columns w-3.5 text-center"></i><span>Cash & Bank</span>
                </a>
                <a href="{{ route('account-expenses.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-expenses.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-money-bill-wave w-3.5 text-center"></i><span>Expenses</span>
                </a>
                <a href="{{ route('account-transfers.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-transfers.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-right-left w-3.5 text-center"></i><span>Transfers</span>
                </a>
                <a href="{{ route('account-categories.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-categories.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-tags w-3.5 text-center"></i><span>Categories</span>
                </a>
                <a href="{{ route('account-transactions.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-transactions.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-book w-3.5 text-center"></i><span>Ledger</span>
                </a>
                <a href="{{ route('account-product-profits.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-product-profits.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-chart-simple w-3.5 text-center"></i><span>Product Profit</span>
                </a>
            </div>
        </div>

         <div class="mb-0.5">
            <button
                class="nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150"
                data-label="Investments" data-sub="sub-investments">
                <i class="fas fa-chart-line w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label flex-1 text-left">Investments</span>
                <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
            </button>
            <div class="submenu sub-indent" id="sub-investments">
                <a href="{{ route('account-investments.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-investments.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-chart-bar w-3.5 text-center"></i><span>Investments</span>
                </a>
                <a href="{{ route('account-categories.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-categories.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-tags w-3.5 text-center"></i><span>Investment Types</span>
                </a>
                <a href="{{ route('account-transactions.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('account-transactions.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-book w-3.5 text-center"></i><span>Ledger</span>
                </a>
            </div>
        </div>


        <!-- Delivery -->
        <div class="mb-0.5">
            <button
                class="nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150"
                data-label="Delivery" data-sub="sub-delivery">
                <i class="fas fa-shipping-fast w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label flex-1 text-left">Delivery</span>
                <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
            </button>
            <div class="submenu sub-indent" id="sub-delivery">
                <a href="{{ route('shipments.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('shipments.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-truck w-3.5 text-center"></i><span>Shipments</span>
                </a>
                <a href="{{ route('shipment-events.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('shipment-events.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-timeline w-3.5 text-center"></i><span>Events</span>
                </a>
                <a href="{{ route('delivery-drivers.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('delivery-drivers.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-user-tie w-3.5 text-center"></i><span>Drivers</span>
                </a>
                <a href="{{ route('delivery-zones.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('delivery-zones.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-map-location-dot w-3.5 text-center"></i><span>Zones</span>
                </a>
            </div>
        </div>

        <!-- POS -->
        <div class="mb-0.5">
            <button
                class="nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150"
                data-label="POS" data-sub="sub-pos">
                <i class="fas fa-cash-register w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label flex-1 text-left">POS</span>
                <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
            </button>
            <div class="submenu sub-indent" id="sub-pos">
                <a href="{{ route('pos-registers.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('pos-registers.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-desktop w-3.5 text-center"></i><span>Registers</span>
                </a>
                <a href="{{ route('pos-shifts.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('pos-shifts.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-clock w-3.5 text-center"></i><span>Shifts</span>
                </a>
                <a href="{{ route('pos.sell.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('pos.sell.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-cash-register w-3.5 text-center"></i><span>New Sale</span>
                </a>
                <a href="{{ route('pos-sales.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('pos-sales.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-receipt w-3.5 text-center"></i><span>Sales</span>
                </a>
            </div>
        </div>

        <!-- Reviews & Notifications -->
        <div class="mb-0.5">
            <button
                class="nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150"
                data-label="Reviews" data-sub="sub-reviews">
                <i class="fas fa-star w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label flex-1 text-left">Reviews & Notif.</span>
                <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
            </button>
            <div class="submenu sub-indent" id="sub-reviews">
                <a href="{{ route('product-reviews.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('product-reviews.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-star-half-stroke w-3.5 text-center"></i><span>Product Reviews</span>
                </a>
                <a href="{{ route('notifications.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('notifications.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-bell w-3.5 text-center"></i><span>Notifications</span>
                </a>
                <a href="{{ route('audit-logs.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('audit-logs.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-history w-3.5 text-center"></i><span>Audit Logs</span>
                </a>
                <a href="{{ route('webhooks.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('webhooks.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-plug w-3.5 text-center"></i><span>Webhooks</span>
                </a>
                <a href="{{ route('webhook-deliveries.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('webhook-deliveries.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-paper-plane w-3.5 text-center"></i><span>Webhook Deliv.</span>
                </a>
            </div>
        </div>

        <!-- Frontend -->
        <div class="mb-0.5">
            <button
                class="nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150"
                data-label="Frontend" data-sub="sub-frontend">
                <i class="fas fa-paint-brush w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label flex-1 text-left">Frontend</span>
                <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
            </button>
            <div class="submenu sub-indent" id="sub-frontend">
                <a href="{{ route('frontend.banners.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.banners.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-images w-3.5 text-center"></i><span>Banners</span>
                </a>
                <a href="#"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.sliders.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-sliders-h w-3.5 text-center"></i><span>Sliders</span>
                </a>
                <a href="{{ route('frontend.nav-items.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.nav-items.index') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-bars w-3.5 text-center"></i><span>Navbar Items</span>
                </a>
                <a href="{{ route('frontend.announcement-bars.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.announcement-bars.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-rectangle-ad w-3.5 text-center"></i><span>Announcement Bars</span>
                </a>
                <a href="{{ route('frontend.site-settings.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.site-settings.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-cog w-3.5 text-center"></i><span>Site Settings</span>
                </a>
                <a href="{{ route('frontend.ctas.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.ctas.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-bullhorn w-3.5 text-center"></i><span>Homepage CTAs</span>
                </a>
                <a href="{{ route('frontend.nav-items.subnavbar.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.nav-items.subnavbar.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-list w-3.5 text-center"></i><span>Subnavbar Items</span>
                </a>
                <a href="#"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.pages.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-file w-3.5 text-center"></i><span>Pages</span>
                </a>
                <a href="#"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.testimonials.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-comment-dots w-3.5 text-center"></i><span>Testimonials</span>
                </a>
                <a href="#"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.faqs.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-question-circle w-3.5 text-center"></i><span>FAQs</span>
                </a>
                <a href="#"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.footers.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-shoe-prints w-3.5 text-center"></i><span>Footer</span>
                </a>
            </div>
        </div>

        <!-- Marketing -->
        <div class="mb-0.5">
            <button
                class="nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150"
                data-label="Marketing" data-sub="sub-marketing">
                <i class="fas fa-bullhorn w-4 text-center flex-shrink-0 text-base"></i>
                <span class="nav-label flex-1 text-left">Marketing</span>
                <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
            </button>
            <div class="submenu sub-indent" id="sub-marketing">
                <a href="{{ route('frontend.marketing.gtm.index') }}"
                    class="flex items-center gap-2.5 pl-9 pr-3 py-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-50 text-[13px] transition-colors {{ request()->routeIs('frontend.marketing.gtm.*') ? 'text-primary bg-primary-light font-medium' : '' }}">
                    <i class="fas fa-tags w-3.5 text-center"></i><span>Google Tag Manager</span>
                </a>
            </div>
        </div>

        <!-- Reports -->
        <a href="#"
            class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150 mb-0.5"
            data-label="Reports">
            <i class="fas fa-chart-line w-4 text-center flex-shrink-0 text-base"></i>
            <span class="nav-label">Reports</span>
        </a>

        <!-- Settings -->
        <a href="#"
            class="nav-item flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-gray-800 text-sm font-medium transition-colors duration-150 mb-0.5"
            data-label="Settings">
            <i class="fas fa-cog w-4 text-center flex-shrink-0 text-base"></i>
            <span class="nav-label">Settings</span>
        </a>

    </nav>
</aside>
