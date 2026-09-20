<?php

namespace Modules\Store\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;
use Modules\Store\Models\StoreDomain;
use Modules\Store\Support\StoreDomainResolver;
use Modules\Store\Services\StoreDemoDataSeeder;

class StoreRegistrationService
{
    public function __construct(private StoreDemoDataSeeder $demoDataSeeder) {}

    /**
     * Create the owner's user account with the default "Store Owner" role.
     * Used by both public registration and admin-side store creation.
     */
    public function createOwnerUser(array $data): User
    {
        $user = User::create([
            'public_id' => (string) Str::uuid(),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password_hash' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        // By default every registered store owner receives the "Store Owner"
        // role. The role is created automatically if it does not exist yet;
        // permissions are configured separately by the admin from the backend.
        $role = Role::firstOrCreate(
            ['name' => User::STORE_OWNER_ROLE],
            ['description' => 'SaaS tenant - owns a store and manages its products, catalog and dashboard']
        );
        $user->roles()->attach($role->id);

        return $user;
    }

    /**
     * Core tenant creation for public API registration:
     * user + "Store Owner" role + their store, inside one transaction.
     *
     * NOTE: Only the default role is assigned here. Permissions are
     * managed by the platform admin from the backend (Role/Permission UI).
     *
     * @return array{user: User, store: Store}
     */
    public function createStoreOwner(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = $this->createOwnerUser($data);

            $store = Store::create([
                'owner_id' => $user->id,
                'name' => $this->formatStoreName($data['store_name']),
                'slug' => $this->generateUniqueSlug($data['store_slug'] ?? null, $data['store_name']),
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => 'active',
                'currency_code' => strtoupper($data['currency_code'] ?? 'BDT'),
                'timezone' => $data['timezone'] ?? 'Asia/Dhaka',
            ]);

            // Seed demo data for the new store
            $this->demoDataSeeder->seed($store->id);

            // Provision the free wildcard subdomain ({slug}.{suffix}) right
            // away — it is always trusted and needs no DNS verification.
            $storeDomain = $this->createSubdomain($store);

            return ['user' => $user, 'store' => $store, 'store_domain' => $storeDomain];
        });
    }

    /**
     * Register a SaaS tenant through the public JSON API.
     */
    public function registerStoreOwner(array $data): JsonResponse
    {
        try {
            ['user' => $user, 'store' => $store, 'store_domain' => $storeDomain] = $this->createStoreOwner($data);

            return ApiResponse::created([
                'user' => $user->load('roles'),
                'store' => $store->fresh(),
                'store_url' => StoreDomainResolver::urlForDomain($storeDomain->domain),
            ], 'Store owner registration successful. Please login with your credentials.');
        } catch (\Throwable $e) {
            report($e);

            return ApiResponse::error('Registration failed. Please try again later.', 500);
        }
    }

    /**
     * Provision the free wildcard subdomain for a store. It resolves
     * immediately (wildcard DNS + wildcard certificate) and needs no
     * DNS ownership verification.
     */
    private function createSubdomain(Store $store): StoreDomain
    {
        return $store->domains()->create([
            'domain' => $store->slug.'.'.StoreDomainResolver::primarySuffix(),
            'type' => 'subdomain',
            'is_primary' => true,
            'ssl_status' => 'active',
            'verified_at' => now(),
        ]);
    }

    /**
     * Normalise a store/company name into a professional presentation:
     * collapse whitespace and capitalise fully-lowercase words while
     * preserving acronyms such as "BD", "GSM" or "3M".
     */
    private function formatStoreName(string $name): string
    {
        $name = Str::squish(trim($name));

        return (string) preg_replace_callback(
            '/\b[\p{Ll}]+\b/u',
            fn (array $m) => mb_convert_case($m[0], MB_CASE_TITLE, 'UTF-8'),
            $name
        );
    }

    /**
     * Build a unique store slug from the provided slug or store name.
     */
    private function generateUniqueSlug(?string $slug, string $name): string
    {
        $base = Str::slug($slug ?: $name) ?: 'store';
        $candidate = $base;
        $suffix = 1;

        while (Store::withTrashed()->where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.++$suffix;
        }

        return $candidate;
    }
}
