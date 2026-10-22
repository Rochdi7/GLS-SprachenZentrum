# GLS Sprachenzentrum — French keyword strategy for Morocco (Phase 4)

All volumes below are **Semrush, database `ma` (Morocco), retrieved 2026-10-21 12:56–13:01 UTC**, fresh API retrievals. Semrush does not expose the update date of the underlying monthly estimates. KD = Semrush Keyword Difficulty from `phrase_kdi` (12 keywords only, 50 units each); "KD 0" on low-volume terms usually means not computed and is treated as unreliable. "Competition" is Semrush's paid-search competition index (0–1), not organic difficulty. `NO_DATA` = requested in the batch and not returned by Semrush for `ma`. Nothing from the French (`fr`) database is used.

Measured data is in `02-keyword-research-all.csv` and `pages/*.csv`. Everything under "Recommended" is my analysis, not Semrush data.

## Reading the market first

- Demand is small. The whole relevant French-language cluster in Morocco is a few hundred searches a month. The biggest clean term is `studienkolleg` (390). The seed ideas from the brief (`centre allemand au Maroc` 30, `centre de langue allemande au Maroc` NO_DATA, `cours allemand au Maroc` NO_DATA) are **not** the demand drivers; Moroccans search `apprendre l'allemand` (170), `cours allemand` (70), `cours en allemand` (110) without the country modifier, and city modifiers only for Casablanca (90/30/20) and Rabat (30).
- `goethe institut casablanca` (590, KD 37) and `goethe institut maroc` (110) are navigational to the Goethe-Institut and are **excluded** as targets.
- `ösd maroc` (1,300, KD 26, CPC 0) is the only large number and its intent is **unverified** (no SERP budget). Treat it as a hypothesis until a SERP check confirms it refers to the ÖSD exam.
- "allemand maroc" broad match is polluted by dog-breed and GIZ queries; that seed is exhausted and should not be re-run.

## Prioritisation framework

Score = relevance to a real GLS service (gate) → intent (enrolment > informational) → Morocco volume → KD when known → existing page strength. Volume alone never decides. No ranking position or enquiry count is promised.

| Priority | Page | Primary keyword | MA volume | KD | Why |
|---|---|---|---|---|---|
| 1 | `/` | apprendre l'allemand | 170 | 0* | Highest relevant demand, strongest page, already aligned |
| 2 | `/studienkollegs` | studienkolleg | 390 | 30 | Largest clean term; page exists but has an English title and no H1 |
| 3 | `/sites/gls-casablanca` | centre de langue allemande casablanca | 90 | 0* | Best local query; page currently 404 from the footer and absent from sitemap |
| 4 | `/intensive-courses` | cours allemand | 70 (+110 variant) | 0* | Core course query |
| 5 | `/exams/goethe` | examen goethe | 30 (+30 certificat) | 0* | Exam intent, GLS prepares on site |
| 6 | `/exams/osd` | examen ösd | 20 (+1,300 unverified) | 26 | Upside if `ösd maroc` is confirmed |
| 7 | `/niveaux/a1` | cours a1 allemand | 30 (+30/20/30) | 0* | Only level with measurable demand |
| 8 | `/online-courses` | cours d'allemand en ligne | 20 | — | Wrong H1 today |
| 9 | `/sites/gls-rabat` | cours d allemand rabat | 30 | — | |
| 10 | `/discover-your-level` | test de niveau allemand | 20 | — | Tool page, supports funnel |
| 11+ | other levels, other centres, pricing, about | relevance-only | 0–20 / NO_DATA | — | Fix metadata, no traffic expectation |

\* KD returned as 0 — unreliable for low-volume terms.

---

## Page-by-page

### 1. Homepage — `https://gls-sprachzentrum.ma/fr`
- **Purpose:** brand + "learn German in Morocco" entry point.
- **Primary:** `apprendre l'allemand` — 170/mo, CPC 0.10, comp 0.15, KD 0*. Evidence: `phrase_these` 2026-10-21.
- **Secondary:** `centre allemand maroc` 30 · `apprendre l'allemand au maroc` 20 (shared with the blog article, see cannibalisation) · supporting: `allemand maroc` 90 (ambiguous), `apprendre allemand maroc` 0, `centre de langue allemande (maroc)` NO_DATA, `cours allemand` (owner: intensive page).
- **Intent:** commercial.
- **Why:** the only "learn German" query with real Moroccan volume; the current title already uses it, so this is a consolidation, not a pivot.
- **Excluded:** `goethe institut maroc` (navigational), `giz maroc …`, dog-breed queries.
- **Cannibalisation:** `/about` H1 also claims "école de langue allemande au Maroc" → about page becomes brand/institutional, homepage owns the concept. Blog article owns the informational "pourquoi apprendre l'allemand au Maroc".
- **Recommended title:** `Apprendre l'allemand au Maroc | GLS Sprachenzentrum` (keep; 52 chars)
- **Recommended meta:** `Apprenez l'allemand au Maroc avec GLS : cours d'allemand A1 à C1, préparation aux examens Goethe et ÖSD, 6 centres (Casablanca, Rabat, Marrakech…).`
- **Recommended H1:** `Apprendre l'allemand au Maroc : votre premier pas vers l'Allemagne` — move the Arabic line out of the `<h1>` into its own element.
- **Content:** the "Nos cours" block should link each level card to `/niveaux/*`, and the centres block to the correct `gls-*` slugs.
- **Internal links:** → `/intensive-courses` (anchor "cours d'allemand intensifs"), → `/studienkollegs` ("Studienkolleg en Allemagne"), → `/sites/gls-casablanca` ("centre de langue allemande à Casablanca").
- **Confidence:** high on the primary; low on all NO_DATA concept terms.

### 2. About — `/about`
- **Primary:** `école de langue allemande` — NO_DATA in MA. Relevance-only (institutional page); not a traffic target.
- **Supporting:** `école allemande maroc`, `institut allemand maroc` — NO_DATA.
- **Intent:** commercial/brand.
- **Cannibalisation:** H1 currently duplicates the homepage concept. Keep the wording but let the title lead with the brand.
- **Title:** `À propos de GLS – École de langue allemande au Maroc`
- **Meta:** `GLS Sprachenzentrum, école de langue allemande au Maroc depuis Rabat : notre équipe, nos 6 centres, nos méthodes et nos certifications Goethe et ÖSD.`
- **H1:** keep `GLS Sprachenzentrum – L'école de langue allemande au Maroc`.
- **Links:** → `/student-stories`, → `/contact`.
- **Confidence:** low (no measurable demand).

### 3. Intensive courses — `/intensive-courses`
- **Primary:** `cours allemand` — 70, CPC 0.30, comp 0.28, KD 0*.
- **Secondary:** `cours en allemand` 110 (ambiguous: may mean courses taught in German; use as a copy variant) · `allemand cours` 70 · `cours de langue allemande` 30 · `formation en allemand` 30 · `formation allemand` 20 · `cours intensif allemand` 10 · supporting: `cour d allemand` 20 (misspelling), `cours intensifs allemand`, `cours allemand maroc`, `cours d'allemand au maroc`, `formation allemand maroc` NO_DATA.
- **Intent:** commercial.
- **Why:** the product page for courses; homepage mentions courses but should not compete for the title.
- **Excluded:** `cours b1 allemand pdf` (free-material intent).
- **Title:** `Cours d'allemand intensifs A1 à C1 au Maroc | GLS`
- **Meta:** `Cours d'allemand intensifs et formation en allemand au Maroc, du niveau A1 au C1 : petits groupes, 6 centres, préparation aux examens Goethe et ÖSD. Inscription en ligne.`
- **H1:** `Cours d'allemand intensifs – A1 à C1`
- **Content:** add one H2 "Formation en allemand pour adultes et professionnels" (uses the `formation` vocabulary); link each level to `/niveaux/*`.
- **Links:** → `/pricing`, → `/niveaux/a1`, → `/online-courses`.
- **Confidence:** high.

### 4. Online courses — `/online-courses`
- **Primary:** `cours d'allemand en ligne` — 20, CPC 0.37, comp 0.75.
- **Secondary:** `apprendre l'allemand en ligne` 20 · `cours allemand en ligne` 10.
- **Intent:** commercial.
- **Cannibalisation:** current H1 "Apprenez l'allemand – Niveau A1" collides with `/niveaux/a1`.
- **Title:** `Cours d'allemand en ligne A1 à C1 | GLS Sprachenzentrum`
- **Meta:** `Apprenez l'allemand en ligne avec GLS : cours d'allemand à distance en petits groupes, du A1 au C1, horaires matin, midi et soir, certification à la clé.`
- **H1:** `Cours d'allemand en ligne : apprenez l'allemand à distance`
- **Links:** → `/pricing`, → `/discover-your-level`.
- **Confidence:** high (low volume).

### 5. Pricing — `/pricing`
- **Primary:** `prix cours allemand` — verified 0 in MA; `tarif cours allemand` NO_DATA. Relevance-only.
- **Title:** `Tarifs des cours d'allemand au Maroc | GLS Sprachenzentrum`
- **Meta:** `Prix et tarifs des cours d'allemand GLS au Maroc : cours intensifs, cours en ligne et préparation aux examens. Tarifs clairs, sans frais cachés.`
- **H1:** keep. **Content:** page is thin (379 words) — add what is included per formula.
- **Confidence:** low.

### 6–10. Levels — `/niveaux/a1 … c1`
Shared cluster "cours d'allemand + niveau". Remove the hidden English duplicate from every H1 (`title_secondary`).

| Page | Primary (MA) | Secondary | Title | H1 |
|---|---|---|---|---|
| a1 | `cours a1 allemand` 30 | `allemand a1` 30, `a1 allemand cours` 20, `cours allemand pour débutant` 30, `cours allemand débutant` 10 | `Cours d'allemand A1 pour débutants au Maroc \| GLS` | `Cours d'allemand A1 – niveau débutant` |
| a2 | `cours allemand a2` NO_DATA | `allemand a2` NO_DATA | `Cours d'allemand A2 au Maroc \| GLS Sprachenzentrum` | `Cours d'allemand A2 – consolidez vos bases` |
| b1 | `allemand b1` 20 | `cours allemand b1` NO_DATA; excluded `cours b1 allemand pdf` 30 | `Cours d'allemand B1 au Maroc \| GLS Sprachenzentrum` | `Cours d'allemand B1 – niveau intermédiaire` |
| b2 | `b2 allemand cours` 20 | `allemand b2` NO_DATA | `Cours d'allemand B2 au Maroc \| GLS Sprachenzentrum` | `Cours d'allemand B2 – niveau avancé` |
| c1 | `allemand c1` NO_DATA | — | `Cours d'allemand C1 au Maroc \| GLS Sprachenzentrum` | `Cours d'allemand C1 – niveau supérieur` |

Meta pattern: `Cours d'allemand {niveau} à {ville 1, ville 2…} et en ligne : objectifs du niveau, durée, horaires et préparation à l'examen {Goethe/ÖSD} {niveau}.` Add `/niveaux/c1` to the sitemap. Confidence: medium for A1, low elsewhere.

### 11. Goethe exam — `/exams/goethe`
- **Primary:** `examen goethe` 30, KD 0*. **Secondary:** `certificat goethe` 30 · `goethe zertifikat` 20 · `certification allemand` 20 · supporting `examen goethe maroc` NO_DATA.
- **Excluded:** `goethe institut casablanca` 590 / `goethe institut maroc` 110 — navigational to the Goethe-Institut; mention it as the awarding body, do not target it.
- **Title:** `Examen Goethe au Maroc : préparation et certificat | GLS`
- **Meta:** `Préparez l'examen Goethe (A1 à C1) au Maroc avec GLS : préparation interne, examens blancs, inscription et calendrier des sessions. Obtenez votre Goethe-Zertifikat.`
- **H1:** `Examen Goethe au Maroc – préparation et certification`
- **Cannibalisation:** same H2 skeleton as the ÖSD page; keep titles exam-specific.
- **Confidence:** high.

### 12. ÖSD exam — `/exams/osd`
- **Primary:** `examen ösd` 20. **Secondary:** `ösd maroc` 1,300 (KD 26, CPC 0) — **intent unverified**; include "ÖSD au Maroc" in the title now (it costs nothing), confirm with a SERP check before building content around it.
- **Title:** `Examen ÖSD au Maroc : préparation et certification | GLS`
- **Meta:** `Passez l'examen ÖSD au Maroc avec GLS : préparation en interne, sessions sur place dans nos centres, certificat reconnu en Allemagne et en Autriche.`
- **H1:** `Examen ÖSD au Maroc – préparation et certification`
- **Confidence:** medium.

### 13–18. Centres — `/sites/gls-*`
Prerequisites: fix the footer slugs (six 404s), add the six URLs to the sitemap, translate the Salé page into French.

| Centre | Primary (MA) | Secondary | Title |
|---|---|---|---|
| Casablanca | `centre de langue allemande casablanca` 90, KD 0* | `cours d allemand casablanca` 30, `cours allemand casablanca` 20, `institut allemand casablanca` 20 | `Centre de langue allemande à Casablanca – Cours d'allemand \| GLS` |
| Rabat | `cours d allemand rabat` 30 | — | `Cours d'allemand à Rabat – Centre GLS Sprachenzentrum` |
| Marrakech | `cours allemand marrakech` 20 | — | `Cours d'allemand à Marrakech – Centre GLS Sprachenzentrum` |
| Kénitra | `cours allemand kenitra` 0 (verified) | — | `Cours d'allemand à Kénitra – Centre GLS Sprachenzentrum` |
| Agadir | `cours allemand agadir` NO_DATA | — | `Cours d'allemand à Agadir – Centre GLS Sprachenzentrum` |
| Salé | `cours allemand salé` NO_DATA | — | `Cours d'allemand à Salé – Centre GLS Sprachenzentrum` |

H1 pattern: `GLS {Ville} – centre de langue allemande et cours d'allemand`. Meta pattern: `Centre GLS {Ville} : cours d'allemand A1 à C1 en petits groupes, horaires matin, midi et soir, préparation Goethe et ÖSD. Adresse, horaires et inscription.` Excluded on Casablanca: `goethe institut casablanca`. Confidence: high (Casablanca), low (Agadir, Salé, Kénitra).

### 19. Studienkollegs index — `/studienkollegs`
- **Primary:** `studienkolleg` 390, KD 30, CPC 0.55. **Secondary:** `etudier en allemagne` 70 · `études en allemagne` 10 · `studienkolleg maroc` 10.
- **Intent:** informational → commercial (GLS prepares the language level needed).
- **Why:** largest clean term in the dataset and the page is under-optimised (English title, no H1).
- **Title:** `Studienkolleg en Allemagne : liste, admission et préparation | GLS`
- **Meta:** `Tous les Studienkollegs en Allemagne : conditions d'admission, dates limites, cours préparatoires. GLS vous prépare au niveau d'allemand requis pour étudier en Allemagne.`
- **H1 (new):** `Studienkollegs en Allemagne`
- **Content:** add an intro H2 "Qu'est-ce qu'un Studienkolleg ?" and "Quel niveau d'allemand pour un Studienkolleg ?" linking to `/niveaux/b1` and `/niveaux/b2`. The 21 detail pages inherit the template: give them `meta_description` from their own data (name + city) instead of the site default.
- **Confidence:** high.

### 20. Student stories — `/student-stories`
- **Primary:** `ausbildung allemagne` 20 (relevance; page is about these journeys). Meta currently 187 chars → shorten.
- **Title:** `Témoignages : du Maroc à l'Allemagne (Ausbildung, Studienkolleg) | GLS`
- **Meta:** `Ils ont appris l'allemand chez GLS et sont partis en Allemagne : Ausbildung, Studienkolleg, études et contrats de travail. Découvrez leurs parcours.`
- **Confidence:** medium.

### 21. Level test — `/discover-your-level`
- **Primary:** `test de niveau allemand` 20. **Secondary:** `niveau allemand` 20 · `test allemand` 10.
- **Title:** `Test de niveau d'allemand gratuit en ligne (A1–C1) | GLS`
- **Meta:** `Évaluez votre niveau d'allemand gratuitement en ligne, du A1 au C1, et trouvez le cours GLS adapté. Résultat immédiat.`
- **H1:** keep. Replace the English H2 "Select your Quiz!" by `Choisissez votre test`.
- **Confidence:** medium.

### 22. Blog article — `/blog/pourquoi-apprendre-lallemand-au-maroc-…`
- **Primary:** `apprendre l'allemand au maroc` 20 (informational "pourquoi"). Shared with the homepage as a secondary phrase — justified: different intent; the article should link to the homepage and `/intensive-courses` with that anchor.
- **Title/meta:** leave (owned by the CMS); add a `meta_description` field to posts later.
- **Confidence:** medium.

### Pages without a keyword target (metadata hygiene only)
`/faq`, `/contact`, `/blog` (index), `/exams/gls` (placeholder), `/certificate-check`, `/gls-inscription` (needs its own title), `/demande-attestation`, `/feedback`, `/traductions/suivi`, `/terms`, `/privacy`, `/partners/fc-marokko`, `/lp/*` (noindex). Each should get a unique, descriptive meta description instead of the site default; no keyword research spent on them.

## Keywords deliberately excluded (all pages)
Navigational: `goethe institut casablanca` (590), `goethe institut maroc` (110). Irrelevant: `giz maroc agence de coopération internationale allemande` (260), `berger allemand prix maroc` (140), `emploi allemand maroc` (40), 5 dog-breed queries (30 each). Wrong intent: `cours b1 allemand pdf` (30).

## New-page opportunities (not created)
- "Studienkolleg : niveau d'allemand requis" guide — supports the 390-volume term; overlap risk with `/studienkollegs` index is low if it stays a guide.
- B1 learning resources ("cours b1 allemand pdf" 30) — informational blog post, not a course page.

## Data limitations
18 of 50 validated candidates have no Semrush data in Morocco (listed in `04-research-validation.md`). KD covers 12 keywords; 9 came back as 0 and are unreliable. No SERP report was run (budget), so `ösd maroc` and `cours en allemand` intents are unverified. Trend values are Semrush's 12 relative monthly points (0–1) and are included in the CSVs, not interpreted here.
