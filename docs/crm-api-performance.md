# CRM â€” Performance & Architecture Guide

> **How to read timing columns**
> - **Cold** = first request, cache empty, full DB/API work
> - **Warm** = subsequent request, served from Laravel `Cache::remember()`
> - **TTL** = how long the cache key lives before expiring

---

## 1. Quick Reference Table

| Route | What it loads | Cold est. | Warm est. | TTL | Data source |
|---|---|---|---|---|---|
| `GET /crm/presence-suivi` | Calendar + fraud + employee stats + all-time totals | **2â€“6 s** | ~50 ms | 10 min | Local DB (crm_attendance, crm_classes) |
| `GET /crm/presence-suivi/details` | All sessions per group (saisie or draft), all time | **3â€“8 s** | ~40 ms | 10 min | Local DB |
| `GET /crm/statistiques` | Encaissement + recouvrement + inscriptions charts | **0.5â€“2 s** | ~30 ms | 10 min | Local DB (snapshots, registrations) |
| `GET /crm/collections` | KPIs + aging buckets + top debtors + upcoming dues | **1â€“3 s** | ~30 ms | 15 min | Local DB (crm_collection_rows) |
| `GET /crm/insights/group-evolution` | DÃ©but/Ajout/Quittant/Changement per group | **5â€“15 s** | ~60 ms | 60 min | Local DB + **live Wimschool API** (paginated) |
| `GET /crm/groups/classes` | All classes list with student counts | **1â€“4 s** | ~40 ms | varies | Live Wimschool API |
| `GET /crm/groups/level-sessions` | Level + session breakdown | **1â€“4 s** | ~40 ms | varies | Live Wimschool API |
| `GET /crm/payments` | Payment list | **1â€“3 s** | ~30 ms | varies | Live Wimschool API |
| `GET /crm/payment-checks` | Cheque payments | **1â€“3 s** | ~30 ms | varies | Live Wimschool API |
| `GET /crm/payment-collection` | Collection list | **1â€“3 s** | ~30 ms | varies | Live Wimschool API |
| `GET /crm/insights/reconciliation` | Reconciliation dashboard | **1â€“4 s** | ~30 ms | varies | Live Wimschool API |
| `GET /crm/insights/retention` | Retention metrics | **1â€“4 s** | ~30 ms | varies | Live Wimschool API |
| `GET /crm/insights/forecast` | Revenue forecast | **1â€“4 s** | ~30 ms | varies | Live Wimschool API |
| `GET /crm/insights/payment-activity` | Payment activity timeline | **1â€“4 s** | ~30 ms | varies | Live Wimschool API |
| `GET /crm/insights/advances` | Advance payments | **1â€“3 s** | ~30 ms | varies | Live Wimschool API |
| `GET /crm/reports` | Daily CEO report index | **<0.5 s** | ~20 ms | N/A | Local DB only |
| `POST /crm/statistiques/refresh` | Bust cache + re-run snapshot | **20â€“40 s** | â€” | â€” | Runs `crm:snapshot-payments` artisan |
| `POST /crm/collections/refresh` | Bust 5 collection cache keys | **<0.2 s** | â€” | â€” | Cache::forget only |

---

## 2. Endpoint Deep Dives

---

### 2.1 `GET /crm/presence-suivi` â€” Suivi des PrÃ©sences

**Controller:** `PresenceSuiviController::index()`
**Service:** `PresenceSuiviService`

#### What it does
Renders a monthly attendance calendar for a center.
The controller calls **4 service methods in sequence**:

```
buildMonth()       â† calendar grid + fraud list per class
globalFraud()      â† cross-center fraud summary for the month
allTimeTotals()    â† total saisie vs draft since forever
employeeStats()    â† saisie operator leaderboard
```

#### Queries inside `buildMonth()` (the heaviest)

| # | Query | Why it's slow |
|---|---|---|
| 1 | `CrmClass::where(site_id)` | Load all classes for center |
| 2 | `CrmAttendance::whereIn(crmIds)->whereBetween(date, month)` | **Full month attendance rows** â€” one row per student per session |
| 3 | `CrmAttendance::whereIn(crmIds)->where(date >= -90 days)` â€” GROUP BY class+DOW | 90-day lookback to infer session schedule (expected weekdays) |

Then in PHP, for every day of the month Ã— every class â†’ O(days Ã— classes) loop to build dots and detect draft sessions.

#### Queries inside `allTimeTotals()` (second heaviest)

| # | Query | Why it's slow |
|---|---|---|
| 1 | `CrmClass::where(site_id)` | |
| 2 | `CrmAttendance::whereIn(crmIds)->where(date <= today)` GROUP BY class+date | **ALL attendance ever** â€” can be 50k+ rows |
| 3 | `CrmAttendance::whereIn(crmIds)->where(date >= -90d)` GROUP BY DOW | Same 90-day DOW inference |
| 4 | `CrmAttendance::min('date')` | Find earliest ever attendance to start the date-walk |
| PHP loop | Walk every date from `earliest` to today Ã— every class | Can walk **1+ years Ã— 50+ classes** |

#### Timing breakdown (cold)

```
buildMonth()      ~1â€“2 s   (month attendance + DOW inference)
globalFraud()     ~0.5 s   (JOIN crm_attendance + crm_classes for whole month)
allTimeTotals()   ~1â€“3 s   (ALL-TIME scan + PHP date loop)
employeeStats()   ~0.3 s   (aggregated GROUP BY on crm_attendance)
â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
TOTAL cold:       2â€“6 s
TOTAL warm:       ~50 ms   (all 4 keys cached separately, TTL=10 min)
```

---

### 2.2 `GET /crm/presence-suivi/details` â€” DÃ©tails par Groupe

**Controller:** `PresenceSuiviController::details()`
**Service:** `PresenceSuiviService::groupDetails()`

#### What it does
Returns a JSON drill-down: all sessions per group, filtered by status (`saisie` or `draft`).

#### Queries

| Query | Cost |
|---|---|
| `CrmClass::where(site_id)` | Fast |
| `CrmAttendance::whereIn(crmIds)->where(date <= today)` GROUP BY class+date with 7 JSON_EXTRACT columns | **Medium-heavy** â€” full history scan |
| If `status=draft`: same 90-day DOW inference + walk every date from earliest to today | **Expensive** â€” same date-walk as allTimeTotals |

#### Timing

```
status=saisie  cold: 1â€“3 s  |  warm: ~40 ms
status=draft   cold: 3â€“8 s  |  warm: ~40 ms   (date-walk adds ~2â€“5 s)
```

> **Why draft is slower:** it walks every calendar day from the earliest attendance record to today, checking if each class was expected to have a session (via DOW inference) but has no row in the DB.

---

### 2.3 `GET /crm/statistiques` â€” Dashboard Statistiques

**Controller:** `StatsController::index()`

#### What it does
Single cache key wraps 4 private methods:

```
encaissementByCenter()    â† GROUP BY store+month on crm_payment_snapshots
recouvrementByCenter()    â† SUM(rest_amount) GROUP BY store on crm_collection_rows
registrationsByCenter()   â† COUNT + JSON_EXTRACT(DATE_CREATION) GROUP BY store+month
periodComparison()        â† 6 separate SUM/COUNT queries (this/last/prevYear Ã— pay+reg)
```

#### Queries

| Method | Table | Complexity |
|---|---|---|
| `encaissementByCenter` | `crm_payment_snapshots` | WHERE snapshot_date=latest + GROUP BY store+month â†’ fast |
| `recouvrementByCenter` | `crm_collection_rows` | SUM with CASE + GROUP BY â†’ fast |
| `registrationsByCenter` | `crm_registrations` | `JSON_EXTRACT(raw_data, '$.DATE_CREATION')` in WHERE â€” **no index on JSON field** = full scan |
| `periodComparison` | both | 6 queries total, each with JSON_EXTRACT or snapshot filter |

#### Timing

```
months=3   cold: 0.5â€“1 s  |  warm: ~30 ms
months=6   cold: 0.8â€“2 s  |  warm: ~30 ms
months=12  cold: 1.5â€“3 s  |  warm: ~30 ms
```

> **Bottleneck:** `JSON_EXTRACT(raw_data, '$.DATE_CREATION')` in WHERE clause on `crm_registrations` has no index â€” it does a full table scan. Worse at 12 months because more rows pass the date filter.

---

### 2.4 `GET /crm/collections` â€” Dashboard Recouvrement

**Controller:** `CollectionsController::index()`
**Service:** `CollectionsService`

#### What it does
5 service calls, all hitting **local `crm_collection_rows`** only (zero live API):

```
kpis()               â† 8 separate SUM queries (overdue buckets)
topDebtors()         â† GROUP BY student, ORDER BY total_owed DESC LIMIT 20
upcomingDues()       â† WHERE due_date BETWEEN today AND +14d
agingBuckets()       â† cursor() scan, PHP-side bucket assignment
performanceByCenter()â† GROUP BY store+month on crm_payment_snapshots
```

#### Timing

```
Cold: 1â€“3 s   (kpis runs 8 cloned queries + cursor scan for aging)
Warm: ~30 ms  (TTL = 15 min per key)
```

> **Why `agingBuckets` is the slowest of the 5:** it uses `->cursor()` and classifies every row in PHP rather than doing a SQL CASE. Fine for <5k rows, could be slow if `crm_collection_rows` grows large.

---

### 2.5 `GET /crm/insights/group-evolution` â€” Ã‰volution des Groupes

**Controller:** `CrmInsightsController::groupEvolution()`
**Service:** `GroupEvolutionService`

#### What it does
The **most expensive endpoint**. Classifies every (student, group) pair into DÃ©but / Ajout / Quittant / Changement.

#### Data sources

| Source | How | Why costly |
|---|---|---|
| `crm_classes` (local DB) | `CrmClass::where(site_id)` | Fast |
| Wimschool `/payment-allocations` API | **Paginated loop** up to 40 pages Ã— 25 rows = 1000 rows max | Each page = 1 HTTP request (~200â€“500 ms) |
| `crm_collection_rows` (local DB) | Quittant detection: WHERE due_date IN range | Fast |

#### Pagination math

```
max 40 pages Ã— 25 rows/page = 1 000 allocations max
Each HTTP call to Wimschool API â‰ˆ 200â€“500 ms
Worst case: 40 pages Ã— 500 ms = 20 s of HTTP calls alone
```

#### Timing

```
Cold (no API rate limit):  5â€“15 s
Cold (API slow/rate limit): up to 20â€“30 s or timeout
Warm (TTL = 60 min):        ~60 ms
```

> **Cache TTL is 60 minutes** (vs 10 min for presence) because re-fetching is so expensive.
> Date range is **capped to 6 months** in `fetchAllocations()` to prevent fetching from 2020.

---

### 2.6 `GET /crm/reports` â€” Daily CEO Reports

**Controller:** `DailyReportController::index()`

Lists pre-generated reports from `crm_daily_reports` table. Zero API calls on the index page.

```
Cold: < 0.5 s
Warm: same (no cache key on index, it's just a simple SELECT)
```

Report **generation** (`POST /crm/reports/generate`) is different:
- Calls `DailyReportService::generate()` which hits `crm_payment_snapshots` (fast) + `CrmStatsService` (may hit API).
- Takes **2â€“8 s** depending on whether CrmStatsService data is cached.

---

### 2.7 `POST /crm/statistiques/refresh` â€” Force Snapshot

Does two things:

1. `Cache::flush()` â€” clears **all** Laravel cache keys
2. `Artisan::call('crm:snapshot-payments')` â€” **runs synchronously in the HTTP request**

```
Total time: 20â€“40 s   (user sees a white screen / spinner)
```

> This runs in the foreground intentionally (comment in code: "fast enough for a button click â€” ~30s total"). If the DB has many payments, it can feel slow to the user.

---

## 3. Cache Key Reference

| Cache key pattern | TTL | Set by |
|---|---|---|
| `crm.presence_suivi.{storeId}.{yearMonth}` | 10 min | `PresenceSuiviService::buildMonth()` |
| `crm.presence_suivi.global.{yearMonth}` | 10 min | `PresenceSuiviService::globalFraud()` |
| `crm.presence_suivi.totals.{storeId}` | 10 min | `PresenceSuiviService::allTimeTotals()` |
| `crm.presence_suivi.employees.{storeId}` | 10 min | `PresenceSuiviService::employeeStats()` |
| `crm.presence_suivi.details.{storeId}.{status}` | 10 min | `PresenceSuiviService::groupDetails()` |
| `crm.stats.dashboard:{months}:{storeId}` | 10 min | `StatsController::index()` |
| `crm.collections.kpis:{storeId}` | 15 min | `CollectionsService::kpis()` |
| `crm.collections.top_debtors:{storeId}:{limit}` | 15 min | `CollectionsService::topDebtors()` |
| `crm.collections.upcoming:{storeId}:{days}` | 15 min | `CollectionsService::upcomingDues()` |
| `crm.collections.aging:{storeId}` | 15 min | `CollectionsService::agingBuckets()` |
| `crm.collections.perf_by_center` | 15 min | `CollectionsService::performanceByCenter()` |
| `crm.group_evolution.{storeId}.{start}.{end}` | 60 min | `GroupEvolutionService::build()` |
| `crm.group_evolution.classes.{storeId}` | 60 min | `GroupEvolutionService::fetchClasses()` |
| `crm.group_evolution.allocs.{storeId}.{start}.{end}` | 60 min | `GroupEvolutionService::fetchAllocations()` |

---

## 4. Bottlenecks Ranked (Cold, No Cache)

```
ðŸ¥‡  group-evolution          5â€“15 s   (live paginated API, up to 40 HTTP calls)
ðŸ¥ˆ  presence-suivi index     2â€“6 s    (all-time attendance scan + PHP date loop)
ðŸ¥‰  presence-suivi/details   3â€“8 s    (same date-walk, worse for draft status)
4.  statistiques (12 months) 1.5â€“3 s  (JSON_EXTRACT full-scan on registrations)
5.  collections dashboard    1â€“3 s    (8 queries + cursor scan)
```

---

## 5. Why `allTimeTotals` Has a PHP Date Loop

```php
// Walk every date from earliest attendance to today
$period = CarbonPeriod::create(Carbon::parse($earliest)->startOfMonth(), $today);
foreach ($period as $day) {         // â† could be 500+ days
    foreach ($crmIds as $cid) {     // â† could be 50+ classes
        // check if expected session was missing
    }
}
```

This O(days Ã— classes) loop exists because SQL alone cannot detect "expected but absent" sessions â€” it requires knowing each class's inferred schedule (DOW pattern from history). The result is cached so it only runs on first load per center per 10 minutes.

---

## 6. Running the Benchmark

```bash
# Add to .env
CRM_BENCH_USER_ID=1   # admin user id with crm.view permission

# Run all 19 endpoints + summary
php artisan test tests/Feature/Crm/CrmEndpointBenchmarkTest.php --no-coverage

# Run just the summary table
php artisan test --filter=CrmEndpointBenchmarkTest::full_summary --no-coverage
```

Output format:
```
  presence-suivi (current month)             HTTP 200  cold=  3420 ms  warm=   48 ms  speedup=71x    size=  84.3 KB âœ…
  group-evolution                            HTTP 200  cold= 12840 ms  warm=   62 ms  speedup=207x   size= 142.1 KB ðŸ¢
```

Flags: `âœ…` < 2 s / `âš ï¸` 2â€“5 s / `ðŸ¢` > 5 s / `âŒ` HTTP error
