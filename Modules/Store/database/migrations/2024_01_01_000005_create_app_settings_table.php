<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('scope_type', ['global', 'store', 'user'])->default('global');
            $table->unsignedBigInteger('scope_id')->default(0);
            $table->string('setting_key', 120);
            $table->json('setting_value');
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['scope_type', 'scope_id', 'setting_key']);
            $table->index(['is_public', 'setting_key']);
        });

        // Seed default global settings
        DB::table('app_settings')->insert([
            [
                'scope_type' => 'global',
                'scope_id' => 0,
                'setting_key' => 'storefront.enabled',
                'setting_value' => json_encode(['enabled' => true]),
                'is_public' => true,
            ],
            [
                'scope_type' => 'global',
                'scope_id' => 0,
                'setting_key' => 'checkout.tax_inclusive',
                'setting_value' => json_encode(['enabled' => false]),
                'is_public' => false,
            ],
            [
                'scope_type' => 'global',
                'scope_id' => 0,
                'setting_key' => 'delivery.default_provider',
                'setting_value' => json_encode(['provider' => 'local_delivery']),
                'is_public' => false,
            ],
            [
                'scope_type' => 'global',
                'scope_id' => 0,
                'setting_key' => 'pos.require_shift_for_sale',
                'setting_value' => json_encode(['enabled' => true]),
                'is_public' => false,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};