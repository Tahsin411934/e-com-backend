<?php

namespace Modules\Frontend\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Frontend\Models\NavbarItem;

class UpdateNavbarItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $navbarItemId = $this->route('navbar_item') ?? $this->route('id');
        $navbarItem = NavbarItem::findOrFail($navbarItemId);

        return [
            'name' => 'required|string|max:160',
            'slug' => [
                'required',
                'string',
                'max:180',
                Rule::unique('navbar_items', 'slug')->where('store_id', $navbarItem->store_id)->ignore($navbarItem),
            ],
            'url' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'store_id' => 'nullable|integer|exists:stores,id',
            'is_central_navbar_item' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Navbar item name is required.',
            'slug.required' => 'Slug is required.',
            'slug.unique' => 'This slug is already taken.',
            'status.in' => 'Status must be active or inactive.',
            'store_id.exists' => 'The selected store does not exist.',
        ];
    }
}
