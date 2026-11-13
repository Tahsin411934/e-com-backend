<?php

namespace Modules\Frontend\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Product;

class ProductSearchService
{
    protected function productQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Product::query();
    }

    /**
     * Max candidates to pass to Levenshtein scoring.
     */
    protected int $maxCandidates = 100;

    /**
     * Max final results.
     */
    protected int $maxResults = 20;

    /**
     * Levenshtein distance threshold per-word for fuzzy inclusion.
     */
    protected int $fuzzyThreshold = 3;

    /**
     * Minimum query length for FULLTEXT search.
     * MySQL's innodb_ft_min_token_size is typically 3.
     */
    protected int $minFulltextLength = 3;

    /**
     * Search for products using a 2-layer fuzzy matching approach:
     *
     * Layer 1 - MySQL FULLTEXT (BOOLEAN MODE):
     *   Fast index-based search. Each word is expanded with a wildcard.
     *   Returns a candidate pool of up to $maxCandidates products.
     *
     * Layer 2 - Word-level Levenshtein (PHP):
     *   Compares the query against EACH WORD in the product name, not the full string.
     *   This way "ipad" matches "iPad" in "iPad Pro Mmmmm" with distance 0.
     *   Also gives a bonus for prefix/substring matches.
     *
     * Fallback - Interleaved LIKE:
     *   If FULLTEXT returns nothing, insert '%' between each query character.
     *
     * @param  string  $query  User's search term.
     * @param  int  $limit  Max results (default 20).
     * @param  int|null  $categoryId  Optional category ID to filter by.
     * @return array ['products' => Collection, 'suggestion' => string|null]
     */
    public function search(string $query, int $limit = 20, ?int $categoryId = null): array
    {
        $query = trim($query);
        $limit = min($limit, $this->maxResults);
        $results = new Collection;
        $suggestion = null;

        if (strlen($query) < 1) {
            return ['products' => $results, 'suggestion' => null];
        }

        // ── Layer 1: FULLTEXT search (fast path) ──
        $candidates = $this->fulltextSearch($query, $categoryId);

        // ── Fallback: interleaved LIKE if FULLTEXT returned nothing ──
        if ($candidates->isEmpty() && strlen($query) >= 2) {
            $candidates = $this->fallbackLikeSearch($query, $categoryId);
        }

        // ── Layer 2: Word-level Levenshtein scoring & ranking ──
        if ($candidates->isNotEmpty()) {
            $scored = $this->scoreByWordLevelLevenshtein($candidates, $query);
            $filtered = array_filter($scored, fn ($item) => $item['distance'] <= $this->fuzzyThreshold);

            // Sort by score descending
            usort($filtered, fn ($a, $b) => $b['score'] <=> $a['score']);

            // Take top results
            $topIds = array_slice(array_column($filtered, 'id'), 0, $limit);

            if (! empty($topIds)) {
                // Preserve the scored order
                $idOrder = array_flip($topIds);
                $results = $this->productQuery()->with(['images', 'unit', 'variants' => function ($q) {
                    $q->where('status', 'active')->with(['inventoryStocks', 'options.inventoryStocks']);
                }])
                    ->whereIn('id', $topIds)
                    ->where('status', 'active')
                    ->where('visibility', 'public')
                    ->orderBy('order_column')
                    ->get()
                    ->sortBy(fn ($p) => $idOrder[$p->id] ?? PHP_INT_MAX);

                // Assign relevance scores
                $count = count($results);
                $i = 0;
                foreach ($results as $product) {
                    $product->relevance_score = $count > 0 ? round(1 - ($i / $count), 4) : 1;
                    $i++;
                }
            }

            // If the best match has distance > 0, provide a suggestion
            if (! empty($filtered[0]) && $filtered[0]['distance'] > 0 && $filtered[0]['distance'] <= 2) {
                $suggestion = $filtered[0]['closest_name'];
            }
        }

        return [
            'products' => $results,
            'suggestion' => $suggestion,
        ];
    }

    /**
     * FULLTEXT search in BOOLEAN MODE.
     */
    protected function fulltextSearch(string $query, ?int $categoryId = null): Collection
    {
        $words = array_filter(explode(' ', $query), fn ($w) => strlen($w) >= $this->minFulltextLength);

        if (empty($words)) {
            return new Collection;
        }

        // Build BOOLEAN MODE query: "+word* +word2*"
        $booleanQuery = '+'.implode('* +', $words).'*';
        $keywordCastType = DB::connection()->getDriverName() === 'pgsql' ? 'TEXT' : 'CHAR';
        $termPatterns = array_map(function (string $term): string {
            $characters = preg_split('//u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: str_split($term);

            return '%'.implode('%', $characters).'%';
        }, preg_split('/\s+/u', trim($query), -1, PREG_SPLIT_NO_EMPTY) ?: []);

        $queryBuilder = $this->productQuery()->where('status', 'active')
            ->where('visibility', 'public')
            ->where(function ($search) use ($booleanQuery, $keywordCastType, $termPatterns) {
                $search->whereRaw(
                    'MATCH(name, short_description, description) AGAINST(? IN BOOLEAN MODE)',
                    [$booleanQuery]
                )
                    ->orWhereRaw("CAST(keywords AS {$keywordCastType}) LIKE ?", ['%'.implode('%', $termPatterns).'%'])
                    ->orWhereHas('variants', function ($variant) use ($termPatterns, $keywordCastType) {
                        $variant->where('status', 'active')
                            ->where(function ($terms) use ($termPatterns, $keywordCastType) {
                                foreach ($termPatterns as $termPattern) {
                                    $terms->orWhere('name', 'LIKE', $termPattern)
                                        ->orWhere('sku', 'LIKE', $termPattern)
                                        ->orWhereRaw("CAST(attributes AS {$keywordCastType}) LIKE ?", [$termPattern])
                                        ->orWhereHas('options', fn ($option) => $option->where('status', 'active')->where('color_name', 'LIKE', $termPattern));
                                }
                            });
                    });
            });

        if ($categoryId) {
            $queryBuilder->whereHas('categories', fn ($q) => $q->where('categories.id', $categoryId));
        }

        return $queryBuilder
            ->with(['variants.options'])
            ->limit($this->maxCandidates)
            ->get(['id', 'name', 'short_description', 'description', 'keywords']);
    }

    /**
     * Fallback: interleaved LIKE search.
     */
    protected function fallbackLikeSearch(string $query, ?int $categoryId = null): Collection
    {
        // Keep each UTF-8 character intact. str_split() breaks Bangla and other
        // multibyte scripts into invalid byte fragments, so LIKE never matches.
        $characters = preg_split('//u', $query, -1, PREG_SPLIT_NO_EMPTY) ?: str_split($query);
        $pattern = '%'.implode('%', $characters).'%';
        $firstWord = preg_split('/\s+/u', trim($query))[0] ?? $query;
        $prefixCharacters = array_slice(
            preg_split('//u', $firstWord, -1, PREG_SPLIT_NO_EMPTY) ?: str_split($firstWord),
            0,
            2
        );
        $prefixPattern = implode('', $prefixCharacters).'%';
        $keywordCastType = DB::connection()->getDriverName() === 'pgsql' ? 'TEXT' : 'CHAR';
        $variantPatterns = array_map(function (string $term): string {
            $termCharacters = preg_split('//u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: str_split($term);

            return '%'.implode('%', $termCharacters).'%';
        }, preg_split('/\s+/u', trim($query), -1, PREG_SPLIT_NO_EMPTY) ?: []);

        $queryBuilder = $this->productQuery()->where('status', 'active')
            ->where('visibility', 'public')
            ->where(function ($builder) use ($pattern, $prefixPattern, $keywordCastType) {
                $builder->where('name', 'LIKE', $pattern)
                    ->orWhere('name', 'LIKE', $prefixPattern)
                    ->orWhere('short_description', 'LIKE', $pattern)
                    ->orWhere('description', 'LIKE', $pattern)
                    ->orWhereRaw("CAST(keywords AS {$keywordCastType}) LIKE ?", [$pattern])
                    ->orWhereHas('variants', function ($variant) use ($pattern, $keywordCastType) {
                        $variant->where('status', 'active')
                            ->where(function ($terms) use ($pattern, $keywordCastType) {
                                $terms->where('name', 'LIKE', $pattern)
                                    ->orWhere('sku', 'LIKE', $pattern)
                                    ->orWhereRaw("CAST(attributes AS {$keywordCastType}) LIKE ?", [$pattern])
                                    ->orWhereHas('options', fn ($option) => $option->where('status', 'active')->where('color_name', 'LIKE', $pattern));
                            });
                    })
                    ->orWhereHas('variants', function ($variant) use ($variantPatterns, $keywordCastType) {
                        $variant->where('status', 'active')
                            ->where(function ($terms) use ($variantPatterns, $keywordCastType) {
                                foreach ($variantPatterns as $termPattern) {
                                    $terms->orWhere('name', 'LIKE', $termPattern)
                                        ->orWhere('sku', 'LIKE', $termPattern)
                                        ->orWhereRaw("CAST(attributes AS {$keywordCastType}) LIKE ?", [$termPattern])
                                        ->orWhereHas('options', fn ($option) => $option->where('status', 'active')->where('color_name', 'LIKE', $termPattern));
                                }
                            });
                    });
            });

        if ($categoryId) {
            $queryBuilder->whereHas('categories', fn ($q) => $q->where('categories.id', $categoryId));
        }

        return $queryBuilder
            ->with(['variants.options'])
            ->limit($this->maxCandidates)
            ->get(['id', 'name', 'short_description', 'description', 'keywords']);
    }

    /**
     * Score candidates by comparing the query against EACH WORD in the product name.
     *
     * Why this matters:
     *   "iPad Pro Mmmmm" as a whole vs "ipad" → Levenshtein distance = 10 (fails)
     *   But "iPad" vs "ipad" → Levenshtein distance = 0 (perfect match)
     *
     * Also gives a bonus for prefix/substring matches within any word.
     *
     *
     * @return array [['id' => int, 'name' => string, 'distance' => int, 'score' => float, 'closest_name' => string], ...]
     */
    protected function scoreByWordLevelLevenshtein(Collection $candidates, string $query): array
    {
        $queryWords = preg_split('/[\s,]+/u', mb_strtolower(trim($query)), -1, PREG_SPLIT_NO_EMPTY) ?: [mb_strtolower($query)];
        $scored = [];

        foreach ($candidates as $product) {
            $keywords = is_array($product->keywords) ? implode(' ', $product->keywords) : (string) $product->keywords;
            $searchableText = implode(' ', array_filter([
                $product->name,
                $product->short_description,
                $product->description,
                $keywords,
                $product->variants?->flatMap(fn ($variant) => array_filter([
                    $variant->name,
                    is_array($variant->attributes) ? implode(' ', array_map('strval', $variant->attributes)) : (string) $variant->attributes,
                    $variant->options->pluck('color_name')->implode(' '),
                ]))->implode(' '),
            ]));
            $searchableWords = preg_split('/[\s,.;:!?()\[\]{}]+/u', mb_strtolower($searchableText), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $wordDistances = [];

            // Score every requested term against title, descriptions, and SEO keywords.
            foreach ($queryWords as $queryWord) {
                $bestDistance = PHP_INT_MAX;
                $prefixBonus = false;

                foreach ($searchableWords as $word) {
                    $distance = levenshtein($queryWord, $word);
                    $bestDistance = min($bestDistance, $distance);
                    if ($distance <= 2 || str_starts_with($word, $queryWord) || str_starts_with($queryWord, $word)) {
                        $prefixBonus = true;
                    }
                }

                $wordDistances[] = $prefixBonus ? min($bestDistance, 1) : $bestDistance;
            }

            $finalDistance = $wordDistances === [] ? PHP_INT_MAX : max($wordDistances);
            $prefixBonus = $finalDistance <= 1;

            // Score: 1/(1+distance), with prefix bonus getting a boost
            $score = 1 / (1 + $finalDistance);
            if ($prefixBonus && $finalDistance <= 1) {
                $score = max($score, 0.85); // boost prefix matches
            }

            $scored[] = [
                'id' => $product->id,
                'name' => $product->name,
                'distance' => $finalDistance,
                'score' => round($score, 4),
                'closest_name' => $product->name,
            ];
        }

        return $scored;
    }
}
