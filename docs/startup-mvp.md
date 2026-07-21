# GLS Startup CRM — MVP Recommendation

This is **not** a WimSchool clone. It's a from-zero build plan informed by what actually works in WimSchool (see `architecture.md`) and what GLS's own Laravel codebase already suggests about scale and priorities (per `CLAUDE.md`: a single Laravel 11 app, dual frontoffice/backoffice, already has Group/Teacher/Site/Certificate/Quiz models, FR/EN localization, Spatie Media Library). The goal is the smallest system that runs GLS's day-to-day school operations correctly, with a clean seam to grow into the fuller WimSchool feature set later — not to front-load complexity GLS doesn't need yet.

---

## Phase 1 — Required (MVP)

Ordered roughly by dependency — earlier modules are prerequisites for later ones.

### 1. Authentication & Roles
**Why necessary:** Nothing else works without knowing who's acting. GLS's existing app already has Laravel Sanctum + built-in auth with email verification — extend this rather than replacing it.
**Scope:** Staff login (backoffice). A minimal role set — `admin`, `directeur`, `assistante`, `enseignant` — is enough; WimSchool's role list (Directeur/Assistante administrative/Enseignant, confirmed in `architecture.md` §4) validates this is the right starting shape. **Do not** build a granular per-module permission matrix in Phase 1 — WimSchool itself showed no evidence of one, and a flat role check (`if ($user->role === 'enseignant')`) is enough until GLS has more than a handful of staff.

### 2. Students
**Why necessary:** The person taking classes is the anchor entity everything else attaches to (enrollments, payments, attendance).
**Scope, informed by the confirmed WimSchool schema:** Nom/Prénom (start FR-only; add the Arabic pair later only if GLS staff actually need it — WimSchool's own sample data frequently left it blank), Date de naissance, Sexe, Niveau (FK), Téléphone + WhatsApp as two distinct fields (this one is worth keeping from day one — WhatsApp is clearly GLS's dominant channel per every bulk-action button seen across the CRM), Email, Adresse. Skip Profession, Lieu de naissance, Pays, and the "Quantité de mémorisation" field entirely — none showed evidence of being load-bearing to daily operations, and the last one's actual purpose is unconfirmed even in WimSchool itself.
**Parent linkage:** a simple `parent_name` + `parent_phone` pair of columns directly on Student is enough for Phase 1. Don't build a separate `parents` table with its own reference-number series until GLS has a real need to bill or message a parent independently of the student record (see Phase 2).

### 3. Leads (Prospects)
**Why necessary:** GLS is clearly running active lead-generation (the WimSchool data showed 29 unassigned prospects at a glance, WhatsApp campaign buttons, a "commercial" assignment workflow) — this isn't optional CRM theater, it's how new students actually arrive.
**Scope:** Nom, Prénom, Téléphone, État (Nouveau/Contacté/Converti/Perdu — a simple 4-state enum, not WimSchool's open-ended "État du prospect" dropdown), Source (freetext or small enum), Commercial (FK to staff), Note. **The one WimSchool idea absolutely worth keeping from day one:** a single "Convert to enrollment" action that creates the Inscription (and Student, if new) directly from a Prospect record, rather than making staff re-type the same person's info twice.

### 4. Teachers
**Why necessary:** Confirmed WimSchool decision worth adopting directly: **teachers are staff/employees, not a separate table.** GLS's own CLAUDE.md already shows a `Teacher` model distinct from `User` — worth a deliberate decision here rather than accidental drift.
**Scope for Phase 1:** if GLS's current `Teacher` model is working, keep it, but make sure it can eventually merge into a broader `Employee` concept (see Phase 2 HR) without a painful migration — i.e. don't hard-code `teacher_id` as a special-case foreign key type in every other table; use a pattern that could later become `employee_id` with a role filter.

### 5. Courses / Levels
**Why necessary:** WimSchool's confirmed four-level hierarchy (Département → Catégorie de formation → Niveau → Group) is more than GLS needs at MVP scale for one language center. **Collapse it to one level: a `Niveau` (Level) lookup table** — GLS's own CEFR levels (A1.1 through B2.x, matching WimSchool's confirmed 11-entry Classification table almost exactly, since both are German-language schools using the same standard). Skip Département and Catégorie de formation entirely; GLS doesn't run multiple departments or training categories today, and adding that hierarchy speculatively is exactly the kind of premature structure this MVP should avoid.

### 6. Groups
**Why necessary:** The class/cohort a student actually attends.
**Scope:** Nom, Niveau (FK), Enseignant (FK), a simple headcount. Skip the "Copier" duplicate-group feature and the filled/empty/waitlist statistic widget for Phase 1 — nice WimSchool touches, not blockers.

### 7. Attendance
**Why necessary:** Directly maps to the user's own already-in-progress work — the "Professor Payment Automation" project (per project memory) exists specifically to calculate teacher pay from attendance imports. This module is not optional; it's the data source for a project GLS is already building.
**Scope, adopting WimSchool's confirmed two-layer model because it's genuinely the right shape, not over-engineering:**
- A simple recurring weekly schedule per Group (day of week + start/end time) — this alone, without WimSchool's Automatique/Manuelle toggle or online meeting-link fields, is enough to generate dated sessions.
- A `Session` (séance) row per actual class date, with a tri-state attendance record per student (Présent/Retard/Absent) — WimSchool's exact model, because it's simple, well-proven, and is precisely what a teacher-payment-from-attendance calculation needs as its input.
- Skip: cancellation-reason lookup table (a freetext `note` field covers this at MVP scale), the "Séances non traitées" processing-queue concept (add only once attendance volume is high enough that unprocessed sessions become a real operational risk), online meeting links (add if/when GLS actually needs virtual delivery tracked in-app rather than just sharing a Zoom link via WhatsApp).

### 8. Payments
**Why necessary:** GLS needs to know who paid, how much, and what's owed — this is non-negotiable for a paid language school.
**Scope, deliberately simpler than WimSchool's full fee-schedule-allocation engine:** a `Payment` record (Student, Montant, Méthode [Espèces/Virement/Chèque — start with 3, add TPE/card-terminal support only once GLS actually takes card payments in-app], Date, Note) plus a simple `Fee` concept per Inscription (Montant dû, Date d'échéance, Montant payé — computed as sum of linked Payments). **Keep WimSchool's core idea — Frais as a small configurable catalog of fee types (monthly + one-off), not free text** — because it's genuinely useful and cheap to build: a 5–10 row lookup table (Frais d'inscription, Frais mensuel, Frais d'examen) with a name and an amount is enough; skip the prorata/salary-impact/sort-key flags until there's a proven need. **Defer to Phase 2:** the Chèque (post-dated cheque) entity, Caisse (till) tracking, Avances (unallocated prepayments), and the full Recouvrements (AR-aging/dunning) module — all real and valuable ideas, but not required to open the doors and start collecting money correctly.

### 9. Dashboard
**Why necessary:** Every stakeholder (director, admin staff) needs an at-a-glance view without digging through modules — and WimSchool's own UI reinforced this pattern (the unassigned-prospects alert banner, the Caisse balance KPIs) as clearly valuable at a glance.
**Scope:** total active students, total unpaid/overdue fee amount, sessions today, new leads this week. Four numbers, not a configurable widget dashboard.

### 10. Basic Reports
**Why necessary:** "How many students do we have," "who owes money," "what did we collect this month" are baseline questions any director will ask on day one.
**Scope:** three simple filtered/exportable lists — Students (with level, group, payment status), Payments (by date range), Overdue Fees (by days-late) — as Blade views with basic filters, not a dedicated reporting engine. WimSchool's ubiquitous per-module "Rapports" button pattern is worth noting for later, but its actual content was never confirmed in this research (see `architecture.md` §5), so there's nothing concrete to replicate yet.

---

## Phase 2 — Later

Each of these is a real, valuable WimSchool idea — none are being dismissed as bad design. They're deferred because none block day-one school operations, and building them now means guessing at requirements GLS hasn't hit yet.

- **Full Accounting Suite** (Caisse/till tracking, inter-till transfers with approval workflow, Dépenses with the protected-vs-custom expense-type split, Remboursements as a distinct audit trail) — postpone until GLS has more than one cashier or more than one branch; a single till and a simple expense list covers one location fine.
- **Cheque Management** (post-dated guarantee cheques, deposit/encash/overdue lifecycle) — this is genuinely Morocco-market-necessary and should move to Phase 1 immediately if GLS is currently accepting post-dated cheques from students without any system tracking them; otherwise, add it the first time a bounced or lost cheque causes a real problem.
- **Collections / Dunning module** (aging buckets, bulk WhatsApp reminders) — a simple "overdue fees" filtered list in Phase 1's basic reports covers the essential need; build the dedicated multi-tab aging module only once overdue-fee volume makes manual review too slow.
- **Advanced Reports / Analytics** — WimSchool's own Statistique/Rapports screens were never opened in this research and their actual value is unproven; don't invest here before Phase 1's simple lists prove insufficient.
- **Inventory / Stock** — WimSchool has a "Stock" expense type and sidebar entry with zero confirmed detail; add only if GLS starts tracking physical goods (books, materials) at a volume that spreadsheets can't handle.
- **Library / Doc. pédagogique / Devoirs (Homework)** — confirmed to exist in WimSchool by name only, contents never opened; scope these properly with GLS staff input once Phase 1 is stable, rather than guessing at a schema now.
- **Marketing / Campaign automation beyond basic WhatsApp bulk-send** — WimSchool's "Campagne WhatsApp" button is worth a simple Phase 1 equivalent (a "select students, send WhatsApp link" action), but a full campaign/automation engine is Phase 2.
- **SMS** — no evidence WimSchool actually uses SMS as a channel (every bulk action seen was WhatsApp); don't add SMS infrastructure GLS hasn't asked for.
- **Full Automation** (auto-generated recurring sessions via Automatique/Manuelle toggle, auto-validating expenses) — valuable once volume justifies it; Phase 1's manually-triggered weekly-schedule generation is enough at GLS's likely current scale.
- **Third-party Integrations** — none were confirmed in WimSchool beyond WhatsApp deep-links (which aren't a real API integration, just `wa.me` links) — nothing to prioritize here yet.
- **Advanced Finance** (Avances/prepayments as a distinct type, per-fee-line partial payment allocation, financial audit trail tab) — real and useful, but Phase 1's simpler "sum of payments vs. amount due" model covers 90% of the actual bookkeeping need.
- **HR beyond a basic staff list** (Congés et absences / leave tracking, employee categories beyond Teacher/Admin/Director) — defer until GLS's staff count makes informal leave-tracking (a shared calendar, a WhatsApp message) genuinely insufficient.
- **Payroll (Paie)** — confirmed to exist in WimSchool's sidebar, zero detail available; this is the natural Phase 2 destination for the user's own in-progress "Professor Payment Automation" work once Phase 1's Attendance module is producing clean data to calculate from.
- **Multi-school / Multi-branch Management** (Établissement switching, Siège Social flag, per-branch room/staff scoping) — do **not** build this speculatively. If GLS is currently single-location, a multi-tenant abstraction is pure overhead; add it only when GLS actually opens a second physical or online branch, and even then, start with a simple `branch_id` column on the handful of tables that need it rather than WimSchool's full switcher UI.
- **Student/Parent Self-Service Portal** (WimSchool's confirmed "Utilisateurs externes" concept) — a genuinely good long-term idea (students checking their own attendance/payment status), but it's a second application surface with its own auth, UI, and support burden; build the backoffice first and prove the operational model before exposing any of it externally.
- **Cancellation-reason lookup tables, exam-fee-type catalogs, and other small configurable lookups** WimSchool has — cheap to add later, not worth the schema overhead before Phase 1 is running.

---

## Final Recommendation

### Estimated module count
**10 modules for Phase 1** (Auth/Roles, Students, Leads, Teachers, Courses/Levels, Groups, Attendance, Payments, Dashboard, Basic Reports) — versus WimSchool's confirmed ~20+ distinct modules across CRM, Formations, Séances, five separate Finance sub-modules, HR, Settings, and Portal. Roughly **half the surface area**, covering the operations that are actually blocking without today's daily work.

### Estimated database tables
**~12–14 tables for Phase 1**: `users`, `students`, `prospects`, `teachers` (or `employees` if merged early), `niveaux`, `groups`, `horaires` (simple recurring schedule), `seances`, `presences`, `fee_types`, `fees` (per-inscription instance), `payments`, `inscriptions` — versus the **25 tables reconstructed from WimSchool** in `database-schema.md` (several of which are themselves Unknown/Inferred rather than fully confirmed). This is a deliberately tight core; every Phase 2 item above adds roughly 1–4 tables of its own when its time comes.

### Complexity
**Low-to-moderate.** Every Phase 1 table maps to a table GLS's existing Laravel 11 app already has some version of or clearly needs (Group, Teacher, and Site models already exist per `CLAUDE.md`) — this is closer to "extend and formalize what's there" than "build from scratch." The main new surface area is Attendance (needed for the payment-automation project already underway) and a proper Payment/Fee model (currently likely informal, given no Payment-related controller is mentioned in the existing project structure).

### Development priority
1. **Attendance + Fee/Payment core** — highest priority, because it directly unblocks the user's already-in-progress teacher-payment-automation work and is the piece most likely currently done manually or in spreadsheets.
2. **Leads → Enrollment conversion flow** — second priority; this is the single WimSchool idea with the best cost-to-value ratio (one form, one workflow, eliminates duplicate data entry) and directly supports GLS's active lead pipeline.
3. **Dashboard + Basic Reports** — build last among the "required" items, once there's real data flowing through the modules above to actually report on.

### Recommended architecture for a modern Laravel + Next.js CRM

Given GLS's existing stack is **Laravel 11 + Blade + Vite**, not a decoupled API + SPA — the realistic, lowest-risk recommendation is: **stay on Laravel + Blade for Phase 1.** A Next.js frontend is a genuine architectural upgrade worth considering, but only once/if:
- The Student/Parent Portal (Phase 2) is actually being built — a public-facing, highly-interactive self-service surface is where Next.js's strengths (fast client-side nav, better perceived performance, easier PWA/mobile-web story) actually pay for their added complexity.
- The backoffice itself grows complex enough that Blade's server-round-trip model becomes a genuine productivity drag (long multi-tab forms like WimSchool's Inscription-creation modal are the kind of UI where a proper SPA state model starts to matter).

**If/when that point is reached**, the recommended shape is: Laravel stays as the API backend (Sanctum already in place for token auth), a Next.js app consumes it for the portal and/or a rebuilt backoffice, and the existing Blade frontoffice (public marketing site) stays exactly as it is — no reason to touch a working public website. This is a **decouple-when-needed** strategy, not a big-bang rewrite: build Phase 1 as Laravel + Blade controllers/views extending the existing `Backoffice` controller structure, and treat "do we need Next.js" as a Phase 2 decision informed by which Phase 2 module gets built first.

---

*End of startup-mvp.md. This plan intentionally rebuilds only what GLS needs now, using WimSchool as a reference for proven ideas (Frais catalog, two-layer attendance, unified lead-conversion form) rather than a checklist to replicate in full.*
