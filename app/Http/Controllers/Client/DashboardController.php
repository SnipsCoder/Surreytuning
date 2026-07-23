<?php

namespace App\Http\Controllers\Client;

use App\Enums\FileRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\FileRequest;
use App\Models\Invoice;
use App\Models\Noticeboard;
use App\Models\OpeningHour;
use App\Models\PortalStatus;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $dealerId = $user->dealer_id;
        $dealer = $user->dealer;

        $stats = [
            'pending' => FileRequest::where('dealer_id', $dealerId)->where('status', FileRequestStatus::Pending)->count(),
            'in_progress' => FileRequest::where('dealer_id', $dealerId)->where('status', FileRequestStatus::Progress)->count(),
            'completed_this_year' => FileRequest::where('dealer_id', $dealerId)->where('status', FileRequestStatus::Closed)->whereYear('updated_at', now()->year)->count(),
            'file_balance' => $dealer?->file_credit_balance ?? 0,
            'evc_balance' => $dealer?->evc_credit_balance ?? 0,
        ];

        $deltas = [
            'pending_yesterday' => FileRequest::where('dealer_id', $dealerId)->whereDate('created_at', now()->subDay())->count(),
            'in_progress_today' => FileRequest::where('dealer_id', $dealerId)->where('status', FileRequestStatus::Progress)->whereDate('updated_at', today())->count(),
            'completed_this_month' => FileRequest::where('dealer_id', $dealerId)->where('status', FileRequestStatus::Closed)->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->count(),
        ];

        // 12-month spend chart from paid invoices
        $spendData = [];
        $spendLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $spendLabels[] = $month->format('M');
            $spendData[] = Invoice::where('dealer_id', $dealerId)
                ->where('status', 'paid')
                ->whereYear('paid_at', $month->year)
                ->whereMonth('paid_at', $month->month)
                ->sum('amount_gross');
        }

        $recentFileRequests = FileRequest::where('dealer_id', $dealerId)
            ->with('fileStage')
            ->latest()
            ->take(6)
            ->get();

        $notices = Noticeboard::active()
            ->take(5)
            ->get();

        // Notices flagged as an out-of-office banner across the top of the page.
        $frontPageNotices = Noticeboard::frontPage()->get();

        $portalStatus = PortalStatus::find(1);

        $todayHours = OpeningHour::where('day_of_week', now()->dayOfWeek)->first();

        $totalSpent = Invoice::where('dealer_id', $dealerId)
            ->where('status', 'paid')
            ->sum('amount_gross');

        $totalSpentThisYear = Invoice::where('dealer_id', $dealerId)
            ->where('status', 'paid')
            ->whereYear('paid_at', now()->year)
            ->sum('amount_gross');

        return view('client.dashboard', compact(
            'stats',
            'deltas',
            'spendData',
            'spendLabels',
            'recentFileRequests',
            'notices',
            'frontPageNotices',
            'portalStatus',
            'todayHours',
            'totalSpent',
            'totalSpentThisYear',
            'dealer',
        ));
    }
}
