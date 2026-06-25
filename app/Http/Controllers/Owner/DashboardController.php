<?php

namespace App\Http\Controllers\Owner;

use App\Enums\ApplicationStatus;
use App\Enums\DealerStatus;
use App\Enums\FileRequestStatus;
use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\DealerApplication;
use App\Models\FileRequest;
use App\Models\Invoice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('owner.dashboard', [
            'pendingFileRequestsCount' => FileRequest::where('status', FileRequestStatus::Pending)->count(),
            'fileRequestsTodayCount' => FileRequest::whereDate('created_at', today())->count(),
            'activeDealersCount' => Dealer::where('status', DealerStatus::Approved)->count(),
            'revenueThisMonth' => Invoice::where('status', InvoiceStatus::Paid)
                ->whereYear('paid_at', now()->year)
                ->whereMonth('paid_at', now()->month)
                ->sum('amount_gross'),
            'recentFileRequests' => FileRequest::with('dealer')->latest()->take(10)->get(),
            'pendingApplicationsCount' => DealerApplication::where('status', ApplicationStatus::Pending)->count(),
        ]);
    }
}
