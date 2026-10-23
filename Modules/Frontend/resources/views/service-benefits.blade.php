<x-app-layout>
    <div class="p-4 sm:p-6 max-w-6xl mx-auto">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div><h1 class="text-2xl font-bold text-gray-900 dark:text-white">Service Benefits</h1><p class="mt-2 text-sm text-gray-500">Add your store's benefits. Only active items appear above the storefront footer. An empty list stays hidden.</p></div>
            <a href="{{ route('documentation.index') }}#benefits" class="text-sm text-primary underline">Documentation ↗</a>
        </div>
        @if(session('status'))<p role="status" class="mb-4 rounded-lg bg-green-50 p-4 text-green-800">{{ session('status') }}</p>@endif
        @if($errors->any())<div role="alert" class="mb-4 rounded-lg bg-red-50 p-4 text-red-800"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @if($storeId === null)
            <form method="get" class="flex flex-wrap items-end gap-3 mb-6">
                <div><label for="benefit-store-filter" class="block text-sm mb-1">Filter by store</label><select id="benefit-store-filter" name="store_id" class="rounded-lg border-gray-300 dark:bg-gray-800"><option value="">All stores</option>@foreach($stores as $store)<option value="{{ $store->id }}" @selected((string) request('store_id') === (string) $store->id)>{{ $store->name }}</option>@endforeach</select></div>
                <button class="px-4 py-2 rounded-lg bg-primary text-white" type="submit">Apply</button>
            </form>
        @endif
        @if(auth()->user()->hasPermission('frontend.service-benefits.create'))
            <details class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 sm:p-6 mb-6" @if(old('_benefit_id') === 'new' || $benefits->isEmpty()) open @endif>
                <summary class="font-semibold cursor-pointer text-gray-900 dark:text-white">Add service benefit</summary>
                <form action="{{ route('frontend.service-benefits.store') }}" method="post" class="mt-4">
                    @csrf
                    @include('frontend::service-benefit-fields', ['benefit' => null])
                    <button type="submit" class="mt-4 px-4 py-2 rounded-lg bg-primary text-white">Add benefit</button>
                </form>
            </details>
        @endif
        <div class="space-y-4">
            @forelse($benefits as $benefit)
                <article class="min-w-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3"><i class="{{ $icons[$benefit->icon]['class'] ?? 'fa-solid fa-box' }} text-xl text-primary" aria-hidden="true"></i><div><h2 class="font-semibold break-words text-gray-900 dark:text-white">{{ $benefit->title }}</h2><p class="text-xs text-gray-500">{{ $benefit->store?->name }} · Order {{ $benefit->sort_order }} · {{ $benefit->is_active ? 'Active' : 'Inactive' }}</p></div></div>
                        @if(auth()->user()->hasPermission('frontend.service-benefits.delete'))
                            <form action="{{ route('frontend.service-benefits.destroy', $benefit->id) }}" method="post" onsubmit="return confirm('Delete this service benefit?')">@csrf @method('DELETE')<button type="submit" class="text-sm text-red-600">Delete</button></form>
                        @endif
                    </div>
                    @if($benefit->description)<p class="mt-3 text-sm text-gray-500 break-words">{{ $benefit->description }}</p>@endif
                    @if(auth()->user()->hasPermission('frontend.service-benefits.edit'))
                        <details class="mt-4" @if((string) old('_benefit_id') === (string) $benefit->id) open @endif><summary class="text-sm text-primary cursor-pointer">Edit benefit</summary>
                            <form action="{{ route('frontend.service-benefits.update', $benefit->id) }}" method="post" class="mt-4">@csrf @method('PUT')
                                @include('frontend::service-benefit-fields', ['benefit' => $benefit])
                                <button type="submit" class="mt-4 px-4 py-2 rounded-lg bg-primary text-white">Save changes</button>
                            </form>
                        </details>
                    @endif
                </article>
            @empty
                <p class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-gray-500">No service benefits yet. Nothing will be displayed on this store until you add an active benefit.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $benefits->links() }}</div>
    </div>
</x-app-layout>
