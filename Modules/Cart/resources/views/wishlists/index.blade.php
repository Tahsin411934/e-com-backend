<x-app-layout>
    <x-entity-crud
        id="wishlist"
        title="Wishlists"
        icon="fa-solid fa-heart"
        :columns="['ID','User','Product','Created','Action']"
        :dtColumns="[
            ['data' => 'id'],
            ['data' => 'user_email'],
            ['data' => 'product_name'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('wishlists.dataTable') }}"
        storeUrl="{{ route('wishlists.store') }}"
        updateUrl="{{ route('wishlists.update', ':id') }}"
        showUrl="{{ route('wishlists.show', ':id') }}"
        destroyUrl="{{ route('wishlists.destroy', ':id') }}"
        drawerTitle="Wishlist"
        dataKey="data"
        idField="wishlist_id"
        :order="[[0, 'desc']]"
    >
        <div class="mb-4">
            <x-form-input label="User ID" name="user_id" id="wishlist_user_id" type="number" placeholder="User ID" />
        </div>
        <div class="mb-4">
            <x-form-input label="Product ID" name="product_id" id="wishlist_product_id" type="number" placeholder="Product ID" required />
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillWishlistForm = function(data) {
            $('#wishlist_user_id').val(data.user_id);
            $('#wishlist_product_id').val(data.product_id);
        };
    </script>
    @endpush
</x-app-layout>