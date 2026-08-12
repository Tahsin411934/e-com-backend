<?php

namespace Modules\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'expense_id' => ['nullable', 'integer', 'exists:account_expenses,id'],
            'account_id' => ['required', 'integer', 'exists:account_accounts,id'],
            'category_id' => ['required', 'integer', 'exists:account_categories,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'expense_date' => ['required', 'date'],
            'vendor_name' => ['nullable', 'string', 'max:180'],
            'reference_no' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
