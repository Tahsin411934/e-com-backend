<x-app-layout>
    <div class="p-4">
        <x-entity-crud id="plan" createPermission="plans" title="Subscription Plans" icon="fa-solid fa-tags"
            :columns="['Plan','Slug','Price','Duration','Products','Status','Action']"
            :dtColumns="[['data'=>'name'],['data'=>'slug'],['data'=>'price'],['data'=>'duration_days'],['data'=>'product_limit'],['data'=>'is_active'],['data'=>'action','orderable'=>false,'searchable'=>false]]"
            ajaxUrl="{{ route('plans.dataTable') }}" storeUrl="{{ route('plans.store') }}" updateUrl="{{ route('plans.update', ':id') }}"
            showUrl="{{ route('plans.show', ':id') }}" destroyUrl="{{ route('plans.destroy', ':id') }}" drawerTitle="Plan" dataKey="data" idField="plan_id" :order="[[2,'asc']]">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-input label="Plan name" name="name" id="plan_name" placeholder="Free Trial" required />
                <x-form-input label="Slug" name="slug" id="plan_slug" placeholder="free-trial" required />
                <x-form-input label="Price" name="price" id="plan_price" type="number" step="0.01" min="0" required />
                <x-form-input label="Currency" name="currency" id="plan_currency" value="BDT" maxlength="3" required />
                <x-form-input label="Duration (days)" name="duration_days" id="plan_duration_days" type="number" min="1" placeholder="Empty = no expiry" />
                <x-form-input label="Product limit" name="product_limit" id="plan_product_limit" type="number" min="1" placeholder="Empty = unlimited" />
            </div>
            <div class="mt-4"><x-form-textarea label="Description" name="description" id="plan_description" rows="2" /></div>
            <div class="mt-4"><x-form-textarea label="Feature summary (optional)" name="features" id="plan_features" rows="2" /></div>
            <div class="mt-4"><div class="mb-2 text-sm font-medium text-gray-700">Included capabilities</div><div class="grid grid-cols-1 md:grid-cols-2 gap-2 rounded-lg border border-gray-200 p-3">@foreach($features as $feature)<label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="feature_ids[]" value="{{ $feature->id }}" data-feature-id="{{ $feature->id }}" class="plan-feature rounded border-gray-300"> {{ $feature->name }}</label>@endforeach</div></div>
            <div class="mt-4 flex flex-wrap gap-5 text-sm"><label class="inline-flex items-center gap-2"><input type="checkbox" name="is_free" id="plan_is_free" value="1" class="rounded border-gray-300"> Free plan</label><label class="inline-flex items-center gap-2"><input type="checkbox" name="is_public" id="plan_is_public" value="1" class="rounded border-gray-300" checked> Visible publicly</label><label class="inline-flex items-center gap-2"><input type="checkbox" name="is_active" id="plan_is_active" value="1" class="rounded border-gray-300" checked> Active</label></div>
        </x-entity-crud>
    </div>
    @push('scripts')<script>
        Crud.register('plan', 'fill', function (data) {
            $('#plan_name').val(data.name); $('#plan_slug').val(data.slug); $('#plan_price').val(data.price); $('#plan_currency').val(data.currency);
            $('#plan_duration_days').val(data.duration_days); $('#plan_product_limit').val(data.product_limit); $('#plan_description').val(data.description || '');
            $('#plan_features').val(Array.isArray(data.features) ? data.features.join('\n') : ''); $('#plan_is_free').prop('checked', !!data.is_free);
            $('#plan_is_public').prop('checked', !!data.is_public); $('#plan_is_active').prop('checked', !!data.is_active);
            $('.plan-feature').prop('checked', false); (data.features || []).forEach(function (feature) { $('.plan-feature[data-feature-id="' + feature.id + '"]').prop('checked', true); });
        });
    </script>@endpush
</x-app-layout>
