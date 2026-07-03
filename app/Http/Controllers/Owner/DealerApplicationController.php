<?php

namespace App\Http\Controllers\Owner;

use App\Enums\ApplicationStatus;
use App\Enums\DealerStatus;
use App\Enums\UserRole;
use App\Events\DealerApplicationApproved;
use App\Events\DealerApplicationRejected;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\RejectApplicationRequest;
use App\Models\Dealer;
use App\Models\DealerApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DealerApplicationController extends Controller
{
    public function index(Request $request)
    {
        $applications = DealerApplication::query()
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->orderByRaw("status = 'pending' desc")
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('owner.dealer-applications.index', [
            'applications' => $applications,
            'statuses' => ApplicationStatus::cases(),
        ]);
    }

    public function show(DealerApplication $dealerApplication)
    {
        $dealerApplication->load('reviewedBy');

        return view('owner.dealer-applications.show', [
            'application' => $dealerApplication,
        ]);
    }

    public function approve(Request $request, DealerApplication $dealerApplication)
    {
        if ($dealerApplication->status !== ApplicationStatus::Pending) {
            return back()->with('error', 'This application has already been reviewed.');
        }

        [$dealer, $user] = DB::transaction(function () use ($request, $dealerApplication) {
            $dealerApplication->update([
                'status' => ApplicationStatus::Approved,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $dealer = Dealer::create([
                'company_name' => $dealerApplication->company_name,
                'country' => $dealerApplication->country,
                'status' => DealerStatus::Approved,
                'approved_at' => now(),
                'approved_by' => $request->user()->id,
            ]);

            $nameParts = explode(' ', $dealerApplication->contact_name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $dealerApplication->email,
                'role' => UserRole::DealerOwner,
                'dealer_id' => $dealer->id,
                'is_primary_contact' => true,
                'email_verified_at' => now(),
                'password' => bcrypt(Str::random(32)),
            ]);

            return [$dealer, $user];
        });

        DealerApplicationApproved::dispatch($dealerApplication, $dealer, $user);

        return redirect()->route('dealer-applications.index')->with('success', 'Application approved. Dealer account created.');
    }

    public function reject(RejectApplicationRequest $request, DealerApplication $dealerApplication)
    {
        $dealerApplication->update([
            'status' => ApplicationStatus::Rejected,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'rejection_reason' => $request->validated('rejection_reason'),
        ]);

        DealerApplicationRejected::dispatch($dealerApplication);

        return back()->with('success', 'Application rejected.');
    }
}
