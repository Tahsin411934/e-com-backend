<?php

namespace Modules\Identity\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Identity\Models\Customer;
use Modules\Store\Support\CurrentStore;
use Modules\Store\Support\StoreDomainResolver;

/** Central accounts have no store. Tenant authentication remains independent. */
class CentralCustomerAuthController extends CustomerAuthController
{
    private function normalizeEmail(Request $request): void
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($request->input('email')))]);
        }
    }

    public function register(Request $request)
    {
        $this->normalizeEmail($request);
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers')->whereNull('store_id')],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            return DB::transaction(function () use ($data) {
                $customer = Customer::create([
                    'store_id' => null,
                    'first_name' => $data['first_name'], 'last_name' => $data['last_name'],
                    'email' => $data['email'], 'phone' => $data['phone'] ?? null,
                    'password_hash' => Hash::make($data['password']), 'status' => 'active',
                    'first_login_at' => now(), 'last_login_at' => now(),
                ]);

                return ApiResponse::created([
                    'customer' => $customer,
                    'token' => $customer->createToken('central-customer')->plainTextToken,
                    'is_first_login' => true,
                ], 'Customer registered successfully.');
            });
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['email' => 'This email is already registered.']);
        }
    }

    public function login(Request $request)
    {
        $this->normalizeEmail($request);
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $customer = Customer::whereNull('store_id')->where('email', $data['email'])->first();
        if (! $customer || ! Hash::check($data['password'], $customer->password_hash)) {
            return ApiResponse::error('The provided credentials are incorrect.', 401);
        }
        if ($customer->status !== 'active') {
            return ApiResponse::error('This customer account is not active.', 403);
        }
        $firstLogin = $customer->first_login_at === null;
        $customer->update(['first_login_at' => $customer->first_login_at ?? now(), 'last_login_at' => now()]);
        $customer->tokens()->delete();

        return ApiResponse::success([
            'customer' => $customer,
            'token' => $customer->createToken('central-customer')->plainTextToken,
            'is_first_login' => $firstLogin,
        ], 'Customer login successful.');
    }

    public function me(Request $request)
    {
        return ApiResponse::success(['customer' => $request->user()]);
    }

    public function refresh(Request $request)
    {
        $token = $request->user()->createToken('central-customer')->plainTextToken;
        $request->user()->currentAccessToken()?->delete();

        return ApiResponse::success(['token' => $token]);
    }

    public function forgotPassword(Request $request)
    {
        $this->normalizeEmail($request);
        $data = $request->validate(['email' => ['required', 'email']]);
        $customer = Customer::whereNull('store_id')->where('email', $data['email'])->where('status', 'active')->first();
        if ($customer) {
            $token = Str::random(64);
            DB::table('customer_password_reset_tokens')->updateOrInsert(['customer_id' => $customer->id], ['token' => Hash::make($token), 'created_at' => now()]);
            $url = StoreDomainResolver::urlForDomain(config('storefront.central_domain')).'/reset-password?'.http_build_query(['token' => $token, 'email' => $customer->email]);
            $customer->notify(new class($url) extends Notification {
                public function __construct(private string $url) {}
                public function via(object $notifiable): array { return ['mail']; }
                public function toMail(object $notifiable): MailMessage
                {
                    return (new MailMessage)->subject('Reset your account password')->line('You requested a password reset.')->action('Reset password', $this->url)->line('This link expires in 60 minutes.');
                }
            });
        }

        return ApiResponse::success(null, 'If this email is registered, a reset link has been sent.');
    }

    public function resetPassword(Request $request)
    {
        $this->normalizeEmail($request);
        CurrentStore::set(null);

        return parent::resetPassword($request);
    }
}
