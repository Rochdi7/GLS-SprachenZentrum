# Research log — homepage keyword expansion (2026-10-21)

## Sources
- Semrush Analytics via the MCP endpoint, through `php artisan seo:keywords` (`App\Services\Seo\SemrushService`), budget guard active on every call (`--budget` passed as the remaining run budget each time, probe rule ≤3 rows for unmeasured reports, no `--allow-unmeasured` used).
- Existing research re-read before any call: `01`–`06`, `02-keyword-research-all.csv` (74 keywords), `pages/*.csv`, `raw/*.csv`, usage log `storage/app/seo/semrush-usage.jsonl`.
- No other SEO tool was used (DataForSEO not connected; claude-seo skills have no Semrush path). No credentials read or printed.

## Pre-flight cost check (13:47 UTC)
Measured prices in the log: `phrase_these` 10 units per keyword returned, `phrase_fullsearch` 20/row, `phrase_kdi` 50/keyword; `phrase_related`, `phrase_questions`, `phrase_organic` unmeasured → probe-only (≤3 rows). Plan: 200 + ≤420 + 80 + 2 probes ≈ ≤900 of the 1,000-unit run budget. KD deliberately not purchased (50/keyword).

## Calls (all live, database as listed, timestamps UTC)

| # | Time | Report | DB | Request | Rows | Units | Cached reuse |
|---|---|---|---|---|---|---|---|
| 1 | 13:48:11 | phrase_fullsearch | ma | "apprendre allemand", limit 10 | 10 | 200 | no |
| 2 | 13:48:13 | phrase_these | ma | 42 candidates (groups A–E) | 23 (19 NO_DATA) | 230 | no |
| 3 | 13:48:15 | phrase_these | fr | 8 core terms (market control) | 8 | 80 | no |
| 4 | 13:48:29 | phrase_questions | ma | "allemand", limit 3 (probe) | 3 | 120 → **40/row measured** | no |
| 5 | 13:48:31 | phrase_organic | ma | "apprendre l'allemand", limit 3 (probe) | no_data | **unknown** (no usage metadata on no_data; reported as unknown, not 0) | no |
| 6 | 13:49:02 | phrase_these | ma | 24 candidates wave 2 | 11 (13 NO_DATA) | 110 | no |
| 7 | 13:49:03 | phrase_organic | ma | "centre allemand", limit 3 | 3 | 30 → **10/row measured** | no |
| 8 | 13:49:29 | phrase_organic | ma | "academie allemand", limit 3 | 3 | 30 | no |
| 9 | 13:49:31 | phrase_organic | ma | "ausbildung", limit 3 | 3 | 30 | no |
| — | 13:49 | (tinker) re-read of 7–9 from cache | | | | 0 | 3 cache hits |

**Totals: 9 live calls, 830 units known + 1 call with unknown units; 3 cached reuses.** Budget 1,000 not exceeded; no guard stop triggered; no retries; one definitive `no_data`. Remaining account balance is not exposed by the API (Semrush → Subscription info). Earlier the same day (12:56–13:01) the base research used 1,390 units — separate run, separate report.

Snapshots for diffing: `storage/app/seo/keywords/ma/*2026-10-21_1348*`, `*_1349*`, `storage/app/seo/keywords/fr/*`.
Raw exports: `docs/seo/french/raw/07-broad-apprendre-allemand-ma.csv`, `07-validate-ma-42.csv`, `07-validate-ma-wave2.csv`, `07-validate-fr-8.csv`, `07-questions-allemand-ma-probe.csv`, `07-serp-*-ma.csv` (SERP CSVs have an empty keyword column — tooling nit, raw rows are quoted in the analysis).

## Caching
Cache TTL 24 h. Nothing from the 12:56 run was re-requested (different phrases/sets), so no cache hit occurred on live reports; the three SERP results were re-read from cache at 0 units. All rows in `07-*.csv` are `fresh_api_retrieval`.

## Freshness statement
Semrush reports retrieved at the timestamps above. The exact update date of the underlying search-volume data was not available.

## Limitations recorded
- KD: not requested → NOT_AVAILABLE for all 45 new Morocco keywords.
- SERP: 3 terms only; `apprendre l'allemand` has no organic data in `ma`.
- 32 of 66 requested candidates returned no Morocco data (listed in the expansion CSV as REQUESTED_NO_DATA).
- Question report in `ma` is noise; not worth further units.
- France figures are a control only and are never merged with Morocco figures.
- Tooling nits (not fixed here, no app files touched): `--estimate` for batch types uses `--limit` instead of the keyword count; `--csv` export for `phrase_organic` rows is empty-keyed.

## Files written by this task
`docs/seo/french/07-homepage-keyword-expansion.csv`, `07-keyword-page-mapping.csv`, `07-homepage-keyword-analysis.md`, `07-research-log.md`, `raw/07-*.csv`. No application, translation, view, route or sitemap file was modified; nothing staged or committed.

## Addendum 2026-10-27 — KD for 20 target keywords + Excel export
- 1 live call, `phrase_kdi`, database `ma`, 20 keywords, **1,000 units** (budget 1,000, guard estimate 50/keyword, no overshoot). Export: `raw/08-kd-top20-ma.csv`.
- Real KD returned only for `ausbildung` 64 and `centre allemand` 44. The other 18 (volumes 20–260) returned 0 = not computed. Combined with 2026-10-21 (9 of 12 at 0), Semrush computes KD in `ma` only for roughly ≥ 300 searches/month. Buying KD for the remaining 62 keywords (~3,100 units) would return almost only zeros — not recommended.
- Consolidated workbook: `GLS-keywords-research.xlsx` (Read me · All keywords (Morocco) 151 rows · By page 91 rows · France reference 8 rows). Type = intent classification by wording, SERP-confirmed for 3 keywords.
