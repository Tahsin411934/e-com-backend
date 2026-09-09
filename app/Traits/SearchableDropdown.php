<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait SearchableDropdown
{
    /**
     * Build Select2 AJAX dropdown options with an index-friendly prefix search.
     *
     * Performance contract (O(log N)):
     *  - Searches with `WHERE name LIKE 'term%'` — a *suffixed* wildcard only,
     *    so a B-tree index on the label column is used instead of a full scan.
     *  - Caps the row count (default 10, hard cap 50) so one keystroke can
     *    never pull thousands of rows.
     *  - Fetches limit+1 rows to compute `pagination.more` for Select2's
     *    infinite scroll without an extra COUNT(*) query.
     *
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $extraFilters  e.g. ['status' => 'active']
     * @return array{results: array<int, array{id: int|string, text: string}>, pagination: array{more: bool}}
     */
    protected function dropdownOptions(
        Request $request,
        string $modelClass,
        string $labelColumn = 'name',
        array $extraFilters = [],
        int $defaultLimit = 10,
        int $maxLimit = 50,
        ?callable $textResolver = null,
    ): array {
        $model = new $modelClass;

        $q = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $limit = min(max(1, (int) $request->query('limit', $defaultLimit)), $maxLimit);

        $query = $model->newQuery()->orderBy($labelColumn);

        foreach ($extraFilters as $column => $value) {
            $query->where($column, $value);
        }

        if ($q !== '') {
            // Escape LIKE wildcards so "%"/"_" typed by the user cannot turn
            // the prefix search into a scan or leak unrelated rows.
            $term = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);

            $query->where($model->qualifyColumn($labelColumn), 'LIKE', "{$term}%");
        }

        // Composite display text may need extra columns — select all only
        // when a resolver is configured, otherwise fetch the lean pair.
        $selectColumns = $textResolver
            ? ['*']
            : [$model->qualifyColumn('id'), $model->qualifyColumn($labelColumn)];

        $rows = $query
            ->forPage($page, $limit + 1)
            ->get($selectColumns);

        $more = $rows->count() > $limit;

        return [
            'results' => $rows->take($limit)->map(fn (Model $row) => [
                'id' => $row->getKey(),
                'text' => $textResolver ? (string) $textResolver($row) : (string) $row->{$labelColumn},
            ])->all(),
            'pagination' => ['more' => $more],
        ];
    }
}
