<?php

namespace Modules\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $accountId = $this->route('account_account') ?? $this->input('account_id');

        return [
            'account_id' => ['nullable', 'integer', 'exists:account_accounts,id'],
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('account_accounts', 'code')->ignore($accountId)],
            'type' => ['required', Rule::in(['cash', 'bank', 'mobile_banking', 'card', 'gateway', 'other'])],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'branch_name' => ['nullable', 'string', 'max:150'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'account_holder_name' => ['nullable', 'string', 'max:150'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
