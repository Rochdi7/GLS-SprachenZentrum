# `centre allemand`: plan to compete for #1 on Google Morocco

Research only. Nothing on the site was changed. Data: Semrush database `ma`, 2026-11-03 (`raw/08-serp-ma.json`, `raw/08-competitors-domain-backlinks.json`, `raw/08-gls-organic-positions-ma.json`, `raw/08-competitor-pages-crawl.json`). Semrush does not expose the update date of its monthly estimates. Position #1 cannot be promised. This is the evidence and the gap list.

## 1. The keyword

| Metric | Value (Semrush `ma`) |
|---|---|
| Volume | **590 / month** |
| KD | **44** (the only "centre" term with a computed KD) |
| Semrush intent | **Commercial** |
| CPC / paid competition | 0.39 $ / 0.03 (almost no advertisers) |
| GLS position today | **#10**, URL `https://gls-sprachzentrum.ma/fr` |

Related demand GLS can capture with the same work:

| Keyword | Volume |
|---|---|
| centre allemand rabat | 260 |
| centre de langue allemand | 260 (the ungrammatical form is what people type) |
| centre allemand casablanca | 210 |
| centre allemand agadir | 170 |
| centre allemand marrakech | 140 |
| centre de langue allemande casablanca | 90 |
| centre allemand kenitra | 90 |
| centre allemand maroc | 30 |

Total ≈ **1,850 searches/month** in the "centre (de langue) allemand(e)" family. No data for: centre de langue allemande, centre d'allemand, centre allemand salé, meilleur centre allemand maroc.

## 2. Who ranks today (Semrush organic top 10, `ma`)

| # | URL | What it is | Organic keywords / traffic (`ma`) | Authority Score / referring domains |
|---|---|---|---|---|
| 1 | centreallemand-hamra.com | Marrakech school, "27 ans d'expérience", **exact-match domain** | 7 / 94 | 8 / 1,932 |
| 2 | academieallemande.ma | Casablanca, telc exams, **brand searched 2,900×/month** | 42 / 4,850 | 11 / 634 |
| 3 | bremencenter.com | Tanger centre, Arabic title, programmes 15/9/8 h/week | 11 / 312 | 9 / 1,761 |
| 4 | germarok.ma | "Le 1er Centre d'Allemand au Maroc" | 16 / 1,209 | 11 / 348 |
| 5 | goethe.de (Goethe-Institut Maroc courses page) | Official institute | n/a (crawler blocked, 403) | not measured |
| 6 | mdazentrum.ma | Rabat, H1 "Centre de langue allemand Rabat" | 6 / 17 | not measured |
| 7 | lial-agadir.com | Multi-language school, Agadir | not measured | not measured |
| 8 | deutschplusma.com | Meknès, language + translation centre | 4 / 15 | not measured |
| 9 | instagram.com/goetheinstitut_marokko | Social profile | — | — |
| **10** | **gls-sprachzentrum.ma/fr** | **6 centres + online** | **26 / 329** | **9 / 7** |

Intent is confirmed **commercial and local**: 8 of 10 results are language schools. Google shows single-city schools for a national query. No result presents a national network on its homepage. That is GLS's opening.

Semrush returned SERP feature codes `3,7,9,13,21,36` for this keyword. In Semrush's code list, 3 is the local pack and 21 is People Also Ask. Verify in the Semrush UI before relying on it. Semrush had no SERP data for `centre allemand rabat` or `centre allemand casablanca`.

## 3. On-page comparison

| Signal | Top-3 competitors | GLS homepage |
|---|---|---|
| "centre" + "allemand(e)" in `<title>` | hamra: "Centre de langue allemande à Marrakech"; Bremen: "المركز الألماني بطنجة" (= "the German centre in Tanger"); GerMarok: "Le 1er Centre d'Allemand au Maroc" | **No.** "Apprendre l'allemand au Maroc \| Cours d'allemand GLS" |
| In H1 | hamra: "Centre de la langue allemande, Marrakech…"; MDA: "Centre de langue allemand Rabat" | **No.** "Apprendre l'allemand au Maroc… Votre premier pas vers l'Allemagne" |
| Phrase "centre allemand" in body | MDA: 2×. Others use "centre de (la) langue allemande" | **0×**, and 0× "centre de langue allemande". It appears only in the JSON-LD description |
| Meta description | mentions "centre" (Académie, Bremen, Deutschplus) | no "centre" |
| Content length | 260–1,171 words; Académie is a JS app (16 words without JS) and still ranks #2 | 1,235 words |
| Structured data | generic WordPress Organization | EducationalOrganization (homepage only); **no LocalBusiness on the 6 centre pages** |
| Domain | hamra has an exact-match domain | brand domain |

**Conclusion: the homepage is not targeting `centre allemand` at all.** It ranks #10 on domain relevance alone. This is the cheapest and most certain gap to close.

## 4. Authority and off-page

- **Authority Score is not the gap.** GLS scores 9; the top 4 score 8–11.
- **Referring domains are.** GLS has 7; competitors have 348–1,932. Their link quality was not audited. Such counts at Authority Score 8–11 usually mean directories or low-quality networks. **Do not copy them by buying links.**
- **Brand demand is a gap.** `academie allemand` gets 2,900 searches/month and `germania center` 480. GLS brand searches are not visible in Semrush, and "gls" collides with GLS parcels and Mercedes GLS (see `08-rejected-keywords.csv`). Always write "GLS Sprachenzentrum" in full.

## 5. Local SEO (likely decisive for a commercial-local query)

Not measured by Semrush:
1. One Google Business Profile per centre (6), with category "École de langues" or "Centre de formation linguistique", the exact site address and the centre page as website URL.
2. The "4.9/5 (+677 avis)" claim: if those reviews are Google reviews, attach them to the GBPs. If not, remove the number or name its source.
3. NAP consistency: the site, GBP and directories must show the same name, address and phone per centre. The site writes the same number in two formats (homepage "+212 808 549717", centre page "+212 8 08 54 97 17"), and the homepage lists further numbers (e.g. +212 651 447651) whose centre is not stated. Use one format and one main number per centre.
4. LocalBusiness / EducationalOrganization JSON-LD on each centre page: name "GLS Sprachenzentrum {Ville}", address, geo, openingHours, telephone, `parentOrganization` pointing to the homepage entity.

## 6. Internal linking

- No GLS page contains the anchor "centre allemand".
- Each centre page should link to the homepage once, with "centre allemand au Maroc" or "nos centres d'allemand au Maroc".
- The homepage should link to each centre with "centre allemand à {Ville}". This also feeds the city terms (260 / 210 / 170 / 140 / 90).
- The blog article and `/fr/about` should link to the homepage with "centre de langue allemande au Maroc".

## 7. Technical issues to fix first

1. `http://gls-sprachzentrum.ma/fr` returns **404** instead of redirecting to https.
2. Query-string URLs canonicalise to themselves (`/fr/about?ref=x` → canonical `?ref=x`). Strip query strings from the canonical tag.
3. `/fr/online-courses` and the level pages link to 3 dead URLs (`/fr/courses/course-schedules`, `/fr/courses/pricing`, `/fr/german-exams`). `/fr/online-registration` returns 500.
4. 12 utility pages still use the default meta description. They are low priority, but the default contains "Centre de langue allemande au Maroc", so 12 weak pages repeat the homepage concept.

## 8. Recommended homepage structure (for a later implementation task)

| Element | Recommendation |
|---|---|
| Title (≤60) | `Centre allemand au Maroc – Apprendre l'allemand \| GLS` |
| Meta | `GLS Sprachenzentrum, centre de langue allemande au Maroc : cours d'allemand A1 à C1 à Casablanca, Rabat, Marrakech, Kénitra, Salé, Agadir et en ligne. Préparation ÖSD et Goethe.` |
| H1 | `Centre allemand au Maroc : apprendre l'allemand avec GLS` (keep the animation; it renders as normal text) |
| First paragraph | One sentence with "centre de langue allemande" and the 6 cities |
| H2 "Nos centres d'allemand au Maroc" | 6 cards: city, address, "centre allemand à {Ville}" link |
| H2 "Cours d'allemand A1 à C1" | links to the levels, intensive and online pages |
| H2 "Examens ÖSD et Goethe" | links to the exam pages (only after the exam-centre claim is clarified) |
| H2 FAQ | "Où trouver un centre allemand au Maroc ?", "Combien coûte un cours d'allemand ?" (1,300–1,400 DH/mois + 500 DH, verified), "Quel niveau pour l'Ausbildung / le Studienkolleg ?" |
| Schema | Keep EducationalOrganization and add `department` / `subOrganization` entries for the 6 centres |

Do not repeat "centre allemand" more than naturally (title, H1, one paragraph, one H2, anchors). Stuffing is a risk, and the term already holds 0 mentions plus a #10 position.

## 9. Priority actions

| # | Action | Why | Effort |
|---|---|---|---|
| 1 | Homepage title, H1, intro and H2 with "centre allemand" / "centre de langue allemande" | 0 mentions today; competitors all use it | low |
| 2 | Centre pages: titles "Centre allemand à {Ville} – GLS" plus LocalBusiness schema | city terms total 870/mo; schema missing | low |
| 3 | 6 Google Business Profiles, NAP normalised, real review source | commercial-local SERP | medium |
| 4 | Internal anchors centre pages ↔ homepage | zero today | low |
| 5 | Fix http→https 404, query-string canonicals, dead links, 500 | crawl hygiene | low |
| 6 | Earn 10–20 relevant Moroccan links: partners (FC Marokko), German or Austrian institutions where GLS really cooperates, press, school directories | 7 referring domains vs hundreds | high, ongoing |
| 7 | Brand building: always "GLS Sprachenzentrum", GBP posts, video content (9onsol's Talks) | competitors win on brand demand | ongoing |

## 10. Risks and limits

- Google may keep favouring single-city schools and the exact-match domain for a local query. A national homepage may rank well in some cities and not others. Semrush shows one national snapshot only.
- Academie Allemande's brand demand (2,900/mo) and hamra's exact-match domain are structural advantages that on-page work does not remove.
- KD 44 is moderate. It is reachable from #10 by on-page work alone only if competitors stay still.
- Competitor backlink quality, Goethe-Institut metrics and local-pack composition were not measured.
- Re-measure the position monthly: `php artisan seo:keywords "centre allemand" --db=ma --type=serp --limit=10 --fresh` (≈100 units).
