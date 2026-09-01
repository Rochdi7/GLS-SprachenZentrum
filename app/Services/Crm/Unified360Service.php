<?php

namespace App\Services\Crm;

use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

/**
 * Vue 360 — one flat row per PAYMENT, joined to its inscription, its student,
 * its group/class and its centre.
 *
 * Reads ONLY the local CRM mirror tables (no Wimschool API call), so the page
 * and the Excel export stay fast even on tens of thousands of rows:
 *
 *   crm_registrations r   — the inscription (student x class), holds the status
 *     JOIN crm_students  s  ON s.crm_id = r.crm_student_id
 *     LEFT JOIN crm_classes c ON c.crm_id = r.crm_class_id
 *     LEFT JOIN <latest payment snapshot> p ON p.student_id = s.crm_id
 *            AND (p.registration_id = r.crm_id OR p.registration_id IS NULL)
 *
 * The LEFT JOIN is what makes an inscription with zero payments still appear
 * (with empty payment columns) instead of silently vanishing.
 *
 * crm_payment_snapshots holds one row per (payment, snapshot_date), so we must
 * restrict to the LATEST snapshot per payment or every payment would be
 * duplicated once per day it was synced. See latestSnapshotSub().
 */
class Unified360Service
{
    /** Columns projected by the query, in display/export order. */
    public const COLUMNS = [
        'student_ref' => 'Réf. étudiant',
        'student_name' => 'Étudiant',
        'student_phone' => 'Téléphone',
        'student_email' => 'Email',
        'site_name' => 'Centre',
        'class_name' => 'Groupe',
        'class_level' => 'Niveau',
        'registration_id' => 'ID Inscription',
        'registration_date' => 'Date inscription',
        'registration_status' => 'Statut inscription',
        'payment_reference' => 'Réf. paiement',
        'payment_amount' => 'Montant',
        'payment_rest' => 'Reste à payer',
        'payment_date' => 'Date paiement',
        'payment_due_date' => 'Date échéance',
        'payment_method' => 'Méthode',
        'payment_type' => 'Type',
        'payment_created_by' => 'Encaissé par',
    ];

    /** Columns of the per-inscription summary sheet. */
    public const SUMMARY_COLUMNS = [
        'student_ref' => 'Réf. étudiant',
        'student_name' => 'Étudiant',
        'student_phone' => 'Téléphone',
        'site_name' => 'Centre',
        'class_name' => 'Groupe',
        'class_level' => 'Niveau',
        'registration_id' => 'ID Inscription',
        'registration_date' => 'Date inscription',
        'registration_status' => 'Statut inscription',
        'nb_paiements' => 'Nb paiements',
        'total_paye' => 'Total payé',
        'total_reste' => 'Reste à payer',
        'premier_paiement' => 'Premier paiement',
        'dernier_paiement' => 'Dernier paiement',
    ];

    /**
     * Build the joined query. Filters are applied here (not in PHP) so that
     * pagination and the export walk the same, index-backed SQL.
     *
     * @param  array<string,mixed>  $f  Filter bag, straight from the request.
     */
    public function query(array $f = []): Builder
    {
        $refPath = "JSON_UNQUOTE(JSON_EXTRACT(s.raw_data, '$.REFERENCE'))";

        $q = DB::table('crm_registrations as r')
            ->join('crm_students as s', 's.crm_id', '=', 'r.crm_student_id')
            ->leftJoin('crm_classes as c', 'c.crm_id', '=', 'r.crm_class_id')
            ->leftJoin('sites as site', 'site.crm_store_id', '=', 'r.crm_store_id')
            ->leftJoinSub($this->latestSnapshotSub(), 'p', function ($join) {
                // Prefer the explicit registration link; fall back to the
                // student link for payments the allocation sync never resolved
                // to a registration (registration_id stays NULL for those).
                $join->on('p.student_id', '=', 's.crm_id')
                    ->where(function ($w) {
                        $w->whereColumn('p.registration_id', '=', 'r.crm_id')
                            ->orWhereNull('p.registration_id');
                    });
            })
            ->select([
                DB::raw("COALESCE({$refPath}, s.crm_id) as student_ref"),
                DB::raw("TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''))) as student_name"),
                's.phone as student_phone',
                's.email as student_email',
                's.crm_id as student_id',
                DB::raw("COALESCE(site.name, CONCAT('Store ', r.crm_store_id)) as site_name"),
                'c.name as class_name',
                'c.level as class_level',
                'c.crm_id as class_id',
                'r.crm_id as registration_id',
                'r.date_creation as registration_date',
                DB::raw('COALESCE(r.status_label, r.status) as registration_status'),
                'p.reference as payment_reference',
                'p.amount as payment_amount',
                'p.rest_amount as payment_rest',
                DB::raw('COALESCE(p.effective_date, p.date_creation_date) as payment_date'),
                'p.due_date as payment_due_date',
                'p.payment_method_name as payment_method',
                'p.payment_type_name as payment_type',
                'p.user_creation_full_name as payment_created_by',
                'p.crm_payment_id',
            ]);

        return $this->applyFilters($q, $f);
    }

    /**
     * One row per payment, taking only its most recent snapshot.
     *
     * crm_payment_snapshots is append-only per night, so a payment synced for
     * 3 months carries ~90 rows. Without this the same payment appears once
     * per snapshot_date and every total is inflated.
     *
     * PERFORMANCE — this is the hot spot of the whole page.
     *
     * Deriving "latest" at query time is what caused the nginx 504. Both
     * natural formulations degrade with snapshot HISTORY rather than with the
     * number of payments, so the page got slower every night the sync ran:
     *
     *   - correlated subquery (WHERE snapshot_date = (SELECT MAX(...)))
     *     → MariaDB plans it as DEPENDENT SUBQUERY, re-running the MAX()
     *       for every candidate row.
     *   - grouped derived table joined on (payment, max date)
     *     → MariaDB plans it as LATERAL DERIVED, re-evaluated per row.
     *
     * Measured on 420k snapshot rows across 28 dates, both left a single page
     * load in the 5-19s range depending on the filter.
     *
     * So the flag is persisted instead: CrmSnapshotPaymentsCommand maintains
     * is_latest after each nightly sync, and reads become an indexed equality
     * lookup whose cost stays flat as history accumulates.
     */
    private function latestSnapshotSub(): Builder
    {
        return DB::table('crm_payment_snapshots as ps')
            ->where('ps.is_latest', true)
            ->select([
                'ps.crm_payment_id', 'ps.student_id', 'ps.registration_id',
                'ps.reference', 'ps.amount', 'ps.rest_amount',
                'ps.effective_date', 'ps.date_creation_date', 'ps.due_date',
                'ps.payment_method_name', 'ps.payment_type_name',
                'ps.user_creation_full_name',
            ]);
    }

    /**
     * @param  array<string,mixed>  $f
     * @param  bool  $sorted  false when the caller supplies its own ORDER BY
     */
    private function applyFilters(Builder $q, array $f, bool $sorted = true): Builder
    {
        if (! empty($f['strStoreId'])) {
            $q->where('r.crm_store_id', (int) $f['strStoreId']);
        }

        if (! empty($f['search'])) {
            $term = '%'.trim($f['search']).'%';
            $q->where(function ($w) use ($term) {
                $w->where('s.phone', 'like', $term)
                    ->orWhere('s.email', 'like', $term)
                    ->orWhere('p.reference', 'like', $term)
                    ->orWhereRaw("CONCAT(COALESCE(s.first_name,''), ' ', COALESCE(s.last_name,'')) like ?", [$term]);
            });
        }

        if (! empty($f['classId'])) {
            $q->where('r.crm_class_id', (int) $f['classId']);
        }

        if (! empty($f['studentId'])) {
            $q->where('r.crm_student_id', (int) $f['studentId']);
        }

        if (! empty($f['registrationStatus'])) {
            $q->whereRaw('COALESCE(r.status_label, r.status) = ?', [$f['registrationStatus']]);
        }

        if (! empty($f['paymentMethod'])) {
            $q->where('p.payment_method_name', $f['paymentMethod']);
        }

        if (! empty($f['paymentType'])) {
            $q->where('p.payment_type_name', $f['paymentType']);
        }

        // Payment date range — on the effective date, falling back to the
        // creation date for rows the CRM left without an effective date.
        if (! empty($f['startDate'])) {
            $q->whereRaw('COALESCE(p.effective_date, p.date_creation_date) >= ?', [$f['startDate']]);
        }
        if (! empty($f['endDate'])) {
            $q->whereRaw('COALESCE(p.effective_date, p.date_creation_date) <= ?', [$f['endDate']]);
        }

        // Inscription date range — independent of the payment range.
        if (! empty($f['regStartDate'])) {
            $q->whereDate('r.date_creation', '>=', $f['regStartDate']);
        }
        if (! empty($f['regEndDate'])) {
            $q->whereDate('r.date_creation', '<=', $f['regEndDate']);
        }

        // "Sans paiement" — inscriptions where the LEFT JOIN found nothing.
        if (($f['paymentPresence'] ?? '') === 'without') {
            $q->whereNull('p.crm_payment_id');
        } elseif (($f['paymentPresence'] ?? '') === 'with') {
            $q->whereNotNull('p.crm_payment_id');
        }

        // Unpaid balance only.
        if (! empty($f['unpaidOnly'])) {
            $q->where('p.rest_amount', '>', 0);
        }

        if (! $sorted) {
            return $q;
        }

        return $q->orderBy('s.last_name')
            ->orderBy('s.first_name')
            ->orderBy('r.crm_id')
            ->orderByRaw('COALESCE(p.effective_date, p.date_creation_date)');
    }

    /**
     * A page of the 360 table.
     *
     * PERFORMANCE — why this is not just query()->paginate().
     *
     * The display order is by student name, but the row count is driven by
     * PAYMENTS (one row per payment). Sorting the fully-joined set means
     * MariaDB must materialise and filesort every matching row before it can
     * return the 50 the page shows — the EXPLAIN reads "Using temporary;
     * Using filesort" over the whole join. Measured on 420k snapshot rows
     * that was ~19s for a single page, versus ~0.18s to sort and slice the
     * inscriptions alone. Adding an index on (last_name, first_name) does not
     * help, because the sort happens after the LEFT JOIN has already expanded
     * the rows.
     *
     * So: page over INSCRIPTIONS (a stable, indexable unit), then join the
     * payments for just that page. The user still sees whole inscriptions,
     * never an inscription split across two pages — which is also the more
     * correct reading of "all payments of this inscription".
     *
     * @param  array<string,mixed>  $f
     */
    public function page(array $f, int $perPage, int $currentPage, string $pageName = 'page'): LengthAwarePaginator
    {
        // 1) Which inscriptions are on this page — sorted and sliced cheaply,
        //    with only the joins the filters actually need.
        $ids = $this->registrationIdQuery($f)
            ->forPage($currentPage, $perPage)
            ->pluck('crm_id')
            ->all();

        // Counted through a subquery: registrationIdQuery() is grouped, so a
        // plain ->count() would return the size of the FIRST group (always 1)
        // instead of the number of groups, silently capping the paginator at
        // one page.
        $total = DB::query()->fromSub($this->registrationIdQuery($f), 'ids')->count();

        // 2) Full detail for just those inscriptions.
        $rows = $ids === []
            ? collect()
            : $this->query($f)->whereIn('r.crm_id', $ids)->get();

        return new LengthAwarePaginator($rows, $total, $perPage, $currentPage, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * The inscriptions matching the filters, in display order — the driving
     * set for pagination. Joins crm_students for the sort, and the payment
     * snapshot only when a payment-level filter demands it, so the common
     * unfiltered case stays a cheap two-table sort.
     *
     * @param  array<string,mixed>  $f
     */
    private function registrationIdQuery(array $f): Builder
    {
        $needsPayments = ! empty($f['paymentMethod'])
            || ! empty($f['paymentType'])
            || ! empty($f['startDate'])
            || ! empty($f['endDate'])
            || ! empty($f['unpaidOnly'])
            || ! empty($f['search'])
            || ($f['paymentPresence'] ?? '') !== '';

        $q = DB::table('crm_registrations as r')
            ->join('crm_students as s', 's.crm_id', '=', 'r.crm_student_id');

        if ($needsPayments) {
            $q->leftJoinSub($this->latestSnapshotSub(), 'p', function ($join) {
                $join->on('p.student_id', '=', 's.crm_id')
                    ->where(function ($w) {
                        $w->whereColumn('p.registration_id', '=', 'r.crm_id')
                            ->orWhereNull('p.registration_id');
                    });
            });
        }

        $this->applyFilters($q, $f, sorted: false);

        // One entry per inscription regardless of how many payments matched.
        //
        // GROUP BY rather than DISTINCT: the payment join can multiply a row,
        // but ordering by the student name means the sort columns are not in
        // the select list — and under ONLY_FULL_GROUP_BY (default on MySQL 8,
        // which production runs) "SELECT DISTINCT r.crm_id ... ORDER BY
        // s.last_name" is rejected outright with error 3065. Grouping on
        // r.crm_id is accepted because the sort columns are functionally
        // dependent on it: one inscription belongs to exactly one student.
        return $q->select('r.crm_id')
            ->groupBy('r.crm_id', 's.last_name', 's.first_name')
            ->orderBy('s.last_name')
            ->orderBy('s.first_name')
            ->orderBy('r.crm_id');
    }

    /**
     * Aggregate totals for the KPI strip. Runs on the same filtered query so
     * the numbers always match what the table is showing.
     *
     * @param  array<string,mixed>  $f
     * @return array{rows:int,students:int,registrations:int,payments:int,total:float,rest:float}
     */
    public function totals(array $f = []): array
    {
        $row = DB::query()->fromSub($this->query($f), 't')->selectRaw('
            COUNT(*)                                as rows_count,
            COUNT(DISTINCT t.student_id)            as students,
            COUNT(DISTINCT t.registration_id)       as registrations,
            COUNT(DISTINCT t.crm_payment_id)        as payments,
            COALESCE(SUM(t.payment_amount), 0)      as total_amount,
            COALESCE(SUM(t.payment_rest), 0)        as total_rest
        ')->first();

        return [
            'rows' => (int) ($row->rows_count ?? 0),
            'students' => (int) ($row->students ?? 0),
            'registrations' => (int) ($row->registrations ?? 0),
            'payments' => (int) ($row->payments ?? 0),
            'total' => (float) ($row->total_amount ?? 0),
            'rest' => (float) ($row->total_rest ?? 0),
        ];
    }

    /** Distinct values for the filter dropdowns, read from the mirror itself. */
    public function filterOptions(?int $strStoreId = null): array
    {
        $classes = DB::table('crm_classes as c')
            ->when($strStoreId, fn ($q) => $q->whereIn('c.crm_id', function ($sub) use ($strStoreId) {
                $sub->select('crm_class_id')->from('crm_registrations')->where('crm_store_id', $strStoreId);
            }))
            ->whereNotNull('c.name')
            ->orderBy('c.name')
            ->pluck('c.name', 'c.crm_id');

        return [
            'classes' => $classes,
            'statuses' => DB::table('crm_registrations')
                ->when($strStoreId, fn ($q) => $q->where('crm_store_id', $strStoreId))
                ->selectRaw('DISTINCT COALESCE(status_label, status) as v')
                ->whereRaw('COALESCE(status_label, status) IS NOT NULL')
                ->orderBy('v')->pluck('v'),
            'methods' => DB::table('crm_payment_snapshots')
                ->selectRaw('DISTINCT payment_method_name as v')
                ->whereNotNull('payment_method_name')
                ->orderBy('v')->pluck('v'),
            'types' => DB::table('crm_payment_snapshots')
                ->selectRaw('DISTINCT payment_type_name as v')
                ->whereNotNull('payment_type_name')
                ->orderBy('v')->pluck('v'),
        ];
    }

    /**
     * Per-inscription summary — one row per inscription with its payments
     * aggregated. Feeds the second sheet of the Excel export.
     *
     * @param  array<string,mixed>  $f
     */
    public function summaryQuery(array $f = []): Builder
    {
        return DB::query()->fromSub($this->query($f), 't')->selectRaw('
            t.student_ref, t.student_name, t.student_phone, t.student_email,
            t.site_name, t.class_name, t.class_level,
            t.registration_id, t.registration_date, t.registration_status,
            COUNT(t.crm_payment_id)                   as nb_paiements,
            COALESCE(SUM(t.payment_amount), 0)        as total_paye,
            COALESCE(SUM(t.payment_rest), 0)          as total_reste,
            MIN(t.payment_date)                       as premier_paiement,
            MAX(t.payment_date)                       as dernier_paiement
        ')->groupBy(
            't.student_ref', 't.student_name', 't.student_phone', 't.student_email',
            't.site_name', 't.class_name', 't.class_level',
            't.registration_id', 't.registration_date', 't.registration_status'
        )->orderBy('t.student_name');
    }
}
