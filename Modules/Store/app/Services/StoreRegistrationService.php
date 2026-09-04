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

class StoreRegistrationService
{
    /**
     * Register a SaaS tenant in one transaction:
     * user + "Store Owner" role + their store.
     */
    public function registerStoreOwner(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $user = User::create([
                    'public_id' => (string) Str::uuid(),
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'password_hash' => Hash::make($data['password']),
                    'status' => 'active',
                ]);

                // Roles can never be set from public registration input
                // (prevents privilege escalation).
                if ($role = Role::where('name', User::STORE_OWNER_ROLE)->first()) {
                    $user->roles()->attach($role->id);
                }

                $store = Store::create([
                    'owner_id' => $user->id,
                    'name' => $this->formatStoreName($data['store_name']),
                    'slug' => $this->generateUniqueSlug($data['store_slug'] ?? null, $data['store_name']),
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'status' => 'active',
                    'currency_code' => strtoupper($data['currency_code'] ?? 'USD'),
                    'timezone' => $data['timezone'] ?? 'UTC',
                ]);

                $token = $user->createToken('auth_token')->plainTextToken;

                return ApiResponse::created([
                    'user' => $user->load('roles'),
                    'store' => $store->fresh(),
                    'token' => $token,
                ], 'Store owner registration successful.');
            });
        } catch (\Throwable $e) {
            report($e);

            return ApiResponse::error('Registration failed. Please try again.', 500);
        }
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
