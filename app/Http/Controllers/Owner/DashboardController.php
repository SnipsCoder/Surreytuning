<?php

namespace App\Http\Controllers\Owner;

use App\Enums\FileRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\FileRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('owner.dashboard', [
            'openRequestsCount' => FileRequest::whereNotIn('status', [FileRequestStatus::Closed, FileRequestStatus::Void])->count(),
            'pendingActionCount' => FileRequest::where('status', FileRequestStatus::Pending)->count(),
            'closedThisMonthCount' => FileRequest::where('status', FileRequestStatus::Closed)->where('closed_at', '>=', now()->subDays(30))->count(),
            'dealerCount' => Dealer::count(),
        ]);
    }

}
