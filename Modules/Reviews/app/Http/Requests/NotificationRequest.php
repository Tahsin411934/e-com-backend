<?php

namespace Modules\Reviews\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'type' => ['required', 'string', 'max:100'],
            'channel' => ['required', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'data' => ['nullable', 'array'],
            'is_read' => ['nullable', 'boolean'],
            'read_at' => ['nullable', 'date'],
            'sent_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_read')) {
            $this->merge(['read_at' => $this->boolean('is_read') ? now() : null]);
        }
    }
}
