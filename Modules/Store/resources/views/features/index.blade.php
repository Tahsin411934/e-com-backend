<x-app-layout>
    <div class="p-4">
        <x-entity-crud id="feature" createPermission="features" title="Plan Features" icon="fa-solid fa-puzzle-piece"
            :columns="['Feature','Slug','Type','Description','Status','Action']"
            :dtColumns="[['data'=>'name'],['data'=>'slug'],['data'=>'type'],['data'=>'description'],['data'=>'is_active'],['data'=>'action','orderable'=>false,'searchable'=>false]]"
            ajaxUrl="{{ route('features.dataTable') }}" storeUrl="{{ route('features.store') }}" updateUrl="{{ route('features.update', ':id') }}" showUrl="{{ route('features.show', ':id') }}" destroyUrl="{{ route('features.destroy', ':id') }}" drawerTitle="Feature" dataKey="data" idField="feature_id" :order="[[0,'asc']]">
            <x-form-input label="Feature name" name="name" id="feature_name" placeholder="Pixel Setup" required />
            <x-form-input label="Slug" name="slug" id="feature_slug" placeholder="pixel_setup" required />
            <x-form-select label="Feature type" name="type" id="feature_type" required><option value="boolean">Boolean capability</option><option value="number">Numeric limit</option><option value="text">Text configuration</option></x-form-select>
            <div class="mt-4"><x-form-textarea label="Description" name="description" id="feature_description" rows="3" /></div>
            <label class="mt-4 inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" id="feature_is_active" value="1" class="rounded border-gray-300" checked> Active</label>
        </x-entity-crud>
    </div>
    @push('scripts')<script>
        Crud.register('feature', 'fill', function (data) { $('#feature_name').val(data.name); $('#feature_slug').val(data.slug); $('#feature_type').val(data.type); $('#feature_description').val(data.description || ''); $('#feature_is_active').prop('checked', !!data.is_active); });
    </script>@endpush
</x-app-layout>
