<?php

namespace Modules\Frontend\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Catalog\Models\Product;

class ProductSearchService
{
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
     * @param string $query       User's search term.
     * @param int    $limit       Max results (default 20).
     * @param int|null $categoryId Optional category ID to filter by.
     *
     * @return array  ['products' => Collection, 'suggestion' => string|null]
     */
    public function search(string $query, int $limit = 20, ?int $categoryId = null): array
    {
        $query   = trim($query);
        $limit   = min($limit, $this->maxResults);
        $results = new Collection();
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
            $filtered = array_filter($scored, fn($item) => $item['distance'] <= $this->fuzzyThreshold);

            // Sort by score descending
            usort($filtered, fn($a, $b) => $b['score'] <=> $a['score']);

            // Take top results
            $topIds = array_slice(array_column($filtered, 'id'), 0, $limit);

            if (!empty($topIds)) {
                // Preserve the scored order
                $idOrder = array_flip($topIds);
                $results = Product::with(['images', 'variants' => function ($q) {
                        $q->where('status', 'active');
                    }])
                    ->whereIn('id', $topIds)
                    ->where('status', 'active')
                    ->where('visibility', 'public')
                    ->orderBy('order_column')
                    ->get()
                    ->sortBy(fn($p) => $idOrder[$p->id] ?? PHP_INT_MAX);

                // Assign relevance scores
                $count = count($results);
                $i = 0;
                foreach ($results as $product) {
                    $product->relevance_score = $count > 0 ? round(1 - ($i / $count), 4) : 1;
                    $i++;
                }
            }

            // If the best match has distance > 0, provide a suggestion
            if (!empty($filtered[0]) && $filtered[0]['distance'] > 0 && $filtered[0]['distance'] <= 2) {
                $suggestion = $filtered[0]['closest_name'];
            }
        }

        return [
            'products'   => $results,
            'suggestion' => $suggestion,
        ];
    }

    /**
     * FULLTEXT search in BOOLEAN MODE.
     *
     * @param string     $query
     * @param int|null   $categoryId
     * @return Collection
     */
    protected function fulltextSearch(string $query, ?int $categoryId = null): Collection
    {
        $words = array_filter(explode(' ', $query), fn($w) => strlen($w) >= $this->minFulltextLength);

        if (empty($words)) {
            return new Collection();
        }

        // Build BOOLEAN MODE query: "+word* +word2*"
        $booleanQuery = '+' . implode('* +', $words) . '*';

        $queryBuilder = Product::where('status', 'active')
            ->where('visibility', 'public')
            ->whereRaw(
                'MATCH(name, short_description, description) AGAINST(? IN BOOLEAN MODE)',
                [$booleanQuery]
            );

        if ($categoryId) {
            $queryBuilder->whereHas('categories', fn($q) => $q->where('categories.id', $categoryId));
        }

        return $queryBuilder
            ->limit($this->maxCandidates)
            ->get(['id', 'name']);
    }

    /**
     * Fallback: interleaved LIKE search.
     *
     * @param string     $query
     * @param int|null   $categoryId
     * @return Collection
     */
    protected function fallbackLikeSearch(string $query, ?int $categoryId = null): Collection
    {
        $pattern = '%' . implode('%', str_split($query)) . '%';

        $queryBuilder = Product::where('status', 'active')
            ->where('visibility', 'public')
            ->where('name', 'LIKE', $pattern);

        if ($categoryId) {
            $queryBuilder->whereHas('categories', fn($q) => $q->where('categories.id', $categoryId));
        }

        return $queryBuilder
            ->limit($this->maxCandidates)
            ->get(['id', 'name']);
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
     * @param Collection $candidates
     * @param string     $query
     *
     * @return array  [['id' => int, 'name' => string, 'distance' => int, 'score' => float, 'closest_name' => string], ...]
     */
    protected function scoreByWordLevelLevenshtein(Collection $candidates, string $query): array
    {
        $lowerQuery = mb_strtolower($query);
        $scored = [];

        foreach ($candidates as $product) {
            $lowerName  = mb_strtolower($product->name);
            $nameWords  = explode(' ', $lowerName);

            // Find the best (minimum) distance against ANY word in the product name
            $bestDistance = PHP_INT_MAX;
            $prefixBonus = false;

            foreach ($nameWords as $word) {
                $dist = levenshtein($lowerQuery, $word);

                // Prefix/substring match: query is a prefix of this word
                // e.g. "ipa" → "ipad" or "ipad" → "ipad"
                if ($dist <= 2 || str_starts_with($word, $lowerQuery) || str_starts_with($lowerQuery, $word)) {
                    $prefixBonus = true;
                    $bestDistance = min($bestDistance, $dist);
                } else {
                    $bestDistance = min($bestDistance, $dist);
                }
            }

            // If prefix bonus applies but distance is still high, clamp it down
            $finalDistance = $prefixBonus ? min($bestDistance, 1) : $bestDistance;

            // Score: 1/(1+distance), with prefix bonus getting a boost
            $score = 1 / (1 + $finalDistance);
            if ($prefixBonus && $finalDistance <= 1) {
                $score = max($score, 0.85); // boost prefix matches
            }

            $scored[] = [
                'id'           => $product->id,
                'name'         => $product->name,
                'distance'     => $finalDistance,
                'score'        => round($score, 4),
                'closest_name' => $product->name,
            ];
        }

        return $scored;
    }
}