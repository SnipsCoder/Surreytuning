<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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

        if ($guard = $this->guardReenrolment($request)) {
            return $guard;
        }

        $google2fa = new Google2FA;

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

        if ($guard = $this->guardReenrolment($request)) {
            return $guard;
        }

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

        if ($guard = $this->guardReenrolment($request)) {
            return $guard;
        }

        $code = preg_replace('/\s+/', '', $request->input('code'));

        if (! $this->verifyCode($user, $code)) {
            return back()->withErrors(['code' => 'The code you entered is incorrect or has expired.']);
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ])->save();
        session(['two_factor_verified' => true]);

        // Do NOT use redirect()->intended(): a guest bounced from an owner-only URL
        // (e.g. /dashboard) has that path stashed as url.intended, which would send a
        // dealer into the owner portal and trip IsOwnerUser (403). Always use the
        // role-based homeRoute, and drop the stale intended URL. Mirrors the same
        // decision in AuthenticatedSessionController@store.
        $request->session()->forget('url.intended');

        // Surface the recovery codes exactly once, on the redirect after setup, so
        // the user can store them. They are never shown again in plaintext.
        return redirect($this->homeRoute($user))
            ->with('success', 'Two-factor authentication has been enabled.')
            ->with('recovery_codes', $recoveryCodes);
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

        // Accept either a live OTP/TOTP code or a one-time recovery code (used when
        // the authenticator/email is unavailable). Recovery codes are consumed.
        if (! $this->verifyCode($user, $code) && ! $this->consumeRecoveryCode($user, $request->input('code'))) {
            return back()->withErrors(['code' => 'The code you entered is incorrect or has expired.']);
        }

        session(['two_factor_verified' => true]);

        // See confirm(): a stale url.intended stashed by an owner-only route would
        // land a dealer on the owner dashboard (403). Always use the role-based home.
        $request->session()->forget('url.intended');

        return redirect($this->homeRoute($user));
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
            'two_factor_recovery_codes' => null,
        ])->save();

        session()->forget('two_factor_verified');

        return back()->with('success', 'Two-factor authentication has been disabled.');
    }

    /**
     * Prevent a session that has authenticated with a password but NOT yet
     * passed the 2FA challenge from re-enrolling a fresh factor (which would
     * overwrite the confirmed secret and let an attacker with only the password
     * bypass 2FA entirely). Users setting up 2FA for the first time have no
     * confirmed factor and pass through; users who have already verified this
     * session (e.g. changing their method from settings) also pass through.
     */
    private function guardReenrolment(Request $request): ?RedirectResponse
    {
        $user = $request->user();

        if ($user->two_factor_confirmed_at && ! $request->session()->get('two_factor_verified')) {
            return redirect()->route('two-factor.challenge');
        }

        return null;
    }

    private function verifyCode($user, string $code): bool
    {
        if ($user->two_factor_method === 'totp') {
            $google2fa = new Google2FA;
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

    /**
     * @return array<int, string>
     */
    private function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => Str::random(10).'-'.Str::random(10))
            ->all();
    }

    private function consumeRecoveryCode($user, ?string $input): bool
    {
        $input = trim((string) $input);

        if ($input === '') {
            return false;
        }

        $codes = $user->two_factor_recovery_codes ?? [];

        if (! in_array($input, $codes, true)) {
            return false;
        }

        // Single-use: remove the consumed code and persist the remainder.
        $remaining = array_values(array_filter($codes, fn ($code) => ! hash_equals($code, $input)));

        $user->forceFill(['two_factor_recovery_codes' => $remaining])->save();

        return true;
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
        return in_array($user->role, [UserRole::Owner, UserRole::Tuner])
            ? route('owner.dashboard')
            : route('client.dashboard');
    }
}
