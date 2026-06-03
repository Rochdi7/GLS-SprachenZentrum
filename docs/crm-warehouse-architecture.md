# CRM Local Data Warehouse — Architecture & Implementation Guide

**Project:** GLS Sprachenzentrum — Hostinger Shared Hosting  
**Goal:** Eliminate all live Homeschool API calls from dashboard pages. Move every expensive
operation into scheduled background sync jobs. Dashboards become pure SELECT queries.

---

## The Problem (Before)

Every dashboard timeout traces to the same root cause: **the Homeschool API is called
inside an HTTP request cycle**.

| Dashboard | What it did | Worst case |
|-----------|-------------|------------|
| `group-evolution` | 40 live API calls (payment-allocations, page size 25) | 5–300 seconds |
| `presence-suivi` | PHP CarbonPeriod loop: 700+ days × 30 classes = 21,000 iterations | 2–15 seconds |
| `statistiques` | `JSON_EXTRACT(raw_data,'$.DATE_CREATION')` in WHERE — full table scan | 1–10 seconds |
| `collections aging` | `cursor()` loop, PHP date math per row | 0.5–5 seconds |
| `stats/refresh` button | `Artisan::call('crm:snapshot-payments')` — 120s command inside HTTP | instant timeout |
| Attendance sync | Standard endpoint page size 25 — 320 API requests per sync | rate limit risk |

---

## The Solution (After)

```
HOSTINGER CRON (every minute)
  * * * * *  php artisan schedule:run

      fires at every even hour
              │
              ▼
  php artisan crm:sync-all
  ├── Step 1  homeschool:mirror-core       mirror classes/students/registrations
  ├── Step 2  crm:sync-attendance          bulk endpoint, 500/page, 600ms delay
  ├── Step 3  crm:sync-collections         bulk endpoint, 500/page, 500ms delay
  ├── Step 4  crm:snapshot-payments        daily fraud detection snapshot
  ├── Step 5  crm:sync-payment-allocations NEW — bulk 500/page, mirrors allocations
  ├── Step 6  crm:build-presence-summary   NEW — SQL aggregation, replaces PHP loops
  ├── Step 7  crm:build-group-evolution    NEW — reads local tables, zero API calls
  └── Step 8  crm:daily-report             CEO morning report

              writes to
              │
              ▼
  LOCAL MySQL (precomputed tables)
  ├── crm_payment_allocations       NEW mirror table
  ├── crm_presence_summary          NEW aggregate table
  ├── crm_group_evolution_snapshot  NEW aggregate table
  └── crm_sync_log                  NEW progress tracking

              read by
              │
              ▼
  Dashboards  ←  pure SELECT queries, < 200ms
```

**Three rules that permanently prevent timeouts:**

1. `HomeschoolClient::get()` is only called from `app/Console/Commands/`. Never from controllers or services used by dashboards.
2. Heavy computation (CarbonPeriod, cursor loops, aggregations) lives in build commands, not in service methods called during requests.
3. `Artisan::call()` is never used inside controllers. Refresh buttons clear cache only.

---

## What We Changed (File by File)

### New Migrations

| File | What it does |
|------|-------------|
| `2026_06_04_000001_normalize_crm_columns.php` | Adds `date_creation`, `session_reference` columns to `crm_attendance`; `date_creation`, `status_label` to `crm_registrations`; `date_creation_date` to `crm_payment_snapshots`. Replaces `JSON_EXTRACT` in WHERE with indexed columns. |
| `2026_06_04_000002_create_crm_payment_allocations_table.php` | New mirror table for `/bulk/payment-allocations`. Indexed on `(crm_store_id, allocation_month)`, `(class_id, allocation_month)`, `(student_id, class_id)`. |
| `2026_06_04_000003_create_crm_group_evolution_snapshot_table.php` | Precomputed group evolution buckets (debuts/ajouts/quittants/changements/actifs) per class per date range. GroupEvolutionService reads this instead of calling the API. |
| `2026_06_04_000004_create_crm_presence_summary_table.php` | Monthly per-class attendance aggregates. Replaces CarbonPeriod PHP loops. |
| `2026_06_04_000005_create_crm_sync_log_table.php` | Tracks step progress, status, errors. Enables `--resume` flag on `crm:sync-all`. |

### New Models

| File | Table |
|------|-------|
| `app/Models/CrmPaymentAllocation.php` | `crm_payment_allocations` |
| `app/Models/CrmGroupEvolutionSnapshot.php` | `crm_group_evolution_snapshot` |
| `app/Models/CrmPresenceSummary.php` | `crm_presence_summary` |
| `app/Models/CrmSyncLog.php` | `crm_sync_log` |

### New Commands

| Command | File | What it does |
|---------|------|-------------|
| `crm:sync-all` | `CrmSyncAllCommand.php` | Master orchestrator. Runs all sync steps in order. Supports `--resume`, `--from=step`, `--dry-run`. |
| `crm:sync-payment-allocations` | `SyncCrmPaymentAllocationsCommand.php` | Mirrors `/bulk/payment-allocations` (500/page) into `crm_payment_allocations`. 1s delay between pages, 2s between centers. Exponential backoff on 429. |
| `crm:build-group-evolution` | `BuildGroupEvolutionCommand.php` | Computes group evolution snapshot entirely from local tables. Zero API calls. Writes to `crm_group_evolution_snapshot`. |
| `crm:build-presence-summary` | `BuildPresenceSummaryCommand.php` | Single SQL GROUP BY aggregation over `crm_attendance`. Replaces 21,000-iteration PHP loop. Writes to `crm_presence_summary`. |
| `crm:backfill-columns` | `BackfillNormalizedColumnsCommand.php` | One-time: populates new normalized columns from existing `raw_data` JSON. Run once after migration. |

### Fixed Commands

| Command | Change |
|---------|--------|
| `crm:sync-attendance` | `PAGE_SIZE` changed from `25` → `500`. Endpoint changed from standard to `/bulk/session-presence`. 20× fewer API requests. |
| `crm:sync-registrations` | Upsert now also writes `date_creation` and `status_label` normalized columns. |
| `crm:snapshot-payments` | Upsert now also writes `date_creation_date` normalized column. |

### Fixed Services

| Service | Change |
|---------|--------|
| `GroupEvolutionService` | `build()` now reads from `crm_group_evolution_snapshot`. `fetchAllocations()` deleted. No more live API calls during page load. |
| `PresenceSuiviService` | `allTimeTotals()` reads from `crm_presence_summary` (one SELECT). `groupDetails()` uses normalized `date_creation` column instead of `JSON_EXTRACT` in WHERE. `globalFraud()` uses normalized column. |

### Fixed Controllers

| Controller | Change |
|------------|--------|
| `StatsController::registrationsByCenter()` | `JSON_EXTRACT(raw_data,'$.DATE_CREATION')` replaced with `date_creation` indexed column. |
| `StatsController::periodComparison()` | Same fix — normalized column. |
| `StatsController::refresh()` | `Artisan::call('crm:snapshot-payments')` removed. Now only does `Cache::flush()`. |

### Updated Kernel.php

Old schedule (8 individual commands at different times) replaced with:

```php
// Every 2 hours — master sync
$schedule->command('crm:sync-all')
    ->cron('0 */2 * * *')
    ->timezone('Africa/Casablanca')
    ->withoutOverlapping(120)
    ->runInBackground();

// CEO report after morning sync
$schedule->command('crm:daily-report')
    ->dailyAt('07:00')
    ->timezone('Africa/Casablanca')
    ->withoutOverlapping();
```

---

## Deployment Commands (Run in This Order)

### Phase 1 — Run once on the server

```bash
# 1. Run all new migrations
php artisan migrate

# 2. Backfill normalized columns from existing raw_data JSON (safe to re-run)
php artisan crm:backfill-columns

# 3. First manual sync — populate all new tables
php artisan crm:sync-all

# 4. Verify tables have data
# Run in MySQL:
#   SELECT COUNT(*) FROM crm_payment_allocations;
#   SELECT COUNT(*) FROM crm_group_evolution_snapshot;
#   SELECT COUNT(*) FROM crm_presence_summary;

# 5. Test dashboards load fast
# Visit /backoffice/crm/group-evolution → should be < 1 second
# Visit /backoffice/crm/presence-suivi  → should be < 1 second
# Visit /backoffice/crm/statistiques    → should be < 1 second
```

### Phase 2 — Hostinger Cron (set once, never touch again)

In **Hostinger → Hosting → Cron Jobs**, add exactly ONE entry:

```
* * * * *   /usr/local/bin/php /home/USERNAME/domains/DOMAIN/artisan schedule:run >> /dev/null 2>&1
```

Replace `USERNAME` and `DOMAIN` with your actual values.

Verify it works:
```bash
php artisan schedule:list
# Should show: crm:sync-all   Every 2 hours
```

### Phase 3 — Monitor first automatic run

Check the log after the next even hour:
```bash
tail -f storage/logs/crm-sync-all.log
```

---

## Manual Commands Reference

```bash
# Run full sync (normal)
php artisan crm:sync-all

# Dry run — shows what would run without executing anything
php artisan crm:sync-all --dry-run

# Resume after a failure (skips already-done steps from today)
php artisan crm:sync-all --resume

# Restart from a specific step (useful when one step fails)
php artisan crm:sync-all --from=allocations
php artisan crm:sync-all --from=group_evolution
php artisan crm:sync-all --from=presence_summary

# Check sync step status
# In MySQL: SELECT step, status, completed_at, last_error FROM crm_sync_log;

# Sync only one store for testing
php artisan crm:sync-payment-allocations --store=1234 --months=1
php artisan crm:build-group-evolution --store=1234
php artisan crm:build-presence-summary --all --months=1

# One-time backfill of normalized columns (run after migration)
php artisan crm:backfill-columns
```

---

## Rate Limit Budget

With bulk endpoints (500/page) and 1-second delays:

| Step | Endpoint | Rows/center | Pages | Requests | Time |
|------|----------|-------------|-------|----------|------|
| attendance | bulk/session-presence | ~4,000 | 8 | 8 | ~8s |
| collections | bulk/payment-collection | ~500 | 1 | 1 | ~1s |
| payments snapshot | payments (standard 25/pg) | ~2,000 | 80 | 80 | ~80s |
| allocations | bulk/payment-allocations | ~3,000 | 6 | 6 | ~6s |
| **Per center** | | | | **~95 req** | **~95s** |
| **4 centers** | +2s between centers | | | **~384 req** | **~13 min** |

Average rate: **~30 req/min** — safely under the 60/min API limit.

---

## Expected Performance After Deployment

| Dashboard | Before | After |
|-----------|--------|-------|
| `group-evolution` | 5–300 seconds | < 80ms |
| `presence-suivi` | 2–8 seconds | < 100ms |
| `presence-suivi/details` | 3–15 seconds | < 150ms |
| `statistiques` | 1–10 seconds | < 80ms |
| `collections aging` | 0.5–5 seconds | < 30ms |
| `stats/refresh button` | 30–120s timeout | instant |

---

## If a Dashboard Shows "No Data"

This means `crm:sync-all` has not run yet or failed partway.

```bash
# Check sync log
# MySQL: SELECT step, status, last_error FROM crm_sync_log ORDER BY id;

# Re-run the failed step
php artisan crm:sync-all --from=FAILED_STEP_NAME

# Or re-run everything
php artisan crm:sync-all
```

The dashboard will show a "Data pending — run crm:sync-all" message instead of hanging
or timing out. This is intentional — an empty page is always better than a 300-second timeout.
