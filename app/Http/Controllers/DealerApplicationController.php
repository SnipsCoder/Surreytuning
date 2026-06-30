<?php

namespace App\Http\Controllers;

use App\Enums\DealerStatus;
use App\Enums\UserRole;
use App\Events\DealerApplicationApproved;
use App\Models\Dealer;
use App\Models\DealerApplication;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DealerApplicationController extends Controller
{
    public function create(Request $request): View
    {
        $terms = Setting::get()->terms_and_conditions;

        return view('auth.apply', compact('terms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name'  => ['required', 'string', 'max:255'],
            'contact_name'  => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:50'],
            'country'       => ['required', 'string', 'max:100'],
            'message'       => ['nullable', 'string'],
            'terms_accepted' => ['accepted'],
        ]);

        $setting = Setting::get();

        if ($setting->dealer_auto_onboard) {
            [$application, $dealer, $user] = DB::transaction(function () use ($data) {
                $application = DealerApplication::create([
                    'company_name'    => $data['company_name'],
                    'contact_name'    => $data['contact_name'],
                    'email'           => $data['email'],
                    'phone'           => $data['phone'],
                    'country'         => $data['country'],
                    'message'         => $data['message'] ?? null,
                    'terms_accepted_at' => now(),
                    'status'          => 'approved',
                    'reviewed_by'     => null,
                    'reviewed_at'     => now(),
                ]);

                $dealer = Dealer::create([
                    'company_name' => $data['company_name'],
                    'country'      => $data['country'],
                    'status'       => DealerStatus::Approved,
                    'approved_at'  => now(),
                ]);

                $nameParts = explode(' ', $data['contact_name'], 2);

                $user = User::create([
                    'first_name'         => $nameParts[0],
                    'last_name'          => $nameParts[1] ?? '',
                    'email'              => $data['email'],
                    'role'               => UserRole::DealerOwner,
                    'dealer_id'          => $dealer->id,
                    'is_primary_contact' => true,
                    'email_verified_at'  => now(),
                    'password'           => bcrypt(Str::random(32)),
                ]);

                return [$application, $dealer, $user];
            });

            DealerApplicationApproved::dispatch($application, $dealer, $user);
        } else {
            DealerApplication::create([
                'company_name'     => $data['company_name'],
                'contact_name'     => $data['contact_name'],
                'email'            => $data['email'],
                'phone'            => $data['phone'],
                'country'          => $data['country'],
                'message'          => $data['message'] ?? null,
                'terms_accepted_at' => now(),
                'status'           => 'pending',
            ]);
        }

        session()->flash('contact_name', $data['contact_name']);

        return redirect()->route('apply.received');
    }
}
