<?php

namespace App\Services\Encaissement;

use App\Models\Encaissement;
use App\Models\SiteExpense;
use App\Models\Prime;
use App\Models\PresencePaymentSummary;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Analytics & reporting service for the encaissement module.
 * Computes KPIs, rentability, operator performance, breakdowns.
 */
class EncaissementAnalyticsService
{
    /**
     * Dashboard summary for a given period and optional site.
     *
     * $month accepts either:
     *   - 'YYYY-MM' (single month, original behaviour)
     *   - 'YYYY'    (whole year)
     */
    public function getDashboardData(?int $siteId = null, ?string $month = null): array
    {
        $month = $month ?: now()->format('Y-m');

        if (preg_match('/^\d{4}$/', $month)) {
            $start = Carbon::parse($month . '-01-01')->startOfYear();
            $end = $start->copy()->endOfYear();
        } else {
            $start = Carbon::parse($month . '-01')->startOfMonth();
            $end = $start->copy()->endOfMonth();
        }

        $query = Encaissement::whereBetween('collected_at', [$start, $end]);
        if ($siteId) $query->where('site_id', $siteId);

        // Total revenue
        $totalRevenue = (clone $query)->sum('amount');
        $totalCount = (clone $query)->count();

        // By payment method
        $byMethod = (clone $query)
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        // By fee type
        $byFeeType = (clone $query)
            ->select('fee_type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('fee_type')
            ->get()
            ->keyBy('fee_type');

        // By operator
        $byOperator = (clone $query)
            ->select('operator_name', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->whereNotNull('operator_name')
            ->groupBy('operator_name')
            ->orderByDesc('total')
            ->get();

        // By site (if no specific site selected)
        $bySite = null;
        if (!$siteId) {
            $bySite = (clone $query)
                ->join('sites', 'encaissements.site_id', '=', 'sites.id')
                ->select('sites.name as site_name', 'encaissements.site_id',
                    DB::raw('SUM(encaissements.amount) as total'),
                    DB::raw('COUNT(*) as count'))
                ->groupBy('encaissements.site_id', 'sites.name')
                ->orderByDesc('total')
                ->get();
        }

        // Daily evolution for the month
        $dailyEvolution = (clone $query)
            ->select(
                DB::raw('DATE(collected_at) as day'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE(collected_at)'))
            ->orderBy('day')
            ->get();

        return [
            'month' => $month,
            'total_revenue' => $totalRevenue,
            'total_count' => $totalCount,
            'by_method' => $byMethod,
            'by_fee_type' => $byFeeType,
            'by_operator' => $byOperator,
            'by_site' => $bySite,
            'daily_evolution' => $dailyEvolution,
        ];
    }

    /**
     * Compute rentability for a site for a given month.
     *
     * Rentabilité = Recettes - Charges
     * Charges = site_expenses + teacher payments (presence_payment_summaries) + primes
     */
    public function getRentabilite(int $siteId, string $month): array
    {
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        // Revenue
        $revenue = Encaissement::where('site_id', $siteId)
            ->whereBetween('collected_at', [$start, $end])
            ->sum('amount');

        // Fixed expenses
        $expenses = SiteExpense::where('site_id', $siteId)
            ->where('month', $start->format('Y-m-d'))
            ->sum('amount');

        // Teacher payments (from existing presence system)
        $teacherPayments = PresencePaymentSummary::whereHas('presenceImport', function ($q) use ($siteId, $start) {
                $q->whereHas('group', fn($gq) => $gq->where('site_id', $siteId))
                  ->where('month', $start->format('Y-m-d'));
            })
            ->whereNotNull('approved_at')
            ->sum('total_payment');

        // Primes
        $primesTotal = Prime::where('site_id', $siteId)
            ->where('month', $start->format('Y-m-d'))
            ->sum('amount');

        $totalCharges = $expenses + $teacherPayments + $primesTotal;
        $margin = $revenue - $totalCharges;
        $marginRate = $revenue > 0 ? round(($margin / $revenue) * 100, 1) : 0;

        return [
            'month' => $month,
            'site_id' => $siteId,
            'revenue' => round($revenue, 2),
            'charges' => [
                'expenses' => round($expenses, 2),
                'teacher_payments' => round($teacherPayments, 2),
                'primes' => round($primesTotal, 2),
                'total' => round($totalCharges, 2),
            ],
            'margin' => round($margin, 2),
            'margin_rate' => $marginRate,
        ];
    }

    /**
     * Multi-month rentability for a site (for charts).
     */
    public function getRentabiliteHistory(int $siteId, int $monthsBack = 6): array
    {
        $results = [];
        $current = now()->startOfMonth();

        for ($i = 0; $i < $monthsBack; $i++) {
            $month = $current->copy()->subMonths($i)->format('Y-m');
            $results[] = $this->getRentabilite($siteId, $month);
        }

        return array_reverse($results);
    }

    /**
     * Compare all sites for a given month.
     */
    public function compareSites(string $month): array
    {
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return Encaissement::join('sites', 'encaissements.site_id', '=', 'sites.id')
            ->whereBetween('encaissements.collected_at', [$start, $end])
            ->select(
                'sites.id as site_id',
                'sites.name as site_name',
                DB::raw('SUM(encaissements.amount) as total_revenue'),
                DB::raw('COUNT(*) as total_operations'),
                DB::raw('COUNT(DISTINCT encaissements.operator_name) as operators_count'),
                DB::raw('COUNT(DISTINCT DATE(encaissements.collected_at)) as active_days')
            )
            ->groupBy('sites.id', 'sites.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->toArray();
    }

    /**
     * Monthly revenue evolution for charts (last N months).
     */
    public function getMonthlyEvolution(?int $siteId = null, int $monthsBack = 12): array
    {
        $results = [];
        $current = now()->startOfMonth();

        for ($i = $monthsBack - 1; $i >= 0; $i--) {
            $monthStart = $current->copy()->subMonths($i);
            $monthEnd = $monthStart->copy()->endOfMonth();

            $query = Encaissement::whereBetween('collected_at', [$monthStart, $monthEnd]);
            if ($siteId) $query->where('site_id', $siteId);

            $revenue = (clone $query)->sum('amount');
            $count = (clone $query)->count();

            // Expenses for this month
            $expQuery = SiteExpense::where('month', $monthStart->format('Y-m-d'));
            if ($siteId) $expQuery->where('site_id', $siteId);
            $expenses = $expQuery->sum('amount');

            $results[] = [
                'month' => $monthStart->format('Y-m'),
                'month_label' => $monthStart->translatedFormat('M Y'),
                'revenue' => round($revenue, 2),
                'expenses' => round($expenses, 2),
                'margin' => round($revenue - $expenses, 2),
                'operations' => $count,
            ];
        }

        return $results;
    }

    /**
     * Revenue by payment method over months (for stacked chart).
     */
    public function getMethodEvolution(?int $siteId = null, int $monthsBack = 6): array
    {
        $results = [];
        $current = now()->startOfMonth();

        for ($i = $monthsBack - 1; $i >= 0; $i--) {
            $monthStart = $current->copy()->subMonths($i);
            $monthEnd = $monthStart->copy()->endOfMonth();

            $query = Encaissement::whereBetween('collected_at', [$monthStart, $monthEnd]);
            if ($siteId) $query->where('site_id', $siteId);

            $byMethod = (clone $query)
                ->select('payment_method', DB::raw('SUM(amount) as total'))
                ->groupBy('payment_method')
                ->pluck('total', 'payment_method')
                ->toArray();

            $results[] = [
                'month' => $monthStart->translatedFormat('M Y'),
                'especes' => round($byMethod['especes'] ?? 0, 2),
                'tpe' => round($byMethod['tpe'] ?? 0, 2),
                'virement' => round($byMethod['virement'] ?? 0, 2),
                'cheque' => round($byMethod['cheque'] ?? 0, 2),
            ];
        }

        return $results;
    }

    /**
     * Operator performance ranking for a period.
     */
    public function getOperatorPerformance(?int $siteId, string $month): array
    {
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $query = Encaissement::whereBetween('collected_at', [$start, $end])
            ->whereNotNull('operator_name');

        if ($siteId) $query->where('site_id', $siteId);

        return $query->select(
                'operator_name',
                DB::raw('SUM(amount) as total_collected'),
                DB::raw('COUNT(*) as operations_count'),
                DB::raw('COUNT(DISTINCT DATE(collected_at)) as active_days'),
                DB::raw('ROUND(SUM(amount) / COUNT(DISTINCT DATE(collected_at)), 2) as avg_per_day')
            )
            ->groupBy('operator_name')
            ->orderByDesc('total_collected')
            ->get()
            ->toArray();
    }
}
