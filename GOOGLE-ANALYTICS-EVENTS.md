# GLS — Google Analytics 4 (GA4) Event Tracking Setup

This document lists every custom tracking event already implemented in the GLS Sprachenzentrum
website codebase, and explains step-by-step how to connect them to **Google Analytics 4 (GA4)**
so they show up as reports / conversions.

You can paste this whole file into ChatGPT (or share with whoever manages the Google
Analytics / Google Ads account) and ask them to walk through the "GA4 Setup Steps" section.

---

## 1. How tracking currently works on this site (context)

- The site loads Google's `gtag.js` (used for Google Ads, tag id `AW-17817493313`).
- It is loaded **only after the visitor accepts the cookie consent banner**
  (`localStorage['gls_cookie_choice'] === 'accepted'`), via `public/assets/js/consent-loader.js`.
- Once loaded, `window.gtag` and `window.dataLayer` are available globally.
- A helper file, `public/assets/js/gls-events.js`, exposes `window.glsTrack(eventName, params)`,
  which safely calls `gtag('event', eventName, params)` (and does nothing if consent hasn't been
  given yet — no errors, just silently skipped).
- **A GA4 property is already live**, Measurement ID `G-STVL64P4J1` (stream: "GLS Sprachenzentrum",
  `https://glssprachenzentrum.ma`), already receiving traffic. It has been wired into the codebase:
  `ga4Id: 'G-STVL64P4J1'` was added to `window.GLS_TRACKING` in `layouts/app.blade.php`, and
  `consent-loader.js`'s `loadGtag()` now calls `gtag('config', cfg.ga4Id)` right after the Ads
  config call. **All custom events in Section 2 now flow into this GA4 property automatically**
  as soon as the visitor accepts cookie consent — no further code changes needed. Section 3 below
  is now just verification + conversion setup on the GA4 UI side.

---

## 2. Full list of custom events already implemented

Event names use `Readable_Title_Case` (underscores instead of spaces) so anyone glancing at GA4
reports understands them immediately — GA4 only allows letters/numbers/underscores in event
names, so this is the most readable format GA4 won't silently rewrite.

### Site-wide auto-detected (no per-page markup needed — `public/assets/js/gls-events.js`)

| Event name                | Fires when…                                                                                   | Event params sent |
|-----------------------------|------------------------------------------------------------------------------------------------|----------------------|
| `WhatsApp_Button_Click`     | Visitor clicks **any** link pointing to `wa.me/...`                                            | `event_category: 'WhatsApp'`, `event_label: <page path>` |
| `Google_Maps_Click`         | Visitor clicks **any** link to Google Maps (`google.*/maps`, `maps.app.goo.gl`, `goo.gl/maps`) | `event_category: 'Google Maps'`, `event_label: <page path>` |
| `Phone_Number_Click`        | Visitor clicks **any** `tel:` link                                                             | `event_category: 'Contact'`, `event_label: <page path>` |
| `Email_Link_Click`          | Visitor clicks **any** `mailto:` link                                                          | `event_category: 'Contact'`, `event_label: <page path>` |
| `Social_Media_Click`        | Visitor clicks a link to Instagram/Facebook/YouTube/TikTok/LinkedIn/Twitter-X (found in `contact.blade.php` footer-socials-block) | `event_category: 'Social Media'`, `event_label: <platform>` |
| `Partner_Link_Click`        | Visitor clicks an outbound link to the fc-marokko.de partner site                              | `event_category: 'Partner'`, `event_label: 'fc-marokko'` |
| `Language_Switch_Click`     | Visitor clicks FR/EN/DE/AR in the language switcher (`.nav-lang-btn`, header)                  | `event_category: 'Navigation'`, `event_label: <language code>` |
| `Enroll_Button_Click`       | Visitor clicks any button/link opening the GLS enroll modal (`data-bs-target="#glsEnrollModal"`) — tells you which page drove the click | `event_category: 'GLS Inscription'`, `event_label: <page path>` |
| `Consultation_Button_Click` | Visitor clicks any button/link opening the Consultation modal (`data-bs-target="#consultationModal"`), e.g. the blog page's "Contact" CTA | `event_category: 'GLS Inscription'`, `event_label: <page path>` |
| `Certificate_Download_Click`| Visitor clicks the certificate PDF download button (`.vc-download-btn`) after a successful certificate lookup | `event_category: 'Certificate'`, `event_label: <page path>` |
| `Tawk_Chat_Opened`          | Visitor opens/maximizes the Tawk.to live chat widget                                           | `event_category: 'Live Chat'` |

### Every form on the site: "Send clicked" vs "Completed" — kept as separate events

Every form now fires **two** distinct events instead of one: a `*_Send_Clicked` event the instant
the visitor clicks the submit/send button (pure intent — fires even if the submission then fails
validation or the server rejects it), and a `*_Completed`/`*_Submitted` event only when the
server actually confirms success. This lets you measure submit-button clicks vs real conversions
separately — the gap between the two numbers is your validation/error drop-off.

| Form                          | "Send clicked" event              | "Completed" event                    | File(s) |
|--------------------------------|--------------------------------------|------------------------------------------|---------|
| Consultation popup             | `Consultation_Send_Clicked`          | `Consultation_Form_Submitted`             | `public/assets/js/consultation-form.js` |
| Group Apply modal              | `Group_Apply_Send_Clicked`           | `Group_Apply_Form_Submitted`              | `public/assets/js/apply-group.js` |
| GLS Inscription — standalone page | `Inscription_Page_Send_Clicked`   | `Inscription_Page_Form_Submitted`         | `public/assets/js/gls-form-page.js` |
| GLS Inscription — modal popup (3-step) | `Inscription_Modal_Send_Clicked` (fires on the final-step Continuer click) | `Inscription_Modal_Submitted` | `public/assets/js/gls-form.js` |
| GLS Inscription — ad landing page (Meta/Google) | `Inscription_LP_Send_Clicked` | `Inscription_LP_Form_Submitted`      | `public/assets/js/gls-lp-form.js` |
| Attestation request            | `Attestation_Send_Clicked`           | `Attestation_Form_Submitted` (fires on the success page load) | `resources/views/frontoffice/attestation-request.blade.php` + `attestation-request-success.blade.php` |
| General Contact page           | `Contact_Send_Clicked`               | `Contact_Form_Completed` (fires when the page reloads with `session('success')`) | `resources/views/frontoffice/contact.blade.php` |
| Certificate lookup              | `Certificate_Search_Send_Clicked`    | `Certificate_Search_Completed` (fires when the result section renders) | `resources/views/frontoffice/certificates/check.blade.php` |
| Newsletter signup (footer)     | `Newsletter_Send_Clicked`            | `Newsletter_Signup_Completed`             | `public/assets/js/newsletter.js` |

**Notes on "Completed" reliability by form type:**
- AJAX forms (Consultation, Group Apply, Inscription Page/Modal/LP, Newsletter) fire "Completed"
  only after the server responds with a genuine success status — accurate, no false positives.
- Plain POST/full-reload forms (Contact, Certificate Search, Attestation) can't intercept the
  response client-side, so "Completed" is inferred from a session-flash success flag rendered on
  the reloaded page — still accurate (only fires when the server actually processed the request
  successfully), just implemented differently under the hood.

### GLS Inscription modal — step-by-step funnel and per-step abandonment

The 3-step enroll popup (`#glsEnrollModal`) now tracks exactly which step a visitor gets stuck on,
not just a generic "abandoned":

| Event name                                   | Fires when… |
|-------------------------------------------------|----------------|
| `Enroll_Button_Click`                            | Visitor clicks any button/link that opens the modal (site-wide, tells you which page drove it) |
| `Inscription_Modal_Viewed`                       | The modal opens |
| `Inscription_Modal_Step1_Completed`              | Visitor fills step 1 (Informations) and clicks "Continuer" |
| `Inscription_Modal_Step2_Completed`              | Visitor fills step 2 (Centre/Type) and clicks "Continuer" |
| `Inscription_Modal_Send_Clicked`                 | Visitor fills step 3 (Groupe/Niveau) and clicks the final "Envoyer" |
| `Inscription_Modal_Submitted`                    | Server confirms the submission succeeded |
| `Inscription_Modal_Abandoned_At_Step_1`          | Visitor advanced past step 1, navigated **Back** to step 1, then closed the modal |
| `Inscription_Modal_Abandoned_At_Step_2`          | Visitor completed step 1, reached step 2, then closed the modal without finishing |
| `Inscription_Modal_Abandoned_At_Step_3`          | Visitor completed steps 1–2, reached step 3, then closed the modal without submitting |

Abandonment only fires if the visitor engaged with at least step 1 first (opening and immediately
closing the modal with zero interaction does **not** count as abandonment — there's nothing to
recover from that visit). Abandonment reports the step the visitor was **actually on when they
closed** the modal (not the furthest step ever reached), so someone who advances to step 3 and
then navigates Back to step 2 before quitting is correctly attributed to step 2.

**Funnel note:** `Enroll_Button_Click` (which page drove the click) → `Inscription_Modal_Viewed` →
`Inscription_Modal_Step1_Completed` → `Inscription_Modal_Step2_Completed` →
`Inscription_Modal_Send_Clicked` → `Inscription_Modal_Submitted`. At any point after Step1, a
close instead of Continuer fires the matching `Abandoned_At_Step_N` event.

### Other form/page-specific events

| Event name                | Fires when…                                                                                   | Event params sent                                              | File |
|-----------------------------|------------------------------------------------------------------------------------------------|------------------------------------------------------------------|------|
| `Video_Play_Click`          | Visitor clicks any click-to-load video facade (marketing/testimonial videos, Vimeo/YouTube)    | `event_category: 'Video'`, `event_label: <video title or id>`   | `public/assets/js/video-facade.js` |
| `niveau_test_started`       | Visitor clicks "Start" on the level-test quiz (already existed before this project — different naming convention, left as-is) | `quiz_level` | `public/assets/js/quiz.js` |
| `niveau_test_completed`     | Level-test quiz result renders (server-side, already existed before this project)              | `quiz_level`, `score_correct`, `score_total`, `score_percent`   | `resources/views/frontoffice/quiz/index.blade.php` |
| `Studienkolleg_Apply_Click` | Visitor clicks "Apply now" / "Start application" / mobile "Apply" bar on a Studienkolleg detail page (outbound to the school's own application portal) | `event_category: 'Studienkolleg'`, `event_label: <school name>` | `resources/views/frontoffice/studienkollegs/show.blade.php` |

**Known gaps not yet tracked (found during the site-wide sweep, low priority / intentionally skipped):**
- FAQ search/category filters, accordion opens, location-card expand toggles — pure UI filtering with
  no navigation, would be noisy without much analytical value.
- Blog category filters, "read more" post clicks, blog search — plain internal links/forms, lower
  priority than the conversion-focused events above; can be added later the same way if useful.
- Blog post social-share icons (Facebook/Twitter share intents, copy-link) — cosmetic, can add later.
- Studienkolleg "Add to Favorites" button, official-website link, entrance-exam link — secondary
  engagement signals, not conversions.
- A few exam-page "4 steps" cards use dead `href="#"` placeholder links (not a tracking gap — those
  buttons aren't functional yet, worth a separate bug report to whoever owns those pages).

All events are only sent **after the visitor has accepted cookies** (GDPR-compliant — same gate as
the existing Google Ads/Meta Pixel/Ahrefs trackers).

---

## 3. GA4 Setup Steps

### Step 1 — GA4 property ✅ already done
- Property/stream: "GLS Sprachenzentrum", `https://glssprachenzentrum.ma`
- **Measurement ID: `G-STVL64P4J1`**
- Confirmed "Data collection is active in the past 48 hours" in the GA4 UI.

### Step 2 — Connect the Measurement ID to the site ✅ already done
`window.GLS_TRACKING.ga4Id` is now set to `'G-STVL64P4J1'` in
`resources/views/frontoffice/layouts/app.blade.php`, and `consent-loader.js`'s `loadGtag()` now
calls `gtag('config', cfg.ga4Id)` right after the Google Ads `config` call, e.g.:

```js
window.gtag('config', cfg.gtagId);   // Google Ads (AW-17817493313)
if (cfg.ga4Id) {
    window.gtag('config', cfg.ga4Id);  // GA4 (G-STVL64P4J1)
}
```

Because every custom event in this codebase calls `window.glsTrack(...)` → `gtag('event', ...)`
(not tied to a specific `send_to` id), **all events listed in Section 2 now flow into this GA4
property automatically**, as soon as the visitor accepts cookie consent. No per-event code changes
are needed — what's left is verification and conversion setup in the GA4 UI (Steps 3–5 below).

### Step 3 — Verify events are arriving in GA4
1. In GA4, go to **Admin → DebugView** (or **Reports → Realtime**).
2. On your phone/computer, enable debug mode temporarily by opening the site with
   `?gtm_debug=x` in the URL, or install the **Google Analytics Debugger** Chrome extension.
3. Accept the cookie consent banner on the site (required — events won't fire before consent).
4. Click a WhatsApp link, a Google Maps link, open Tawk chat, or click a form's send button.
5. Within a few seconds you should see the matching event name (e.g. `WhatsApp_Button_Click`,
   `Consultation_Send_Clicked`, then `Consultation_Form_Submitted` a moment later) appear in
   DebugView with its parameters.

### Step 4 — Mark key events as GA4 Conversions
Once events are confirmed arriving, mark the **"Completed"** events as conversions (not the
"Send_Clicked" ones — those are intent signals, useful for drop-off analysis but not real
conversions on their own):
1. GA4 → **Admin → Events**.
2. Find each event you want counted as a conversion (recommended: `Inscription_Page_Form_Submitted`,
   `Inscription_Modal_Submitted`, `Inscription_LP_Form_Submitted`, `Consultation_Form_Submitted`,
   `Group_Apply_Form_Submitted`, `Attestation_Form_Submitted`, `Contact_Form_Completed`,
   `Certificate_Search_Completed`, `Newsletter_Signup_Completed`, `Studienkolleg_Apply_Click`,
   `WhatsApp_Button_Click`).
3. Toggle **"Mark as conversion"** next to each.
4. They'll now appear under **Admin → Conversions** and in acquisition/campaign reports as
   goal completions.

### Step 5 (optional) — Build a drop-off funnel for the inscription modal
1. GA4 → **Explore → Funnel exploration**.
2. Add steps in order:
   - Step 1: `Inscription_Modal_Viewed`
   - Step 2: `Inscription_Modal_Step1_Completed`
   - Step 3: `Inscription_Modal_Step2_Completed`
   - Step 4: `Inscription_Modal_Submitted`
3. GA4 will show the % drop-off between each step — i.e. how many people opened the form, how
   many got past step 1, how many got past step 2, and how many actually finished.
   `Inscription_Modal_Abandoned_At_Step_2` / `_At_Step_3` are emitted as separate events for
   anyone who leaves at that step, so you can also build a simple report comparing
   `Inscription_Modal_Step1_Completed` vs `Inscription_Modal_Abandoned_At_Step_2` counts, and
   `Inscription_Modal_Step2_Completed` vs `Inscription_Modal_Abandoned_At_Step_3` counts — this
   tells you exactly which step is losing the most people.

### Step 6 (optional) — Google Ads conversions (not GA4, but related)
Since a Google Ads tag (`AW-17817493313`) is already loaded, you can also mark specific events as
Google Ads conversions (useful for Ads bidding optimization), separately from GA4. This requires:
1. Creating Conversion Actions in Google Ads (Tools → Conversions → **+ New conversion action** →
   "Website").
2. Getting the conversion label Google Ads gives you (looks like `AW-17817493313/AbC-D_efG...`).
3. Sending that as a **separate** `gtag('event', 'conversion', { send_to: 'AW-XXX/YYY' })` call
   alongside the existing event — this is a follow-up step, not required for GA4 to work.

---

## 4. Quick reference — what to tell ChatGPT / your analytics person

> "I have a Laravel website that already fires `gtag('event', ...)` calls for ~35 custom events
> covering WhatsApp/Maps/phone/email/social clicks, the enroll and consultation modals, and every
> form on the site (9 forms total: consultation, group apply, inscription page/modal/landing-page,
> attestation, contact, newsletter, certificate search) — each form fires a distinct 'Send_Clicked'
> event on submit-button click AND a separate 'Completed'/'Submitted' event only on confirmed
> server success, so I can measure the drop-off between clicking send and actually succeeding. The
> 3-step inscription modal additionally tracks per-step abandonment (which exact step visitors get
> stuck on). Full list in Section 2 of this doc. A GA4 property already exists (Measurement ID
> `G-STVL64P4J1`) and is wired into the site alongside our Google Ads tag (`AW-17817493313`) via
> `gtag('config', ...)`. I need help to: (1) verify the events show up correctly in GA4 DebugView,
> (2) mark the recommended 'Completed' conversion events from Step 4 as conversions in GA4 (not the
> 'Send_Clicked' ones), (3) build the step-by-step funnel exploration from Step 5 for the
> inscription modal drop-off."
