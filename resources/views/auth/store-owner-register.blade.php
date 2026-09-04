<x-guest-layout>
    <div
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
        <!-- Store Branding -->
        <div class="mb-2 text-center">
            <div class="flex justify-center gap-5 items-center">
                <div
                    class="w-16 h-16 bg-gradient-to-br from-indigo-600 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200 dark:shadow-indigo-950/40">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M13 21v-6a2 2 0 012-2h5a2 2 0 012 2v6m-13 0v-3a2 2 0 012-2h2m-9 4v-6a2 2 0 012-2h2m0-7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-4xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 dark:from-white dark:to-slate-300 bg-clip-text text-transparent">
                        Open Your Store</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-2">Register your store and start selling today</p>
                </div>
            </div>
        </div>

        <!-- Registration Card -->
        <div
            class="w-full sm:max-w-2xl px-8 py-10 bg-white dark:bg-slate-800/90 shadow-2xl overflow-hidden sm:rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition-all duration-300">
            <form method="POST" action="{{ route('store-owner.register.post') }}">
                @csrf

                @if ($errors->any())
                    <div class="mb-6 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700/60 p-4">
                        <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Store Name -->
                <div class="mb-6">
                    <x-input-label for="store_name" value="Store / Company Name"
                        class="text-slate-700 dark:text-slate-200 font-semibold text-sm mb-1" />
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <x-text-input id="store_name"
                            class="block w-full pl-10 pr-3 py-3.5 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-all duration-200 text-base"
                            type="text" name="store_name" :value="old('store_name')" required autofocus
                            placeholder="e.g. Rahim Electronics" />
                    </div>
                    <x-input-error :messages="$errors->get('store_name')" class="mt-1.5 text-sm" />
                </div>

                <!-- First / Last Name -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <x-input-label for="first_name" value="First Name"
                            class="text-slate-700 dark:text-slate-200 font-semibold text-sm mb-1" />
                        <x-text-input id="first_name"
                            class="block w-full py-3.5 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-all duration-200 text-base"
                            type="text" name="first_name" :value="old('first_name')" required autocomplete="given-name" />
                        <x-input-error :messages="$errors->get('first_name')" class="mt-1.5 text-sm" />
                    </div>
                    <div>
                        <x-input-label for="last_name" value="Last Name"
                            class="text-slate-700 dark:text-slate-200 font-semibold text-sm mb-1" />
                        <x-text-input id="last_name"
                            class="block w-full py-3.5 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-all duration-200 text-base"
                            type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" />
                        <x-input-error :messages="$errors->get('last_name')" class="mt-1.5 text-sm" />
                    </div>
                </div>
<!-- Email / Phone -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <x-input-label for="email" value="Email Address"
                            class="text-slate-700 dark:text-slate-200 font-semibold text-sm mb-1" />
                        <x-text-input id="email"
                            class="block w-full py-3.5 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-all duration-200 text-base"
                            type="email" name="email" :value="old('email')" required autocomplete="email" placeholder="you@example.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-sm" />
                    </div>
                    <div>
                        <x-input-label for="phone" value="Phone (optional)"
                            class="text-slate-700 dark:text-slate-200 font-semibold text-sm mb-1" />
                        <x-text-input id="phone"
                            class="block w-full py-3.5 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-all duration-200 text-base"
                            type="text" name="phone" :value="old('phone')" autocomplete="tel" placeholder="01XXXXXXXXX" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-1.5 text-sm" />
                    </div>
                </div>

                <!-- Currency / Timezone -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <x-input-label for="currency_code" value="Currency"
                            class="text-slate-700 dark:text-slate-200 font-semibold text-sm mb-1" />
                        <select id="currency_code" name="currency_code"
                            class="block w-full py-3.5 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-all duration-200 text-base">
                            <option value="USD" @selected(old('currency_code', 'USD') === 'USD')>USD - US Dollar</option>
                            <option value="BDT" @selected(old('currency_code') === 'BDT')>BDT - Bangladeshi Taka</option>
                            <option value="INR" @selected(old('currency_code') === 'INR')>INR - Indian Rupee</option>
                            <option value="PKR" @selected(old('currency_code') === 'PKR')>PKR - Pakistani Rupee</option>
                            <option value="GBP" @selected(old('currency_code') === 'GBP')>GBP - British Pound</option>
                            <option value="EUR" @selected(old('currency_code') === 'EUR')>EUR - Euro</option>
                            <option value="AED" @selected(old('currency_code') === 'AED')>AED - UAE Dirham</option>
                            <option value="SAR" @selected(old('currency_code') === 'SAR')>SAR - Saudi Riyal</option>
                        </select>
                        <x-input-error :messages="$errors->get('currency_code')" class="mt-1.5 text-sm" />
                    </div>
                    <div>
                        <x-input-label for="timezone" value="Timezone"
                            class="text-slate-700 dark:text-slate-200 font-semibold text-sm mb-1" />
                        <select id="timezone" name="timezone"
                            class="block w-full py-3.5 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-all duration-200 text-base">
                            @php $tz = old('timezone', 'UTC'); @endphp
                            @foreach (['UTC', 'Asia/Dhaka', 'Asia/Kolkata', 'Asia/Karachi', 'Asia/Singapore', 'Asia/Dubai', 'Europe/London', 'America/New_York'] as $t)
                                <option value="{{ $t }}" @selected($tz === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('timezone')" class="mt-1.5 text-sm" />
                    </div>
                </div>
<!-- Password / Confirm -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <x-input-label for="password" value="Password"
                            class="text-slate-700 dark:text-slate-200 font-semibold text-sm mb-1" />
                        <x-text-input id="password"
                            class="block w-full py-3.5 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-all duration-200 text-base"
                            type="password" name="password" required autocomplete="new-password" placeholder="Min 8 characters" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-sm" />
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" value="Confirm Password"
                            class="text-slate-700 dark:text-slate-200 font-semibold text-sm mb-1" />
                        <x-text-input id="password_confirmation"
                            class="block w-full py-3.5 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-all duration-200 text-base"
                            type="password" name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-sm" />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-8 pt-2">
                    <a class="text-center sm:text-left text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium transition-colors duration-200 inline-flex items-center justify-center"
                        href="{{ route('login') }}">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to login
                    </a>

                    <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 active:from-indigo-800 active:to-indigo-700 border border-transparent rounded-xl font-semibold text-sm text-white uppercase tracking-wide transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Create Store
                    </button>
                </div>

                <!-- Terms & Footer -->
                <div class="mt-8 pt-5 border-t border-slate-200 dark:border-slate-700/70 text-center">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        By registering, you agree to our
                        <a href="#" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">Terms of Service</a>
                        and
                        <a href="#" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">Privacy Policy</a>.
                        Your store goes live immediately.
                    </p>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="mt-10 text-center text-xs text-slate-400 dark:text-slate-500">
            © {{ date('Y') }} Marketplace. All rights reserved.
        </div>
    </div>
</x-guest-layout>