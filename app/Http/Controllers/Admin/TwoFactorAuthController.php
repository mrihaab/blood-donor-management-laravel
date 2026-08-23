<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $google2fa = new Google2FA();

        if (!$user->google2fa_secret || strlen($user->google2fa_secret) < 16) {
            $user->google2fa_secret = $google2fa->generateSecretKey(16);
            $user->save();
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'LifeBlood'),
            $user->email,
            $user->google2fa_secret
        );

        return view('admin.two-factor', [
            'user' => $user,
            'secret' => $user->google2fa_secret,
            'qrCodeUrl' => $qrCodeUrl,
            'recoveryCodes' => json_decode($user->two_factor_recovery_codes ?? '[]', true),
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $google2fa = new Google2FA();

        if (!$user->google2fa_secret) {
            return back()->withErrors(['code' => 'No 2FA secret found.']);
        }

        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code, 8);

        if (!$valid) {
            return back()->withErrors(['code' => 'The provided 6-digit authentication code is invalid.']);
        }

        $recoveryCodes = collect(range(1, 8))->map(fn () => Str::random(10))->toArray();

        $user->google2fa_enabled = true;
        $user->two_factor_recovery_codes = json_encode($recoveryCodes);
        $user->save();

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('Admin enabled Two-Factor Authentication (2FA)');

        return back()->with('success', 'Two-Factor Authentication has been successfully enabled.');
    }

    public function disable(Request $request)
    {
        $user = $request->user();
        $user->google2fa_enabled = false;
        $user->google2fa_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('Admin disabled Two-Factor Authentication (2FA)');

        return back()->with('success', 'Two-Factor Authentication has been disabled.');
    }
}
