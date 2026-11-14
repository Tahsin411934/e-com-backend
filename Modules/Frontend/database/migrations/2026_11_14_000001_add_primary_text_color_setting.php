<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Frontend\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        if (! Setting::whereNull('store_id')->where('key', 'primary_text_color')->exists()) {
            Setting::create([
                'store_id' => null,
                'group' => 'general',
                'key' => 'primary_text_color',
                'value' => '#FFFFFF',
                'type' => 'color',
                'label' => 'Primary Text Color',
                'sort_order' => 6,
            ]);
        }
    }

    public function down(): void
    {
        Setting::whereNull('store_id')->where('key', 'primary_text_color')->delete();
    }
};
