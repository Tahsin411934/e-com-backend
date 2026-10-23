<?php

namespace Tests\Feature\Saas;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Frontend\Database\Seeders\ServiceBenefitPermissionsSeeder;
use Modules\Frontend\Models\ServiceBenefit;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;
use Modules\Store\Support\CurrentStore;
use Modules\Store\Support\StoreDomainResolver;
use Tests\TestCase;

class ServiceBenefitTest extends TestCase
{
    use RefreshDatabase;

    private function makeStore(string $slug): Store
    {
        $role = Role::firstOrCreate(['name' => 'Store Owner']);
        $owner = User::create([
            'public_id' => (string) Str::uuid(), 'first_name' => 'Owner', 'last_name' => $slug,
            'email' => $slug.'@example.com', 'password_hash' => Hash::make('password123'), 'status' => 'active',
        ]);
        $owner->forceFill(['email_verified_at' => now()])->save();
        $owner->roles()->attach($role->id);
        (new ServiceBenefitPermissionsSeeder)->run();

        return Store::create(['owner_id' => $owner->id, 'name' => $slug, 'slug' => $slug, 'status' => 'active', 'currency_code' => 'BDT', 'timezone' => 'Asia/Dhaka']);
    }

    private function item(Store $store, array $attributes = []): ServiceBenefit
    {
        return ServiceBenefit::create(array_merge(['store_id' => $store->id, 'title' => 'Custom benefit', 'description' => null, 'icon' => 'truck', 'sort_order' => 0, 'is_active' => true], $attributes));
    }

    protected function tearDown(): void
    {
        CurrentStore::reset();
        StoreDomainResolver::reset();
        parent::tearDown();
    }

    public function test_api_only_returns_active_items_of_the_resolved_store_in_order(): void
    {
        config(['storefront.domain_suffix' => 'example.test', 'storefront.fallback_store' => null]);
        $a = $this->makeStore('alpha');
        $b = $this->makeStore('beta');
        $last = $this->item($a, ['title' => 'Last', 'sort_order' => 9]);
        $first = $this->item($a, ['title' => 'First', 'sort_order' => 1]);
        $this->item($a, ['is_active' => false]);
        $this->item($b, ['title' => 'Other store']);

        $this->withHeader('X-Store-Host', 'alpha.example.test')
            ->getJson('/api/v1/storefront/service-benefits?store_id='.$b->id.'&is_active=0')
            ->assertOk()->assertJsonCount(2, 'data.items')
            ->assertJsonPath('data.items.0.id', $first->id)->assertJsonPath('data.items.1.id', $last->id);
    }

    public function test_empty_or_inactive_only_store_returns_empty_items(): void
    {
        config(['storefront.domain_suffix' => 'example.test', 'storefront.fallback_store' => null]);
        $store = $this->makeStore('empty');
        $this->item($store, ['is_active' => false]);

        $this->withHeader('X-Store-Host', 'empty.example.test')->getJson('/api/v1/storefront/service-benefits')
            ->assertOk()->assertJsonPath('data.items', []);
    }

    public function test_owner_cannot_forge_store_assignment_or_modify_another_stores_items(): void
    {
        $a = $this->makeStore('owner-a');
        $b = $this->makeStore('owner-b');
        $other = $this->item($b);
        $this->actingAs($a->owner);
        $this->post('/service-benefits', ['store_id' => $b->id, 'title' => 'My benefit', 'icon' => 'wallet', 'sort_order' => 3, 'is_active' => 1])->assertRedirect();
        $this->assertDatabaseHas('store_service_benefits', ['title' => 'My benefit', 'store_id' => $a->id]);
        $this->put('/service-benefits/'.$other->id, ['title' => 'Stolen'])->assertNotFound();
        $this->delete('/service-benefits/'.$other->id)->assertNotFound();
        $this->get('/service-benefits')->assertOk()->assertDontSee('owner-b');
    }

    public function test_owner_can_update_visibility_sort_order_and_delete_own_item(): void
    {
        $store = $this->makeStore('manage');
        $item = $this->item($store);
        $this->actingAs($store->owner)->put('/service-benefits/'.$item->id, ['title' => 'Updated', 'icon' => 'clock', 'sort_order' => 8, 'is_active' => 0])->assertRedirect();
        $this->assertDatabaseHas('store_service_benefits', ['id' => $item->id, 'title' => 'Updated', 'sort_order' => 8, 'is_active' => false]);
        $this->delete('/service-benefits/'.$item->id)->assertRedirect();
        $this->assertDatabaseMissing('store_service_benefits', ['id' => $item->id]);
    }

    public function test_icon_and_text_validation_and_guest_management_access(): void
    {
        $store = $this->makeStore('validate');
        $this->get('/service-benefits')->assertRedirect('/login');
        $this->actingAs($store->owner)->postJson('/service-benefits', ['title' => str_repeat('x', 101), 'icon' => '<script>', 'sort_order' => -1, 'is_active' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors(['title', 'icon', 'sort_order']);
    }

    public function test_platform_admin_must_choose_a_store_and_can_manage_it(): void
    {
        $store = $this->makeStore('platform');
        $role = Role::firstOrCreate(['name' => 'Admin']);
        (new ServiceBenefitPermissionsSeeder)->run();
        $user = $store->owner;
        $user->roles()->attach($role->id);
        $this->actingAs($user)->postJson('/service-benefits', ['title' => 'Test', 'icon' => 'truck', 'sort_order' => 0, 'is_active' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('store_id');
        $this->post('/service-benefits', ['store_id' => $store->id, 'title' => 'Platform created', 'icon' => 'truck', 'sort_order' => 0, 'is_active' => 1])->assertRedirect();
        $this->assertDatabaseHas('store_service_benefits', ['store_id' => $store->id, 'title' => 'Platform created']);
    }
}
