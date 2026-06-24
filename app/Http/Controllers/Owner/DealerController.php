<?php

namespace App\Http\Controllers\Owner;

use App\Enums\DealerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\AdjustCreditsRequest;
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
            'slaveCreditTransactions' => fn ($query) => $query->latest()->limit(20),
            'evcCreditTransactions' => fn ($query) => $query->latest()->limit(20),
        ]);

        return view('owner.dealers.show', [
            'dealer' => $dealer,
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

        return back()->with('success', 'Credits adjusted successfully.');
    }

    public function suspend(Request $request, Dealer $dealer)
    {
        $dealer->update(['status' => DealerStatus::Suspended]);

        return back()->with('success', 'Dealer suspended.');
    }

    public function reactivate(Request $request, Dealer $dealer)
    {
        $dealer->update(['status' => DealerStatus::Approved]);

        return back()->with('success', 'Dealer reactivated.');
    }
}
