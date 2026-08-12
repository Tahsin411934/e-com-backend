<?php

namespace Modules\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountInvestmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'investment_id' => ['nullable', 'integer', 'exists:account_investments,id'],
            'account_id' => ['required', 'integer', 'exists:account_accounts,id'],
            'investment_type' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'investment_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:180'],
            'expected_return' => ['nullable', 'numeric'],
            'actual_return' => ['nullable', 'numeric'],
            'status' => ['nullable', 'string'],
            'partner_name' => ['nullable', 'string', 'max:180'],
            'reference_no' => ['nullable', 'string', 'max:120'],
            'attachment_path' => ['nullable', 'string'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}