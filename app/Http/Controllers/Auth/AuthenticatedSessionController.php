<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
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

        $request->session()->regenerate();

        $user = $request->user();

        $user->updateLastLogin();

        // Tuners land on File Requests (their sole workspace); dealers on their
        // own dashboard; the owner on the admin dashboard.
        $redirect = match ($user->role) {
            UserRole::DealerOwner, UserRole::DealerUser => '/my/dashboard',
            UserRole::Tuner => '/file-requests',
            default => '/dashboard',
        };

        // Do NOT use redirect()->intended() here. If the guest was bounced to /login
        // from a protected route, Laravel stashes that URL as `url.intended` in the
        // session and intended() will silently redirect there instead of $redirect,
        // ignoring the role check above. Always redirect to the role-based path.
        return redirect($redirect);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
