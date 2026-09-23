<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::guard('web')->user();

        // Block non-admin accounts (e.g. frontend-registered customers)
        // from entering the admin panel, even with valid credentials.
        if (! $user->hasAdminAccess()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'This account does not have access to the admin panel.',
            ]);
        }

        $request->session()->regenerate();

        $isFirstLogin = is_null($user->first_login_at);
        $now = now();
        $user->update([
            'first_login_at' => $user->first_login_at ?: $now,
            'last_login_at' => $now,
        ]);

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('first_login', $isFirstLogin);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
