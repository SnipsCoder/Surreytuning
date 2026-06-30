<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function setup(Request $request): View
    {
        return view('auth.two-factor.setup', [
            'user' => $request->user(),
        ]);
    }

    public function initTotp(Request $request): RedirectResponse
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        $secret = $google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_method' => 'totp',
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => null,
        ])->save();

        $qrUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret,
        );

        return redirect()->route('two-factor.setup')->with([
            'totp_secret' => $secret,
            'totp_qr_url' => $qrUrl,
        ]);
    }

    public function initEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'two_factor_method' => 'email',
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->sendEmailOtp($user);

        return redirect()->route('two-factor.setup')->with('email_otp_sent', true);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();
        $code = preg_replace('/\s+/', '', $request->input('code'));

        if (! $this->verifyCode($user, $code)) {
            return back()->withErrors(['code' => 'The code you entered is incorrect or has expired.']);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();
        session(['two_factor_verified' => true]);

        return redirect()->intended($this->homeRoute($user))
            ->with('success', 'Two-factor authentication has been enabled.');
    }

    public function challenge(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.setup');
        }

        if ($user->two_factor_method === 'email') {
            $this->sendEmailOtp($user);
        }

        return view('auth.two-factor.challenge', ['user' => $user]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();
        $code = preg_replace('/\s+/', '', $request->input('code'));

        if (! $this->verifyCode($user, $code)) {
            return back()->withErrors(['code' => 'The code you entered is incorrect or has expired.']);
        }

        session(['two_factor_verified' => true]);

        return redirect()->intended($this->homeRoute($user));
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->two_factor_method !== 'email') {
            return back();
        }

        $this->sendEmailOtp($user);

        return back()->with('success', 'A new code has been sent to your email address.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $request->user()->forceFill([
            'two_factor_method' => null,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'email_otp_code' => null,
            'email_otp_expires_at' => null,
        ])->save();

        session()->forget('two_factor_verified');

        return back()->with('success', 'Two-factor authentication has been disabled.');
    }

    private function verifyCode($user, string $code): bool
    {
        if ($user->two_factor_method === 'totp') {
            $google2fa = new Google2FA();
            $secret = decrypt($user->two_factor_secret);

            return (bool) $google2fa->verifyKey($secret, $code);
        }

        if ($user->two_factor_method === 'email') {
            return $user->email_otp_code === $code
                && $user->email_otp_expires_at
                && $user->email_otp_expires_at->isFuture();
        }

        return false;
    }

    private function sendEmailOtp($user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'email_otp_code' => $code,
            'email_otp_expires_at' => now()->addMinutes(10),
        ])->save();

        Mail::to($user->email)->send(new TwoFactorCodeMail($code));
    }

    private function homeRoute($user): string
    {
        return in_array($user->role, [\App\Enums\UserRole::Owner, \App\Enums\UserRole::Technician])
            ? route('owner.dashboard')
            : route('client.dashboard');
    }
}
