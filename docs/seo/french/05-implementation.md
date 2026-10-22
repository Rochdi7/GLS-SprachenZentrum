# Implementation & verification (Phases 7–8) — 2026-10-21

Applied only after `04-research-validation.md` recorded the review gate as passed. Source of every change: `03-keyword-strategy.md`.

## What changed (55 modified, 9 new files; 234 insertions / 109 deletions)

**Metadata mechanism**
- `app/Support/seo_helpers.php` (new, autoloaded via `composer.json` "files"): `seo_trans($key, $default, $replace)` translates an SEO key only if it exists in the *current* locale. Without it, Laravel's fallback (`fr`) put French titles/descriptions on EN/DE/AR pages — caught during verification and fixed.
- Every targeted view now declares `@section('meta_description', seo_trans('…'))`; EN/DE/AR fall back to their own `config/seo.php` defaults as before.

**Titles / meta descriptions / H1 (French translation files)**
`home`, `about`, `intensive`, `online`, `pricing`, `niveaux/a1…c1`, `goethe`, `osd`, `sites/{6 centres}`, `studienkollegs` (new fr + en), `student-stories`, `quiz/level_test`, `contact`, `faq`, `templates/gls-form` (fr + en).
- Level pages: hidden English line (`title_secondary`) emptied and its `<span>` wrapped in `@if`, so each H1 is French only.
- Homepage: Arabic tagline moved out of the `<h1>` into a sibling `<p>` with the same animation classes.
- Online courses: H1 no longer says "Niveau A1".
- Salé centre: hero, about, offer list, info labels, Deutshow and CTA sections translated to French (prices, address, phone unchanged).
- Studienkollegs: French title + description, new H1 "Studienkollegs en Allemagne"; detail template uses `meta_description` (was the wrong section name `description`, so the default was shown) with a `:name`/`:city` fallback.
- `/gls-inscription`: own title and description (was the site default).

**Technical fixes**
- `footer.blade.php`: six centre links now use the live slugs `gls-*` (were 404 on every page).
- `GenerateSitemapCommand`: added `/niveaux/c1` and the six `/sites/gls-*` pages (regenerated locally: 200 URLs, includes the new paths; `public/sitemap.xml` is untracked and is regenerated on the server by `php artisan sitemap:generate`).

Not changed: URLs, canonical/hreflang generation, structured data, forms, CSS/JS (one 3-line style block added inside the Studienkolleg index view for the new H1), factual course/price content, EN/DE/AR copy.

## Verification

| Check | Result |
|---|---|
| Rendered through the Laravel HTTP kernel (local DB) | 18 FR pages: 200, new title/meta/H1 present, **no duplicate title or description** among them |
| EN (`/en/about`, `/en/niveaux/a1`, `/en/studienkollegs`, `/en/gls-inscription`, `/en/sites/marrakech`, `/en/exams/osd`) | English titles unchanged, English default description |
| DE / AR samples | German / Arabic defaults, no French leakage |
| `/fr/online-courses` | 2 `<h1>` in the template — **pre-existing** (same count at HEAD), not introduced; second H1 is in the online-course block |
| Broken `/sites/<city>` links | none from the footer; the only remaining hit is the local DB's `marrakech` slug in the header (production slugs are `gls-*`) |
| `php -l` on every changed PHP/lang file | clean |
| `php artisan view:cache` | compiles |
| `./vendor/bin/phpunit --testsuite Unit` | 31 tests, 60 assertions, OK |
| Laravel Pint on changed app files | clean |
| `git diff` scanned for the Semrush key | 0 hits; `.env` not tracked |
| Research files | all present before and after implementation (`01`…`04`, `pages/*.csv`, `raw/*.csv`) |

Not verifiable here: the six live centre pages and the Studienkolleg detail pages with production data (local DB has one centre and different Studienkolleg metadata). Rankings are not claimed; monitor after deploy.

## Deploy notes
1. `composer install` (new autoload file) → `php artisan view:clear` → `php artisan sitemap:generate`. **Never `route:cache`** (see CLAUDE.md).
2. In Search Console, request indexing for `/fr/sites/gls-*`, `/fr/niveaux/c1`, `/fr/studienkollegs`.

## Left unimplemented (documented, needs a decision)
- New guide page "Studienkolleg : niveau d’allemand requis" and a B1-resources blog post (see strategy §New-page opportunities).
- `ösd maroc` (1,300/mo) SERP check before building content around it (≈ 20 units/row with `phrase_organic`, limit 10).
- Per-post `meta_description` field for blog articles (CMS change).
- Content expansion on `/pricing` (379 words) and `/exams/gls` (placeholder).
