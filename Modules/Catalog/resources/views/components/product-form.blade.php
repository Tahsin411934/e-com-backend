@props([
    'product' => null,
    'brands' => [],
    'categories' => [],
    'units' => [],
    'sizes' => [],
    'taxRates' => [],
    'navbarItems' => [],
    'subnavbarItems' => [],
    'formTitle' => 'Create New Product',
    'submitButton' => 'Save Product',
    'isEdit' => false,
])

<x-app-layout>
    <div class="p-4">
        <!-- Breadcrumb & Header -->
        <div class="flex items-center justify-between mb-5">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                    <a href="{{ route('products.index') }}" class="hover:text-primary transition-colors">Products</a>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                    <span class="text-gray-800 font-medium">{{ $isEdit ? 'Edit' : 'Create New' }}</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-800">{{ $formTitle }}</h1>
            </div>
            <a href="{{ route('products.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition active:scale-95 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>

        <form id="productForm" class="space-y-6" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="product_id" value="{{ $product?->id ?? '' }}">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-info-circle text-primary"></i> Basic Information
                            </h2>
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-form-input label="Product Name" name="name" id="name" placeholder="Enter product name" value="{{ $product?->name ?? '' }}" required />
                                </div>
                                <div>
                                    <x-form-input label="Slug" name="slug" id="slug" placeholder="auto-generated-slug" value="{{ $product?->slug ?? '' }}" required />
                                </div>
                            </div>
                            <div>
                                <x-form-textarea label="Short Description" name="short_description" id="short_description" placeholder="Brief description (max 500 chars)" rows="3" value="{{ $product?->short_description ?? '' }}" />
                            </div>
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Full Description</label>
                                <textarea name="description" id="description" rows="8" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm" placeholder="Detailed product description...">{{ $product?->description ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Media & Images -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-images text-green-600"></i> Product Images
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <label for="images" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-primary hover:bg-primary rounded-lg cursor-pointer transition active:scale-95 shadow-sm">
                                    <i class="fas fa-upload"></i> Upload Images
                                </label>
                                <span class="text-xs text-gray-400">JPG, PNG, WebP up to 5MB each</span>
                                <input type="file" name="images[]" id="images" accept="image/*" multiple class="hidden">
                            </div>
                            <div id="imagePreviewContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                                @if($isEdit && $product?->images)
                                    @foreach($product->images as $image)
                                        <div class="relative group preview-card bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden {{ $image->is_main ? 'ring-2 ring-indigo-500' : '' }}">
                                            <img src="{{ $image->image_url }}" class="h-40 w-full object-cover" />
                                            @if($image->is_main)
                                                <div class="absolute top-1 left-1 bg-indigo-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">Main</div>
                                            @endif
                                            <button type="button" class="remove-preview absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity shadow-lg" data-image-id="{{ $image->id }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <div class="p-2 bg-gray-50 border-t border-gray-100 flex items-center justify-center gap-2">
                                                <input type="radio" name="main_image_id" value="{{ $image->id }}" {{ $image->is_main ? 'checked' : '' }} class="w-3.5 h-3.5 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                                <span class="text-[10px] font-medium text-gray-500">Main Photo</span>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- SEO -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-search text-purple-600"></i> SEO Settings
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <x-form-input label="SEO Title" name="seo_title" id="seo_title" placeholder="Custom SEO title" value="{{ $product?->seo_title ?? '' }}" />
                            <x-form-textarea label="SEO Description" name="seo_description" id="seo_description" placeholder="Custom meta description" rows="2" value="{{ $product?->seo_description ?? '' }}" />
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Organization -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-sitemap text-orange-600"></i> Organization
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <x-form-select label="Brand" name="brand_id" id="brand_id">
                                <option value="">None</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ $product?->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </x-form-select>
                            <x-form-select label="Unit" name="unit_id" id="unit_id">
                                <option value="">None</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" {{ $product?->unit_id == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->short_name }})</option>
                                @endforeach
                            </x-form-select>
                            <x-form-select label="Size" name="size_id" id="size_id">
                                <option value="">None</option>
                                @foreach ($sizes as $size)
                                    <option value="{{ $size->id }}" {{ $product?->size_id == $size->id ? 'selected' : '' }}>{{ $size->group_name }}</option>
                                @endforeach
                            </x-form-select>
                            <x-form-select label="Tax Rate" name="tax_rate_id" id="tax_rate_id">
                                <option value="">No Tax</option>
                                @foreach ($taxRates as $taxRate)
                                    <option value="{{ $taxRate->id }}" {{ $product?->tax_rate_id == $taxRate->id ? 'selected' : '' }}>
                                        {{ $taxRate->name }} ({{ $taxRate->type === 'percentage' ? $taxRate->rate . '%' : '৳' . number_format($taxRate->rate, 2) }})
                                    </option>
                                @endforeach
                            </x-form-select>
                            <div>
                                <label for="category_ids" class="block text-sm font-medium text-gray-700 mb-2">Categories</label>
                                <select name="category_ids[]" id="category_ids" multiple class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm" size="5">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ $isEdit && $product?->categories->contains($category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple</p>
                            </div>
                            <x-form-select label="Navbar Item" name="navbar_item_id" id="navbar_item_id">
                                <option value="">None</option>
                                @foreach ($navbarItems as $navbarItem)
                                    <option value="{{ $navbarItem->id }}" {{ $isEdit && $product?->navbar_item_id == $navbarItem->id ? 'selected' : '' }}>{{ $navbarItem->name }}</option>
                                @endforeach
                            </x-form-select>
                            <x-form-select label="Subnavbar Item" name="subnavbar_item_id" id="subnavbar_item_id">
                                <option value="">None</option>
                            </x-form-select>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-cog text-gray-600"></i> Settings
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <x-form-select label="Product Type" name="product_type" id="product_type">
                                <option value="physical" {{ ($product?->product_type ?? 'physical') === 'physical' ? 'selected' : '' }}>Physical</option>
                                <option value="digital" {{ $product?->product_type === 'digital' ? 'selected' : '' }}>Digital</option>
                                <option value="service" {{ $product?->product_type === 'service' ? 'selected' : '' }}>Service</option>
                                <option value="bundle" {{ $product?->product_type === 'bundle' ? 'selected' : '' }}>Bundle</option>
                            </x-form-select>
                            <x-form-select label="Status" name="status" id="status">
                                <option value="draft" {{ ($product?->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="active" {{ $product?->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="archived" {{ $product?->status === 'archived' ? 'selected' : '' }}>Archived</option>
                            </x-form-select>
                            <x-form-select label="Visibility" name="visibility" id="visibility">
                                <option value="public" {{ ($product?->visibility ?? 'public') === 'public' ? 'selected' : '' }}>Public</option>
                                <option value="hidden" {{ $product?->visibility === 'hidden' ? 'selected' : '' }}>Hidden</option>
                                <option value="private" {{ $product?->visibility === 'private' ? 'selected' : '' }}>Private</option>
                            </x-form-select>
                            <x-form-input label="Published At" name="published_at" id="published_at" type="datetime-local" value="{{ $product?->published_at?->format('Y-m-d\TH:i') ?? '' }}" />
                            <div class="pt-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Show on Homepage</label>
                                <div class="flex items-center gap-6">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="is_homepage" value="1" {{ $product?->is_homepage ? 'checked' : '' }} class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                                        <span class="text-sm text-gray-700">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="is_homepage" value="0" {{ !$product?->is_homepage ? 'checked' : '' }} class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                                        <span class="text-sm text-gray-700">No</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Variants Card -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-code-branch text-cyan-600"></i> Variants
                            </h2>
                            <button type="button" id="btnAddVariant" class="text-sm text-primary hover:text-primary-hover font-medium flex items-center gap-1">
                                <i class="fas fa-plus-circle"></i> Add Variant
                            </button>
                        </div>
                        <div class="p-6">
                            <div id="variantsContainer">
                                <p id="noVariantsMsg" class="text-sm text-gray-400 text-center py-4 {{ $isEdit && $product?->variants->count() > 0 ? 'hidden' : '' }}">No variants added yet.</p>
                                @if($isEdit && $product?->variants->count() > 0)
                                    @foreach($product->variants as $vIdx => $v)
                                    <div class="variant-item bg-gray-50 rounded-lg p-4 mb-3 border border-gray-200 relative" data-variant-edit="{{ $vIdx }}">
                                        <button type="button" class="remove-variant absolute top-2 right-2 text-gray-400 hover:text-red-500" data-index="{{ $vIdx }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <input type="hidden" name="variants[{{ $vIdx }}][id]" class="variant-id" value="{{ $v->id }}">
                                        <div class="grid grid-cols-2 gap-3 mb-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">SKU *</label>
                                                <input type="text" name="variants[{{ $vIdx }}][sku]" value="{{ $v->sku }}" class="variant-sku w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm" required>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Name *</label>
                                                <input type="text" name="variants[{{ $vIdx }}][name]" value="{{ $v->name }}" class="variant-name w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm" required>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-3 gap-3 mb-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Cost Price</label>
                                                <input type="number" step="0.0001" min="0" name="variants[{{ $vIdx }}][cost_price]" value="{{ $v->cost_price }}" class="variant-cost w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Sale Price *</label>
                                                <input type="number" step="0.0001" min="0" name="variants[{{ $vIdx }}][sale_price]" value="{{ $v->sale_price }}" class="variant-price w-full rounded-lg border-gray-300 shadow-sm text-sm" required>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Compare At</label>
                                                <input type="number" step="0.0001" min="0" name="variants[{{ $vIdx }}][compare_at_price]" value="{{ $v->compare_at_price }}" class="variant-compare w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-3 gap-3 mb-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                                                <input type="text" name="variants[{{ $vIdx }}][attributes][color]" value="{{ $v->attributes['color'] ?? '' }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Size</label>
                                                <input type="text" name="variants[{{ $vIdx }}][attributes][size]" value="{{ $v->attributes['size'] ?? '' }}" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Color Hex</label>
                                                <input type="color" name="variants[{{ $vIdx }}][attributes][color_hex]" value="{{ isset($v->attributes['color_hex']) ? '#' . ltrim($v->attributes['color_hex'], '#') : '#000000' }}" class="w-full h-11 rounded-lg border-gray-300 shadow-sm text-sm">
                                            </div>
                                        </div>

                                        <!-- Variant Options (Color options per size) -->
                                        <div class="mb-3 border-t border-gray-200 pt-3">
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="text-xs font-semibold text-gray-700">Color Options (for this size)</label>
                                                <button type="button" class="add-option-btn text-xs text-primary hover:text-primary-hover font-medium" data-variant-id="{{ $v->id }}" data-edit-index="{{ $vIdx }}">
                                                    <i class="fas fa-plus-circle"></i> Add Color
                                                </button>
                                            </div>
                                            <div class="variant-options-container space-y-2" data-edit-index="{{ $vIdx }}">
                                                @if($v->options->count() > 0)
                                                    @foreach($v->options as $optIdx => $option)
                                                    <div class="option-item flex flex-wrap gap-2 items-end p-2 bg-white rounded border border-gray-200">
                                                        <input type="hidden" name="variants[{{ $vIdx }}][options][{{ $optIdx }}][id]" value="{{ $option->id }}">
                                                        <div class="flex-1 min-w-[120px]">
                                                            <label class="block text-[10px] font-medium text-gray-500">Color</label>
                                                            <input type="text" name="variants[{{ $vIdx }}][options][{{ $optIdx }}][color_name]" value="{{ $option->color_name }}" class="w-full rounded border-gray-200 text-sm">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-medium text-gray-500">Hex</label>
                                                            <input type="color" name="variants[{{ $vIdx }}][options][{{ $optIdx }}][color_code]" value="{{ $option->color_code ?? '#000000' }}" class="h-8 w-14 rounded border-gray-200">
                                                        </div>
                                                        <div class="w-20">
                                                            <label class="block text-[10px] font-medium text-gray-500">Price ±</label>
                                                            <input type="number" step="1" name="variants[{{ $vIdx }}][options][{{ $optIdx }}][price_adjustment]" value="{{ $option->price_adjustment }}" class="w-full rounded border-gray-200 text-sm">
                                                        </div>
                                                        <div class="w-16">
                                                            <label class="block text-[10px] font-medium text-gray-500">Stock</label>
                                                            <input type="number" name="variants[{{ $vIdx }}][options][{{ $optIdx }}][stock]" value="{{ $option->stock }}" class="w-full rounded border-gray-200 text-sm">
                                                        </div>
                                                        <button type="button" class="remove-option text-red-500 hover:text-red-700 text-lg pb-1" title="Remove">&times;</button>
                                                    </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-3 gap-3 mb-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Barcode</label>
                                                <input type="text" name="variants[{{ $vIdx }}][barcode]" value="{{ $v->barcode }}" class="variant-barcode w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Weight (g)</label>
                                                <input type="number" min="0" name="variants[{{ $vIdx }}][weight_grams]" value="{{ $v->weight_grams }}" class="variant-weight w-full rounded-lg border-gray-300 shadow-sm text-sm">
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-4 mt-3">
                                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                                <input type="checkbox" name="variants[{{ $vIdx }}][track_inventory]" value="1" {{ $v->track_inventory ? 'checked' : '' }} class="rounded border-gray-300 text-primary"> Track Inventory
                                            </label>
                                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                                <input type="checkbox" name="variants[{{ $vIdx }}][allow_backorder]" value="1" {{ $v->allow_backorder ? 'checked' : '' }} class="rounded border-gray-300 text-primary"> Allow Backorder
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 bg-white rounded-xl border border-gray-200 shadow-sm px-6 py-4">
                <a href="{{ route('products.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition active:scale-95">Cancel</a>
                <button type="submit" id="saveBtn" class="px-6 py-2.5 text-sm font-medium text-white bg-primary hover:bg-primary rounded-lg transition active:scale-95 flex items-center gap-2 shadow-sm">
                    <i class="fas fa-save"></i> <span id="submitBtnText">{{ $submitButton }}</span>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        let isSaving = false;
        let variantIndex = {{ $isEdit ? ($product?->variants->count() ?? 0) : 0 }};
        let productDescriptionEditor;
        let deletedImageIds = [];
        let optionCounters = {};
        @if($isEdit && $product?->variants)
            @foreach($product->variants as $vIdx => $v)
                optionCounters[{{ $vIdx }}] = {{ $v->options->count() }};
            @endforeach
        @endif

        ClassicEditor.create(document.querySelector('#description'), {
            toolbar: ['heading','|','bold','italic','underline','link','bulletedList','numberedList','|','undo','redo'],
            heading: { options: [
                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
            ]}
        }).then(editor => { productDescriptionEditor = editor; });

        function getOptionTemplate(vIdx, optIdx) {
            return `
            <div class="option-item flex flex-wrap gap-2 items-end p-2 bg-white rounded border border-gray-200">
                <input type="hidden" name="variants[${vIdx}][options][${optIdx}][id]" value="">
                <div class="flex-1 min-w-[120px]">
                    <label class="block text-[10px] font-medium text-gray-500">Color</label>
                    <input type="text" name="variants[${vIdx}][options][${optIdx}][color_name]" class="w-full rounded border-gray-200 text-sm" placeholder="e.g. Red">
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-500">Hex</label>
                    <input type="color" name="variants[${vIdx}][options][${optIdx}][color_code]" value="#000000" class="h-8 w-14 rounded border-gray-200">
                </div>
                <div class="w-20">
                    <label class="block text-[10px] font-medium text-gray-500">Price ±</label>
                    <input type="number" step="1" name="variants[${vIdx}][options][${optIdx}][price_adjustment]" value="0" class="w-full rounded border-gray-200 text-sm">
                </div>
                <div class="w-16">
                    <label class="block text-[10px] font-medium text-gray-500">Stock</label>
                    <input type="number" name="variants[${vIdx}][options][${optIdx}][stock]" value="0" class="w-full rounded border-gray-200 text-sm">
                </div>
                <button type="button" class="remove-option text-red-500 hover:text-red-700 text-lg pb-1" title="Remove">&times;</button>
            </div>`;
        }

        function getVariantTemplate(index) {
            return `
            <div class="variant-item bg-gray-50 rounded-lg p-4 mb-3 border border-gray-200 relative">
                <button type="button" class="remove-variant absolute top-2 right-2 text-gray-400 hover:text-red-500" data-index="${index}">
                    <i class="fas fa-times"></i>
                </button>
                <input type="hidden" name="variants[${index}][id]" class="variant-id" value="">
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">SKU *</label>
                        <input type="text" name="variants[${index}][sku]" class="variant-sku w-full rounded-lg border-gray-300 shadow-sm text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Name *</label>
                        <input type="text" name="variants[${index}][name]" class="variant-name w-full rounded-lg border-gray-300 shadow-sm text-sm" required>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Cost Price</label>
                        <input type="number" step="0.0001" min="0" name="variants[${index}][cost_price]" class="variant-cost w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sale Price *</label>
                        <input type="number" step="0.0001" min="0" name="variants[${index}][sale_price]" class="variant-price w-full rounded-lg border-gray-300 shadow-sm text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Compare At</label>
                        <input type="number" step="0.0001" min="0" name="variants[${index}][compare_at_price]" class="variant-compare w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                        <input type="text" name="variants[${index}][attributes][color]" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Size</label>
                        <input type="text" name="variants[${index}][attributes][size]" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Color Hex</label>
                        <input type="color" name="variants[${index}][attributes][color_hex]" value="#000000" class="w-full h-11 rounded-lg border-gray-300 shadow-sm">
                    </div>
                </div>
                <div class="mb-3 border-t border-gray-200 pt-3">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold text-gray-700">Color Options (for this size)</label>
                        <button type="button" class="add-option-btn text-xs text-primary hover:text-primary-hover font-medium" data-new-index="${index}">
                            <i class="fas fa-plus-circle"></i> Add Color
                        </button>
                    </div>
                    <div class="variant-options-container space-y-2" data-new-index="${index}"></div>
                </div>
                <div class="grid grid-cols-3 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Barcode</label>
                        <input type="text" name="variants[${index}][barcode]" class="variant-barcode w-full rounded-lg border-gray-300 shadow-sm text-sm" value="${Date.now()}${Math.floor(Math.random()*900)+100}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Weight (g)</label>
                        <input type="number" min="0" name="variants[${index}][weight_grams]" class="variant-weight w-full rounded-lg border-gray-300 shadow-sm text-sm">
                    </div>
                </div>
                <div class="flex items-center gap-4 mt-3">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="variants[${index}][track_inventory]" value="1" checked class="rounded border-gray-300 text-primary"> Track Inventory
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="variants[${index}][allow_backorder]" value="1" class="rounded border-gray-300 text-primary"> Allow Backorder
                    </label>
                </div>
            </div>`;
        }

        // Add variant
        $('#btnAddVariant').on('click', function() {
            $('#noVariantsMsg').hide();
            $('#variantsContainer').append(getVariantTemplate(variantIndex));
            variantIndex++;
        });

        // Remove variant
        $(document).on('click', '.remove-variant', function() {
            const $item = $(this).closest('.variant-item');
            const id = $item.find('.variant-id').val();
            if (id) {
                $item.append(`<input type="hidden" name="deleted_variant_ids[]" value="${id}">`);
            }
            $item.fadeOut(300, function() { $(this).remove(); });
        });

        // Add color option to a variant
        $(document).on('click', '.add-option-btn', function() {
            const $btn = $(this);
            const $container = $btn.closest('.variant-item').find('.variant-options-container');
            const editIdx = $container.data('edit-index');
            const newIdx = $container.data('new-index');
            const vIdx = editIdx !== undefined ? editIdx : (newIdx !== undefined ? newIdx : '0');
            
            if (!optionCounters[vIdx]) optionCounters[vIdx] = 0;
            const optIdx = optionCounters[vIdx];
            optionCounters[vIdx]++;

            $container.append(getOptionTemplate(vIdx, optIdx));
        });

        // Remove color option
        $(document).on('click', '.remove-option', function() {
            const $item = $(this).closest('.option-item');
            const id = $item.find('input[name*="[id]"]').val();
            if (id) {
                // Mark for deletion by appending a hidden input to the form
                $item.append(`<input type="hidden" name="deleted_option_ids[]" value="${id}">`);
            }
            $item.fadeOut(200, function() { $(this).remove(); });
        });

        // Image handling - preview on selection
        $('#images').on('change', function() {
            const files = this.files;
            if (!files || files.length === 0) return;
            
            const $container = $('#imagePreviewContainer');
            
            // Remove "no images" placeholder if exists
            $container.find('.no-images-msg').remove();
            
            Array.from(files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const isFirst = $container.find('.preview-card').length === 0;
                    const card = `
                        <div class="relative group preview-card bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden ${isFirst ? 'ring-2 ring-indigo-500' : ''}">
                            <img src="${e.target.result}" class="h-40 w-full object-cover" />
                            ${isFirst ? '<div class="absolute top-1 left-1 bg-indigo-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">Main</div>' : ''}
                            <button type="button" class="remove-preview absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="p-2 bg-gray-50 border-t border-gray-100 flex items-center justify-center gap-2">
                                <input type="radio" name="main_image_id" value="new_${index}" ${isFirst ? 'checked' : ''} class="w-3.5 h-3.5 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                <span class="text-[10px] font-medium text-gray-500">Main</span>
                            </div>
                        </div>`;
                    $container.append(card);
                };
                reader.readAsDataURL(file);
            });
            
            // Don't reset input - files must remain in FormData for submission
        });

        $(document).on('change', 'input[name="main_image_id"]', function() {
            $('#imagePreviewContainer .preview-card').removeClass('ring-2 ring-indigo-500');
            $('#imagePreviewContainer .main-badge').remove();
            $(this).closest('.preview-card').addClass('ring-2 ring-indigo-500');
            $(this).closest('.preview-card').prepend('<div class="main-badge absolute top-1 left-1 bg-indigo-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">Main</div>');
        });

        $(document).on('click', '.remove-preview', function(e) {
            e.preventDefault();
            const $card = $(this).closest('.preview-card');
            const imageId = $(this).data('image-id');
            if (imageId) { deletedImageIds.push(imageId); }
            $card.fadeOut(300, function() { $(this).remove(); });
        });

        // Navbar -> Subnavbar
        $('#navbar_item_id').on('change', function() {
            const navbarItemId = $(this).val();
            const $subnavbarSelect = $('#subnavbar_item_id');
            $subnavbarSelect.find('option:not(:first)').remove();
            if (!navbarItemId) return;
            $.get('/api/v1/navbar-items/' + navbarItemId + '/children', function(res) {
                if (res.success && res.data && res.data.length > 0) {
                    $.each(res.data, function(i, item) {
                        $subnavbarSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
                    });
                }
            });
        });

        @if($isEdit && $product?->navbar_item_id)
            setTimeout(function() {
                const currentSubnavbarId = "{{ $product?->subnavbar_item_id ?? '' }}";
                $('#navbar_item_id').trigger('change');
                if (currentSubnavbarId) { $('#subnavbar_item_id').val(currentSubnavbarId); }
            }, 500);
        @endif

        // Auto-slug
        $('#name').on('input', function() {
            if (!$('#product_id').val()) {
                let slug = $(this).val().toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
                $('#slug').val(slug);
            }
        });

        function setButtonState(disabled, text) {
            $('#saveBtn').prop('disabled', disabled).toggleClass('opacity-70 cursor-not-allowed', disabled);
            $('#submitBtnText').text(text);
        }

        // Submit
        $('#productForm').on('submit', function(e) {
            e.preventDefault();
            if (isSaving) return;
            isSaving = true;
            setButtonState(true, 'Saving...');

            let id = $('#product_id').val();
            let url = id ? "{{ route('products.update', ':id') }}".replace(':id', id) : "{{ route('products.store') }}";
            let formData = new FormData(this);
            if (id) formData.append('_method', 'PUT');
            if (deletedImageIds.length > 0) formData.append('deleted_image_ids', JSON.stringify(deletedImageIds));
            if (productDescriptionEditor) formData.set('description', productDescriptionEditor.getData());

            $.ajax({
                url: url, type: 'POST', data: formData, processData: false, contentType: false,
                success: function(res) {
                    isSaving = false;
                    setButtonState(false, id ? 'Update Product' : 'Save Product');
                    if (res.status === 'success') {
                        Toastify({ text: res.message, duration: 3000, gravity: 'bottom', position: 'right',
                            style: { background: 'linear-gradient(135deg, #16a34a, #4ade80)' } }).showToast();
                        setTimeout(() => { window.location.href = "{{ route('products.index') }}"; }, 1000);
                    } else { Swal.fire('Error', res.message, 'error'); }
                },
                error: function(xhr) {
                    isSaving = false;
                    setButtonState(false, id ? 'Update Product' : 'Save Product');
                    let errorMsg = 'Server error';
                    if (xhr.responseJSON?.errors) errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    else if (xhr.responseJSON?.message) errorMsg = xhr.responseJSON.message;
                    Swal.fire({ icon: 'error', title: 'Error', html: errorMsg });
                }
            });
        });
    </script>
    @endpush
</x-app-layout>