<?php

namespace Modules\Store\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Store\Models\StoreDomain;
use Modules\Store\Support\StoreDomainResolver;

class StoreDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'domain' => strtolower(trim((string) $this->input('domain'))),
        ]);
    }

    public function rules(): array
    {
        $suffix = strtolower(StoreDomainResolver::primarySuffix());

        return [
            'domain' => [
                'required',
                'string',
                'max:253',
                'regex:/^(?!-)[a-z0-9-]{1,63}(\.[a-z0-9-]{1,63})+$/',
                function (string $attribute, mixed $value, Closure $fail) use ($suffix) {
                    $domain = strtolower(trim((string) $value));

                    // Wildcard subdomains are provisioned automatically at
                    // registration and can never be added as custom domains.
                    if ($suffix !== '' && ($domain === $suffix || str_ends_with($domain, '.'.$suffix))) {
                        $fail("Use your free {$suffix} subdomain instead of adding it as a custom domain.");
                    }

                    if (StoreDomain::query()->where('domain', $domain)->exists()) {
                        $fail('This domain is already connected to a store.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'domain.regex' => 'Enter a valid domain, e.g. myshop.com.bd (lowercase letters, numbers and dashes only).',
        ];
    }
}
