# GLS Sprachenzentrum — SEO Strategy

**Goal:** drive qualified inscriptions from organic search across 6 centers in Morocco.
**Primary KPI:** form submissions on `/gls.inscription` from organic traffic (not just sessions).
**Locale:** FR primary, EN/DE/AR secondary. Audience: Moroccan students preparing for Germany.

---

## 1. Search intent → who you're actually competing for

The visitor types one of three things into Google:

1. **Transactional / local** — *"cours d'allemand rabat"*, *"école allemand casablanca prix"*, *"goethe institut maroc"*. They want to enroll. Highest conversion.
2. **Goal-oriented** — *"étudier en allemagne depuis le maroc"*, *"comment passer le goethe b1"*, *"studienkolleg c'est quoi"*. They want a roadmap, GLS is the path.
3. **Brand or close-brand** — *"gls sprachenzentrum"*, *"gls maroc avis"*. You should own these completely.

**Action:** every URL on the site should be answerable to one of these three intents. If a page doesn't answer a search question, it's dead weight.

---

## 2. Keyword strategy (where to actually rank)

### 2.1 Core keyword tiers

| Tier | Type | Example FR | Difficulty | Conversion |
|------|------|-----------|------------|------------|
| **A** | Local + transactional | `cours allemand rabat`, `école allemand casablanca`, `apprendre allemand kenitra` | medium | very high |
| **B** | Course-specific | `cours intensif allemand maroc`, `préparation goethe b1 maroc`, `osd maroc` | medium | high |
| **C** | Goal-oriented | `étudier en allemagne depuis le maroc`, `studienkolleg maroc`, `visa étudiant allemagne maroc` | high | medium |
| **D** | Long-tail informational | `combien de temps pour apprendre l'allemand a2`, `différence goethe osd`, `niveau b1 allemand équivalent` | low | low (but feeds funnel) |
| **E** | Brand | `gls sprachenzentrum`, `gls maroc`, `gls rabat avis` | low | very high |

### 2.2 Per-center keyword map (Tier A — your easiest wins)

You already have one landing page per city. Each must target the local pack:

| Center | Primary keyword | Secondary | Sub-page slug |
|--------|----------------|-----------|---------------|
| Rabat | `cours allemand rabat` | `école allemand agdal`, `formation allemand rabat 10000` | `/sites/gls-rabat` |
| Casablanca | `cours allemand casablanca` | `apprendre allemand maarif`, `école allemande casa` | `/sites/gls-casablanca` |
| Marrakech | `cours allemand marrakech` | `école allemand guéliz`, `apprendre allemand marrakech` | `/sites/gls-marrakech` |
| Kénitra | `cours allemand kenitra` | `école allemand kenitra` | `/sites/gls-kenitra` |
| Salé | `cours allemand salé` | `école allemand sale diyar` | `/sites/gls-sale` |
| Agadir | `cours allemand agadir` | `école allemand agadir essalam` | `/sites/gls-agadir` |

**Each city page must contain:**
- H1 with primary keyword exact-match
- NAP block (name, address, phone) — already done, just verify identical formatting across the site
- Embedded Google Map (you already have this — `map_iframe_src` in `resources/lang/*/sites/<city>.php`)
- "Itinéraires depuis [neighborhoods]" — 3-5 paragraphs with neighborhood names ("depuis Hay Riad", "depuis Hassan", etc.). This is what wins local rankings.
- Prices for that center, hours, parking note if relevant
- 2-3 student testimonials *from that city* (you have the Vimeo testimonials — tag them by location)
- LocalBusiness schema (see Section 4)

### 2.3 Course-page keyword pairing (Tier B)

| Existing page | Add target keyword |
|---------------|-------------------|
| `/niveaux/a1` | `cours allemand a1 maroc`, `débuter allemand maroc` |
| `/niveaux/b1` | `préparation b1 allemand maroc`, `niveau b1 examens` |
| `/exam/goethe` | `goethe institut maroc`, `examen goethe a1 a2 b1 maroc`, `goethe vs osd` |
| `/exam/osd` | `osd maroc`, `passer ösd maroc`, `osd b1 préparation` |
| `/intensive-courses` | `cours intensif allemand maroc`, `formation intensive allemand` |

### 2.4 Content gaps to fill (Tier C+D — blog content)

These pages don't exist yet. Each one earns links and ranks for long-tail. Write **one per week**:

1. `Étudier en Allemagne depuis le Maroc — guide complet 2026`
2. `Studienkolleg : qu'est-ce que c'est et comment y entrer depuis le Maroc`
3. `Goethe B1 vs ÖSD B1 : lequel choisir pour un visa étudiant ?`
4. `Combien de temps pour passer de A0 à B2 en allemand ?`
5. `Visa étudiant Allemagne depuis le Maroc — démarches étape par étape`
6. `Top 10 universités allemandes pour étudiants marocains`
7. `Niveau d'allemand requis pour chaque type de visa allemand`
8. `Comment trouver un logement étudiant en Allemagne ?`
9. `Budget mensuel d'un étudiant marocain en Allemagne`
10. `Sperrkonto : tout sur le compte bloqué pour visa étudiant`

Each post: 1500-2500 words, internal links to relevant `/niveau/*` and `/exam/*` pages, ends with a CTA to the inscription form.

### 2.5 Keyword research tools

- **Free:** Google Search Console (your real queries — gold), Google Autocomplete, "People also ask" boxes, AnswerThePublic
- **Paid:** Ahrefs / Semrush ($99-149/mo) — only if you commit to monthly tracking; otherwise overkill
- **AI-assisted:** Use ChatGPT to brainstorm 50 long-tail variations, then validate volume in GSC after publishing

---

## 3. On-page SEO (what to fix in the code)

Most of this is already in place — verify and tighten:

### 3.1 Per-page checklist

- [ ] One H1 per page, contains primary keyword
- [ ] Title tag: `<primary keyword> | GLS Sprachenzentrum` — 50-60 chars
- [ ] Meta description: 140-160 chars, includes keyword + CTA ("Inscription gratuite", "Cours dès 800 DH")
- [ ] OG image (1200×630) per page — generate via `seo-image-gen` skill if missing
- [ ] Canonical tag (`SEOTools::setCanonical(...)`) — the `artesaos/seotools` package is already installed
- [ ] hreflang tags between FR/EN/DE/AR equivalents (use `mcamara/laravel-localization` — already installed)
- [ ] Internal links: every city page links to courses + exams + inscription, every blog post links to 2-3 service pages
- [ ] Alt text on every image, descriptive (not "image1.jpg")

### 3.2 Technical foundation

- [ ] **Sitemap** — `php artisan sitemap:generate` runs in the project. Make it run nightly via Laravel scheduler, submit to GSC.
- [ ] **Robots.txt** — verify `/api/*` and `/backoffice/*` are blocked, sitemap URL is declared
- [ ] **Page speed** — LCP < 2.5s, INP < 200ms, CLS < 0.1. Run `npm run build` and check Lighthouse on each city page. Heaviest issues are usually: Vimeo iframes (use lazy-load + preconnect, you already have this), unoptimized hero images (convert to WebP/AVIF), no `font-display: swap` on `Now-*.otf`.
- [ ] **HTTPS** — non-negotiable, force-redirect HTTP → HTTPS
- [ ] **Mobile** — 80%+ of your traffic is mobile, every layout must work at 360px width

### 3.3 Schema markup (already partial — finish it)

Add JSON-LD via `artesaos/seotools` on each page type:

| Page | Schema type |
|------|-------------|
| Homepage | `EducationalOrganization` + `Organization` |
| `/sites/gls-*` | `LocalBusiness` + `Place` with `geo` coordinates + `openingHours` |
| `/exam/goethe`, `/exam/osd` | `Course` (each level a separate Course) |
| `/blog/*` | `Article` + `Author` + `Publisher` |
| FAQ sections | `FAQPage` |
| Testimonial videos | `VideoObject` with embedUrl + thumbnailUrl |

The Leaflet markers already have the data you need (lat/lng per center) — pipe it into `LocalBusiness` schema.

---

## 4. Off-page SEO — backlinks (the heaviest lever for ranking)

Google still treats backlinks as the #1 trust signal. For a Moroccan language school, you need ~50-150 quality referring domains to compete locally.

### 4.1 Backlink targets, ranked by effort vs. payoff

| Type | Where | Effort | Authority gain |
|------|-------|--------|----------------|
| **Citations / NAP directories** | Yellow Pages MA, Pages Maroc, Hesperis, Maroc Annuaire, Tijara, Avito (business listing) | low | low individually, high cumulatively |
| **University partnerships** | Each Moroccan university's "preparation" or "language partners" page — UM5, UMP, ENCG, FST | medium | high |
| **German cultural institutions** | Goethe Institute Rabat, DAAD Maroc, German embassy site partners list, Auslandsschulwesen | high | very high |
| **Student blogs / YouTube** | Sponsor 5-10 Moroccan student bloggers who write about studying abroad | medium | medium |
| **Local press** | Lematin, Hespress, Le360 — pitch stories: "X étudiants marocains partis en Allemagne en 2026 grâce à GLS" | high | high (one mention from Lematin = 10 directory links) |
| **Edu blogs** | Bayt, Tanmia, EmploiPublic | low | low-medium |
| **HARO / SourceBottle** | Reporter requests for "language school experts" — answer 1 per week | medium | medium-high |

### 4.2 Local citations — the easy 30

Submit identical NAP (Name, Address, Phone) to:
1. Google Business Profile — **for each of the 6 centers** (you should already have these, verify and optimize)
2. Bing Places for Business — same 6
3. Apple Business Connect
4. Yellow Pages Morocco (paginerougesmaroc.ma, pj.ma)
5. Hesperis directory
6. Maroc Annuaire
7. Tijara MA
8. Avito.ma — service category, free listing
9. Yandex Business (German students studying via Russia/EU routes still hit Yandex sometimes)
10. WAZE business listing
11. OpenStreetMap — add each center as a `school` node with website + phone tags
12. Foursquare
13. Yelp Morocco

**Critical:** the NAP must be **byte-identical** everywhere. Different spellings of "Avenue" vs "Av." or different phone formats split your local authority.

### 4.3 Backlink quality — what to avoid

- **No PBNs** (private blog networks) — Google's Helpful Content update detects them
- **No paid backlink packages** ($50 for 500 backlinks) — these are toxic, will trigger a manual action
- **No comment spam** on unrelated blogs
- **No automatic article submission services** (ArticleBase, EzineArticles)

### 4.4 Track your backlink profile

- **Google Search Console** → Links report — free, shows top referring domains
- **Ahrefs Webmaster Tools** — free tier shows your own site's backlinks
- **Moz Link Explorer** — free 10 queries/month
- Use the `seo-backlinks` skill in this project to run a monthly audit

---

## 5. Local SEO — where most of your inscriptions come from

For a service like yours, Google Business Profile (GBP) is more important than your website for the first contact.

### 5.1 GBP optimization per center (do for all 6)

- [ ] Verify ownership (postcard or video verification)
- [ ] Category: **Language school** (primary), **Educational institution** (secondary)
- [ ] Add all 6 levels (A1-C2), Goethe prep, ÖSD prep, Studienkolleg prep as **Services**
- [ ] Hours — exact, including special hours for Ramadan / Eid
- [ ] Phone — **center-specific number** if you have one, not a single national line
- [ ] Photos — minimum 20 per center: exterior, classrooms, teachers, students, lobby. Geotagged.
- [ ] Description — 750 chars, include primary keyword once
- [ ] **Reviews** — this is the single biggest local-pack ranking factor. Target: 50+ reviews per center, 4.7+ avg.
- [ ] **Posts** — 1 per week minimum (new class starting, student success, Goethe exam date)
- [ ] **Q&A** — seed 10 common questions yourself and answer them (Google scans this)
- [ ] **Products / Services** — list each course with price

### 5.2 Review-getting playbook

Most of your local ranking comes from reviews. To get them ethically:

1. Add a step at the end of each course: teacher hands student a card with QR code → `https://search.google.com/local/writereview?placeid=<PLACE_ID>`
2. SMS/WhatsApp follow-up 3 days after course completion with the same link
3. Email signature of every staff member: "Laissez-nous un avis Google → [link]"
4. **Never** offer discounts or gifts in exchange — violates GBP terms, can get reviews wiped
5. Respond to every review within 48h — both positive (thanks + name) and negative (apology + offer to call)

### 5.3 Local pack ranking factors (in order)

1. Number + quality + recency of GBP reviews
2. Proximity of searcher to your pin
3. Categories on GBP matching the search query
4. Website authority (your home page DR)
5. Citations consistency (NAP)
6. Photos + posts freshness

---

## 6. Conversion — turning rank into inscriptions

Ranking #1 with a bad landing page is worthless. Each entry point needs to push to one CTA: **inscription**.

### 6.1 What's working in your current setup

- ✅ Hero form on `/gls.inscription` — visible above the fold
- ✅ 3-step trust signals (data secured, response under 24h, no commitment)
- ✅ Landing pages for Google Ads and Meta Ads
- ✅ Vimeo testimonials (4 students)

### 6.2 What to add

- [ ] **Sticky WhatsApp button** on every page (mobile especially) — 50%+ of Moroccan students prefer WhatsApp over forms
- [ ] **Exit-intent popup** with discount or free level test offer (use Convertful or a self-hosted JS, ~1KB)
- [ ] **Calendar booking** on city pages — Calendly or self-hosted, "Book your free level test in 30 seconds"
- [ ] **Inline mini-form** on every blog post — 3 fields max: name, phone, level
- [ ] **Live chat** during business hours — Tawk.to free tier
- [ ] **Urgency** — "Prochaine session : 15 juin — il reste X places" (real number from DB)
- [ ] **Price transparency** — Moroccan parents Google "[course] prix" before booking. Display it.

### 6.3 Conversion tracking

- [ ] **GA4 events:** `form_submit`, `whatsapp_click`, `phone_click`, `map_click` — already wired? Verify.
- [ ] **Google Ads conversion tracking** — same event, imported into Ads for bidding optimization
- [ ] **GSC → GA4 link** — see queries that lead to conversions, not just clicks
- [ ] **Meta Pixel** — if running Meta ads, the LP already supports this. Add a Lead event on form_submit.
- [ ] **Server-side conversion** — Laravel webhook on `GlsInscription` model `saved` event → posts to GA4 Measurement Protocol. Catches inscriptions even if user has ad-blocker.

---

## 7. Content calendar — the next 90 days

### Month 1 — foundation
- Week 1: Audit GBP for all 6 centers, fix NAP everywhere
- Week 2: Submit to top 10 directories
- Week 3: Add LocalBusiness schema to all 6 city pages
- Week 4: Publish blog post #1 (`Étudier en Allemagne depuis le Maroc`)

### Month 2 — content + outreach
- Week 5: Publish blog post #2 (`Studienkolleg`)
- Week 6: Reach out to 5 student YouTubers, offer free A1 course in exchange for video
- Week 7: Blog post #3 (`Goethe vs ÖSD`) + Lematin pitch
- Week 8: Optimize site speed — LCP under 2s on home page

### Month 3 — scaling
- Week 9-12: 1 blog post per week + 1 outreach campaign per week + monthly GSC review

---

## 8. Tools you actually need

| Tool | Why | Free? |
|------|-----|-------|
| Google Search Console | The truth about your rankings + queries | Free |
| Google Analytics 4 | Behavior + conversions | Free |
| Google Business Profile | Local pack ranking | Free |
| PageSpeed Insights | Core Web Vitals | Free |
| Schema.org validator | Validate your JSON-LD | Free |
| Ahrefs Webmaster Tools | Your own backlinks | Free |
| Screaming Frog SEO Spider (free tier, 500 URLs) | Crawl your own site for issues | Free |
| Local Falcon or `seo-maps` skill | Geo-grid local rank tracking | Paid (~$25/mo) — optional but powerful |
| Semrush or Ahrefs Pro | Competitor + keyword research | Paid (~$100/mo) — only after month 3 |

This project already includes specialized SEO skills (`seo-audit`, `seo-local`, `seo-maps`, `seo-backlinks`, `seo-content`, `seo-technical`). Run `/seo-audit` monthly for a full site health check.

---

## 9. Common mistakes to avoid

1. **One generic page for all 6 centers** — already done correctly, you have separate URLs. Don't undo this.
2. **Translating the same content 4×** — Google can detect near-duplicate translations. Each locale needs slightly different content, not just word-for-word.
3. **Stuffing keywords** — "cours allemand cours d'allemand cours allemand Rabat cours allemand" reads bad and is penalized. Use natural phrasing.
4. **Ignoring mobile** — 80%+ of your traffic is mobile. Test every page at 360px.
5. **No fresh content** — sites that don't update lose rankings. Blog post per week is the minimum.
6. **Buying backlinks from "SEO services"** — toxic, manual penalties, recovery takes 6-12 months.
7. **Not responding to negative reviews** — silent treatment hurts more than a thoughtful reply.
8. **Skipping the inscription form A/B test** — current form has 4 sections; test against a 1-question landing version.

---

## 10. KPI dashboard — what to track weekly

| Metric | Source | Target (90 days) |
|--------|--------|------------------|
| Organic sessions | GA4 | +50% vs baseline |
| Organic conversions (form submits) | GA4 | +75% vs baseline |
| GBP profile views | GBP Insights | +100% per center |
| GBP direction requests | GBP Insights | +60% per center |
| Indexed pages | GSC | All pages indexed |
| Avg position (top 10 keywords) | GSC | <5 for all Tier A |
| Referring domains | Ahrefs / GSC | +30 new |
| Core Web Vitals — % good URLs | GSC | >85% |
| Avg review count per center | GBP | +20 reviews/quarter |
| Click-through rate | GSC | >4% on Tier A |

---

## 11. Quick wins (do these THIS week)

1. **Verify all 6 GBP profiles** are claimed and optimized
2. **Submit fresh sitemap** to GSC (`php artisan sitemap:generate`)
3. **Run `/seo-audit`** in this project — get the baseline report
4. **Add LocalBusiness schema** to one city page (Rabat) as a template, then replicate to the other 5
5. **Write 1 blog post** — the Studienkolleg or Étudier en Allemagne one
6. **Ask 10 current students for Google reviews** via WhatsApp
7. **Audit Tier A keyword rankings** in GSC — note positions, set 90-day targets

Everything else can wait. These 7 things move the needle the most.

---

*Document maintained by the GLS team. Update quarterly.*
*Last updated: 2026-05-21*
