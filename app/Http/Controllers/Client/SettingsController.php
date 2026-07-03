<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $dealer = $user->dealer;

        return view('client.settings.index', compact('user', 'dealer'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tab = $request->input('_tab');

        match ($tab) {
            'account' => $this->updateAccount($request),
            'profile' => $this->updateProfile($request),
            'security' => $this->updatePassword($request),
            'notifications' => $this->updateNotifications($request),
            default => null,
        };

        return back()->with('success', 'Settings saved.');
    }

    private function updateAccount(Request $request): void
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'invoice_address' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->dealer->update($data);
    }

    private function updateProfile(Request $request): void
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
        ]);

        $request->user()->update($data);
    }

    private function updatePassword(Request $request): void
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => $data['password']]);
    }

    private function updateNotifications(Request $request): void
    {
        $request->validate([
            'notify_comments_email' => ['nullable', 'boolean'],
            'notify_file_requests_email' => ['nullable', 'boolean'],
            'notify_file_requests_sms' => ['nullable', 'boolean'],
        ]);

        $request->user()->update([
            'notify_comments_email' => $request->boolean('notify_comments_email'),
            'notify_file_requests_email' => $request->boolean('notify_file_requests_email'),
            'notify_file_requests_sms' => $request->boolean('notify_file_requests_sms'),
        ]);
    }
}
