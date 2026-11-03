# `centre allemand` implementation (2026-11-03, branch `seo/centre-allemand`)

This implements the priority actions 1, 2, 4 and 5 of `09-centre-allemand-position-1-strategy.md`. French only; EN, DE and AR are unchanged.

## Changes
| Area | Before | After |
|---|---|---|
| Homepage title | Apprendre l'allemand au Maroc \| Cours d'allemand GLS | **Centre allemand au Maroc – Apprendre l'allemand \| GLS** |
| Homepage meta | Apprenez l'allemand au Maroc avec GLS… | GLS Sprachenzentrum, centre allemand au Maroc : cours d'allemand A1 à C1 à Casablanca, Rabat, Marrakech, Kénitra, Salé, Agadir et en ligne. |
| Homepage H1 | Apprendre l'allemand au Maroc / Votre premier pas vers l'Allemagne | **Centre allemand au Maroc / Apprendre l'allemand avec GLS** (same animation) |
| Homepage intro | goal sentence only | + "GLS Sprachenzentrum est un centre de langue allemande présent à … ainsi qu'en ligne." |
| Homepage centres H2 | Nos Centres GLS | Nos centres d'allemand au Maroc (also on the noindex LPs) |
| Homepage centre cards | mixed labels ("École d'allemand à Marrakech", "Campus GLS Agadir"…) | "Centre allemand à {Ville} – GLS" (link label + image alt) |
| 6 centre titles | Cours d'allemand à {Ville} – Centre GLS… | **Centre allemand à {Ville} – Cours d'allemand GLS** |
| 6 centre H1 | GLS {Ville} – centre de langue allemande et cours d'allemand | Centre allemand GLS à {Ville} – cours d'allemand A1 à C1 |
| 6 centre metas | "…en petits groupes…" (unverified) | "…groupes du matin au soir…" (verified schedules) |
| 6 centre pages: link to the homepage | none | "GLS {Ville} fait partie de GLS Sprachenzentrum, **centre allemand au Maroc** présent à …" |
| 6 centre pages: structured data | none | EducationalOrganization + LocalBusiness: name, URL, phone, email, address, parentOrganization (no hours, geo or ratings: not reliably stated on the site) |
| Canonical + hreflang | kept every query string (`?ref=`, `?utm_…`, filters) | query strings removed, `?page=` kept |
| `/fr/online-courses` cards | 3 dead links | pricing page, ÖSD page, `#horaires` (new anchor on the schedule section) |
| Level pages A2–C1 CTA | `/online-registration` (500) | registration page |
| `/online-registration` route | 500 (view never existed) | 301 → `/{locale}/gls-inscription` |
| Intensive / online metas | "petits groupes" (unverified) | removed |

## Not changed (needs the owner's answer, or is outside the code)
- Exam-centre wording on the ÖSD, Goethe and About pages. This waits on confirmation of GLS's official status.
- Salé prices (two sets) and the Salé map embed, which points to Agdal, Rabat.
- The "4.9/5 (+677 avis)" claim.
- `http://` → `https://` returns 404. This is a server / Cloudflare setting ("Always Use HTTPS" or an nginx 301), not application code.
- Google Business Profiles, backlinks, the ÖSD B1/B2 sections, Studienkolleg intro content, and the Ausbildung guide.

## Verification (local, rendered through the Laravel kernel)
- `/fr` title, meta, H1 (8 word groups: "Centre allemand au Maroc Apprendre l'allemand avec GLS"), intro, centres H2, 6 card labels.
- 6 centre pages: title, H1, homepage link with anchor "centre allemand au Maroc", JSON-LD with the real address and phone. Marrakech was rendered through the route; the other 5 directly, because the local DB holds one site.
- Canonicals: `/fr/about?ref=x&utm_source=y` → `/fr/about`; `/fr/studienkollegs?q=berlin&page=3` → `?page=3`; `/fr/blog?page=2` kept.
- `/fr` and `/en/online-registration` → 301 to the registration page. `/de` and `/ar` titles unchanged. `/en/sites/marrakech` keeps its English title.
- 31 unit tests OK, `view:cache` OK, `php -l` clean, no credentials in the diff.

## Deploy
`git pull` (or merge the branch), then `php artisan view:clear && php artisan responsecache:clear`. No composer change, no migration, no sitemap change. Never run `route:cache`.

Local note: the local `vendor/composer/autoload_files.php` had lost `app/Support/seo_helpers.php` (every `seo_trans()` page returned 500 locally). `composer dump-autoload` fixed it. Production was not affected; the live crawl on 2026-11-03 rendered every page.
