<?php

namespace Modules\Identity\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Store\Support\StoreDomainResolver;
use Modules\Identity\Models\Customer;
use Modules\Store\Support\CurrentStore;

class CustomerAuthController extends Controller
{
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return ApiResponse::success(null, 'Logged out from all devices.');
    }

    public function refresh(Request $request)
    {
        $token = $request->user()->createToken('storefront-customer')->plainTextToken;
        $request->user()->currentAccessToken()?->delete();
        return ApiResponse::success(['token' => $token]);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate(['current_password' => ['required', 'string'], 'password' => ['required', 'string', 'min:8', 'confirmed']]);
        abort_unless(Hash::check($data['current_password'], $request->user()->password_hash), 422, 'Current password is incorrect.');
        $request->user()->update(['password_hash' => Hash::make($data['password'])]);
        $request->user()->tokens()->where('id', '!=', $request->user()->currentAccessToken()?->id)->delete();
        DB::table('customer_password_reset_tokens')->where('customer_id', $request->user()->id)->delete();
        return ApiResponse::success(null, 'Password changed successfully.');
    }

    public function forgotPassword(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $customer = Customer::where('store_id', CurrentStore::id())->where('email', $data['email'])->where('status', 'active')->first();
        if ($customer) {
            $token = Str::random(64);
            DB::table('customer_password_reset_tokens')->updateOrInsert(['customer_id' => $customer->id], ['token' => Hash::make($token), 'created_at' => now()]);
            $url = StoreDomainResolver::urlForDomain(StoreDomainResolver::host()).'/reset-password?'.http_build_query(['token' => $token, 'email' => $customer->email]);
            $customer->notify(new class($url) extends Notification {
                public function __construct(private string $url) {}
                public function via(object $notifiable): array { return ['mail']; }
                public function toMail(object $notifiable): MailMessage
                {
                    return (new MailMessage)->subject('Reset your store account password')->line('You requested a password reset for this store.')->action('Reset password', $this->url)->line('This link expires in 60 minutes.');
                }
            });
        }
        return ApiResponse::success(null, 'If this email is registered for this store, a reset link has been sent.');
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'token' => ['required', 'string', 'max:255'], 'password' => ['required', 'string', 'min:8', 'confirmed']]);
        return DB::transaction(function () use ($data) {
            $customer = Customer::where('store_id', CurrentStore::id())->where('email', $data['email'])->where('status', 'active')->lockForUpdate()->first();
            $reset = $customer ? DB::table('customer_password_reset_tokens')->where('customer_id', $customer->id)->lockForUpdate()->first() : null;
            abort_unless($reset && \Illuminate\Support\Carbon::parse($reset->created_at)->addMinutes(60)->isFuture() && Hash::check($data['token'], $reset->token), 422, 'Invalid or expired reset link.');
            $customer->update(['password_hash' => Hash::make($data['password'])]);
            $customer->tokens()->delete();
            DB::table('customer_password_reset_tokens')->where('customer_id', $customer->id)->delete();
            return ApiResponse::success(null, 'Password reset successfully.');
        });
    }

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
