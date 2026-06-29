# GLS CRM — How the Percentages (Taux), Scores & Rankings (Classement) Work

> Plain-English reference of every percentage, score and ranking the GLS
> backoffice computes from the Wimschool CRM data. You can paste this whole
> file into ChatGPT (or share it with anyone) to have it explained further.

---

## 0. Where the data comes from

The CRM (Wimschool) is the **source of truth**. GLS pulls data from its REST API
(`/api/external/v1/...`) every ~2 hours (`php artisan crm:sync-all`) and stores it
in local MySQL tables so the dashboards load instantly without hitting the API.

Key local tables:

| Table | What it holds |
|---|---|
| `crm_payment_snapshots` | Every payment, snapshotted over time (history kept) |
| `crm_collection_rows` | Payment-collection tranches = money still owed (échéances) |
| `crm_registrations` | Student enrollments per group/center |
| `crm_attendance` | One row per student per session (présent / absent) |
| `crm_group_evolution_snapshot` | Precomputed student counts per group over time |
| `sites` | The 7 centers, each mapped to a `crm_store_id` |

### Two important data rules

**1. Payment de-duplication.** Payments are snapshotted repeatedly, so the same
payment can appear in many snapshot rows. Every money query keeps only the
**latest snapshot per payment**:

```sql
AND snapshot_date = (
    SELECT MAX(s2.snapshot_date) FROM crm_payment_snapshots s2
    WHERE s2.crm_payment_id = s1.crm_payment_id
)
```

**2. `payment_type_id = 1` (“Réglement”) only.** This filters out internal
cash transfers between registers (inter-caisse) so they aren't double-counted
as revenue.

---

## 1. Taux de présence / absence (attendance rate)

**Where:** `PresenceSuiviService` (présence-suivi → stats & employee leaderboard).

For any group of sessions we count, per attendance row, whether the student was
present (`is_present = 1`) or absent (`is_present = 0`).

```
total   = present + absent
taux_presence (%) = present / total × 100
taux_absence  (%) = absent  / total × 100
```

Both are rounded to 1 decimal. If `total = 0`, the taux is `0`.

- **Per séance** (`sessionStats`): each session card shows `taux = present / total × 100`.
- **Per groupe**: sum present and total across all the group's sessions, then
  `present / total × 100`. The chart sorts groups by this taux descending and
  takes the top 12.
- **Per employee** (saisie operator leaderboard): same formula, over all the
  rows that employee entered. The leaderboard is **sorted by number of sessions
  saisies** (not by taux).

> Note: "saisie" vs "draft/brouillon". A session is **saisie** when attendance
> was actually entered (`PRESENCE_STATUS != 0`), and **draft/brouillon** when a
> session was expected (based on the group's usual weekdays) but no attendance
> was recorded. Draft sessions count toward the "fraud" / not-entered tracking,
> not toward the taux.

---

## 2. Taux de recouvrement (recovery / collection rate)

This answers: *“Of the money in play, how much was actually collected?”*
Higher = better (lots collected, little still owed).

### 2a. Recovery per center — this month (`CollectionsService::recoveryByCenter`)

```
outstanding      = SUM(rest_amount) of ACTIVE registrations, rest_amount > 0   (money still owed)
collected_month  = SUM(amount) of payments this month (type 1, latest snapshot) (money collected)
total_exposure   = outstanding + collected_month
recovery_rate(%) = collected_month / total_exposure × 100      (rounded 1 dp)
```

If `total_exposure = 0`, the rate is `0`.

### 2b. Recovery in the “Résumé annuel” ranking (`StatsController::resumeAnnuelData`)

Same idea but over an arbitrary date range:

```
encaisse  = SUM(amount) collected in range (type 1, latest snapshot)
reste     = SUM(rest_amount) of active receivables due in range
base      = encaisse + reste
recovery_rate(%) = encaisse / base × 100        (rounded 1 dp, null if base = 0)
```

> **Why “Active” only matters:** when a student re-enrolls, the old registration
> is archived but its old tranches keep a stale `rest_amount` in the CRM even
> though the student already paid via the new registration. Counting those would
> double-bill paid students, so collection queries restrict to
> `registration_status_name = 'Active'` (or exclude status 10 = Annulé).

---

## 3. Classement des centres (center ranking) — composite performance score

**Where:** `StatsController::resumeAnnuelData` → "Résumé annuel" page. This is the
ranking that crowns the **best center** (used for primes / bonuses).

Each center is measured on three axes over the chosen date range:

1. **Encaissé** — total money collected (DH).
2. **Recouvrement** — the recovery rate from §2b (already 0–100).
3. **Inscriptions** — number of new registrations created.

### Step 1 — Normalize encaissé & inscriptions to 0–100

Because encaissé (in DH) and inscriptions (a count) are on totally different
scales, each is normalized **relative to the best center**:

```
maxEnc  = highest encaissé among all centers   (min 1, to avoid ÷0)
maxInsc = highest inscriptions among all centers (min 1)

encScore  = encaisse     / maxEnc  × 100      → the top center gets 100
inscScore = inscriptions / maxInsc × 100      → the top center gets 100
recScore  = recovery_rate                     → already 0–100, used as-is
```

### Step 2 — Weighted composite score (0–100)

```
score = encScore × 0.40   (encaissé        — 40%)
      + recScore × 0.35   (recouvrement    — 35%)
      + inscScore × 0.25  (inscriptions    — 25%)
```

Rounded to 1 decimal. **Highest score wins** → that's the `winner`.

### Step 3 — The rankings returned

| Ranking | Sorted by |
|---|---|
| `by_score` | composite `score` desc → **overall classement / winner** |
| `by_encaisse` | `encaisse` desc |
| `by_recouvrement` | `recovery_rate` desc |
| `by_inscriptions` | `inscriptions` desc |

So a center wins by collecting the most money **and** having a high recovery rate
**and** signing up many students — money matters most (40%), recovery next (35%),
then new students (25%).

---

## 4. Taux de rétention professeur (teacher retention) & classement

**Where:** `StatsController::computeTeacherPerformance` → "Performance professeurs".

Rolled up from the group-evolution snapshot, per teacher:

- **débuts** — students at the start of the period
- **ajouts** — students added during the period
- **quittants** — students who left
- **actifs** — students still active

```
entrants      = débuts + ajouts                          (total brought in)
retention (%) = max(0, entrants − quittants) / entrants × 100   (rounded 1 dp)
```

If `entrants = 0`, retention is `null`.

**Classement (teacher ranking):**
1. Best **retention** first (descending).
2. Tie-break: fewest **quittants**.
3. Teachers with no intake (`retention = null`) go last.

---

## 5. Churn / risk score (at-risk students)

**Where:** `ChurnScoringService`. Score **0–100, higher = more at risk** (the
opposite direction from the taux above). Additive, capped at 100.

### Attendance signals (max 55 pts)

| Points | Condition |
|---|---|
| +45 | ≥ 3 consecutive recent absences |
| +25 | 2 consecutive absences (instead of the +45) |
| +10 | No presence in the last 30 days (stopped attending) |
| +10 | No presence ever recorded but has sessions |
| +10 | Attendance rate < 50% |
| +5  | Attendance rate 50–70% |
| +5  | Absent in 2 of the last 3 sessions |

`attendance_pct = present / total_sessions × 100`.

### Payment signals (max 45 pts)

| Points | Condition |
|---|---|
| +30 | Has unpaid balance **AND** ≥ 3 consecutive absences |
| +25 | Has unpaid balance (no absences) — instead of the +30 |
| +15 | Payment overdue > 30 days |
| +10 | Payment overdue 8–30 days |

### Risk levels

| Score | Level | Action |
|---|---|---|
| 0–24 | low | Aucune action requise |
| 25–49 | medium | Faire un suivi |
| 50–74 | high | Appeler en priorité |
| 75–100 | critical | Appeler immédiatement |

---

## 6. Period comparison (mois vs mois, vs année dernière)

**Where:** `StatsController::periodComparison` on the main stats dashboard.

It sums encaissement (payments) and counts inscriptions for three windows:
**this month (1st → today)**, **last month (full)**, and **same month last year
(full)**. The dashboard shows the raw totals; any % change is just:

```
variation (%) = (current − previous) / previous × 100
```

---

## 7. Aging buckets (retard de paiement) — not a %, but related

**Where:** `CollectionsService::agingBuckets`. Overdue receivables are bucketed
by how many days past the due date they are:

| Bucket | Days overdue |
|---|---|
| Retard 0–7 j | ≤ 7 |
| Retard 8–30 j | 8–30 |
| Retard 31–60 j | 31–60 |
| Retard 61–90 j | 61–90 |
| Retard 90 j+ | > 90 |

`diffInDays(today)` positive = overdue. Each bucket sums count and `rest_amount`.

---

## Quick glossary (FR → meaning)

| Term | Meaning |
|---|---|
| Taux de présence | Attendance rate (present / total) |
| Taux d'absence | Absence rate (absent / total) |
| Taux de recouvrement | Recovery rate (collected / (collected + owed)) |
| Encaissé / Encaissement | Money actually collected |
| Reste à payer | Money still owed (outstanding) |
| Échéance | A payment tranche / due installment |
| Inscriptions | New registrations |
| Rétention | Teacher retention (kept / entered) |
| Classement | Ranking |
| Saisie | Attendance was entered |
| Brouillon / draft | Session expected but attendance not entered |
| Impayé | Unpaid |
| Réglement | A real payment (payment_type_id = 1) |
| Prime | Bonus (awarded to the best center) |
