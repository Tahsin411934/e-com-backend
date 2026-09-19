<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('settings'))->pluck('name')->all();

        Schema::table('settings', function (Blueprint $table) use ($indexes) {
            // Some production databases ran the earlier store_id migration
            // before its global key index was removed. Correct that schema
            // while preserving each store's settings.
            if (in_array('settings_key_unique', $indexes, true)) {
                $table->dropUnique('settings_key_unique');
            }
            if (! in_array('settings_key_store_unique', $indexes, true)) {
                $table->unique(['key', 'store_id'], 'settings_key_store_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_key_store_unique');
            $table->unique('key', 'settings_key_unique');
        });
    }
};
