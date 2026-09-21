<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Frontend\Models\Setting;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            Setting::firstOrCreate(['key' => 'site_favicon', 'store_id' => null], [
                'group' => 'general', 'label' => 'Site Favicon / Icon', 'type' => 'image', 'value' => null, 'sort_order' => 3,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings')) Setting::where('key', 'site_favicon')->delete();
    }
};
