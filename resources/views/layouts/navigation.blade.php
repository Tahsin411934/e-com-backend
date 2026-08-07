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
        <button
            class="relative w-9 h-9 border border-gray-200 dark:border-gray-600 rounded-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <i class="fas fa-bell text-[15px]"></i>
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
        </button>

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
