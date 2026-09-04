<?php

namespace Tests\Feature\Saas;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Tests\TestCase;

class PosPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_pages_render_without_server_errors(): void
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin'], ['description' => 'Full access']);

        $admin = User::create([
            'public_id' => (string) Str::uuid(),
            'first_name' => 'Pos',
            'last_name' => 'Admin',
            'email' => 'pos-admin@example.com',
            'password_hash' => Hash::make('secret1234'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $admin->roles()->attach($role->id);

        foreach (['/pos-registers', '/pos-shifts', '/pos-sales'] as $url) {
            $response = $this->actingAs($admin)->get($url);

            $response->assertOk();
        }

        // The POS sell page must render a "Complete Sale" button (both in the
        // payment panel and the always-visible cart footer).
        $sell = $this->actingAs($admin)->get('/pos-sell');

        $sell->assertOk();
        $sell->assertSee('Complete Sale');
        $sell->assertSee('processSaleBtnLeft');
        $sell->assertSee('processSaleBtn');
    }
}
