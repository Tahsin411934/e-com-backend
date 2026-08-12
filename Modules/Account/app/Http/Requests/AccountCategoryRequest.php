<?php

namespace Modules\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $categoryId = $this->route('account_category') ?? $this->input('category_id');

        return [
            'category_id' => ['nullable', 'integer', 'exists:account_categories,id'],
            'parent_id' => ['nullable', 'integer', 'exists:account_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('account_categories', 'slug')->ignore($categoryId)],
            'type' => ['required', Rule::in(['income', 'expense', 'asset', 'liability', 'equity', 'cost_of_goods_sold'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
