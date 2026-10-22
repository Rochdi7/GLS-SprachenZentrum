# Final diff review & deployment readiness — 2026-10-21

Scope: review only. Nothing was committed, pushed or deployed. No Semrush API call was made during this review (usage log unchanged: 5 live calls / 1,390 units on 2026-10-21).

## 1. Git status and real file counts

| | Count | Note |
|---|---|---|
| Modified (tracked) | **55** | matches `05-implementation.md` |
| Untracked files (`git status -uall`) | **43** | `05` said "9 new": that was the collapsed listing (9 untracked *entries*, directories counted once). Real files: 16 code/doc files + 27 research CSVs (`docs/seo/french/pages/` 22, `raw/` 5) |
| Modified outside the SEO scope | 0 | all under `resources/lang`, `resources/views/frontoffice`, `GenerateSitemapCommand`, `composer.json`, `config/services.php`, `.env.example` |
| `composer.lock` | untouched | only `autoload.files` was added to `composer.json` |
| Pre-existing uncommitted user work | none found | tree was clean before this work started (`deb7b73`) |
| Secrets in diff | 0 | `.env` untracked; grep for the key prefix in `git diff` = 0 |

Untracked, non-research code: `.claude/skills/semrush-keywords/SKILL.md`, `app/Console/Commands/SeoKeywordsCommand.php`, `app/Services/Seo/{SemrushService,Exceptions/*}.php`, `app/Support/seo_helpers.php`, `resources/lang/{fr,en}/studienkollegs.php`, `tests/Unit/SemrushServiceTest.php`, `docs/seo-keyword-research.md`, `docs/seo/french/01…06`.

## 2. Implementation vs `05-implementation.md`

| Item | Verified | Finding |
|---|---|---|
| French titles / meta descriptions (22 pages) | yes | rendered through the HTTP kernel; all unique |
| `seo_trans()` helper | yes | `Lang::has($key, locale, fallback=false)` → default; autoloaded via `composer.json` `files` |
| EN/DE/AR | yes | own titles kept, own `config/seo.php` default description; no French leakage (`/en/about`, `/en/niveaux/a1`, `/en/sites/marrakech`, `/en/exams/osd`, `/de/contact`, `/de/sites/marrakech`, `/de/studienkollegs`, `/ar/faq`, `/ar/gls-inscription`) |
| Homepage H1 | yes | French only; Arabic tagline is a sibling `<p>`. **Review fix:** I had added `aria-hidden="true"` to that `<p>` — removed, the tagline is real content |
| Level H1s | yes | English `title_secondary` emptied, span wrapped in `@if` |
| Studienkolleg title / H1 / meta | yes | index: FR title + new H1, EN file present, DE/AR keep the former English default; detail page: wrong `@section('description')` fixed to `meta_description`, DB `meta_*` still take precedence |
| Footer slugs | yes | six links → `gls-*` (diff shows exactly those 6 lines) |
| Sitemap | yes | `+/niveaux/c1`, `+6 × /sites/gls-*` |
| Salé page | yes | hero/about/offers/info/Deutshow/CTA now French; prices, address, phone untouched |
| `/gls-inscription` title | yes | FR + EN keys; DE/AR fall back to site default (as before) |
| `/online-courses` H1 | **corrected the claim** | `05` said the H1 "no longer says Niveau A1". True, but that H1 sits in a `display:none` hero (`.is-course-hero.hide`); the *visible* H1 was and is "Apprenez l’allemand en ligne". The page therefore had **two** `<h1>` (pre-existing). **Review fix:** the hidden one is demoted to `<p>` with the same classes → exactly one H1 |

Unrelated / duplicated / risky changes: none found. `config/services.php` + `.env.example` additions are the Semrush tooling settings from the earlier task (same branch of work).

## 3. SEO behaviour and localisation (rendered via `Illuminate\Contracts\Http\Kernel`, local DB)

- 18 FR pages: 200, intended title/meta/H1, **no duplicate title or description**.
- Canonical = own URL per locale; hreflang fr/en/de/ar + x-default→fr on every page checked (`/fr/about`, `/en/sites/marrakech`, `/ar/niveaux/b1`, `/fr/lp/google`).
- `robots: noindex,nofollow` intact on `/fr/lp/google` and `/en/lp/google`.
- Note on method: locale routes register on the first request of a PHP process, so each locale was rendered in its own process; cross-locale 404s inside one process are a harness artefact, not a site defect.

## 4. Sitemap, canonical, hreflang, robots, footer

- Generated locally (`sitemap:generate`, 200 URLs, untracked file, regenerated on the server): contains `/niveaux/c1` and the six `/sites/gls-*`; contains **no** `/lp/*`, `/gls-inscription`, `/demande-attestation`, `/feedback`, `/traductions/*`.
- **Pre-existing, unchanged, flagged for a decision:** `/terms`, `/privacy`, `/certificate-check`, `/partners/fc-marokko`, `/exams/gls` (placeholder) are in the static list. Not an error; low value. Recommendation: drop `/exams/gls` until the product exists.
- Footer: zero `/sites/<city>` links from the footer in any rendered page. The one remaining hit (`/fr/sites/marrakech`) comes from the header's DB-driven centre list and the local DB's `marrakech` slug; production slugs are `gls-*` (verified on the live site on 2026-10-21).

## 5. CSV integrity and keyword ownership

`review_gate.py` re-run: **ALL 14 CHECKS PASSED** (schema, 1 primary per page, typed metrics, `ma` on all 76 rows, per-page ↔ master agreement, primary first, 2 shared keywords flagged, cannibalisation documented, no navigational/irrelevant keyword as primary/secondary, usage log = 5 calls / 1,390 units).
Spot checks: `ösd maroc` = secondary, confidence low, note "Intent not verified"; KD 0 rows carry "Semrush returned 0 … treat as unreliable"; `NO_DATA` (18) ≠ verified 0 (3) ≠ `NOT_AVAILABLE` KD (62); `goethe institut casablanca` = excluded/navigational; relevance-only primaries (A2, C1, pricing, about, Kénitra, Agadir, Salé) say so explicitly; `cache_status` = `fresh_api_retrieval` on every row.

## 6. Commands executed

| Command | Result |
|---|---|
| `git status --short -uall`, `git diff` (per file) | reviewed; counts above |
| `php verify_render.php …` (kernel render, FR / EN / DE / AR) | all 200, checks above |
| `python review_gate.py` | 14/14 pass |
| `./vendor/bin/phpunit --testsuite Unit` | **OK (31 tests, 60 assertions)** — run 3× during the review, last after the two fixes |
| `./vendor/bin/pint --test` on `app/Support`, `app/Services/Seo`, both commands, the test | clean |
| `php artisan view:clear && php artisan view:cache` | compiles (427 views) |
| `php artisan sitemap:generate` (local) | 200 URLs, new paths present; local file not tracked |
| `php -l` on every changed PHP/lang file | clean |

Deployment commands assessed (not run against production):
- `composer install` — needed because `composer.json` gained `autoload.files`; `composer.lock` is unchanged so no package changes; `install` regenerates the autoloader. `composer dump-autoload` alone would also do.
- `php artisan view:clear` — safe; views are recompiled on demand. (`view:cache` optional.)
- `php artisan sitemap:generate` — needs DB access; writes `public/sitemap.xml`; safe.
- `route:cache` — **must not** be run (CLAUDE.md).

## 7. Semrush budget safeguards (code review, no API calls)

`SemrushService::assertBudget()` runs before every live call: max-calls check → remaining-units check → `measuredUnitsPerRow()` from the JSONL usage log → if no history and rows > `PROBE_ROWS` (3) → stop unless `allowUnmeasured()` (CLI `--allow-unmeasured`, default off) → if history: `ceil(perRow × rows) > remaining` → stop. Rows = `display_limit` or the number of `;`-separated keywords. Cached hits bypass the guard (intended). Tests: `test_unmeasured_report_is_limited_to_a_probe_unless_allowed`, `test_measured_price_blocks_a_call_that_would_exceed_the_budget`, plus the two existing budget tests and the cached-not-blocked test.
Residual uncertainty: (a) the estimate is an average of past units/row — if Semrush changes a price the first call after the change can still overshoot by the difference; (b) a report returning fewer rows than requested is estimated at the requested size (over-estimate, safe side); (c) the usage log is per environment, so a fresh server has no history and starts in probe mode (safe).

## 8. Files modified during this review

- `resources/views/frontoffice/home.blade.php` — removed `aria-hidden="true"` from the Arabic tagline `<p>`.
- `resources/views/frontoffice/online-courses.blade.php` — hidden hero `<h1>` → `<p>` (same classes, block is `display:none`).
- `docs/seo/french/06-final-review.md` — this file.
Both code fixes re-rendered and covered by the test/compile runs above.

## 9. Remaining defects and recommendations (none blocking)

1. Visible `/online-courses` H1 is "Apprenez l’allemand en ligne" (`online.hero.title`), not the strategy's longer wording. Acceptable for intent; optional copy change.
2. Sitemap still lists legal/utility/placeholder pages (pre-existing) — decide whether to drop `/exams/gls`, `/terms`, `/privacy`, `/certificate-check`.
3. Studienkolleg detail pages use DB `meta_title`/`meta_description` (English) when set; the French fallback only applies where those are empty.
4. Semrush key rotation still pending (pasted in chat on 2026-10-19).
5. Live verification of the six centre pages and the Studienkolleg pages with production data is only possible after deploy.

## Verdict

**READY TO COMMIT** (the two review fixes are already applied and tested). Deploy with `composer install` → `php artisan view:clear` → `php artisan sitemap:generate`; never `route:cache`.
