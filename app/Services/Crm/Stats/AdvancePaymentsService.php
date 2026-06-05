<?php

namespace App\Services\Crm\Stats;

use App\Services\Crm\Crm;
use App\Services\Crm\CrmException;
use Illuminate\Support\Facades\Cache;

/**
 * Fetches only "avance" (advance) payments from the CRM.
 *
 * The /payments endpoint doesn't expose an isAvance query parameter, so we
 * paginate everything in parallel and filter IS_AVANCE === 'Y' in PHP.
 *
 * Duplicate detection: same (student + amount + effective_date) → keep one row,
 * mark the rest as duplicates. This matches what Wimschool's UI does internally.
 *
 * Cached 5 min per (center, date-window) combination.
 */
class AdvancePaymentsService
{
    public const CACHE_TTL  = 300;
    public const PAGE_SIZE  = 100;
    public const MAX_PAGES  = 80;

    public function __construct(protected Crm $crm)
    {
    }

    /**
     * @return array{
     *   advances: array<int, array<string,mixed>>,
     *   duplicates: array<int, array<string,mixed>>,
     *   scanned: int,
     *   total_amount: float,
     *   by_handler: array<string, array{count:int,amount:float}>,
     *   error: ?string,
     *   elapsed_ms: int,
     * }
     */
    public function list(?int $strStoreId = null, ?string $startDate = null, ?string $endDate = null, bool $bustCache = false): array
    {
        $key = 'crm.avances:' . md5(($strStoreId ?: 'all') . '|' . ($startDate ?: '') . '|' . ($endDate ?: ''));
        if ($bustCache) Cache::forget($key);

        return Cache::remember($key, self::CACHE_TTL, function () use ($strStoreId, $startDate, $endDate) {
            $t0    = microtime(true);
            $error = null;
            $raw   = [];

            try {
                $baseQuery = array_filter([
                    'strStoreId' => $strStoreId,
                    'startDate'  => $startDate,
                    'endDate'    => $endDate,
                ], fn ($v) => $v !== null);

                // Parallel pagination via the new client helper
                $allRows = $this->crm->client()->pagedScan(
                    path: '/api/external/v1/bulk/payments',
                    baseQuery: $baseQuery,
                    pageSize: 500,
                    maxPages: self::MAX_PAGES,
                    concurrency: 3,
                );

                foreach ($allRows as $row) {
                    if (($row['IS_AVANCE'] ?? null) === 'Y') {
                        $raw[] = $row;
                    }
                }
                $scanned = count($allRows);
            } catch (CrmException $e) {
                $error   = $e->getMessage();
                $scanned = 0;
            }

            // Deduplicate by (student_id + amount + effective_date)
            $seen       = [];
            $unique     = [];
            $duplicates = [];
            foreach ($raw as $a) {
                $sig = ($a['STUDENT_ID'] ?? '?') . '|'
                     . ($a['AMOUNT']     ?? '?') . '|'
                     . substr((string) ($a['EFFECTIVE_DATE'] ?? ''), 0, 10);
                if (isset($seen[$sig])) {
                    $duplicates[] = $a;
                    continue;
                }
                $seen[$sig] = true;
                $unique[]   = $a;
            }

            // Sort most recent first
            usort($unique, fn ($a, $b) => strcmp($b['DATE_CREATION'] ?? '', $a['DATE_CREATION'] ?? ''));

            // Per-handler aggregation (uniques only)
            $byHandler   = [];
            $totalAmount = 0.0;
            foreach ($unique as $a) {
                $h   = $a['USER_CREATION_FULL_NAME'] ?? '(inconnu)';
                $amt = (float) ($a['AMOUNT'] ?? 0);
                $byHandler[$h] ??= ['count' => 0, 'amount' => 0.0];
                $byHandler[$h]['count']++;
                $byHandler[$h]['amount'] += $amt;
                $totalAmount += $amt;
            }
            uasort($byHandler, fn ($a, $b) => $b['amount'] <=> $a['amount']);

            return [
                'advances'     => $unique,
                'duplicates'   => $duplicates,
                'scanned'      => $scanned ?? 0,
                'total_amount' => $totalAmount,
                'by_handler'   => $byHandler,
                'error'        => $error,
                'elapsed_ms'   => (int) round((microtime(true) - $t0) * 1000),
            ];
        });
    }
}
