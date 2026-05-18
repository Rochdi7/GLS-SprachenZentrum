<?php

namespace App\Services\Crm\Stats;

use App\Models\CrmPaymentSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
     *   deleted: \Illuminate\Support\Collection,
     *   created: \Illuminate\Support\Collection,
     *   amount_changed: \Illuminate\Support\Collection,
     *   late_edits: \Illuminate\Support\Collection,
     *   user_changed: \Illuminate\Support\Collection,
     * }
     */
    public function dailyDiff(?string $date = null, ?int $strStoreId = null): array
    {
        $today    = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();
        $yesterday = Carbon::parse($today)->subDay()->toDateString();

        $todayQ = CrmPaymentSnapshot::whereDate('snapshot_date', $today)
            ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId));
        $yestQ  = CrmPaymentSnapshot::whereDate('snapshot_date', $yesterday)
            ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId));

        $todayCount = (clone $todayQ)->count();
        $yestCount  = (clone $yestQ)->count();

        // If yesterday has no snapshot we can only show "today's state" — no diff possible.
        if ($yestCount === 0) {
            return [
                'date'             => $today,
                'previous_date'    => $yesterday,
                'has_baseline'     => false,
                'counts'           => ['today' => $todayCount, 'yesterday' => 0],
                'deleted'          => collect(),
                'created'          => collect(),
                'amount_changed'   => collect(),
                'late_edits'       => collect(),
                'user_changed'     => collect(),
            ];
        }

        // Pull both days indexed by crm_payment_id for fast lookup
        $today_rows = (clone $todayQ)->get()->keyBy('crm_payment_id');
        $yest_rows  = (clone $yestQ)->get()->keyBy('crm_payment_id');

        $deleted        = collect();
        $created        = collect();
        $amount_changed = collect();
        $late_edits     = collect();
        $user_changed   = collect();

        // 1. Deleted: existed yesterday, gone today (red flag for cash fraud)
        foreach ($yest_rows as $id => $y) {
            if (!$today_rows->has($id)) {
                $deleted->push($y);
            }
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

        return [
            'date'             => $today,
            'previous_date'    => $yesterday,
            'has_baseline'     => true,
            'counts'           => [
                'today'          => $todayCount,
                'yesterday'      => $yestCount,
                'deleted'        => $deleted->count(),
                'created'        => $created->count(),
                'amount_changed' => $amount_changed->count(),
                'late_edits'     => $late_edits->count(),
                'user_changed'   => $user_changed->count(),
            ],
            'deleted'          => $deleted->sortByDesc('amount')->values(),
            'created'          => $created->sortByDesc('date_creation')->values(),
            'amount_changed'   => $amount_changed,
            'late_edits'       => $late_edits->sortByDesc('date_update')->values(),
            'user_changed'     => $user_changed,
        ];
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
