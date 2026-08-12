<?php

namespace Modules\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'transfer_id' => ['nullable', 'integer', 'exists:account_transfers,id'],
            'from_account_id' => ['required', 'integer', 'exists:account_accounts,id', 'different:to_account_id'],
            'to_account_id' => ['required', 'integer', 'exists:account_accounts,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'transfer_fee' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'transferred_at' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
