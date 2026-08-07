<x-guest-layout>
    <div class="min-h-screen flex flex-col lg:flex-row bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">

        <!-- Left Side - Branding Section (Hidden on mobile, visible on large screens) -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-slate-900 via-primary to-slate-900 p-12 flex-col justify-between relative overflow-hidden">
            <!-- Animated Background Pattern -->
            <div class="absolute inset-0 opacity-5">
                <div class="absolute inset-0"
                    style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 1px); background-size: 40px 40px;">
                </div>
            </div>

            <!-- Floating Gradient Orbs -->
            <div
                class="absolute top-20 -right-20 w-72 h-72 bg-primary rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse">
            </div>
            <div
                class="absolute bottom-20 -left-20 w-72 h-72 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse delay-1000">
            </div>

            <!-- Top Brand -->
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-12">
                    <div
                        class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center backdrop-blur-sm border border-white/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-white tracking-tight">Shopio</h2>
                        <p class="text-primary-light/70 text-xs">E-Commerce Platform</p>
                    </div>
                </div>

                <!-- Main Quote -->
                <div class="space-y-6">
                    <div class="relative">
                        <div class="absolute -top-4 -left-2 text-6xl text-white/10 font-serif">"</div>
                        <h1 class="text-4xl lg:text-5xl font-bold text-white leading-tight relative z-10">
                            Shop smarter,<br>
                            <span class="bg-gradient-to-r from-primary-light to-white bg-clip-text text-transparent">live
                                better</span>
                        </h1>
                    </div>
                    <p class="text-primary-light/80 text-base leading-relaxed max-w-sm">
                        Discover amazing products, exclusive deals, and seamless shopping experience all in one place.
                    </p>
                </div>

                <!-- Features List -->
                <div class="mt-12 space-y-3">
                    <div class="flex items-center gap-3 text-primary-light/70 text-sm">
                        <div class="w-1.5 h-1.5 bg-primary rounded-full"></div>
                        <span>Curated collections & trending products</span>
                    </div>
                    <div class="flex items-center gap-3 text-primary-light/70 text-sm">
                        <div class="w-1.5 h-1.5 bg-primary rounded-full"></div>
                        <span>Secure checkout & fast delivery</span>
                    </div>
                    <div class="flex items-center gap-3 text-primary-light/70 text-sm">
                        <div class="w-1.5 h-1.5 bg-primary rounded-full"></div>
                        <span>Exclusive member discounts & rewards</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Stats -->
            <div class="relative z-10 mt-12 pt-8 border-t border-white/10">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-2xl font-bold text-white">50K+</p>
                        <p class="text-primary-light/60 text-xs">Happy Customers</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">10K+</p>
                        <p class="text-primary-light/60 text-xs">Products Available</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form Section -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-6 sm:p-8 lg:p-12 min-h-screen">
            <!-- Mobile Branding (Visible only on mobile) -->
            <div class="lg:hidden text-center mb-8">
                <div class="flex justify-center mb-3">
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-primary to-primary-hover rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Shopio</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Sign in to your account</p>
            </div>

            <!-- Login Card -->
            <div
                class="w-full max-w-md border border-gray-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800/50 p-8 shadow-lg ">
                <!-- Welcome Message -->
                <div class="hidden lg:block mb-8">
                    <h2 class="text-3xl font-bold text-slate-800 dark:text-white mb-2">Welcome back</h2>
                    <p class="text-slate-500 dark:text-slate-400">Sign in to your account to continue</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-6" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-5">
                        <x-input-label for="email" :value="__('Email Address')"
                            class="text-slate-700 dark:text-slate-300 font-semibold text-sm mb-1.5" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <x-text-input id="email"
                                class="block w-full pl-10 pr-3 py-3 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-primary focus:ring-primary focus:ring-1 transition-all duration-200"
                                type="email" name="email" :value="old('email')" required autofocus
                                autocomplete="username" placeholder="john@company.com" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-sm" />
                    </div>

                    <!-- Password -->
                    <div class="mb-5">
                        <x-input-label for="password" :value="__('Password')"
                            class="text-slate-700 dark:text-slate-300 font-semibold text-sm mb-1.5" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <x-text-input id="password"
                                class="block w-full pl-10 pr-3 py-3 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-primary focus:ring-primary focus:ring-1 transition-all duration-200"
                                type="password" name="password" required autocomplete="current-password"
                                placeholder="Enter your password" />
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-sm" />
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between mb-6">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox"
                                class="rounded border-slate-300 dark:border-slate-600 text-primary shadow-sm focus:ring-primary focus:ring-1"
                                name="remember">
                            <span
                                class="ms-2 text-sm text-slate-600 dark:text-slate-400">{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-primary dark:text-primary-light hover:text-primary-hover dark:hover:text-primary-light font-medium transition-colors duration-200"
                                href="{{ route('password.request') }}">
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>

                    <!-- Sign In Button -->
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center px-6 py-3.5 bg-primary hover:bg-primary-hover active:bg-primary border border-transparent rounded-xl font-semibold text-sm text-white uppercase tracking-wide transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-slate-800 shadow-md hover:shadow-lg mb-5">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        {{ __('Sign In') }}
                    </button>

                    <!-- Divider -->
                    <div class="relative mb-5">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
                        </div>

                    </div>

                    <!-- Google Sign In Button -->


                    <!-- Create Account Link -->
                    <div class="text-center">
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            Don't have an account?
                            <a href="{{ route('register') }}"
                                class="text-primary dark:text-primary-light hover:text-primary-hover dark:hover:text-primary-light font-semibold ml-1">
                                Create one
                            </a>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-xs text-slate-400 dark:text-slate-500">
                <p class="mb-1">Secure access to your Shopio account</p>
                <p>© {{ date('Y') }} Shopio. All rights reserved.</p>
            </div>
        </div>
    </div>
</x-guest-layout>