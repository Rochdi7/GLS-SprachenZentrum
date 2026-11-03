# Research log, phase 08 (2026-11-03)

## Scope and rules followed
- Research and audit only. No application code, content, translation, route, sitemap or database record was modified. No package was installed. The Semrush integration and its guards were not changed. Nothing was staged or committed.
- Credentials were not read or printed. Every Semrush call went through `App\Services\Seo\SemrushService` with its budget guard, called from `php artisan tinker` with the remaining task budget passed explicitly.
- **Task budget: 1,000 units.** Measured prices from the usage log were checked before each call. Unmeasured reports were probed first with ≤3 rows.

## Sources
| Source | What | File |
|---|---|---|
| Existing research | 01–07, 02 master, 07 expansion, `pages/*.csv`, `raw/*`, the Excel export | reused, not re-queried |
| Live site crawl | 63 French URLs from the sitemap, internal links and known routes, plus 9 URL variants and robots.txt | `raw/08-gls-live-crawl.json` |
| Competitor pages | 8 SERP URLs (goethe.de returned 403) plus the GLS homepage | `raw/08-competitor-pages-crawl.json` |
| Source code | French translations, routes, views (facts in `08-gls-business-understanding.md`) | — |
| Semrush MCP (`mcp.semrush.com`) | report schemas (free) and the calls below | `raw/08-*.json` |

Browser automation and screenshots were not needed: pages are server-rendered Blade, and curl captures the full HTML. The animated homepage H1 was checked in raw HTML and reads as normal text.

## Semrush calls (database `ma` unless noted)

| # | Time UTC | Report | Request | Rows | Units | Result |
|---|---|---|---|---|---|---|
| 1 | 19:00:55 | phrase_these + `intent` + `keyword_difficulty` | 3 known keywords (probe) | 3 | 30 | **KD and intent cost no extra**: 10/keyword vs 50 for phrase_kdi |
| 2 | 19:01 | phrase_organic | "centre allemand", top 10 | 10 | 100 | GLS #10 |
| 3 | 19:01 | domain_rank | gls-sprachzentrum.ma | 1 | 10 | price measured: 10/domain |
| 4 | 19:01 | backlinks_overview (global) | gls-sprachzentrum.ma | 1 | 40 | price measured: 40/domain |
| 5–10 | 19:01 | domain_rank | 6 competitors | 6 | 60 | |
| 11–14 | 19:01 | backlinks_overview | 4 competitors | 4 | 160 | |
| 15 | 19:02 | resource_organic | GLS keywords, probe (sorted by traffic) | 3 | 30 | price measured: 10/row |
| 16 | 19:02 | resource_organic | GLS keywords, offset 3 | 23 | 200 | all 26 GLS rankings |
| 17 | 19:02:50 | phrase_these + intent + KD | 34 new candidates | 21 returned | 210 | 13 without data, not billed |
| 18 | 19:03 | phrase_organic | "centre allemand rabat", top 3 | 0 | unknown | no_data |
| 19 | 19:03 | phrase_organic | "centre allemand casablanca", top 3 | 0 | unknown | no_data |
| — | | cache reuse | first page of #15 and SERP #2 re-read | | 0 | 2 cache hits |

**Total: 19 live calls, 840 units known, plus 2 no_data calls whose usage Semrush does not report.** That stays within the 1,000-unit task budget. There were no guard stops, no retries and no errors besides the 2 no_data results. The remaining account balance is not exposed by the API; read it in Semrush → Subscription info.

Cumulative Semrush spend for the French research: 30 (Oct 19) + 1,390 + 830 (Oct 21) + 1,000 (Oct 27) + 840 (Nov 3) = **≈ 4,090 units**.

## Cost lesson (reported honestly)
`phrase_these` with `export_columns: [intent, keyword_difficulty]` returns KD and Semrush intent at **10 units per keyword returned**. The dedicated `phrase_kdi` report charges 50. The 1,000 units spent on KD for 20 keywords on 2026-10-27 would have cost about 200 this way. For all future KD needs, use `phrase_these` with these columns. The `seo:keywords --type=validate` command does not request them yet: a tooling improvement, not made here because the integration was off-limits.

## Freshness
Semrush reports were retrieved at the timestamps above. The exact update date of the underlying search-volume data was not available. Keywords from earlier phases keep their original retrieval dates (Oct 19–27) and were not re-queried. Values for the 3 probe keywords were refreshed and are unchanged: centre allemand 590, studienkolleg 390, ausbildung 6,600.

## Limitations
- Intent and KD are only computed by Semrush for higher-volume terms (≈ ≥300/month in `ma`). Below that, both come back empty or 0. 16 of 163 relevant keywords have Semrush intent, and 15 have a real KD.
- SERP data exists for `centre allemand` (top 10) only. City SERPs returned no data. Earlier phases have top-3 SERPs for `academie allemand` and `ausbildung`.
- Competitor backlink *quality* was not audited. Only counts and Authority Score were measured.
- Question keywords were tested by exact phrase earlier and returned no data. The Semrush questions report in `ma` was noise (Oct 21), so no further units went there.
- `goethe.de` blocks automated fetches (403), so its page was not analysed.
- France data was not used (phase 07 already showed the markets differ by 10–60×).
- The user's example `berger allemand chien` was not queried. All 7 dog-breed variants that Semrush surfaced earlier are in `08-rejected-keywords.csv`.

## Files created in this phase
`08-gls-business-understanding.md`, `08-complete-page-inventory.csv`, `08-page-keyword-research.csv`, `08-page-keyword-strategy.md`, `08-keyword-page-mapping.csv`, `08-rejected-keywords.csv`, `08-research-log.md`, `09-centre-allemand-position-1-strategy.md`, and the raw files `raw/08-probe-intent-kd-ma.json`, `raw/08-serp-ma.json`, `raw/08-competitors-domain-backlinks.json`, `raw/08-gls-organic-positions-ma.json`, `raw/08-validate-ma-34-intent-kd.json`, `raw/08-gls-live-crawl.json`, `raw/08-competitor-pages-crawl.json`.
