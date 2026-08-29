<?php

namespace Modules\Identity\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Modules\Identity\Http\Requests\ForgotPasswordRequest;
use Modules\Identity\Http\Requests\LoginRequest;
use Modules\Identity\Http\Requests\RegisterRequest;
use Modules\Identity\Http\Requests\ResetPasswordRequest;
use Modules\Identity\Models\User;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'public_id' => (string) Str::uuid(),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password_hash' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        // Assign default role if provided
        if (isset($data['role_id'])) {
            $user->roles()->attach($data['role_id']);
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::created([
            'user' => $user->load('roles'),
            'token' => $token,
        ], 'Registration successful.');
    }

    /**
     * Login user and create token
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password_hash)) {
            return ApiResponse::error('The provided credentials are incorrect.', 401, ['email' => ['The provided credentials are incorrect.']]);
        }

        if ($user->status !== 'active') {
            return ApiResponse::error('Your account is inactive. Please contact support.', 403, ['email' => ['Your account is inactive. Please contact support.']]);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->update(['last_login_at' => now()]);

        return ApiResponse::success([
            'user' => $user->load('roles'),
            'token' => $token,
        ], 'Login successful.');
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logged out successfully.');
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return ApiResponse::success(null, 'Logged out from all devices.');
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return ApiResponse::success(['user' => $request->user()->load('roles')]);
    }

    /**
     * Alias for /me route
     */
    public function me(Request $request)
    {

        return $this->user($request);
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_THROTTLED) {
            return ApiResponse::error('Too many reset attempts. Please try again later.', 429);
        }

        return ApiResponse::success(null, 'Password reset link sent to your email.');
    }

    /**
     * Reset password with token
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->password_hash = Hash::make($request->password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return ApiResponse::success(null, 'Password reset successful.');
        }

        return ApiResponse::error('Failed to reset password. Please try again.', 400);
    }

    /**
     * Change password for authenticated user
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = $request->user();
        $user->password_hash = Hash::make($request->password);
        $user->save();

        // Revoke all tokens except current
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return ApiResponse::success(null, 'Password changed successfully.');
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $token = $request->user()->createToken('auth_token')->plainTextToken;

        // Revoke old token
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(['token' => $token], 'Token refreshed.');
    }
}
