<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backfills registration_id on payment snapshots that were captured without one.
 *
 * WHY ORPHANS EXIST:
 * CrmSnapshotPaymentsCommand resolves PAYMENT_ID → REGISTRATION_ID through the
 * allocations it fetches for its own window (--months). Any payment whose
 * allocation fell outside that window — or was fetched on a night the
 * allocations call failed — lands with registration_id = NULL.
 *
 * WHY IT MATTERS:
 * The Vue 360 join has to guess where a NULL-registration payment belongs. A
 * guess per student with several inscriptions is how the same payment showed
 * up under two inscriptions and inflated "Total encaissé" (observed live:
 * 28,308 rows for 23,725 distinct payments).
 *
 * WHAT THIS DOES:
 * crm_payment_allocations mirrors the same bulk endpoint with a much wider
 * window (--all), and each row's raw_data carries PAYMENT_ID while
 * (student_id, class_id) identifies exactly one registration through the
 * student_class_unique key on crm_registrations. So the true link is usually
 * already sitting in the local mirror — this command just applies it:
 *
 *   allocations.raw_data->PAYMENT_ID   →  which payment
 *   (student_id, class_id)             →  which registration
 *   ⇒ UPDATE snapshots SET registration_id WHERE crm_payment_id = ... AND NULL
 *
 * Idempotent, touches only NULL rows, safe to run any time. Scheduled after
 * the nightly snapshot so new orphans are repaired the same night.
 */
class CrmResolvePaymentLinksCommand extends Command
{
    protected $signature = 'crm:resolve-payment-links {--dry-run : Report what would be fixed without writing}';

    protected $description = 'Backfill registration_id on orphan payment snapshots using the local allocations mirror.';

    public function handle(): int
    {
        $orphanIds = DB::table('crm_payment_snapshots')
            ->whereNull('registration_id')
            ->distinct()
            ->pluck('crm_payment_id');

        $this->info("Orphan payments (registration_id IS NULL): {$orphanIds->count()}");

        if ($orphanIds->isEmpty()) {
            return self::SUCCESS;
        }

        // registration lookup: "studentId:classId" → registration crm_id.
        // Unique by construction (student_class_unique index).
        $regByPair = DB::table('crm_registrations')
            ->whereNotNull('crm_id')
            ->get(['crm_id', 'crm_student_id', 'crm_class_id'])
            ->keyBy(fn ($r) => $r->crm_student_id.':'.$r->crm_class_id)
            ->map(fn ($r) => (int) $r->crm_id);

        $resolved = 0;
        $ambiguous = 0;
        $unresolvable = 0;

        // Walk the orphans in chunks; each UPDATE targets one payment's rows,
        // so no statement ever locks a meaningful slice of the table.
        foreach ($orphanIds->chunk(500) as $chunk) {
            $allocations = DB::table('crm_payment_allocations')
                ->whereIn(DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.PAYMENT_ID')) AS UNSIGNED)"), $chunk->all())
                ->get(['student_id', 'class_id', 'raw_data']);

            // payment → set of candidate registrations. A payment allocated
            // across several classes (rare, but real: split invoices) is left
            // alone rather than guessed at.
            $candidates = [];
            foreach ($allocations as $a) {
                $raw = json_decode($a->raw_data, true);
                $pid = (int) ($raw['PAYMENT_ID'] ?? 0);
                $reg = $regByPair[$a->student_id.':'.$a->class_id] ?? null;
                if ($pid && $reg) {
                    $candidates[$pid][$reg] = true;
                }
            }

            foreach ($candidates as $pid => $regs) {
                if (count($regs) > 1) {
                    $ambiguous++;

                    continue;
                }

                $regId = array_key_first($regs);

                if (! $this->option('dry-run')) {
                    DB::table('crm_payment_snapshots')
                        ->where('crm_payment_id', $pid)
                        ->whereNull('registration_id')
                        ->update(['registration_id' => $regId]);
                }
                $resolved++;
            }

            $unresolvable += $chunk->count() - count($candidates);
        }

        $mode = $this->option('dry-run') ? '[DRY RUN] ' : '';
        $this->info("{$mode}resolved: {$resolved} | ambiguous (multi-class, skipped): {$ambiguous} | no allocation found: {$unresolvable}");

        return self::SUCCESS;
    }
}
