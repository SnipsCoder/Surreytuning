<?php

namespace App\Http\Controllers\Owner;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\InvitePortalUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortalUserController extends Controller
{
    public function index(): View
    {
        $portalUsers = User::ownerTeam()->orderByDesc('created_at')->get();

        return view('owner.portal-users.index', compact('portalUsers'));
    }

    public function store(InvitePortalUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'first_name' => $request->validated('first_name'),
            'last_name' => $request->validated('last_name'),
            'email' => $request->validated('email'),
            'password' => Str::password(32),
            'role' => UserRole::from($request->validated('role')),
            'status' => UserStatus::Active,
        ]);

        Password::sendResetLink(['email' => $user->email]);

        return back()->with('success', 'Invitation sent.');
    }

    public function sendPasswordReset(User $portalUser): RedirectResponse
    {
        Password::sendResetLink(['email' => $portalUser->email]);

        return back()->with('success', 'Password reset link sent.');
    }

    public function destroy(User $portalUser): RedirectResponse
    {
        $portalUser->update(['status' => UserStatus::Inactive]);

        return back()->with('success', 'User deactivated.');
    }
}
