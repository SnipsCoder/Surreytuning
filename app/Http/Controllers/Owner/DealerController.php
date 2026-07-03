<?php

namespace App\Http\Controllers\Owner;

use App\Enums\DealerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\AdjustCreditsRequest;
use App\Models\AuditLog;
use App\Models\Dealer;
use App\Services\CreditService;
use Illuminate\Http\Request;

class DealerController extends Controller
{
    public function index(Request $request)
    {
        $dealers = Dealer::query()
            ->with('primaryContact')
            ->withCount('fileRequests')
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where('company_name', 'like', "%{$search}%"))
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('owner.dealers.index', [
            'dealers' => $dealers,
            'statuses' => DealerStatus::cases(),
        ]);
    }

    public function show(Dealer $dealer)
    {
        $dealer->load([
            'users',
            'fileRequests' => fn ($query) => $query->latest()->limit(10),
            'invoices' => fn ($query) => $query->latest()->limit(10),
        ]);

        $slaveTransactions = $dealer->slaveCreditTransactions()
            ->latest()
            ->paginate(20, ['*'], 'slave_page')
            ->withQueryString();

        $evcTransactions = $dealer->evcCreditTransactions()
            ->latest()
            ->paginate(20, ['*'], 'evc_page')
            ->withQueryString();

        return view('owner.dealers.show', [
            'dealer' => $dealer,
            'slaveTransactions' => $slaveTransactions,
            'evcTransactions' => $evcTransactions,
        ]);
    }

    public function update(Request $request, Dealer $dealer)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $dealer->update($validated);

        return back()->with('success', 'Dealer updated.');
    }

    public function adjustCredits(AdjustCreditsRequest $request, Dealer $dealer, CreditService $creditService)
    {
        $amount = (float) $request->validated('amount');
        $reason = $request->validated('reason');

        if ($request->validated('credit_type') === 'slave') {
            $creditService->manualAdjustSlaveCredits($dealer, $amount, $reason, $request->user());
        } else {
            $creditService->manualAdjustEvcCredits($dealer, $amount, $reason, $request->user());
        }

        AuditLog::record(
            'dealer.credits_adjusted',
            $request->user(),
            $dealer,
            $amount,
            $reason,
            ['credit_type' => $request->validated('credit_type')],
        );

        return back()->with('success', 'Credits adjusted successfully.');
    }

    public function suspend(Request $request, Dealer $dealer)
    {
        $dealer->update(['status' => DealerStatus::Suspended]);

        AuditLog::record('dealer.suspended', $request->user(), $dealer);

        return back()->with('success', 'Dealer suspended.');
    }

    public function reactivate(Request $request, Dealer $dealer)
    {
        $dealer->update(['status' => DealerStatus::Approved]);

        AuditLog::record('dealer.reactivated', $request->user(), $dealer);

        return back()->with('success', 'Dealer reactivated.');
    }
}
