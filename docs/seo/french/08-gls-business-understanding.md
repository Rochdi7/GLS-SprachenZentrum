# GLS Sprachenzentrum — business understanding (2026-11-03)

Basis for every keyword decision in phase 08. Sources: live French site (63 URLs crawled 2026-11-03, `raw/08-gls-live-crawl.json`), French translation files (`resources/lang/fr/*`), routes and views. Nothing below comes from outside the site unless marked. **Verified** = stated consistently on the site. **Site claim** = stated on the site but not checkable or contradicted elsewhere.

## 1. What GLS sells (verified on the site)

| Offer | Detail as stated on the site | Page |
|---|---|---|
| In-person intensive German courses | Levels A1, A2, B1, B2, C1. Monday–Friday, 2h30 per session. Four daily slots per centre (≈10:00, ≈15:00, 17:00, 19:00; Agadir uses 10:00–12:30 / 13:00–15:30 / 16:00–18:00 / 19:00–21:30) | `/fr/intensive-courses`, `/fr/niveaux/*`, centre pages |
| Online German courses | Live groups, morning / midday / afternoon / evening | `/fr/online-courses` |
| Internal level exams | "examen interne GLS" after each level | `/fr/exams/osd`, `/fr/exams/goethe` |
| Preparation and registration for official exams | GLS "inscrit votre nom à l'examen officiel ÖSD/Goethe correspondant" | same |
| Free level test | Online quizzes A1→C1 | `/fr/discover-your-level` |
| Free consultation / WhatsApp booking | Forms on every page | all |
| Studienkolleg directory | 21 public Studienkollegs in Germany (admission, deadlines) | `/fr/studienkollegs` |
| GLS certificate verification | Token/QR certificate check | `/fr/certificate-check` |
| Attendance certificate requests | Form | `/fr/demande-attestation` |
| Translation tracking | "Suivez vos traductions Maroc – Allemagne" (scope of the translation service not described elsewhere) | `/fr/traductions/suivi` |

**Prices (verified, per centre page):** 1,400 DH/month in Casablanca, Rabat and Salé, and 1,300 DH/month in Marrakech, Kénitra and Agadir. Registration fee 500 DH, paid once (`pricing.php`, updated by the `a4d89d4` commit).

## 2. Where GLS operates (verified addresses)

| Centre | Address on the site | URL |
|---|---|---|
| Casablanca | 14 Bd de Paris | `/fr/sites/gls-casablanca` |
| Rabat | Avenue Fal Ould Oumeir, Building 77, 1st floor N°1, Agdal | `/fr/sites/gls-rabat` |
| Marrakech | 3ème étage Bureau 28, Immeuble Espace, Av. Yacoub El Mansour | `/fr/sites/gls-marrakech` |
| Kénitra | 4ème étage, résidence Nezha, Av. Mohamed V | `/fr/sites/gls-kenitra` |
| Salé | Avenue Mohamed V, Rue Halima N°12, Diyar | `/fr/sites/gls-sale` |
| Agadir | 2ème étage, Av. Massoude Al Wafkaoui | `/fr/sites/gls-agadir` |
| Online | nationwide | `/fr/online-courses` |

No centre in Tanger, Fès, Meknès, Oujda or Tétouan. Keywords for those cities are not assigned.

## 3. Audiences and motivations (from site copy and student stories)

1. Moroccan students preparing **studies in Germany**: Studienkolleg, university, B1/B2/C1 needs.
2. Candidates for **Ausbildung** (vocational training) and **work contracts** in Germany: B1/B2 needs, visa files.
3. People who need an **official certificate** (ÖSD or Goethe) for visa, study or work.
4. Beginners (A1) and working adults (evening and online groups).

## 4. Conversion actions

Registration form (`/fr/gls-inscription`), group application modals on centre pages, free consultation form, WhatsApp, phone per centre, online level test.

## 5. Brand positioning and claimed advantages

| Statement | Status |
|---|---|
| 6 physical centres plus online, the widest footprint among the top-10 "centre allemand" results (all the others present one city each on their homepage) | **Verified for GLS.** Competitor coverage is checked only from their homepages |
| Same price grid and schedule in every centre | Verified |
| "4.9 / 5 (+677 avis)" (homepage) | **Site claim.** Source of the reviews not shown |
| "L'école d'allemand la mieux notée au Maroc", "le centre leader" | **Site claim.** Not verifiable |
| "Nos professeurs sont certifiés, expérimentés" | **Site claim.** No detail |
| Video content (9onsol's Talks, Deutshow) and FC Marokko partnership | Verified as content on the site |

## 6. Contradictions and facts that could not be verified (fix before using them in SEO copy)

1. **Official exam-centre status.**
   - `/fr/exams/osd` says "Centre officiel d'examen ÖSD", and `/fr/exams/goethe` says "Centre officiel d'examen Goethe".
   - The same pages say GLS "programme la date de votre examen officiel directement avec le centre ÖSD/Goethe", which describes an external centre.
   - The About page says GLS "proposera bientôt les examens GLS et ÖSD", and the Salé page says "Bientôt : examens officiels ÖSD".
   - **These cannot all be true.** This matters because GLS already ranks #9 for `centre ösd marokko` (1,000/mo). Do not target "centre d'examen ÖSD/Goethe" wording until the actual status is confirmed in writing.
2. **Salé prices.** The page shows a per-level grid (998 / 1,100 / 1,300 / 1,500 DH) and also "1,400 DH / mois" for every level.
3. **"Petits groupes"** and "certificat reconnu en Allemagne et en Autriche" appear only in the meta descriptions written during the October SEO work (`05-implementation.md`), not in the page copy. Verify group size before keeping "petits groupes".
4. Founding year, number of students and teacher qualifications are not stated anywhere.
5. The scope of the translation service is not described beyond a tracking page.

## 7. Page roles derived from this

Commercial: homepage, intensive, online, levels, pricing, six centres, registration.
Exam (commercial plus navigational demand): ÖSD and Goethe pages.
Informational: Studienkolleg directory, blog, FAQ, student stories.
Utility / no target: certificate check, attestation, feedback, translation tracking, legal pages, `/lp/*` (noindex).

**Consequence for keywords:** GLS can credibly own
- "centre allemand" and "apprendre l'allemand" nationally, through its 6 centres;
- "centre allemand + ville" for its 6 cities only;
- course, level and online terms;
- ÖSD and Goethe *preparation* terms;
- Studienkolleg and Ausbildung *information* terms, with GLS as the language step.

It should not target Goethe-Institut navigational searches, competitor brands, cities it does not serve, or private lessons, which the site does not offer.
