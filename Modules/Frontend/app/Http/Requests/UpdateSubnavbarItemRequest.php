<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Frontend\Models\SubnavbarItem;

class UpdateSubnavbarItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subnavbarItemId = $this->route('subnavbar_item') ?? $this->route('id');
        $subnavbarItem = SubnavbarItem::findOrFail($subnavbarItemId);

        return [
            'navbar_item_id' => 'required|exists:navbar_items,id',
            'name' => 'required|string|max:160',
            'slug' => [
                'required',
                'string',
                'max:180',
                Rule::unique('subnavbar_items', 'slug')->where('store_id', $subnavbarItem->store_id)->ignore($subnavbarItem),
            ],
            'url' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'navbar_item_id.required' => 'Parent navbar item is required.',
            'navbar_item_id.exists' => 'Selected parent navbar item does not exist.',
            'name.required' => 'Subnavbar item name is required.',
            'slug.required' => 'Slug is required.',
            'slug.unique' => 'This slug is already taken.',
            'status.in' => 'Status must be active or inactive.',
        ];
    }
}
