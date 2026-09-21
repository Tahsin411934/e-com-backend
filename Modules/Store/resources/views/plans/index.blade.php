<x-app-layout>
    <div class="p-4 md:p-6">
        <div class="mb-5 flex items-center justify-between">
            <div><h1 class="text-xl font-semibold text-gray-900">Subscription Plans</h1><p class="mt-1 text-sm text-gray-500">Manage the plans available to store owners.</p></div>
        </div>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50"><tr><th class="px-5 py-3 text-left font-semibold text-gray-600">Plan</th><th class="px-5 py-3 text-left font-semibold text-gray-600">Price</th><th class="px-5 py-3 text-left font-semibold text-gray-600">Duration</th><th class="px-5 py-3 text-left font-semibold text-gray-600">Product limit</th><th class="px-5 py-3 text-left font-semibold text-gray-600">Status</th></tr></thead>
                <tbody class="divide-y divide-gray-100">@forelse($plans as $plan)<tr><td class="px-5 py-4"><div class="font-medium text-gray-900">{{ $plan->name }}</div><div class="text-xs text-gray-500">{{ $plan->description }}</div></td><td class="px-5 py-4">{{ $plan->is_free ? 'Free' : number_format((float) $plan->price, 2).' '.$plan->currency }}</td><td class="px-5 py-4">{{ $plan->duration_days ? $plan->duration_days.' days' : 'No expiry' }}</td><td class="px-5 py-4">{{ $plan->product_limit ?? 'Unlimited' }}</td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span></td></tr>@empty<tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">No plans configured.</td></tr>@endforelse</tbody>
            </table></div>
        </div>
    </div>
</x-app-layout>
