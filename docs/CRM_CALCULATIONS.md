# CRM Calculations Reference

Complete reference for every calculation performed in the CRM layer (`app/Services/Crm/Stats/`).

---

## Data Flow Overview

```
CRM API (Wimschool)
    ↓ nightly/daily sync
crm_payment_snapshots       ← payment records, one row per payment per snapshot day
crm_attendance              ← session attendance (is_present per student per class per date)
crm_registrations           ← student registrations (status, dates)
crm_collection_rows         ← outstanding invoices / reste-à-payer
crm_group_evolution_snapshot← pre-computed group membership changes
    ↓
Stats Services (aggregate + cache)
    ↓
Controllers → Views / JSON
```

---

## 1. Churn Scoring

**File:** `app/Services/Crm/Stats/ChurnScoringService.php`

Each active student receives a **risk score from 0–100**. Higher = more likely to drop out.

### Attendance signals (up to 55 points)

| Condition | Points |
|-----------|--------|
| 3+ consecutive absences at end of session history | +45 |
| 2 consecutive absences at end of session history | +25 |
| No presence in last 30+ days (or never attended despite sessions existing) | +10 |
| Attendance rate < 50% over full history | +10 |
| Attendance rate 50–70% | +5 |
| 2 absences out of last 3 sessions (when consecutive count < 2) | +5 |

**Attendance rate formula:**
```
attPct = ROUND((count of is_present=true) / (total session rows) * 100)
```

**Consecutive absences:** Walk the sessions sorted ascending by date; count how many `is_present = false` rows appear in an unbroken streak at the tail end.

**Days since last presence:**
```
daysSinceLast = today(Africa/Casablanca).diffInDays(last session where is_present=true)
```

### Payment signals (up to 45 points)

| Condition | Points |
|-----------|--------|
| Outstanding balance (`rest_amount > 0`) AND 3+ consecutive absences | +30 |
| Outstanding balance alone | +25 |
| Payment overdue > 30 days | +15 |
| Payment overdue 8–30 days | +10 |

**Overdue days:**
```
maxOverdue = MAX(payment_delay_days) across all unpaid crm_collection_rows for student
```

### Risk level classification

| Score range | Label |
|-------------|-------|
| 0–24 | `low` |
| 25–49 | `medium` |
| 50–74 | `high` |
| 75–100 | `critical` |

**Data sources:** `crm_attendance`, `crm_collection_rows`, `crm_registrations`

---

## 2. Collections Analytics (Outstanding Receivables)

**File:** `app/Services/Crm/Stats/CollectionsService.php`

Aggregates unpaid `rest_amount` from `crm_collection_rows` where `rest_amount > 0` and registration status = Active.

### KPI buckets

| KPI | Formula |
|-----|---------|
| `outstandingTotal` | `SUM(rest_amount)` — all unpaid rows |
| `dueToday` | `SUM(rest_amount)` where `due_date = today` |
| `dueWeek` | `SUM(rest_amount)` where `due_date` in next 7 days |
| `dueMonth` | `SUM(rest_amount)` where `due_date` before end of current month |
| `overdue7` | `SUM(rest_amount)` where `due_date <= today − 7 days` |
| `overdue30` | `SUM(rest_amount)` where `due_date <= today − 30 days` |
| `overdue60` | `SUM(rest_amount)` where `due_date <= today − 60 days` |
| `overdue90` | `SUM(rest_amount)` where `due_date <= today − 90 days` |

### Aging buckets (how old is the overdue debt)

| Label | Days overdue |
|-------|-------------|
| `current` | 0–7 days past due |
| `mild` | 8–30 days |
| `serious` | 31–60 days |
| `critical` | 61–90 days |
| `extreme` | > 90 days |

### Top debtors

```
total_owed    = SUM(rest_amount) grouped by student_id
oldest_due    = MIN(due_date) for that student
overdue_days  = MAX(0, today.diffInDays(oldest_due))   -- 0 if not yet past due
Sorted by total_owed DESC
```

### Center performance (last 3 months)

```
SUM(amount) grouped by (crm_store_id, year-month)
WHERE payment_type_id = 1  (Réglement only — excludes inter-caisse transfers)
```

**Cache TTL:** 900 s (15 min)

---

## 3. Payment Matrix

**File:** `app/Services/Crm/Stats/PaymentMatrixBuilder.php`

Builds a student × service grid showing payment status per service type.

### Cell status logic

```
status = 'na'      if total <= 0
         'paid'    if paid >= (total − 0.01)   -- tolerance for float rounding
         'unpaid'  if paid <= 0.01
         'partial' otherwise
```

### How `paid` and `total` are filled

- **total** starts as `service_price` from the class `SERVICE_LIST`.
- **paid** is overridden by `SUM(ALLOCATION_AMOUNT)` fetched from the external API for the `(STUDENT_ID, SERVICE_TYPE_NAME)` pair.
- For archived/cancelled students: `total` is set to `canonical_price = MAX(price seen across all students in that class)`.
- Merge rule: `maxPaid = min(max(existing.paid, api_amount), total)`.

### Column totals

```
total[service_label] = SUM(paid) for all students where status in ('paid', 'partial')
```

---

## 4. Daily KPI Statistics

**File:** `app/Services/Crm/Stats/CrmStatsService.php`

Pulls live counts from the Wimschool API (one request per center per metric).

| Metric | Source endpoint |
|--------|----------------|
| `students` | `/students?size=1&includeTotal=true` |
| `registrations` | `/registrations?size=1` + date filters |
| `payments` | `/payments?size=1` + date filters |
| `classes` | `/groups/classes?size=1` |

Uses `pagination.totalElements` — **only the count is read, not the actual rows.**

### Monthly payment trend (last 6 months)

```
For each (center × month):
  count = totalElements from /payments
    with startDate = first_of_month, endDate = last_of_month
```

Returns a time series array per center.

### Annual summary (12 months)

```
For each month:
  collecte          = SUM(AMOUNT) from /payments (first key found: AMOUNT → TOTAL_AMOUNT → PAID_AMOUNT)
  reste_a_payer     = SUM(REST_AMOUNT) from /payment-collection scoped by dueDateStartDate/dueDateEndDate
  encaissements     = same as collecte
  chiffre_affaire   = 0  (not yet sourced)
  depenses          = 0  (not yet sourced)
```

Note: capped at 200 rows per month; logs a warning if hit.

**Cache TTL:** 300 s (5 min)

---

## 5. Daily Report

**File:** `app/Services/Crm/Stats/DailyReportService.php`

Generates a summary for a single target date (default: yesterday).

```
revenue_yesterday = SUM(amount)
  WHERE payment_type_id = 1
    AND DATE(date_creation) = target_date
    AND snapshot_date = resolved_snapshot_date

new_registrations = COUNT(*)
  WHERE DATE(date_creation) = target_date

centers_ranking   = GROUP BY crm_store_id
                    ORDER BY SUM(amount) DESC

top_center_today  = centers_ranking[0]
```

---

## 6. Cash Handler Analytics

**File:** `app/Services/Crm/Stats/InsightsService.php` → `cashHandlers()`

Analyzes payments by the cashier who entered them, over a weekly window.

### Per-handler aggregation

```
total_payments = COUNT(*)
total_amount   = SUM(amount)
avg_amount     = AVG(amount)
cash_amount    = SUM(amount) WHERE payment_method_id = 1
cheque_amount  = SUM(amount) WHERE payment_method_id = 2
transfer_amount= SUM(amount) WHERE payment_method_id = 3
other_amount   = SUM(amount) WHERE payment_method_id NOT IN (1,2,3)
```

### Percentages

```
cash_pct     = ROUND(cash_count / total_payments * 100)
cheque_pct   = ROUND(cheque_count / total_payments * 100)
transfer_pct = ROUND(transfer_count / total_payments * 100)
```

### Outlier detection (statistical)

```
mean      = AVG(total_amount) across all handlers
stddev    = SQRT(VAR(total_amount))
threshold = mean + 2 × stddev
is_outlier = (handler.total_amount > threshold AND threshold > 0)
```
A handler is flagged if they collected more than 2 standard deviations above the average.

### Hourly distribution

24 buckets (hours 0–23). Each bucket = count of payments entered that hour across the whole week window.

**Deduplication filter:** Only the latest snapshot per `crm_payment_id` is included (prevents double-counting across daily snapshots). Only `payment_type_id = 1` (Réglements).

---

## 7. Daily Reconciliation

**File:** `app/Services/Crm/Stats/InsightsService.php` → `reconciliation()`

Breaks down daily collections per center, flags statistical anomalies.

### Per (center × day) aggregation

```
cash     = SUM(amount) WHERE payment_method_id = 1
cheque   = SUM(amount) WHERE payment_method_id = 2
transfer = SUM(amount) WHERE payment_method_id = 3
total    = SUM(amount)
n        = COUNT(*)
```

### Anomaly detection (z-score per center)

```
mean = AVG(total across all days for this center)
std  = SQRT(VAR(total))           -- 0 if only 1 data point
z    = (day_total − mean) / std   -- 0 if std = 0
is_anomaly = ABS(z) > 2           -- 2-sigma rule
```

A day is flagged as anomalous if its total is more than 2 standard deviations from that center's average day.

---

## 8. Retention Funnel

**File:** `app/Services/Crm/Stats/InsightsService.php` → `retention()`

Cohort analysis: students are grouped by the month of their registration `START_DATE`.

### Per cohort

```
total         = COUNT(all registrations in cohort)
active        = COUNT WHERE REGISTRATION_STATUS_ID = 8
archived      = COUNT WHERE REGISTRATION_STATUS_ID = 11
cancelled     = COUNT WHERE REGISTRATION_STATUS_ID = 10
suspended     = COUNT WHERE REGISTRATION_STATUS_ID = 9
reached_end   = COUNT WHERE END_DATE < today
still_running = COUNT WHERE END_DATE IS NOT NULL AND END_DATE >= today
```

### Percentages

```
retention_status_pct = ROUND((active + archived) / total * 100)
retention_date_pct   = ROUND(reached_end / total * 100)
dropout_pct          = ROUND((cancelled + suspended) / total * 100)
```

**Status ID mapping:** 8 = Active, 9 = Suspended, 10 = Cancelled, 11 = Archived

---

## 9. Revenue Forecast

**File:** `app/Services/Crm/Stats/InsightsService.php` → `forecast()`

Simple rolling average projection per center.

### Baseline

```
closedMonths = [amount from 1 month ago, 2 months ago, 3 months ago]
baseline     = SUM(closedMonths) / COUNT(non-zero months)
```

### Per future month

```
actual   = SUM(amount) from crm_payment_snapshots WHERE
             snapshot_date = today AND date_creation_date in [month_start, month_end]
forecast = baseline   (flat projection — same every month)
is_past  = (month_end < today)
```

```
total_forecast = SUM(forecast for all months in horizon)
total_actual   = SUM(actual for all months in horizon)
```

---

## 10. Group Evolution (Debuts / Ajouts / Quittants)

**File:** `app/Services/Crm/Stats/GroupEvolutionService.php`

Tracks student membership changes within a group month by month.

### Definitions

| Term | Meaning |
|------|---------|
| `debuts` | Students whose **first payment** for this group falls in the **same month** the group started |
| `ajouts` | Students whose **first payment** for this group falls **after** the group start month |
| `quittants` | Students who paid in a month then missed the **next** month (1-miss rule = likely dropout) |
| `changements` | Students who stopped paying group A and started paying group B within 30 days |
| `actifs` | Live count from API: `CLASS_COUNT_STUDENTS_ACTIVE` |

### Classification per student (drill endpoint)

```
classStartYm = year-month of class START_DATE
regYm        = year-month of student's registration START_DATE (fallback: classStartYm)
firstForClass = MIN(payment_month) WHERE payment_month >= regYm AND service != inscription

payment_bucket = 'unpaid'  if no classStartYm or no firstForClass
                 'debut'   if firstForClass <= classStartYm
                 'ajout'   if firstForClass > classStartYm
```

**Cache TTL:** 3600 s (1 hour)

---

## 11. Payment Activity Tracking (Change Detection)

**File:** `app/Services/Crm/Stats/PaymentActivityService.php`

Compares two consecutive snapshot dates to detect mutations.

| Category | Detection rule |
|----------|---------------|
| **Deleted** | Was in previous snapshot; absent today AND absent in all snapshots ≥ today |
| **Created** | In today's snapshot; not in previous (may be back-dated) |
| **Amount changed** | Same `payment_id` in both; `amount` differs — records `old_amount`, `new_amount`, `delta` |
| **User changed** | Same `payment_id`; `user_update_id` or `date_update` differs |
| **Late edit** | `date_update > date_creation + 24 h` AND edit happened in last 7 days |

---

## 12. Advance Payments (Avances)

**File:** `app/Services/Crm/Stats/AdvancePaymentsService.php`

Isolates payments flagged as advances (`IS_AVANCE = 'Y'`).

### Deduplication

```
signature = student_id | amount | substr(effective_date, 0, 10)
First occurrence = unique; subsequent = duplicate (excluded from totals)
```

### Per-handler aggregation

```
count        = COUNT(unique advances by this handler)
amount       = SUM(AMOUNT) for unique advances by this handler
total_amount = SUM(all unique advance amounts)
```

---

## 13. Attendance Calendar (Présence Suivi)

**File:** `app/Services/Crm/Stats/PresenceSuiviService.php`

Generates a month-level attendance calendar per class.

### Per session

```
present = COUNT(is_present = true)
absent  = COUNT(is_present = false)
total   = present + absent
```

### Expected sessions inference

Uses a 90-day lookback window to determine which weekday(s) each class normally meets:
```
If a class has >= 2 recorded sessions on weekday D in the past 90 days
  → weekday D is "expected" for that class
```

### Day status labels (calendar dots)

| Label | Condition |
|-------|-----------|
| `saisie` | Session recorded with attendance entered |
| `draft` | Expected session in the past, but no attendance recorded |
| `futur` | Expected session in the future |

---

## 14. Payments Snapshot Total

**File:** `app/Http/Controllers/Backoffice/Crm/CrmPaymentsController.php`

```
snapshotTotal = SUM(amount)
  WHERE payment_type_id = 1           -- Réglements only
    AND date_creation_date BETWEEN startDate AND endDate
    AND snapshot_date = MAX(snapshot_date) per crm_payment_id   -- latest snapshot only
    AND (crm_store_id = filter  if store filter active)
    AND (student_id = filter    if student filter active)

Returns NULL if:
  - No date range provided
  - Filter is set to a non-Réglement payment type
```

---

## 15. Remaining Balance Async Total

**File:** `app/Http/Controllers/Backoffice/Crm/CrmPaymentsController.php`

Computed in a background job (`ComputeCrmTotalSumJob`) to avoid blocking the page.

```
totalRemaining = SUM(rest_amount | open_amount | remaining_amount)
  from the Wimschool collection API, filtered by active store/date filters
```

Result is cached per filter fingerprint (`sha1(json_encode(filters))`). Returns `null` while still computing.

---

## 16. Key Constants & Identifiers

| ID / Value | Meaning |
|------------|---------|
| `payment_type_id = 1` | Réglement (real payment — excluded from inter-caisse) |
| `payment_method_id = 1` | Cash |
| `payment_method_id = 2` | Chèque |
| `payment_method_id = 3` | Virement (bank transfer) |
| `REGISTRATION_STATUS_ID = 8` | Active |
| `REGISTRATION_STATUS_ID = 9` | Suspended |
| `REGISTRATION_STATUS_ID = 10` | Cancelled |
| `REGISTRATION_STATUS_ID = 11` | Archived |

---

## 17. Timezone & Date Fields

All "today" and "now" references use **`Africa/Casablanca`** timezone via Carbon:
```php
Carbon::today('Africa/Casablanca')
Carbon::now('Africa/Casablanca')
```

| Field | Type | Meaning |
|-------|------|---------|
| `snapshot_date` | date | Which day's nightly sync this row came from |
| `date_creation` | datetime | When the cashier entered the payment in the CRM |
| `date_creation_date` | date | Indexed/normalized copy of `date_creation` date part |
| `date_update` | datetime | Last modification timestamp |
| `effective_date` | date | Billing/service date on the invoice |
| `due_date` | date | When the invoice payment is due |
| `date` | date | Attendance session date |

> **Important:** `SESSION_DATE` from the Wimschool API is in UTC. Evening sessions in Casablanca can land on the previous calendar day if not converted. Always call `.setTimezone('Africa/Casablanca')` before using the date part.

---

## 18. Caching Reference

| Service | Cache key pattern | TTL |
|---------|-------------------|-----|
| `CrmStatsService` | `crm.stats.kpis:*`, `crm.stats.trend:*`, `crm.stats.annual:*` | 300 s (5 min) |
| `CollectionsService` | `crm.collections.kpis:*`, `crm.collections.top_debtors:*` | 900 s (15 min) |
| `InsightsService` | `crm.insights.retention:*`, `crm.insights.forecast:*` | 900 s (15 min) |
| `AdvancePaymentsService` | `crm.avances:*` | 300 s (5 min) |
| `PresenceSuiviService` | `crm.presence_suivi.*` | 600 s (10 min) |
| `GroupEvolutionService` | `crm.group_evolution.*` | 3600 s (1 hour) |

All caches can be cleared individually or via `php artisan cache:clear`.
