<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmPaymentSnapshot;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * Diff engine over the crm_payment_snapshots table.
 *
 * Provides two views:
 *   - dailyDiff(date)        : what changed between (date-1) and (date)
 *   - paymentHistory(id)     : full chronological history of one payment
 *
 * No API calls here — everything reads from the local snapshot table populated
 * by the nightly artisan command.
 */
class PaymentActivityService
{
    /**
     * Compare two consecutive snapshot days and bucket changes.
     *
     * @return array{
     *   date: string,
     *   previous_date: string,
     *   has_baseline: bool,
     *   counts: array<string,int>,
     *   deleted: LengthAwarePaginator,
     *   created: LengthAwarePaginator,
     *   amount_changed: LengthAwarePaginator,
     *   late_edits: LengthAwarePaginator,
     *   user_changed: LengthAwarePaginator,
     * }
     */
    public function dailyDiff(?string $date = null, ?int $strStoreId = null, int $perPage = 50, ?int $page = null): array
    {
        $today = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();
        $page  = $page ?: (int) (Paginator::resolveCurrentPage() ?: 1);

        // Find the nearest available "previous" snapshot date (skip gaps).
        // Example: snapshots on 2025-12-31 and 2026-05-17. When user picks
        // 2026-05-17, the previous baseline is 2025-12-31, not 2026-05-16.
        $previousSnapshotDate = CrmPaymentSnapshot::query()
            ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId))
            ->whereDate('snapshot_date', '<', $today)
            ->max('snapshot_date');

        $previous = $previousSnapshotDate ? Carbon::parse($previousSnapshotDate)->toDateString() : null;

        $todayQ = CrmPaymentSnapshot::whereDate('snapshot_date', $today)
            ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId));

        $todayCount = (clone $todayQ)->count();
        $yestCount  = 0;

        // No prior snapshot available → no diff possible (everything is "first seen").
        if (!$previous) {
            return [
                'date'             => $today,
                'previous_date'    => Carbon::parse($today)->subDay()->toDateString(),
                'has_baseline'     => false,
                'counts'           => ['today' => $todayCount, 'yesterday' => 0],
                'deleted'          => $this->paginate(collect(), $perPage, $page, 'p_deleted'),
                'created'          => $this->paginate(collect(), $perPage, $page, 'p_created'),
                'amount_changed'   => $this->paginate(collect(), $perPage, $page, 'p_amount'),
                'late_edits'       => $this->paginate(collect(), $perPage, $page, 'p_late'),
                'user_changed'     => $this->paginate(collect(), $perPage, $page, 'p_user'),
            ];
        }

        $yestQ = CrmPaymentSnapshot::whereDate('snapshot_date', $previous)
            ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId));
        $yestCount = (clone $yestQ)->count();

        // Pull both days indexed by crm_payment_id for fast lookup
        $today_rows = (clone $todayQ)->get()->keyBy('crm_payment_id');
        $yest_rows  = (clone $yestQ)->get()->keyBy('crm_payment_id');

        // To avoid false positives across long gaps: a payment is only truly
        // "deleted" if it doesn't appear in ANY snapshot taken on or after $today.
        // Pull all crm_payment_ids that exist in snapshots ≥ $today — these are
        // alive and should NOT be flagged as deleted.
        $aliveIds = CrmPaymentSnapshot::query()
            ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId))
            ->whereDate('snapshot_date', '>=', $today)
            ->pluck('crm_payment_id')
            ->flip();

        $deleted        = collect();
        $created        = collect();
        $amount_changed = collect();
        $late_edits     = collect();
        $user_changed   = collect();

        // 1. Deleted: existed in previous snapshot, gone today AND never seen since.
        foreach ($yest_rows as $id => $y) {
            if ($today_rows->has($id)) continue;
            if ($aliveIds->has($id))   continue; // re-appears in a later snapshot → not deleted
            $deleted->push($y);
        }

        // 2. Created today (could be legit OR could be back-dated fraud)
        foreach ($today_rows as $id => $t) {
            if (!$yest_rows->has($id)) {
                $created->push($t);
            }
        }

        // 3. Changed: existed in both, but payload_hash differs
        foreach ($today_rows as $id => $t) {
            $y = $yest_rows->get($id);
            if (!$y || $y->payload_hash === $t->payload_hash) continue;

            // Amount changed
            if ((string) $y->amount !== (string) $t->amount) {
                $amount_changed->push([
                    'today'         => $t,
                    'yesterday'     => $y,
                    'old_amount'    => (float) $y->amount,
                    'new_amount'    => (float) $t->amount,
                    'delta'         => (float) $t->amount - (float) $y->amount,
                ]);
            }

            // user_update changed (someone touched it)
            if ($y->user_update_id !== $t->user_update_id || $y->date_update?->ne($t->date_update)) {
                $user_changed->push(['today' => $t, 'yesterday' => $y]);
            }
        }

        // 4. Late edits: DATE_UPDATE significantly later than DATE_CREATION (today snapshot)
        $cutoff = Carbon::today()->subDay();
        foreach ($today_rows as $t) {
            if (!$t->date_creation || !$t->date_update) continue;
            $createdAt = Carbon::parse($t->date_creation);
            $updatedAt = Carbon::parse($t->date_update);
            // More than 24h gap AND the update happened in the last 7 days
            if ($updatedAt->diffInHours($createdAt) > 24 && $updatedAt->isAfter($cutoff->subDays(7))) {
                $late_edits->push($t);
            }
        }

        $deletedSorted    = $deleted->sortByDesc('amount')->values();
        $createdSorted    = $created->sortByDesc('date_creation')->values();
        $lateEditsSorted  = $late_edits->sortByDesc('date_update')->values();

        return [
            'date'             => $today,
            'previous_date'    => $previous,
            'has_baseline'     => true,
            'counts'           => [
                'today'          => $todayCount,
                'yesterday'      => $yestCount,
                'deleted'        => $deletedSorted->count(),
                'created'        => $createdSorted->count(),
                'amount_changed' => $amount_changed->count(),
                'late_edits'     => $lateEditsSorted->count(),
                'user_changed'   => $user_changed->count(),
            ],
            'deleted'          => $this->paginate($deletedSorted, $perPage, $page, 'p_deleted'),
            'created'          => $this->paginate($createdSorted, $perPage, $page, 'p_created'),
            'amount_changed'   => $this->paginate($amount_changed, $perPage, $page, 'p_amount'),
            'late_edits'       => $this->paginate($lateEditsSorted, $perPage, $page, 'p_late'),
            'user_changed'     => $this->paginate($user_changed, $perPage, $page, 'p_user'),
        ];
    }

    /**
     * Paginate an in-memory collection using a dedicated page query param so
     * each table on the page can be paginated independently.
     */
    private function paginate(\Illuminate\Support\Collection $items, int $perPage, int $page, string $pageName): LengthAwarePaginator
    {
        $current = max(1, (int) request()->query($pageName, 1));
        $sliced  = $items->forPage($current, $perPage)->values();

        return new LengthAwarePaginator(
            $sliced,
            $items->count(),
            $perPage,
            $current,
            [
                'path'     => Paginator::resolveCurrentPath(),
                'query'    => request()->except($pageName),
                'pageName' => $pageName,
            ]
        );
    }

    /**
     * Pull every snapshot of a single payment, oldest first, and decorate each
     * with a list of fields that differ from the previous snapshot.
     *
     * @return array{
     *   payment_id: int,
     *   versions: \Illuminate\Support\Collection,
     *   not_found: bool,
     * }
     */
    public function paymentHistory(int $paymentId): array
    {
        $versions = CrmPaymentSnapshot::where('crm_payment_id', $paymentId)
            ->orderBy('snapshot_date')
            ->get();

        if ($versions->isEmpty()) {
            return ['payment_id' => $paymentId, 'versions' => collect(), 'not_found' => true];
        }

        // Fields we care about tracking for diffs (keep the list small for clarity)
        $tracked = [
            'amount', 'effective_date',
            'payment_method_id', 'payment_method_name',
            'payment_type_id', 'payment_type_name',
            'student_id', 'reference',
            'user_creation_id', 'user_creation_full_name',
            'user_update_id', 'user_update_full_name',
            'date_creation', 'date_update',
        ];

        $previous = null;
        foreach ($versions as $v) {
            $changes = [];
            if ($previous) {
                foreach ($tracked as $f) {
                    $oldVal = $previous->{$f};
                    $newVal = $v->{$f};
                    // Cast dates / decimals to comparable strings
                    if ($oldVal instanceof Carbon) $oldVal = $oldVal->toDateTimeString();
                    if ($newVal instanceof Carbon) $newVal = $newVal->toDateTimeString();
                    if ((string) $oldVal !== (string) $newVal) {
                        $changes[$f] = ['old' => $oldVal, 'new' => $newVal];
                    }
                }
            }
            $v->setAttribute('change_set', $changes);
            $v->setAttribute('is_first', $previous === null);
            $previous = $v;
        }

        // Detect deletion gap: if the last snapshot is older than today, the
        // payment likely disappeared from the API after that date.
        $last = $versions->last();
        $isDeleted = $last && Carbon::parse($last->snapshot_date)->lt(Carbon::today());

        return [
            'payment_id' => $paymentId,
            'versions'   => $versions,
            'not_found'  => false,
            'is_deleted' => $isDeleted,
            'last_seen'  => $last?->snapshot_date,
        ];
    }
}
