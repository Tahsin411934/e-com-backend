<?php

namespace Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Identity\Models\User;
use Modules\Identity\Http\Requests\LoginRequest;
use Modules\Identity\Http\Requests\RegisterRequest;
use Modules\Identity\Http\Requests\ForgotPasswordRequest;
use Modules\Identity\Http\Requests\ResetPasswordRequest;

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

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful.',
            'user' => $user->load('roles'),
            'token' => $token,
        ], 201);
    }

    /**
     * Login user and create token
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The provided credentials are incorrect.',
                'errors' => ['email' => ['The provided credentials are incorrect.']],
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is inactive. Please contact support.',
                'errors' => ['email' => ['Your account is inactive. Please contact support.']],
            ], 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->update(['last_login_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful.',
            'user' => $user->load('roles'),
            'token' => $token,
        ], 200);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out from all devices.',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user' => $request->user()->load('roles'),
        ]);
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
            return response()->json([
                'status' => 'error',
                'message' => 'Too many reset attempts. Please try again later.',
            ], 429);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset link sent to your email.',
        ]);
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
            return response()->json([
                'status' => 'success',
                'message' => 'Password reset successful.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to reset password. Please try again.',
        ], 400);
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

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $token = $request->user()->createToken('auth_token')->plainTextToken;

        // Revoke old token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Token refreshed.',
            'token' => $token,
        ]);
    }
}