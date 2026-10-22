# Research validation (Phase 5/6) — 2026-10-21

## Counts

| Item | Value |
|---|---|
| Pages inventoried | 36 rows in `01-page-inventory.csv` (one row stands for the 21 Studienkolleg detail pages → 56 live URLs) |
| Pages researched (own CSV) | 22 |
| Pages not researched | 14 — all `NOT_RESEARCHED_NO_TARGET` (no organic target: forms, legal, utilities, placeholder exam page, partner page, noindex landing pages, FAQ/contact/blog index, Studienkolleg detail template). None is `NOT_RESEARCHED_BUDGET_LIMIT`. |
| Keywords in the master file | 76 rows, 74 unique keywords (2 keywords deliberately on two pages, flagged) |
| Keywords with a Morocco (`ma`) Semrush value | 56 (of which 3 are verified zero volume: `apprendre allemand maroc`, `cours allemand kenitra`, `prix cours allemand`) |
| Keywords requested with no Semrush data in `ma` | 18 (`NO_DATA`, see list) |
| Keywords with KD | 12 (`phrase_kdi`); 9 returned KD 0 (treated as unreliable), 3 meaningful: `goethe institut casablanca` 37, `studienkolleg` 30, `ösd maroc` 26 |
| Keywords with CPC / competition / trend | 56 (all rows with volume); `NO_DATA` otherwise |
| Excluded keywords kept for provenance | 11 (8 irrelevant from discovery, 2 navigational, 1 wrong intent) |

`NO_DATA` list: centre de langue allemande maroc · centre de langue allemande · école allemande maroc · école de langue allemande · institut allemand maroc · cours allemand maroc · cours d'allemand au maroc · cours allemand agadir · cours allemand salé · cours intensifs allemand · formation allemand maroc · allemand a2 · allemand b2 · allemand c1 · cours allemand b1 · cours allemand a2 · examen goethe maroc · tarif cours allemand.

## Semrush calls (database `ma`, all live, all logged in `storage/app/seo/semrush-usage.jsonl`)

| # | Time (UTC) | Report | Request | Rows | Units | Cache |
|---|---|---|---|---|---|---|
| 1 | 12:56:18 | `phrase_fullsearch` | "allemand maroc", limit 10 | 10 | 200 | fresh |
| 2 | 12:56:21 | `phrase_fullsearch` | "cours allemand", limit 12 | 12 | 240 | fresh |
| 3 | 12:56:46 | `phrase_these` | 50 candidates (`--fresh`) | 32 returned | 320 | fresh (cache bypassed) |
| 4 | 12:57:08 | `phrase_kdi` | 12 finalists | 12 | **600** | fresh |
| 5 | 13:00:49 | `phrase_these` | 3 seeds from 2026-10-19 (cache had expired, 24 h TTL) | 3 | 30 | fresh |
| — | 13:00 | re-exports of calls 1–3 to add the trend column | — | 0 | 3 cache hits |

**Total: 5 live calls, 1,390 API units, 3 cached reuses.** Remaining account balance: not exposed by the Semrush MCP API; read it in Semrush → Subscription info.

### Budget deviation (reported, not hidden)
The run budget was 1,000 units. Calls 1–3 used 760. The KD call was launched with `--budget=240` expecting ~10 units/keyword (the price measured for `phrase_these`); `phrase_kdi` actually costs **50 units/keyword** and the guard only checked the budget *before* the call, so one call consumed 600 and the day ended at 1,390 (+390 over budget). Call 5 (30 units) re-validated three seeds whose 2026-10-19 cache entry had expired.

Fix applied before any further research: `SemrushService::assertBudget()` now estimates each call from the measured units/row in the usage log and refuses calls whose estimate exceeds the remaining budget; a report with no price history may request at most 3 rows unless `--allow-unmeasured` is passed. Covered by two new unit tests (23 tests green). The same 12-keyword KD call now stops with: *"Estimated cost of phrase_kdi for 6 rows is ~300 units (measured ~50/row) but only 240 of 240 remain"*.

## Freshness
Every value is a fresh API retrieval made on 2026-10-21 (`cache_status = fresh_api_retrieval`). Semrush exposes no update date for its monthly volume estimates; the CSVs carry the retrieval timestamp and the statement "the exact update date of the underlying search-volume data is not exposed by the API". Snapshots of each call are stored under `storage/app/seo/keywords/ma/` for future diffs.

## Errors / incomplete data
- No API errors, no rate limits, no retries.
- No SERP (`phrase_organic`) report was run, so the intent of `ösd maroc` (1,300) and `cours en allemand` (110) is unverified.
- KD is missing for 62 keywords (not requested at 50 units each) and unreliable (0) for 9 of the 12 requested.
- The `allemand maroc` discovery seed returned 8 irrelevant rows out of 10; this seed is exhausted.

## Review gate
`review_gate.py` (scratchpad) parses all CSVs and checks: schema, one primary per page, numeric-or-labelled metrics, `ma` on every row, per-page ↔ master agreement, primary-first ordering, duplicates flagged, cannibalisation documented, no irrelevant keyword assigned, usage log = 5 calls / 1,390 units. Result recorded below by the run.

## Gate run 2026-10-21

`review_gate.py`: **ALL 14 CHECKS PASSED** (schema, one primary per page, metrics typed, `ma` on every row, per-page ↔ master agreement, primary-first, duplicates flagged, cannibalisation documented, no irrelevant assignment, usage log = 5 calls / 1,390 units). 22 pages RESEARCHED, 14 NOT_RESEARCHED_NO_TARGET.
