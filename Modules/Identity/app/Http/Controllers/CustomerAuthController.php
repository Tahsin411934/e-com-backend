<?php

namespace Modules\Identity\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Identity\Models\Customer;
use Modules\Store\Support\CurrentStore;

class CustomerAuthController extends Controller
{
    public function register(Request $request)
    {
        $store = CurrentStore::store();
        abort_unless($store, 404, 'Store not found.');

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $exists = Customer::where('store_id', $store->id)->where('email', $data['email'])->exists();
        if ($exists) {
            return ApiResponse::error('This email is already registered for this store.', 422, ['email' => ['This email is already registered for this store.']]);
        }

        $customer = Customer::create([
            'store_id' => $store->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password_hash' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        $token = $customer->createToken('storefront-customer')->plainTextToken;

        return ApiResponse::created([
            'customer' => $customer,
            'store' => $store,
            'token' => $token,
            'is_first_login' => true,
        ], 'Customer registered successfully.');
    }

    public function login(Request $request)
    {
        $store = CurrentStore::store();
        abort_unless($store, 404, 'Store not found.');

        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $customer = Customer::where('store_id', $store->id)->where('email', $data['email'])->first();
        if (! $customer || ! Hash::check($data['password'], $customer->password_hash)) {
            return ApiResponse::error('The provided credentials are incorrect.', 401);
        }
        if ($customer->status !== 'active') {
            return ApiResponse::error('This customer account is not active.', 403);
        }

        $isFirstLogin = is_null($customer->first_login_at);
        $now = now();
        $customer->update(['first_login_at' => $customer->first_login_at ?: $now, 'last_login_at' => $now]);
        $customer->tokens()->delete();
        $token = $customer->createToken('storefront-customer')->plainTextToken;

        return ApiResponse::success([
            'customer' => $customer,
            'store' => $store,
            'token' => $token,
            'is_first_login' => $isFirstLogin,
        ], 'Customer login successful.');
    }

    public function me(Request $request)
    {
        return ApiResponse::success(['customer' => $request->user(), 'store' => CurrentStore::store()]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Customer logged out successfully.');
    }
}
