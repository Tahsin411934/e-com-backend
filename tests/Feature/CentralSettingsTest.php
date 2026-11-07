<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Frontend\Models\Setting;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;
use Tests\TestCase;

class CentralSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function setting(string $key, ?string $value, string $type = 'text', ?int $storeId = null): void
    {
        Setting::create([
            'store_id' => $storeId, 'group' => 'general', 'key' => $key,
            'value' => $value, 'type' => $type, 'label' => $key, 'sort_order' => 0,
        ]);
    }

    public function test_public_settings_are_available_without_a_tenant_or_login(): void
    {
        $this->setting('site_name', 'Central Marketplace');
        $this->setting('phone', null);
        $this->getJson('/api/v1/central/settings')->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.site_name', 'Central Marketplace')
            ->assertJsonPath('data.phone', null);
    }

    public function test_tenant_headers_and_input_cannot_override_central_settings(): void
    {
        $owner = User::create([
            'public_id' => (string) Str::uuid(), 'first_name' => 'Store', 'last_name' => 'Owner',
            'email' => 'owner@example.test', 'password_hash' => 'unused', 'status' => 'active',
        ]);
        $store = Store::create([
            'owner_id' => $owner->id, 'name' => 'Seller', 'slug' => 'seller',
            'email' => 'seller@example.test', 'status' => 'active', 'currency_code' => 'BDT',
        ]);
        $this->setting('site_name', 'Central Marketplace');
        $this->setting('site_name', 'Seller Store', 'text', $store->id);
        $this->setting('email', 'seller-only@example.test', 'email', $store->id);

        $this->withHeader('X-Store-Host', 'seller.example.test')
            ->getJson('/api/v1/central/settings?store_id='.$store->id)
            ->assertOk()->assertExactJson([
                'status' => 'success', 'message' => 'Central settings retrieved successfully.',
                'data' => ['site_name' => 'Central Marketplace'],
            ]);
    }

    public function test_unknown_secrets_and_deleted_settings_are_not_exposed(): void
    {
        $this->setting('site_name', 'Central Marketplace');
        $this->setting('payment_secret', 'private');
        $this->setting('smtp_password', 'private');
        $this->setting('custom_admin_field', 'private');
        $this->setting('phone', 'deleted');
        Setting::where('key', 'phone')->delete();
        $this->getJson('/api/v1/central/settings')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.site_name', 'Central Marketplace');
    }

    public function test_image_paths_are_absolute_and_empty_settings_are_an_object(): void
    {
        $response = $this->getJson('/api/v1/central/settings')->assertOk();
        $this->assertIsObject(json_decode($response->getContent())->data);

        $this->setting('site_logo', '/storage/settings/logo.png', 'image');
        $this->setting('site_favicon', 'https://cdn.example.test/icon.png', 'image');
        $this->getJson('/api/v1/central/settings')->assertOk()
            ->assertJsonPath('data.site_logo', asset('storage/settings/logo.png'))
            ->assertJsonPath('data.site_favicon', 'https://cdn.example.test/icon.png');
    }
}
