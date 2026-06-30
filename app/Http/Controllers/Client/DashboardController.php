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
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user     = $request->user();
        $dealerId = $user->dealer_id;
        $dealer   = $user->dealer;

        $stats = [
            'pending'            => FileRequest::where('dealer_id', $dealerId)->where('status', FileRequestStatus::Pending)->count(),
            'in_progress'        => FileRequest::where('dealer_id', $dealerId)->where('status', FileRequestStatus::Progress)->count(),
            'completed_this_year'=> FileRequest::where('dealer_id', $dealerId)->where('status', FileRequestStatus::Closed)->whereYear('updated_at', now()->year)->count(),
            'slave_balance'      => $dealer?->slave_credit_balance ?? 0,
            'evc_balance'        => $dealer?->evc_credit_balance ?? 0,
        ];

        // 12-month spend chart from paid invoices
        $spendData  = [];
        $spendLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $spendLabels[] = $month->format('M');
            $spendData[]   = Invoice::where('dealer_id', $dealerId)
                ->where('status', 'paid')
                ->whereYear('paid_at', $month->year)
                ->whereMonth('paid_at', $month->month)
                ->sum('total') / 100;
        }

        $recentFileRequests = FileRequest::where('dealer_id', $dealerId)
            ->with('fileStage')
            ->latest()
            ->take(6)
            ->get();

        $notices = Noticeboard::active()
            ->orderByDesc('priority')
            ->take(5)
            ->get();

        $portalStatus = PortalStatus::find(1);

        $todayHours = OpeningHour::where('day_of_week', now()->dayOfWeek)->first();

        $totalSpent = Invoice::where('dealer_id', $dealerId)
            ->where('status', 'paid')
            ->sum('total') / 100;

        return view('client.dashboard', compact(
            'stats',
            'spendData',
            'spendLabels',
            'recentFileRequests',
            'notices',
            'portalStatus',
            'todayHours',
            'totalSpent',
            'dealer',
        ));
    }
}
