<?php

namespace App\Http\Controllers\Client;

use App\Enums\FileRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\FileRequest;
use App\Models\Noticeboard;
use App\Models\OpeningHour;
use App\Models\PortalStatus;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dealerId = $request->user()->dealer_id;

        $stats = [
            'pending' => FileRequest::where('dealer_id', $dealerId)
                ->where('status', FileRequestStatus::Pending)
                ->count(),
            'in_progress' => FileRequest::where('dealer_id', $dealerId)
                ->where('status', FileRequestStatus::Progress)
                ->count(),
            'completed_this_year' => FileRequest::where('dealer_id', $dealerId)
                ->where('status', FileRequestStatus::Closed)
                ->whereYear('updated_at', now()->year)
                ->count(),
        ];

        $recentFileRequests = FileRequest::where('dealer_id', $dealerId)
            ->latest()
            ->take(5)
            ->get();

        $notices = Noticeboard::active()
            ->orderByDesc('priority')
            ->take(3)
            ->get();

        $portalStatus = PortalStatus::current();

        $todayHours = OpeningHour::where('day_of_week', now()->dayOfWeek)->first();

        return view('client.dashboard', [
            'stats' => $stats,
            'recentFileRequests' => $recentFileRequests,
            'notices' => $notices,
            'portalStatus' => $portalStatus,
            'todayHours' => $todayHours,
        ]);
    }
}
