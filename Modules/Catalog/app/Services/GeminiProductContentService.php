<?php

namespace Modules\Catalog\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GeminiProductContentService
{
    public function generate(string $name, ?string $context = null): array
    {
        $apiKey = config('services.gemini.api_key');

        if (! $apiKey) {
            throw new RuntimeException('Gemini API key is not configured. Set GEMINI_API_KEY in .env.');
        }

        $prompt = <<<PROMPT
Create draft ecommerce catalog content for this product. Return only valid JSON with keys: short_description, description, seo_title, seo_description, product_type.

Product name: {$name}
Additional context: {$context}

Rules:
- Write customer-friendly content in English.
- Do not invent precise specifications, materials, dimensions, certifications, warranty, or included accessories not supported by the name/context.
- Keep short_description under 300 characters and seo_description under 160 characters.
- Description should be 2-3 concise paragraphs and avoid unsupported factual claims.
- product_type must be one of: physical, digital, service, bundle. Use physical when uncertain.
- These are editable drafts for a human reviewer.
PROMPT;

        $response = Http::timeout(30)
            ->acceptJson()
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.config('services.gemini.model').':generateContent?key='.urlencode($apiKey), [
                'contents' => [[
                    'parts' => [['text' => $prompt]],
                ]],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'responseSchema' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'short_description' => ['type' => 'STRING'],
                            'description' => ['type' => 'STRING'],
                            'seo_title' => ['type' => 'STRING'],
                            'seo_description' => ['type' => 'STRING'],
                            'product_type' => ['type' => 'STRING', 'enum' => ['physical', 'digital', 'service', 'bundle']],
                        ],
                        'required' => ['short_description', 'description', 'seo_title', 'seo_description', 'product_type'],
                    ],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Gemini could not generate product content. Please try again.');
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
        $content = is_string($text) ? json_decode($text, true) : null;

        if (! is_array($content)) {
            throw new RuntimeException('Gemini returned an invalid response. Please try again.');
        }

        $type = $content['product_type'] ?? 'physical';

        return [
            'short_description' => Str::limit(strip_tags((string) ($content['short_description'] ?? '')), 500, ''),
            'description' => strip_tags((string) ($content['description'] ?? ''), '<p><ul><ol><li><strong><em><br>'),
            'seo_title' => Str::limit(strip_tags((string) ($content['seo_title'] ?? '')), 255, ''),
            'seo_description' => Str::limit(strip_tags((string) ($content['seo_description'] ?? '')), 500, ''),
            'slug' => Str::slug($name),
            'product_type' => in_array($type, ['physical', 'digital', 'service', 'bundle'], true) ? $type : 'physical',
        ];
    }
}
