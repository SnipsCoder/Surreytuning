<?php

namespace App\Http\Controllers\Client;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortalUserController extends Controller
{
    public function index(Request $request): View
    {
        $dealerId = $request->user()->dealer_id;

        $users = User::where('dealer_id', $dealerId)
            ->orderByDesc('created_at')
            ->get();

        return view('client.portal-users.index', compact('users'));
    }

    public function invite(Request $request): RedirectResponse
    {
        $dealerId = $request->user()->dealer_id;

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'password'   => Str::password(32),
            'role'       => UserRole::DealerUser,
            'dealer_id'  => $dealerId,
            'status'     => 'active',
        ]);

        Password::sendResetLink(['email' => $user->email]);

        return back()->with('success', 'Invitation sent.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->dealer_id === $request->user()->dealer_id, 403);

        $user->update(['status' => 'inactive']);

        return back()->with('success', 'User removed.');
    }
}
