<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if($store)
                @php
                    $primaryDomain = $store->domains->firstWhere('is_primary', true) ?? $store->domains->first();
                    $customDomain = $store->domains->firstWhere('type', 'custom');
                    $storeUrl = $primaryDomain ? \Modules\Store\Support\StoreDomainResolver::urlForDomain($primaryDomain->domain) : null;
                @endphp
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:p-6 border-b border-gray-100">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-xl bg-primary text-white flex items-center justify-center text-xl font-bold">
                                    {{ strtoupper(substr($store->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Your store</p>
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $store->name }}</h3>
                                    <p class="text-sm text-gray-500 mt-0.5">{{ $store->email ?: 'Store contact email not set' }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 self-start px-2.5 py-1 rounded-full text-xs font-semibold {{ $store->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $store->status === 'active' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                {{ ucfirst($store->status ?? 'pending') }}
                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
                        <div class="p-4 sm:p-5">
                            <p class="text-xs font-medium text-gray-400">Storefront domain</p>
                            @if($storeUrl)
                                <a href="{{ $storeUrl }}" target="_blank" rel="noopener" class="mt-1 block text-sm font-semibold text-primary hover:underline truncate">{{ $primaryDomain->domain }}</a>
                            @else
                                <p class="mt-1 text-sm text-gray-500">Not configured</p>
                            @endif
                        </div>
                        <div class="p-4 sm:p-5">
                            <p class="text-xs font-medium text-gray-400">Custom domain</p>
                            @if($customDomain)
                                <p class="mt-1 text-sm font-semibold text-gray-800 truncate">{{ $customDomain->domain }}</p>
                                <p class="text-xs {{ $customDomain->isVerified() ? 'text-emerald-600' : 'text-amber-600' }}">{{ $customDomain->isVerified() ? 'Verified' : 'Verification pending' }}</p>
                            @else
                                <p class="mt-1 text-sm text-gray-500">No custom domain</p>
                            @endif
                        </div>
                        <div class="p-4 sm:p-5">
                            <p class="text-xs font-medium text-gray-400">Account role</p>
                            <p class="mt-1 text-sm font-semibold text-gray-800">{{ $user->roles->pluck('name')->implode(', ') ?: 'Store Owner' }}</p>
                            <p class="text-xs text-gray-500">Store owner account</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
