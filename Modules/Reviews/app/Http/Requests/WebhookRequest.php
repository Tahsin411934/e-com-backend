<?php

namespace Modules\Reviews\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'url' => ['required', 'url:http,https', 'max:500'],
            'secret' => ['nullable', 'string', 'max:255'],
            'events' => ['nullable', 'array'],
            'events.*' => ['string', 'max:100'],
            'status' => ['required', 'in:active,inactive,failed'],
            'retry_count' => ['nullable', 'integer', 'min:0'],
            'timeout_seconds' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('events'))) {
            $events = collect(explode(',', $this->input('events')))
                ->map(fn (string $event) => trim($event))
                ->filter()
                ->values()
                ->all();

            $this->merge(['events' => $events]);
        }
    }
}
