# Page keyword strategy, phase 08 (2026-11-03)

Supersedes the page assignments in `03` and `07` where they differ. Earlier files are kept unchanged.

- **Data:** `08-page-keyword-research.csv` (163 keywords that passed the relevance filter) and `08-keyword-page-mapping.csv` (one owner per keyword). Rejected terms: `08-rejected-keywords.csv` (37).
- **Database:** all volumes are Semrush **Morocco (`ma`)**, retrieved 2026-10-19 → 2026-11-03.
- **KD:** a real value only where Semrush computed it (≈ ≥300 searches/month). "0" means not computed, and "—" means not measured.
- **Intent:** "(S)" marks Semrush's own intent label. All other intents are my classification from the wording, with SERP evidence for 3 terms.
- **GLS position:** from Semrush `resource_organic`, 2026-11-03.

## The big change since phase 07: the ÖSD cluster

Earlier research missed the keywords GLS **already ranks for**. The organic report shows the ÖSD exam page is GLS's main traffic source:

| Keyword | Volume | KD | GLS position |
|---|---|---|---|
| osd | 9,900 | 21 | **#2** |
| ösd | 9,900 | 24 | #5 |
| ösd maroc | 1,300 | 26 | **#2** |
| osd ma | 1,300 | 29 | #3 |
| centre ösd marokko | 1,000 | 30 | #9 |
| ösd b1 | 880 | 13 | #13 |
| ösd prüfung b1 (DE page) | 880 | 12 | #12 |
| ösd prüfung (DE page) | 720 | 19 | #6 |

This settles the open question on `ösd maroc`: it is the ÖSD exam, and GLS is already the second Moroccan result.

## Page clusters

### Homepage `/fr`: GLS as the German-language centre in Morocco
| Role | Keyword | Vol | KD | Intent | GLS |
|---|---|---|---|---|---|
| **Primary** | centre allemand | 590 | 44 | commercial (S) | #10 |
| Secondary | centre de langue allemand | 260 | 0 | commercial | — |
| Secondary | apprendre l'allemand / apprendre l allemand | 170 / 170 | 0 / — | commercial | — |
| Secondary | apprendre allemand | 70 | 0 | commercial | — |
| Secondary | centre allemand maroc | 30 | 0 | commercial | — |
| Supporting | langue allemande 210 · allemand maroc 90 (both **manual review**: ambiguous) · apprendre la langue allemande 30 · école allemande / ecole allemand 30 (ambiguous) · apprendre à parler allemand 20 · apprendre l'allemand rapidement 20 | | | | |
| No data | centre de langue allemande, centre d'allemand, meilleur centre allemand maroc, centre de formation allemand | | | | |

The full position-1 plan is in `09-centre-allemand-position-1-strategy.md`. Recommended title: `Centre allemand au Maroc – Apprendre l'allemand | GLS`. Do not put cities, levels, prices or exam names in the homepage title.

### ÖSD exam `/fr/exams/osd` (French) + `/de/exams/osd` (German)
| Role | Keyword | Vol | KD | Intent | GLS |
|---|---|---|---|---|---|
| **Primary** | ösd | 9,900 | 24 | navigational (S) | #5 |
| Secondary | osd · ösd maroc · osd ma | 9,900 · 1,300 · 1,300 | 21 · 26 · 29 | navigational (S) | #2 · #2 · #3 |
| Secondary | centre ösd marokko | 1,000 | 30 | informational/transactional (S) | #9 — **claim risk**, see business doc §6 |
| Secondary | ösd b1 · osd b1 | 880 · 590 | 13 · 15 | informational (S) | #13 · — |
| Secondary | ösd b2 · osd b2 | 390 · 390 | 15 · 0 | informational (S) | #20 · — |
| Secondary | ösd a1 · osd a1 | 140 · 140 | 0 | — | — |
| Supporting | examen ösd 20 · ösd zertifikat 20 · no data: inscription osd, osd casablanca, osd a2, ösd a2, ösd c1 | | | | |
| DE page owns | ösd prüfung b1 880 (#12) · ösd prüfung 720 (#6) · ösd prüfung b2 480 (#11) · ösd b1 prüfung 390 (#8) | | | | |

The intent is navigational: people want the exam itself. GLS ranks because it is the most relevant Moroccan page.

**Opportunity:** level sections or sub-pages for **ÖSD B1** (880 + 590) and **ÖSD B2** (390 + 390), covering dates, format and how GLS registers candidates. This is the largest realistic traffic gain on the site.

**Precondition:** clarify the exam-centre status before using "centre d'examen ÖSD" wording.

### Goethe exam `/fr/exams/goethe`
| Role | Keyword | Vol | KD | Intent |
|---|---|---|---|---|
| **Primary** | examen goethe | 30 | 0 | commercial |
| Secondary | goethe b2 | 390 | 18 | navigational/transactional (S) |
| Secondary | goethe b1 · goethe a1 · examen goethe b1 | 170 · 70 · 10 | 0 | navigational/transactional |
| Secondary | certificat goethe 30 · goethe zertifikat 20 · certification allemand 20 | | | commercial |

Target the level terms as **"préparation Goethe B1/B2"**, never as the exam provider. Goethe-Institut navigational terms are rejected: goethe casablanca 480, on which GLS ranks #11 (unearned), goethe institut casablanca 590, goethe rabat 880.

### Centre pages `/fr/sites/gls-{ville}`
| Centre | Primary | Vol | Secondary |
|---|---|---|---|
| Rabat | centre allemand rabat | 260 | cours d allemand rabat 30, cours d'allemand rabat 30 |
| Casablanca | centre allemand casablanca | 210 | centre de langue allemande casablanca 90, cours d(')allemand casablanca 30/30, cours allemand casablanca 20, institut allemand casablanca 20 |
| Agadir | centre allemand agadir | 170 | — |
| Marrakech | centre allemand marrakech | 140 | cours allemand marrakech 20 |
| Kénitra | centre allemand kenitra | 90 | cours allemand kenitra = 0 (verified) |
| Salé | cours allemand salé | no data | centre allemand salé no data (relevance-only) |

Intent is commercial/local. KD was not computed. The pattern for all six is "centre allemand + ville". Title: `Centre allemand à {Ville} – Cours d'allemand GLS`. Add LocalBusiness schema and link to the homepage with "centre allemand au Maroc".

### Intensive courses `/fr/intensive-courses`
- **Primary:** cours allemand 70.
- **Secondary:** cours en allemand 110 (**manual review**: it can mean "courses taught in German"), allemand cours 70, cours de langue allemande 30, formation en allemand 30, formation allemand 20, cours d allemand 20, cours d'allemand 20, formation langue allemande 20, cours intensif allemand 10.
- **Supporting:** cours du soir allemand 10.
- **No data in Morocco:** cours intensif(s) d'allemand, formation intensive allemand, cours allemand maroc, cours d'allemand au maroc, cours d'allemand en présentiel, cours d'allemand pour adultes, inscription cours allemand.

### Online courses `/fr/online-courses`
- **Primary:** cours d'allemand en ligne 20.
- **Secondary:** apprendre l'allemand en ligne 20, formation allemand en ligne 20, cours allemand en ligne 10.
- **No data:** cours allemand en ligne maroc.
- Fix the 3 dead links on this page.

### Levels `/fr/niveaux/*`
| Level | Primary | Secondary |
|---|---|---|
| A1 | cours a1 allemand 30 | apprendre l allemand a1 40, apprendre allemand a1 30, allemand a1 30, cours allemand pour débutant 30, a1 allemand cours 20, cours allemand a1 20, allemand pour débutant 20, cours allemand débutant 10 |
| A2 | cours allemand a2 (no data) | allemand a2 (no data) |
| B1 | allemand b1 20 | cours allemand b1 (no data). Link to the ÖSD page with anchor "examen ÖSD B1" and to the Goethe page with "préparation Goethe B1" |
| B2 | b2 allemand cours 20 | cours allemand b2 20. Link to Studienkolleg with "niveau B2 pour le Studienkolleg" |
| C1 | allemand c1 (no data) | — |

Exam-level terms (ösd b1, goethe b2…) belong to the exam pages, not the level pages. That prevents cannibalisation.

### Studienkolleg `/fr/studienkollegs`
- **Primary:** studienkolleg 390, KD 30, informational (S).
- **Secondary:** etudier en allemagne 70, étudier en allemagne maroc 20, études en allemagne 10, studienkolleg maroc 10.
- **Problem:** GLS ranks #36 with a *detail* page (Hamburg), not with the index. The index needs French intro content: "Qu'est-ce qu'un Studienkolleg ?" and "Quel niveau d'allemand ?". The 21 detail pages still carry English titles from the database.

### New guide (proposed, not created): "Ausbildung et travail en Allemagne"
- **Primary:** ausbildung 6,600, KD 64, informational (S). The SERP shows German job portals, so this is a hard term and only worth a genuinely useful French guide.
- **Secondary:** travailler en allemagne 170, travail en allemagne 170, ausbildung maroc 30, ausbildung en allemagne 20, ausbildung allemagne 20, travailler en allemagne maroc 20.
- **Angle:** the language level required (B1/B2) and GLS's preparation path, with links to `/fr/niveaux/b1` and `/fr/student-stories`.
- **Confidence:** low for `ausbildung` itself, medium for the long tail.

### Other pages
| Page | Primary | Notes |
|---|---|---|
| `/fr/discover-your-level` | test de niveau allemand 20 | niveau allemand 20, test allemand 10 |
| `/fr/pricing` | prix cours allemand (verified 0) | Relevance-only. Add the verified 1,300–1,400 DH + 500 DH grid as text |
| `/fr/about` | école de langue allemande (no data) | Brand page; links to the homepage with "centre de langue allemande au Maroc" |
| Blog article (pourquoi apprendre…) | apprendre l'allemand au maroc 20 | Informational twin of the homepage term (documented duplicate) |
| New blog article | comment apprendre l'allemand 20 | Question query |
| `/fr/student-stories` | — | No measured term of its own. Social proof for the Ausbildung guide |
| `/fr/faq`, `/fr/contact`, `/fr/blog`, utilities, legal, `/lp/*` | — | No target; unique meta descriptions only |

## Cannibalisation rules

1. "centre allemand" (national) → homepage only. "centre allemand + ville" → that centre page only.
2. "cours (d')allemand" generic → intensive page. The homepage mentions it, nothing more.
3. "apprendre l'allemand au maroc" → the blog article owns the informational intent and the homepage uses it as a phrase. This duplicate is documented.
4. ÖSD/Goethe level terms → exam pages. Course-level terms → level pages.
5. "ösd prüfung …" (German wording) → `/de/exams/osd`. This is a justified separate language page.
6. Studienkolleg → index page, not detail pages. Detail pages target their institution name only.
