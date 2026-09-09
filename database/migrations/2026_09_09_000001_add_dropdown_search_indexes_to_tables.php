<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * B-tree indexes backing the Select2 AJAX prefix searches.
     *
     * The dropdown endpoint filters with `WHERE name LIKE 'term%'` — a
     * suffixed-only LIKE — so MySQL can seek these B-tree indexes in
     * O(log N) instead of scanning the whole table.
     *
     * `products_status_name_index` additionally serves the exact shape used
     * by the products source (`WHERE status = 'active' AND name LIKE 'x%'`)
     * with a single composite index probe.
     */
    public function up(): void
    {
        // [table => [index name => column(s)]]
        $indexes = [
            'products' => [
                'products_name_index' => 'name',
                'products_status_name_index' => ['status', 'name'],
            ],
            'categories' => ['categories_name_index' => 'name'],
            'brands' => ['brands_name_index' => 'name'],
            'units' => ['units_name_index' => 'name'],
            'sizes' => ['sizes_group_name_index' => 'group_name'],
            'tax_rates' => ['tax_rates_name_index' => 'name'],
            'navbar_items' => ['navbar_items_name_index' => 'name'],
            'subnavbar_items' => ['subnavbar_items_name_index' => 'name'],
            'account_accounts' => ['account_accounts_name_index' => 'name'],
            'stores' => ['stores_name_index' => 'name'],
        ];

        foreach ($indexes as $tableName => $tableIndexes) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $tableIndexes) {
                foreach ($tableIndexes as $indexName => $columns) {
                    if (! Schema::hasIndex($tableName, $indexName)) {
                        $table->index($columns, $indexName);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        $indexes = [
            'products' => ['products_name_index', 'products_status_name_index'],
            'categories' => ['categories_name_index'],
            'brands' => ['brands_name_index'],
            'units' => ['units_name_index'],
            'sizes' => ['sizes_group_name_index'],
            'tax_rates' => ['tax_rates_name_index'],
            'navbar_items' => ['navbar_items_name_index'],
            'subnavbar_items' => ['subnavbar_items_name_index'],
            'account_accounts' => ['account_accounts_name_index'],
            'stores' => ['stores_name_index'],
        ];

        foreach ($indexes as $tableName => $tableIndexes) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableIndexes) {
                foreach ($tableIndexes as $indexName) {
                    if (Schema::hasIndex($table->getTable(), $indexName)) {
                        $table->dropIndex($indexName);
                    }
                }
            });
        }
    }
};
