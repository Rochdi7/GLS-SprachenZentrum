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

| Event name                              | Fires when…                                                                                   | Event params sent                                              | File |
|-------------------------------------------|------------------------------------------------------------------------------------------------|------------------------------------------------------------------|------|
| `WhatsApp_Button_Click`                   | Visitor clicks **any** link on the site pointing to `wa.me/...` (WhatsApp)                     | `event_category: 'WhatsApp'`, `event_label: <page path>`         | `public/assets/js/gls-events.js` (site-wide, auto-detected) |
| `Google_Maps_Click`                       | Visitor clicks **any** link to Google Maps (`google.*/maps`, `maps.app.goo.gl`, `goo.gl/maps`) | `event_category: 'Google Maps'`, `event_label: <page path>`      | `public/assets/js/gls-events.js` (site-wide, auto-detected) |
| `Tawk_Chat_Opened`                        | Visitor opens/maximizes the Tawk.to live chat widget                                           | `event_category: 'Live Chat'`                                    | `public/assets/js/gls-events.js` |
| `Consultation_Form_Submitted`             | "Consultation" popup form is submitted successfully (AJAX success)                             | `event_category: 'Consultation'`, `event_label: 'Consultation Modal'` | `public/assets/js/consultation-form.js` |
| `Group_Apply_Form_Submitted`              | "Apply to a group" modal form is submitted successfully                                        | `event_category: 'Group Application'`, `event_label: <group name>` | `public/assets/js/apply-group.js` |
| `Inscription_Page_Form_Submitted`         | GLS Inscription **standalone page** form (`/gls-inscription`) is submitted successfully        | `event_category: 'GLS Inscription'`, `event_label: 'Page Form'`  | `public/assets/js/gls-form-page.js` |
| `Inscription_Modal_Viewed`                | The GLS Inscription **modal popup** (3-step) is opened                                         | `form_source: 'modal'`                                           | `public/assets/js/gls-form.js` |
| `Inscription_Modal_Step1_Completed`       | Inside the GLS Inscription **modal popup**: visitor fills step 1 (Informations) and clicks "Continuer" | `event_category: 'GLS Inscription'`, `event_label: 'Modal - Step 1 Completed'` | `public/assets/js/gls-form.js` |
| `Inscription_Modal_Abandoned`             | Visitor closes the GLS Inscription modal **after** completing step 1 but **without** finishing/submitting | `event_category: 'GLS Inscription'`, `event_label: 'Modal - Abandoned at step N'` | `public/assets/js/gls-form.js` |
| `Inscription_Modal_Submitted`             | GLS Inscription **modal popup** form is submitted successfully                                 | `form_source: 'modal'`                                           | `public/assets/js/gls-form.js` |
| `Attestation_Form_Submitted`              | Attestation request form is submitted successfully (lands on success page)                     | `event_category: 'Attestation'`, `event_label: 'Attestation Request'` | `resources/views/frontoffice/attestation-request-success.blade.php` |

**Funnel note for the inscription modal:** `Inscription_Modal_Viewed` → `Inscription_Modal_Step1_Completed`
→ `Inscription_Modal_Submitted` (or `Inscription_Modal_Abandoned` if they leave early). This lets you
build a GA4 funnel exploration showing exactly where visitors drop off in the 3-step form.

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
4. Click a WhatsApp link, a Google Maps link, open Tawk chat, or submit one of the forms.
5. Within a few seconds you should see the matching event name (e.g. `WhatsApp_Button_Click`,
   `Inscription_Modal_Abandoned`) appear in DebugView with its parameters.

### Step 4 — Mark key events as GA4 Conversions
Once events are confirmed arriving:
1. GA4 → **Admin → Events**.
2. Find each event you want counted as a conversion (recommended: `Inscription_Page_Form_Submitted`,
   `Inscription_Modal_Submitted`, `Consultation_Form_Submitted`, `Group_Apply_Form_Submitted`,
   `Attestation_Form_Submitted`, `WhatsApp_Button_Click`).
3. Toggle **"Mark as conversion"** next to each.
4. They'll now appear under **Admin → Conversions** and in acquisition/campaign reports as
   goal completions.

### Step 5 (optional) — Build a drop-off funnel for the inscription modal
1. GA4 → **Explore → Funnel exploration**.
2. Add steps in order:
   - Step 1: `Inscription_Modal_Viewed`
   - Step 2: `Inscription_Modal_Step1_Completed`
   - Step 3: `Inscription_Modal_Submitted`
3. GA4 will show the % drop-off between each step — i.e. how many people opened the form,
   how many got past step 1, and how many actually finished. `Inscription_Modal_Abandoned` is
   emitted as a separate event for anyone who leaves after step 1, so you can also just build a
   simple report comparing `Inscription_Modal_Step1_Completed` vs `Inscription_Modal_Abandoned` counts.

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

> "I have a Laravel website that already fires `gtag('event', ...)` calls for these events:
> `WhatsApp_Button_Click`, `Google_Maps_Click`, `Tawk_Chat_Opened`, `Consultation_Form_Submitted`,
> `Group_Apply_Form_Submitted`, `Inscription_Page_Form_Submitted`, `Inscription_Modal_Viewed`,
> `Inscription_Modal_Step1_Completed`, `Inscription_Modal_Abandoned`, `Inscription_Modal_Submitted`,
> `Attestation_Form_Submitted`. A GA4 property already exists (Measurement ID `G-STVL64P4J1`) and is
> wired into the site alongside our Google Ads tag (`AW-17817493313`) via `gtag('config', ...)`. I
> need help to: (1) verify the events show up correctly in GA4 DebugView, (2) mark the
> form-submission and WhatsApp-click events as conversions in GA4, (3) build a funnel exploration
> for the inscription modal drop-off (`Inscription_Modal_Viewed` → `Inscription_Modal_Step1_Completed`
> → `Inscription_Modal_Submitted`/`Inscription_Modal_Abandoned`)."
