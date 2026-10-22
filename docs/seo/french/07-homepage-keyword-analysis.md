# Homepage keyword expansion — analysis (2026-10-21)

Research only. No website code, metadata, translation, routing or sitemap file was modified. Data files: `07-homepage-keyword-expansion.csv` (85 rows), `07-keyword-page-mapping.csv` (32 rows), raw exports `raw/07-*.csv`, log `07-research-log.md`.

## 1. Method and coverage

| Step | Report (Semrush MCP) | DB | Rows | Units |
|---|---|---|---|---|
| Broad-match discovery "apprendre allemand" | `phrase_fullsearch` | ma | 10 | 200 |
| Batch validation, 42 hand-built candidates (groups A–E) | `phrase_these` | ma | 23 returned, 19 NO_DATA | 230 |
| France comparison, 8 core terms | `phrase_these` | fr | 8 | 80 |
| Question-query probe "allemand" | `phrase_questions` | ma | 3 | 120 (40/row, now measured) |
| SERP probe "apprendre l'allemand" | `phrase_organic` | ma | no_data | unknown (no usage metadata on no_data) |
| Batch validation wave 2, 24 candidates (centre/Goethe/Ausbildung) | `phrase_these` | ma | 11 returned, 13 NO_DATA | 110 |
| SERP probes "centre allemand", "academie allemand", "ausbildung" | `phrase_organic` | ma | 3 each | 30 each (10/row, now measured) |
| **Total** | 9 live calls, 0 cached reuses needed | | | **830 known + 1 unknown** (budget 1,000; guard active on every call) |

All volumes = Semrush monthly estimates, database **`ma` (Morocco)** unless the row says `fr`. KD was not requested (50 units/keyword) → `NOT_AVAILABLE` on every new row. No France figure is used for any assignment. Retrieval 13:48–13:49 UTC; Semrush exposes no update date for the underlying estimates.

Gaps in the existing research that this run targeted: generic "centre allemand" / "école" / "langue allemande" vocabulary, apostrophe variants, city + "centre" combinations, Ausbildung/work-in-Germany goals, question queries, and the France market as a control.

## 2. What changed in the picture

**a. "centre allemand" is the real homepage concept term.** `centre allemand` = **590/mo** (CPC 0.39). The SERP top-3 are private German-language centres in Morocco (centreallemand-hamra.com, academieallemande.ma, bremencenter.com), so the intent is commercial/local — exactly GLS. The brief's seed "centre allemand au Maroc" (30) was the right idea with the wrong modifier: Moroccans drop "au Maroc" and add the city instead.

**b. City demand is "centre allemand + ville", not "cours allemand + ville".**
| City | centre allemand + city | Previous best term |
|---|---|---|
| Rabat | **260** | cours d allemand rabat 30 |
| Casablanca | **210** | centre de langue allemande casablanca 90 |
| Agadir | **170** | NO_DATA |
| Marrakech | **140** | cours allemand marrakech 20 |
| Kénitra | **90** | cours allemand kenitra 0 |
| Salé | NO_DATA | NO_DATA |

**c. Big numbers that are NOT targets.** `ausbildung` 6,600 (SERP = ausbildung.de, azubiyo.de, arbeitsagentur.de → German job portals, informational), `academie allemand` 2,900 (SERP = academieallemande.ma + its social profiles → competitor brand, navigational), `goethe rabat` 880 / `institut goethe rabat` 110 / `centre culturel allemand*` (Goethe-Institut, navigational).

**d. Confirmed, smaller additions.** `apprendre allemand` 70, `langue allemande` 210 (ambiguous, likely partly dictionary intent), `travailler en allemagne` 170 (+ `maroc` 20), `apprendre la langue allemande` 30, `école allemande` 30 (ambiguous: can mean a German school), `apprendre l allemand a1` 40 / `apprendre allemand a1` 30 / `allemand pour débutant` 20, `cours d'allemand` 20 (apostrophe form), `cours du soir allemand` 10, `ausbildung maroc` 30, `ausbildung en allemagne` 20, `comment apprendre l'allemand` 20.

**e. Still no demand in Morocco (32 NO_DATA):** école d'allemand, institut de langue allemande, centre de formation allemand, inscription cours allemand, cours intensif d'allemand, cours d'allemand en présentiel, cours particulier(s) allemand, prof/professeur d'allemand, cours d'allemand pour adultes, préparation examen allemand, meilleur centre allemand maroc, école (de langue) allemande casablanca/rabat, centre de langue allemande rabat/marrakech, cours de langue allemande casablanca/rabat, langue allemande cours, centre d'apprentissage allemand, formation ausbildung, ausbildung allemagne maroc, centre allemand salé, académie allemande (accented) … Full list in the CSV with `status = REQUESTED_NO_DATA`.

**f. France control (never merged):** `apprendre l'allemand` 1,900, `cours d'allemand` 1,300, `cours allemand` 720, `cours d'allemand en ligne` 720, `formation allemand` 590 in `fr` vs 170/20/70/20/20 in `ma`. The French market is 10–60× larger with very different CPCs (1.4–3.4 €); it says nothing about Moroccan demand and is not used for any decision. `apprendre l'allemand au maroc` = 0 in `fr`.

## 3. Intent and competition evidence

- SERP evidence exists for 3 terms (above). For all other terms intent is inferred from wording and marked `medium`/`low` where ambiguous (`langue allemande`, `école allemande`, `apprendre l'allemand rapidement`).
- `phrase_organic` returned **no_data** for `apprendre l'allemand` in `ma`, so the direct competitor set for the current primary could not be read.
- Competition column = Semrush paid-competition index, not organic difficulty. KD not collected in this run.
- Question report (`phrase_questions "allemand"`) is noise in `ma` (translation and dog queries) — not worth more units.

## 4. Recommended homepage keyword set (recommendation, not implemented)

Structure requested: homepage = GLS as the German-language school/centre in Morocco, broad intent only.

| Role | Keyword (ma) | Volume | Evidence |
|---|---|---|---|
| **Primary** | `centre allemand` | 590 | SERP = Moroccan language centres; highest relevant generic demand |
| Co-primary / title | `apprendre l'allemand` | 170 | current primary, keep in the title |
| Secondary | `centre allemand maroc` | 30 | brief's seed, exact concept |
| Secondary | `apprendre allemand` | 70 | variant |
| Secondary | `cours allemand` | 70 | mention only; owner = `/intensive-courses` |
| Supporting (copy) | `langue allemande` 210 · `apprendre la langue allemande` 30 · `école allemande` 30 · `apprendre à parler allemand` 20 · `apprendre l'allemand rapidement` 20 · `apprendre l'allemand au maroc` 20 | | natural phrasing in hero/intro, no title use |

Suggested title (≤60 chars, for a later implementation task): `Centre allemand au Maroc – Apprendre l'allemand | GLS` — combines the two strongest terms without stuffing. H1 can stay "Apprendre l'allemand au Maroc : votre premier pas vers l'Allemagne" with "centre de langue allemande" in the first paragraph. Do **not** put city names, levels, exam names or prices in the homepage title; each has its own page.

Cannibalisation rules: homepage owns "centre allemand" + "apprendre l'allemand" (generic); city pages own "centre allemand + ville"; `/intensive-courses` owns "cours (d')allemand"; `/about` stays brand-only (its "école de langue allemande" term has no demand).

## 5. Keywords better assigned elsewhere

| Page | Add | Note |
|---|---|---|
| `/sites/gls-rabat` | `centre allemand rabat` 260 → **new primary**, `cours d'allemand rabat` 30 | previous primary 30 becomes secondary |
| `/sites/gls-casablanca` | `centre allemand casablanca` 210 → secondary (primary stays `centre de langue allemande casablanca` 90? — recommend swapping: 210 > 90), `cours d'allemand casablanca` 30 | |
| `/sites/gls-agadir` | `centre allemand agadir` 170 → **new primary** | page had no measured term |
| `/sites/gls-marrakech` | `centre allemand marrakech` 140 → **new primary** | |
| `/sites/gls-kenitra` | `centre allemand kenitra` 90 → **new primary** | |
| `/niveaux/a1` | `apprendre l allemand a1` 40, `apprendre allemand a1` 30, `allemand pour débutant` 20 | |
| `/intensive-courses` | `cours d'allemand` 20, `formation langue allemande` 20, `cours du soir allemand` 10 | |
| `/student-stories` | `travailler en allemagne` 170, `travailler en allemagne maroc` 20 | until a guide page exists |
| `/blog` (new article) | `comment apprendre l'allemand` 20 | informational |

Title pattern suggestion for centre pages (later task): `Centre allemand à {Ville} – Cours d'allemand GLS`.

## 6. New landing-page opportunities (evidence-based, not created)

1. **"Ausbildung en Allemagne : conditions, démarches et niveau d'allemand"** (guide). `ausbildung` 6,600 + `ausbildung maroc` 30 + `ausbildung en allemagne` 20 + `travailler en allemagne` 170. The SERP is German portals, so a French guide for Moroccans is a genuine gap; GLS's role (B1/B2 preparation, student stories) must be the angle. Informational traffic, indirect conversions. Low overlap with `/studienkollegs`.
2. No evidence for a separate "école d'allemand" or "inscription" page (all NO_DATA).

## 7. Limitations

- KD missing for every new keyword (not purchased). KD 0 elsewhere remains unreliable.
- SERP evidence for 3 terms only; `apprendre l'allemand` SERP unavailable in `ma`.
- `langue allemande`, `école allemande`, `apprendre l'allemand rapidement` intents unverified.
- Salé still has no measurable term in any phrasing tested.
- Semrush estimates are monthly averages with no exposed refresh date; `ma` data is thin, so 10–30 values are low-confidence.
- `--estimate` for batch reports prints the default `--limit` (30) instead of the keyword count (tooling nit; the guard itself counts keywords correctly). The `--csv` export of `phrase_organic` rows is not usable (keyword column empty: the normaliser expects a Keyword column); raw rows were read from the cache. Both noted for a later tooling fix; not changed in this task.

## Recommendation

The homepage set is now evidence-backed: `centre allemand` (590, SERP-confirmed commercial/local) + `apprendre l'allemand` (170) are the two terms worth the title, with a short secondary cluster. The expansion mostly strengthens the **city pages** (260/210/170/140/90) and surfaces one **new guide opportunity** (Ausbildung). Remaining uncertainty: no KD for the new terms, no SERP for the current primary, and ambiguous intent on three supporting phrases.
