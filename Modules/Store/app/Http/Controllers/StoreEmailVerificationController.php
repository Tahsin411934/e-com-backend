<?php

namespace Modules\Store\Http\Controllers;

use App\Mail\StoreRegisteredMail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Models\User;

class StoreEmailVerificationController extends Controller
{
    public function resendFromLogin(Request $request, \Modules\Store\Services\StoreRegistrationService $registrationService)
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);
        $user = User::where('email', $request->string('email'))->first();

        if ($user && $user->isStoreOwner() && ! $user->email_verified_at) {
            $registrationService->sendVerificationEmail($user);
        }

        return back()->with('verification-status', 'If this account is unverified, a new verification email has been sent.');
    }

    public function resend(Request $request, \Modules\Store\Services\StoreRegistrationService $registrationService)
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);
        $user = User::where('email', $request->string('email'))->first();

        // Always return the same response to prevent account enumeration.
        if ($user && $user->isStoreOwner() && ! $user->email_verified_at) {
            $registrationService->sendVerificationEmail($user);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'If an unverified account exists for this email, a verification link has been sent.',
        ], 202);
    }

    public function verify(Request $request, int $id, string $hash)
    {
        $user = User::find($id);
        abort_unless($user && hash_equals(sha1($user->email), $hash), 403, 'Invalid verification link.');

        $store = $user->ownedStore()->with('domains')->first();
        abort_unless($store, 404, 'Store not found.');
        $primaryDomain = $store->domains->firstWhere('is_primary', true) ?? $store->domains->first();

        $welcomeSent = false;
        $verifiedNow = DB::transaction(function () use ($user) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($locked->email_verified_at) return false;
            $locked->forceFill(['email_verified_at' => now()])->save();
            return true;
        });
        if ($verifiedNow) {
            if ($primaryDomain) {
                try {
                    Mail::to($user->email)->queue(new StoreRegisteredMail($user, $store, $primaryDomain));
                    $welcomeSent = true;
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }
        }

        $adminUrl = rtrim((string) config('app.admin_url', config('app.url')), '/');
        $publicUrl = rtrim((string) config('app.public_site_url', $adminUrl), '/').'/email-verified';

        return redirect()->away($publicUrl.'?'.http_build_query([
            'store_url' => $primaryDomain ? 'https://'.$primaryDomain->domain : '',
            'admin_url' => $adminUrl,
            'welcome_sent' => $welcomeSent ? '1' : '0',
        ]));
    }
}
