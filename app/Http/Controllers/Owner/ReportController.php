<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner;

use App\Enums\EvcCreditTransactionType;
use App\Enums\FileCreditTransactionType;
use App\Enums\FileRequestStatus;
use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\EvcCreditTransaction;
use App\Models\FileCreditTransaction;
use App\Models\FileRequest;
use App\Models\Invoice;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    /**
     * Selectable reporting windows (days). 'all' is handled separately.
     */
    private const PERIODS = [
        '7' => 'Last 7 days',
        '30' => 'Last 30 days',
        '90' => 'Last 90 days',
        '365' => 'Last 12 months',
        'all' => 'All time',
    ];

    public function index(Request $request)
    {
        $period = (string) $request->query('period', '30');
        if (! array_key_exists($period, self::PERIODS)) {
            $period = '30';
        }

        $start = $period === 'all' ? null : now()->subDays((int) $period)->startOfDay();

        // --- Headline metrics -------------------------------------------------
        $revenue = Invoice::query()
            ->where('status', InvoiceStatus::Paid)
            ->when($start, fn ($q) => $q->where('paid_at', '>=', $start))
            ->sum('amount_gross');

        $filesReceived = FileRequest::query()
            ->when($start, fn ($q) => $q->where('created_at', '>=', $start))
            ->count();

        $filesCompleted = FileRequest::query()
            ->where('status', FileRequestStatus::Closed)
            ->whereNotNull('closed_at')
            ->when($start, fn ($q) => $q->where('closed_at', '>=', $start))
            ->count();

        // Average turnaround (created_at -> closed_at) for closed requests in range.
        $closed = FileRequest::query()
            ->where('status', FileRequestStatus::Closed)
            ->whereNotNull('closed_at')
            ->when($start, fn ($q) => $q->where('closed_at', '>=', $start))
            ->get(['created_at', 'closed_at']);

        $avgTurnaroundHours = null;
        if ($closed->isNotEmpty()) {
            $totalHours = $closed->sum(
                fn (FileRequest $fr) => $fr->created_at->diffInMinutes($fr->closed_at) / 60
            );
            $avgTurnaroundHours = $totalHours / $closed->count();
        }

        // Credits sold = file-credit top-ups + EVC purchases (money in).
        $fileCreditsSold = FileCreditTransaction::query()
            ->where('type', FileCreditTransactionType::TopUp)
            ->when($start, fn ($q) => $q->where('created_at', '>=', $start))
            ->sum('amount');

        $evcCreditsSold = EvcCreditTransaction::query()
            ->where('type', EvcCreditTransactionType::Purchase)
            ->when($start, fn ($q) => $q->where('created_at', '>=', $start))
            ->sum('amount');

        $productRevenue = ProductOrder::query()
            ->where('status', 'paid')
            ->when($start, fn ($q) => $q->where('created_at', '>=', $start))
            ->sum('total_gross');

        // --- Breakdowns -------------------------------------------------------
        $topDealers = Dealer::query()
            ->withCount(['fileRequests' => function ($q) use ($start) {
                $q->when($start, fn ($qq) => $qq->where('created_at', '>=', $start));
            }])
            ->having('file_requests_count', '>', 0)
            ->orderByDesc('file_requests_count')
            ->take(8)
            ->get();

        $topMakes = FileRequest::query()
            ->selectRaw('make, COUNT(*) as total')
            ->whereNotNull('make')
            ->where('make', '!=', '')
            ->when($start, fn ($q) => $q->where('created_at', '>=', $start))
            ->groupBy('make')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        // Status breakdown across requests created in range.
        $statusCounts = FileRequest::query()
            ->selectRaw('status, COUNT(*) as total')
            ->when($start, fn ($q) => $q->where('created_at', '>=', $start))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusBreakdown = collect(FileRequestStatus::cases())
            ->map(fn (FileRequestStatus $status) => [
                'status' => $status,
                'count' => (int) ($statusCounts[$status->value] ?? 0),
            ])
            ->filter(fn ($row) => $row['count'] > 0)
            ->sortByDesc('count')
            ->values();

        // Daily file volume for the trend bar chart (capped so it stays readable).
        $trendDays = $period === 'all' ? 30 : min((int) $period, 90);
        $trendStart = now()->subDays($trendDays - 1)->startOfDay();
        $dailyRaw = FileRequest::query()
            ->where('created_at', '>=', $trendStart)
            ->get(['created_at'])
            ->groupBy(fn (FileRequest $fr) => $fr->created_at->format('Y-m-d'))
            ->map->count();

        $dailyVolume = collect(range(0, $trendDays - 1))
            ->map(function (int $offset) use ($trendStart, $dailyRaw) {
                $day = $trendStart->copy()->addDays($offset);

                return [
                    'date' => $day,
                    'count' => (int) ($dailyRaw[$day->format('Y-m-d')] ?? 0),
                ];
            });
        $dailyPeak = max(1, (int) $dailyVolume->max('count'));

        return view('owner.reports.index', [
            'period' => $period,
            'periods' => self::PERIODS,
            'periodLabel' => self::PERIODS[$period],
            'revenue' => (float) $revenue,
            'filesReceived' => $filesReceived,
            'filesCompleted' => $filesCompleted,
            'avgTurnaroundHours' => $avgTurnaroundHours,
            'creditsSold' => (float) $fileCreditsSold + (float) $evcCreditsSold,
            'fileCreditsSold' => (float) $fileCreditsSold,
            'evcCreditsSold' => (float) $evcCreditsSold,
            'productRevenue' => (float) $productRevenue,
            'topDealers' => $topDealers,
            'topMakes' => $topMakes,
            'statusBreakdown' => $statusBreakdown,
            'dailyVolume' => $dailyVolume,
            'dailyPeak' => $dailyPeak,
            'trendDays' => $trendDays,
        ]);
    }
}
