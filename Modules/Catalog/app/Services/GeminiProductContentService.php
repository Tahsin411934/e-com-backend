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
Create polished ecommerce catalog content for this product. Return only valid JSON with keys: short_description, description, seo_title, seo_description, keywords, product_type.

Product name: {$name}
Additional context: {$context}

Rules:
- Write naturally in English unless the additional context requests another language.
- Start the full description with 1-2 clear sentences explaining what the product is and its general purpose.
- Write exactly 3 or 4 informative paragraphs in the full description, using readable HTML <p> tags. Explain the product's purpose, likely use cases, general benefits, and relevant buying considerations in useful detail.
- Keep claims appropriate to what can reasonably be inferred from the product name and additional context. Do not invent exact specifications, colors, sizes, materials, dimensions, certifications, warranty, or included accessories.
- Keep short_description under 300 characters and seo_description under 160 characters.
- Make the short description clear, natural, and SEO-friendly, with the main product phrase and likely relevant search terms where they fit naturally.
- The keywords field must contain 6-12 relevant search terms/phrases based on the product name/context, such as product type, brand/model if included in the name, common synonyms, and likely buyer search terms. Do not keyword-stuff.
- product_type must be one of: physical, digital, service, bundle. Use physical when uncertain.
- Return keywords as a JSON array of strings. These are editable drafts for a human reviewer.
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
                            'keywords' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                            'product_type' => ['type' => 'STRING', 'enum' => ['physical', 'digital', 'service', 'bundle']],
                        ],
                        'required' => ['short_description', 'description', 'seo_title', 'seo_description', 'keywords', 'product_type'],
                    ],
                ],
            ]);

        if (! $response->successful()) {
            $error = data_get($response->json(), 'error.message');
            $status = $response->status();

            // Keep provider diagnostics out of logs because the request URL contains the API key.
            logger()->warning('Gemini product content request failed.', [
                'status' => $status,
                'provider_message' => is_string($error) ? Str::limit($error, 500) : null,
            ]);

            $message = match ($status) {
                400 => 'Gemini rejected the request. Check the model name and request format.',
                401, 403 => 'Gemini rejected the API key. Verify GEMINI_API_KEY on the server.',
                404 => 'The configured Gemini model was not found. Check GEMINI_MODEL.',
                429 => 'Gemini quota or rate limit reached. Check the API key project quota and try again later.',
                default => 'Gemini request failed (HTTP '.$status.'). Check the Laravel log for provider details.',
            };

            throw new RuntimeException($message);
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
            'keywords' => collect($content['keywords'] ?? [])->filter(fn ($keyword) => is_string($keyword))->map(fn ($keyword) => Str::limit(strip_tags($keyword), 80, ''))->take(12)->values()->all(),
            'slug' => Str::slug($name),
            'product_type' => in_array($type, ['physical', 'digital', 'service', 'bundle'], true) ? $type : 'physical',
        ];
    }
}
