# GLS Sprachenzentrum — French page inventory (Phase 1)

Crawled 2026-10-21 from the live site `https://gls-sprachzentrum.ma/fr` (the canonical host; `glssprachenzentrum.ma` 301s to it) plus the Laravel routes, Blade views and `sitemap.xml`. Source data: `01-page-inventory.csv` (36 rows, one row stands for the 21 Studienkolleg detail pages).

## How SEO metadata works in this project

- Every public view extends `frontoffice.layouts.app`, which includes `frontoffice/partials/seo-head.blade.php`.
- Title = `@section('title')`, description = `@section('meta_description')`. When a view defines neither, `config/seo.php` `defaults.fr` is used.
- Canonical and hreflang (fr/en/de/ar + x-default→fr) are generated per URL by `LaravelLocalization`. OG/Twitter tags mirror title/description.
- Texts come from `resources/lang/fr/*.php`, so keyword changes are translation-file edits, not template edits.

## Page groups

| Group | Pages | Purpose | Research |
|---|---|---|---|
| A-core | `/`, `/about`, `/intensive-courses`, `/online-courses`, `/pricing` | Centre/brand concept, course formats, prices | Dedicated keyword groups |
| B-levels | `/niveaux/a1 … c1` | One page per CEFR level | Shared cluster "cours d’allemand + niveau" |
| C-exams | `/exams/goethe`, `/exams/osd` | Exam preparation + on-site exams | Dedicated (Goethe / ÖSD) |
| D-centres | `/sites/gls-{casablanca,rabat,marrakech,kenitra,sale,agadir}` | Local landing pages | Shared cluster "cours d’allemand + ville" |
| E-germany | `/studienkollegs` (+21 detail), `/student-stories` | Study-in-Germany pathway | Index page only; details are entity pages |
| F-support | `/discover-your-level`, `/faq`, `/blog`, blog article, `/contact` | Tools, informational, contact | Supporting keywords only |
| none | `/exams/gls` (coming soon), forms, legal, utilities, `/partners/fc-marokko`, `/lp/*` (noindex) | No organic target | Not researched |

## Problems found (before any keyword work)

1. **32 of 36 pages share the same meta description** — the `config/seo.php` fallback. Only `/`, `/blog`, the blog article and `/student-stories` define their own. This is the biggest on-page issue.
2. **Footer links to six 404 URLs.** `footer.blade.php` builds `/sites/casablanca`, `/sites/rabat`, … but the live slugs are `gls-casablanca`, `gls-rabat`, … Every page on the site therefore carries six broken internal links.
3. **Centre pages and `/niveaux/c1` are missing from `sitemap.xml`.**
4. **English leaking into FR pages:** `/studienkollegs` title "Studienkollegs in Germany" (and no H1); `/sites/gls-sale` content fully English ("Welcome to GLS Salé", "What We Offer"); level pages' H1 contain a hidden English copy ("Learn German at Level A1"); `/discover-your-level` H2 "Select your Quiz!".
5. **Wrong H1 on `/online-courses`:** "Apprenez l’allemand – Niveau A1" (page is about online courses, not A1) → cannibalises `/niveaux/a1`.
6. **Homepage H1 mixes French and Arabic** in one tag ("Votre premier pas vers l'Allemagne خطوتك الأولى نحو ألمانيا").
7. **`/gls-inscription` has no title section** → shows the site default title (duplicate of `/lp/*`).
8. `/about` H1 ("L’école de langue allemande au Maroc") and the homepage both target the "école/centre de langue allemande au Maroc" concept → decide one owner (homepage).
9. `/exams/goethe` and `/exams/osd` share the same H2 skeleton; fine as long as titles/H1 stay exam-specific.
10. Thin pages: `/pricing` (379 words), `/exams/gls` (placeholder), `/studienkollegs` index relies on cards.
11. `/student-stories` meta description is 187 characters (truncated in SERPs).

## Missing information

- The local DB has 1 site (`marrakech`) and 0 blog posts; production has 6 sites and 1 article. Inventory uses the live site.
- Keyword difficulty, volumes: not yet collected (Phase 3).
- `/exams/gls` product details unknown → excluded from targeting.

Website code was not modified in this phase.
