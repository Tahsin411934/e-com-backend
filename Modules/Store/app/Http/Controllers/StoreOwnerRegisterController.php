<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Store\Http\Requests\StoreOwnerRegistrationRequest;
use Modules\Store\Services\StoreRegistrationService;

class StoreOwnerRegisterController extends Controller
{
    public function __construct(private StoreRegistrationService $registrationService) {}

    /**
     * Public web registration form. The storefront links straight to this
     * page ("Create Store"); the tenant registers on this backend.
     */
    public function create()
    {
        return view('auth.store-owner-register');
    }

    /**
     * Handle the public web registration: create user + store owner role
     * + store, then sign the owner into the admin panel.
     */
    public function store(StoreOwnerRegistrationRequest $request): RedirectResponse
    {
        ['user' => $user] = $this->registrationService->createStoreOwner($request->validated());

        // Store owners are activated immediately on registration, exactly
        // like their store (marketplace default) - so they land directly on
        // the dashboard without a pending email-verification wall.
        $user->update(['email_verified_at' => now()]);

        Auth::guard('web')->login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
