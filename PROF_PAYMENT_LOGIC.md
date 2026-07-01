# Professor Payment Calculation — Logic Specification

This document explains how professor (teacher) payment is calculated from student attendance data in the GLS Sprachenzentrum system. It is written to be self-contained so it can be handed to an LLM (e.g. ChatGPT) as context.

---

## 1. Big picture

A teacher is paid based on **student attendance across the 4 weeks of a month**.

- Attendance is imported from Excel (or the CRM API) as a `PresenceImport` snapshot for one group/month.
- Each import contains many students. Each student has daily attendance records (`present` / `absent`).
- For every **student × week** combination, the system decides whether that week "qualifies" (student showed up enough), and if so the teacher earns a fixed per-week amount for that student.
- The **total professor payment** = sum of all qualifying student-weeks (plus any manual overrides).

The month is always modeled as **exactly 4 week-buckets** (semaines), never more, never fewer.

---

## 2. Key inputs / parameters

| Parameter | Source | Default | Meaning |
|-----------|--------|---------|---------|
| `base_price` (payment_per_student) | Import override → else Teacher default | — | Full monthly price per student for this group |
| `weekly_rate_percent` | Import | **25%** | Fraction of base price earned per qualifying week |
| `weekly_threshold` | Import | **3** | Min. present days in a week for it to qualify |
| `weekly_unit_amount` | Derived | — | `round(base_price × rate% / 100, 2)` |

**Effective base price resolution:**
1. If the import has its own `payment_per_student`, use it.
2. Otherwise fall back to the teacher's `payment_per_student`.
3. Otherwise `0`.

**Weekly unit amount example:**
`base_price = 500 DH`, `rate = 25%` → each qualifying student-week is worth `500 × 25 / 100 = 125 DH`.

Because there are 4 weeks, a student who qualifies all 4 weeks generates `4 × 125 = 500 DH` (the full base price).

---

## 3. Per-week qualification rule

For each student, for each of the 4 weeks:

```
count = number of present days in that week (capped at 5)
if count >= weekly_threshold (default 3):
    auto_amount = weekly_unit_amount   (e.g. 125 DH)
    week qualifies
else:
    auto_amount = 0
    week does not qualify
```

- Present days per ISO week are **capped at 5** (school runs Mon–Fri only; a week can never legitimately exceed 5 presences).
- A **manual override** can replace the auto amount for any single student-week (`week_N_amount_override`). If an override is set, it wins over the automatic value.

```
effective_amount = override (if set) else auto_amount
student_total    = sum of effective_amount over weeks 1..4
```

---

## 4. Inactive students

A student is treated as **inactive** (all 4 weeks forced to 0 presence) if:

- the student is **cancelled** OR **transferred**, AND
- the student has **no present records at all**.

Inactive students contribute 0 to the teacher's payment.

---

## 5. Mapping real calendar weeks → 4 buckets

Real months don't cleanly split into 4 equal weeks (holidays, partial weeks, a 5th ISO week spilling over). The system normalizes this via `buildWeekMap()`:

1. Collect all **distinct course dates** (any date with at least one attendance record), in chronological order.
2. Count course days per **ISO week** (`GGGG-WW` format).
3. Walk the ISO weeks in order, assigning each to a bucket (1..4):
   - Open a **new bucket** only once the current bucket has accumulated at least `threshold` course days (and we're still below bucket 4).
   - A week too short to ever qualify on its own (e.g. a holiday week with 1 course day) **joins** the current bucket instead of wasting a slot.
   - Any weeks beyond bucket 4 are **folded into bucket 4**.
4. If the **last bucket** ends up too short to ever qualify (`openDays < threshold`), it is **merged into the previous bucket**.

**Why this matters:** it prevents a short/holiday week from consuming one of the 4 payment slots and becoming permanently unwinnable. Buckets are built from days that *actually had class*, not the raw calendar.

Then per student, present days are tallied into these buckets and each bucket count is capped at 5.

---

## 6. Worked example

Assume: `base_price = 500`, `rate = 25%` → `unit = 125 DH`, `threshold = 3`.

Student A presence per week: `[4, 3, 2, 5]`

| Week | Present days | ≥ 3? | Auto amount |
|------|-------------|------|-------------|
| 1 | 4 | ✅ | 125 |
| 2 | 3 | ✅ | 125 |
| 3 | 2 | ❌ | 0 |
| 4 | 5 | ✅ | 125 |

Student A total = **375 DH** (3 qualifying weeks).

If a responsable manually overrides week 3 to `125`, Student A total becomes **500 DH**.

The teacher's grand total = sum of every student's total.

---

## 7. Outputs stored

**Per student** (`PresenceImportStudent`):
- `week_N_presence` — present-day count for week N
- `week_N_amount` — auto amount for week N
- `weighted_amount` — student's total (rounded)
- `active_quarters` + `category` — legacy classification (see below)

**Per import** (`PresencePaymentSummary`):
- `base_price`, `weekly_unit_amount`
- `count_qualified_weeks`, `count_unqualified_weeks`
- `total_students` — count of students with at least one qualifying week
- `total_payment` — **the teacher's total pay** (rounded to 2 decimals)
- Legacy category counts: `count_full`, `count_three_quarter`, `count_half`, `count_quarter`, `count_zero`

### Legacy categories (informational only)
Based on how many of the 4 buckets had any presence (`active_quarters`):

| Active weeks | Category |
|--------------|----------|
| 4 | FULL |
| 3 | THREE_QUARTER |
| 2 | HALF |
| 1 | QUARTER |
| 0 | ZERO |

These are kept for old views/exports and **do not drive** the payment total — the per-week qualification rule does.

---

## 8. Summary formula

```
weekly_unit_amount = round(base_price × weekly_rate_percent / 100, 2)

for each student:
    for each week w in 1..4:
        count_w   = min(present_days_in_bucket_w, 5)          # 0 if inactive
        auto_w    = (count_w >= threshold) ? weekly_unit_amount : 0
        eff_w     = override_w ?? auto_w
    student_total = eff_1 + eff_2 + eff_3 + eff_4

total_payment = sum(student_total for all students)
```

**Defaults:** `weekly_threshold = 3`, `weekly_rate_percent = 25%`.

---

## 9. Source files

- `app/Services/Payroll/ProfPaymentCalculationService.php` — main calculation
- `app/Models/PresenceImport.php` — parameters (threshold, unit amount, base price resolution)
- `app/Models/PresenceImportStudent.php` — per-student week fields & categories
- `app/Models/PresencePaymentSummary.php` — stored totals
