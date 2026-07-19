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
        // A custom range (both from & to supplied and valid) overrides the presets.
        $from = $this->parseDate($request->query('from'));
        $to = $this->parseDate($request->query('to'));

        $customRange = $from !== null && $to !== null;
        if ($customRange && $from->gt($to)) {
            // Swap so the earlier date is always the lower bound.
            [$from, $to] = [$to, $from];
        }

        if ($customRange) {
            $period = 'custom';
            $start = $from->copy()->startOfDay();
            $end = $to->copy()->endOfDay();
        } else {
            $period = (string) $request->query('period', '30');
            if (! array_key_exists($period, self::PERIODS)) {
                $period = '30';
            }
            $start = $period === 'all' ? null : now()->subDays((int) $period)->startOfDay();
            $end = null;
        }

        $periodLabel = $customRange
            ? $start->format('j M Y') . ' – ' . $end->format('j M Y')
            : self::PERIODS[$period];

        // Upper-bound helper: applies ->where($col, '<=', $end) only for a custom range.
        $upTo = fn (string $col) => fn ($q) => $end ? $q->where($col, '<=', $end) : $q;

        // --- Headline metrics -------------------------------------------------
        $revenue = Invoice::query()
            ->where('status', InvoiceStatus::Paid)
            ->when($start, fn ($q) => $q->where('paid_at', '>=', $start))
            ->when($end, $upTo('paid_at'))
            ->sum('amount_gross');

        $filesReceived = FileRequest::query()
            ->when($start, fn ($q) => $q->where('created_at', '>=', $start))
            ->when($end, $upTo('created_at'))
            ->count();

        $filesCompleted = FileRequest::query()
            ->where('status', FileRequestStatus::Closed)
            ->whereNotNull('closed_at')
            ->when($start, fn ($q) => $q->where('closed_at', '>=', $start))
            ->when($end, $upTo('closed_at'))
            ->count();

        // Average turnaround (created_at -> closed_at) for closed requests in range.
        $closed = FileRequest::query()
            ->where('status', FileRequestStatus::Closed)
            ->whereNotNull('closed_at')
            ->when($start, fn ($q) => $q->where('closed_at', '>=', $start))
            ->when($end, $upTo('closed_at'))
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
            ->when($end, $upTo('created_at'))
            ->sum('amount');

        $evcCreditsSold = EvcCreditTransaction::query()
            ->where('type', EvcCreditTransactionType::Purchase)
            ->when($start, fn ($q) => $q->where('created_at', '>=', $start))
            ->when($end, $upTo('created_at'))
            ->sum('amount');

        $productRevenue = ProductOrder::query()
            ->where('status', 'paid')
            ->when($start, fn ($q) => $q->where('created_at', '>=', $start))
            ->when($end, $upTo('created_at'))
            ->sum('total_gross');

        // --- Breakdowns -------------------------------------------------------
        $topDealers = Dealer::query()
            ->withCount(['fileRequests' => function ($q) use ($start, $end) {
                $q->when($start, fn ($qq) => $qq->where('created_at', '>=', $start))
                    ->when($end, fn ($qq) => $qq->where('created_at', '<=', $end));
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
            ->when($end, $upTo('created_at'))
            ->groupBy('make')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        // Status breakdown across requests created in range.
        $statusCounts = FileRequest::query()
            ->selectRaw('status, COUNT(*) as total')
            ->when($start, fn ($q) => $q->where('created_at', '>=', $start))
            ->when($end, $upTo('created_at'))
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
        if ($customRange) {
            // Span the selected window, but cap the number of bars so it stays legible.
            $rangeDays = (int) $start->diffInDays($end) + 1;
            $trendDays = min($rangeDays, 90);
            $trendStart = $end->copy()->startOfDay()->subDays($trendDays - 1);
            $trendEnd = $end->copy()->endOfDay();
        } else {
            $trendDays = $period === 'all' ? 30 : min((int) $period, 90);
            $trendStart = now()->subDays($trendDays - 1)->startOfDay();
            $trendEnd = null;
        }

        $dailyRaw = FileRequest::query()
            ->where('created_at', '>=', $trendStart)
            ->when($trendEnd, fn ($q) => $q->where('created_at', '<=', $trendEnd))
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
            'periodLabel' => $periodLabel,
            'customRange' => $customRange,
            'from' => $customRange ? $start->format('Y-m-d') : null,
            'to' => $customRange ? $end->format('Y-m-d') : null,
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

    /**
     * Parse a Y-m-d query param into a Carbon date, or null if empty/invalid.
     */
    private function parseDate(?string $value): ?Carbon
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', trim($value));
        } catch (\Throwable) {
            return null;
        }
    }
}
