<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmDailyReport;
use App\Models\CrmPaymentSnapshot;
use App\Models\CrmRegistration;
use App\Models\Site;
use App\Services\Crm\Crm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Builds the daily CEO report from local snapshot data + live CRM API.
 *
 * Reads:
 *   - crm_payment_snapshots  (revenue, best center — fast, no API hit)
 *   - CRM API registrations  (new registrations count)
 *   - CRM API payment-collection (outstanding receivables proxy)
 *   - InsightsService::retention() (students at risk)
 *   - CrmStatsService::perCenterKpis() (attention items)
 */
class DailyReportService
{
    public function __construct(
        protected Crm $crm,
        protected CrmStatsService $stats,
        protected InsightsService $insights,
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

        $revenueYesterday       = $this->revenueYesterday($yesterday);
        $newRegistrations       = $this->newRegistrations($yesterday);
        $outstandingReceivables = $this->outstandingReceivables();
        $studentsAtRisk         = $this->studentsAtRisk();
        $bestCenter             = $this->bestCenter($yesterday);
        $attentionItems         = $this->attentionItems($yesterday);

        return [
            'report_date'             => $yesterday,
            'revenue_yesterday'       => $revenueYesterday,
            'new_registrations'       => $newRegistrations,
            'outstanding_receivables' => $outstandingReceivables,
            'students_at_risk'        => $studentsAtRisk,
            'best_center'             => $bestCenter,
            'attention_items'         => $attentionItems,
            'generated_at'            => now()->toDateTimeString(),
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
                'payload'                 => $data,
                'revenue_yesterday'       => $data['revenue_yesterday'],
                'new_registrations'       => $data['new_registrations'],
                'outstanding_receivables' => $data['outstanding_receivables'],
                'students_at_risk'        => $data['students_at_risk'],
                'best_center'             => $data['best_center'],
                'generated_at'            => $data['generated_at'],
            ]
        );
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    private function revenueYesterday(string $date): float
    {
        // Use effective_date (when money was collected) from the most recent snapshot.
        // snapshot_date only tells us when the job ran, not when the payment happened.
        $latestSnapshot = CrmPaymentSnapshot::max('snapshot_date');
        if (!$latestSnapshot) {
            return 0.0;
        }

        return (float) CrmPaymentSnapshot::query()
            ->where('snapshot_date', $latestSnapshot)
            ->whereDate('effective_date', $date)
            ->sum('amount');
    }

    private function newRegistrations(string $date): int
    {
        // Count from local mirror using REGISTRATION_DATE stored in raw_data.
        return CrmRegistration::query()
            ->whereRaw("DATE(JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.REGISTRATION_DATE'))) = ?", [$date])
            ->count();
    }

    private function outstandingReceivables(): float
    {
        try {
            $resp = $this->crm->payments()->collection(
                page: 0,
                size: 25,
                includeTotal: false,
            );
            $rows = $resp['data'] ?? [];
            $sum  = 0.0;
            foreach ($rows as $row) {
                foreach (['REST_AMOUNT', 'OPEN_AMOUNT', 'REMAINING_AMOUNT', 'AMOUNT_DUE', 'AMOUNT'] as $key) {
                    if (isset($row[$key]) && is_numeric($row[$key])) {
                        $sum += (float) $row[$key];
                        break;
                    }
                }
            }
            return $sum;
        } catch (\Throwable $e) {
            Log::warning('DailyReportService: payment-collection API failed', ['error' => $e->getMessage()]);
            return 0.0;
        }
    }

    private function studentsAtRisk(): int
    {
        try {
            $retention = $this->insights->retention();
            if (!empty($retention['error'])) {
                return 0;
            }
            $total = 0;
            foreach ($retention['cohorts'] ?? [] as $cohort) {
                $total += (int) ($cohort['cancelled'] ?? 0);
                $total += (int) ($cohort['suspended'] ?? 0);
            }
            return $total;
        } catch (\Throwable $e) {
            Log::warning('DailyReportService: retention call failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function bestCenter(string $date): ?string
    {
        $latestSnapshot = CrmPaymentSnapshot::max('snapshot_date');
        if (!$latestSnapshot) {
            return null;
        }

        $row = CrmPaymentSnapshot::query()
            ->where('snapshot_date', $latestSnapshot)
            ->whereDate('effective_date', $date)
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
                ->whereDate('effective_date', $date)
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
