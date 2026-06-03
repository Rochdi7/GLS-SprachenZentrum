<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmCollectionRow;
use App\Models\CrmDailyReport;
use App\Models\CrmPaymentSnapshot;
use App\Models\CrmRegistration;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Builds the daily CEO report from local snapshot data + live CRM API.
 *
 * Reads:
 *   - crm_payment_snapshots  (revenue, best center — fast, no API hit)
 *   - CRM API registrations  (new registrations count)
 *   - CRM API payment-collection (outstanding receivables proxy)
 *   - CrmStatsService::perCenterKpis() (attention items)
 */
class DailyReportService
{
    public function __construct(
        protected CrmStatsService $stats,
    ) {
    }

    /**
     * Build the full report payload for the given date.
     * Defaults to yesterday when $date is null.
     *
     * @return array<string, mixed>
     */
    public function generate(?string $date = null): array
    {
        $yesterday = $date
            ? Carbon::parse($date)->toDateString()
            : Carbon::yesterday()->toDateString();

        $revenueYesterday = $this->revenueYesterday($yesterday);
        $newRegistrations = $this->newRegistrations($yesterday);
        $bestCenter       = $this->bestCenter($yesterday);
        $topCenterToday   = $this->topCenterToday($yesterday);
        $attentionItems   = $this->attentionItems($yesterday);

        return [
            'report_date'        => $yesterday,
            'revenue_yesterday'  => $revenueYesterday,
            'new_registrations'  => $newRegistrations,
            'best_center'        => $bestCenter,
            'top_center_today'   => $topCenterToday,
            'centers_ranking'    => $this->centersRanking($yesterday),
            'attention_items'    => $attentionItems,
            'generated_at'       => now()->toDateTimeString(),
        ];
    }

    /**
     * Upsert (update or create) the report row for the given date.
     */
    public function store(array $data): CrmDailyReport
    {
        return CrmDailyReport::updateOrCreate(
            ['report_date' => $data['report_date']],
            [
                'payload'           => $data,
                'revenue_yesterday' => $data['revenue_yesterday'],
                'new_registrations' => $data['new_registrations'],
                'best_center'       => $data['best_center'],
                'generated_at'      => $data['generated_at'],
            ]
        );
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    private function revenueYesterday(string $date): float
    {
        // date_creation = when the cashier entered the payment in the CRM (local datetime).
        // effective_date = the billing/service date set on the invoice, often the class start date —
        // not reliable for "what was collected today".
        $latestSnapshot = CrmPaymentSnapshot::max('snapshot_date');
        if (!$latestSnapshot) {
            return 0.0;
        }

        return (float) CrmPaymentSnapshot::query()
            ->where('snapshot_date', $latestSnapshot)
            ->where('payment_type_id', 1)
            ->whereDate('date_creation', $date)
            ->sum('amount');
    }

    private function newRegistrations(string $date): int
    {
        // Uses normalized date_creation column (indexed) — avoids JSON_EXTRACT full table scan.
        // Populated by crm:backfill-columns (one-time) + crm:sync-registrations (ongoing).
        return CrmRegistration::query()
            ->whereDate('date_creation', $date)
            ->whereNotNull('date_creation')
            ->count();
    }

    /**
     * Top center by cash collected on $date, with amount.
     * Returns ['name' => string, 'amount' => float] or null.
     */
    private function topCenterToday(string $date): ?array
    {
        $latestSnapshot = CrmPaymentSnapshot::max('snapshot_date');
        if (!$latestSnapshot) return null;

        $row = CrmPaymentSnapshot::query()
            ->where('snapshot_date', $latestSnapshot)
            ->where('payment_type_id', 1)
            ->whereDate('date_creation', $date)
            ->selectRaw('crm_store_id, SUM(amount) as total')
            ->groupBy('crm_store_id')
            ->orderByDesc('total')
            ->first();

        if (!$row || $row->total <= 0) return null;

        $site = Site::where('crm_store_id', $row->crm_store_id)->first();
        return [
            'name'   => $site?->name ?? "Store #{$row->crm_store_id}",
            'amount' => (float) $row->total,
        ];
    }

    /**
     * All centers ranked by cash collected on $date, descending.
     * Returns [['name' => string, 'amount' => float], ...]
     */
    private function centersRanking(string $date): array
    {
        $latestSnapshot = CrmPaymentSnapshot::max('snapshot_date');
        if (!$latestSnapshot) return [];

        $rows = CrmPaymentSnapshot::query()
            ->where('snapshot_date', $latestSnapshot)
            ->where('payment_type_id', 1)
            ->whereDate('date_creation', $date)
            ->selectRaw('crm_store_id, SUM(amount) as total')
            ->groupBy('crm_store_id')
            ->orderByDesc('total')
            ->get();

        $sites = Site::whereNotNull('crm_store_id')->get()->keyBy('crm_store_id');

        $ranking = $rows->map(fn($row) => [
            'name'   => $sites->get($row->crm_store_id)?->name ?? "Store #{$row->crm_store_id}",
            'amount' => (float) $row->total,
        ])->values()->toArray();

        // Add centers with zero payments at the bottom
        $rankedIds = $rows->pluck('crm_store_id')->toArray();
        foreach ($sites as $storeId => $site) {
            if (!in_array($storeId, $rankedIds)) {
                $ranking[] = ['name' => $site->name, 'amount' => 0.0];
            }
        }

        return $ranking;
    }

    private function bestCenter(string $date): ?string
    {
        $latestSnapshot = CrmPaymentSnapshot::max('snapshot_date');
        if (!$latestSnapshot) {
            return null;
        }

        $row = CrmPaymentSnapshot::query()
            ->where('snapshot_date', $latestSnapshot)
            ->where('payment_type_id', 1)
            ->whereDate('date_creation', $date)
            ->selectRaw('crm_store_id, SUM(amount) as total')
            ->groupBy('crm_store_id')
            ->orderByDesc('total')
            ->first();

        if (!$row) {
            return null;
        }

        $site = Site::where('crm_store_id', $row->crm_store_id)->first();
        return $site?->name ?? "Store #{$row->crm_store_id}";
    }

    /**
     * Build a list of human-readable alert strings for the CEO.
     *
     * @return string[]
     */
    private function attentionItems(string $date): array
    {
        $items = [];

        // Centers with zero payments yesterday (by effective_date in latest snapshot)
        $sites = Site::whereNotNull('crm_store_id')->orderBy('name')->get();
        $latestSnapshot = CrmPaymentSnapshot::max('snapshot_date');
        $storesWithPayments = $latestSnapshot
            ? CrmPaymentSnapshot::query()
                ->where('snapshot_date', $latestSnapshot)
                ->where('payment_type_id', 1)
                ->whereDate('date_creation', $date)
                ->pluck('crm_store_id')
                ->unique()
                ->toArray()
            : [];

        foreach ($sites as $site) {
            if (!in_array($site->crm_store_id, $storesWithPayments)) {
                $items[] = "Aucun paiement enregistré à {$site->name}";
            }
        }

        // Low registration centers (threshold: < 10 registrations in current period)
        try {
            $kpis = $this->stats->perCenterKpis();
            foreach ($kpis as $kpi) {
                $regCount = (int) ($kpi['registrations'] ?? 0);
                $siteName = $kpi['site']?->name ?? 'Centre inconnu';
                if ($regCount !== null && $regCount < 10 && $regCount >= 0) {
                    $items[] = "Faible nombre d'inscriptions à {$siteName} ({$regCount})";
                }
            }
        } catch (\Throwable $e) {
            Log::warning('DailyReportService: perCenterKpis failed', ['error' => $e->getMessage()]);
        }

        return array_unique($items);
    }
}
