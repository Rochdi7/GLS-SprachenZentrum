# WimSchool CRM — Architecture Reverse-Engineering Report

**Source material:** 65 screenshots, `C:\Users\ASUS\Desktop\rochdi\wimschool` (flat folder, no subfolders), all timestamped `2026-07-20 19:32:34` through `19:47:24` — a single continuous click-through session of a live WimSchool instance used by **GLS Sprachenzentrum** (branches seen: `GLS ONLINE`, `GLS MARRAKECH`).

**Method:** Every screenshot was read in chronological capture order. Most captures came in near-identical pairs (two screenshots ~1 second apart of the same screen) — these are noted as duplicates and merged into one finding rather than double-counted. Product is confirmed to live at `app.wimschool.com`, an Angular/Vue-style SPA with hash routing (`#/setting/schoolConfig/room`), French-first UI with an Arabic secondary-name field pattern on most entities, targeting the Moroccan market (MAD/DH currency, `+212` phone prefixes, real Moroccan bank names, Morocco-specific post-dated cheque practices, "Match Maroc" as a cancellation reason).

**Confidence key** used throughout this document:
- **Confirmed** — directly visible in a screenshot (field label, table column, tab name, real data).
- **Inferred** — a logical deduction from confirmed evidence (e.g. a workflow implied by tab names + status badges, or a relationship implied by two screens sharing an ID).
- **Unknown** — plausible but not evidenced; flagged explicitly so it isn't mistaken for a confirmed fact.

---

## 1. Top-Level Navigation Model

Two distinct navigation layers were observed:

1. **Left sidebar**, organized into labeled sections (partially visible in screenshot pair `194413/194414`, right-edge text only — left edge was cut off in the capture). Confirmed section headers, reconstructed from fragments:
   - **Formations / Pédagogie** (implicit — no header fragment captured, but this is where Groupes, Séances, Emploi du temps, Devoirs, Doc. pédagogique live based on shared tab bars)
   - **Financière** (Finance) — Paiements, Dépenses, Caisse, Recouvrements
   - **[Ressources Hu]maines** (HR) — Employés, Paie (Payroll)
   - **[Établis]sement** (Settings/branch config)
   - **Services** — unexplored, name only
   - **Stock** — unexplored, name only (also exists as a Dépense "Type")
   - **[Année] scolaire** — Academic year, folded into Settings > Calendrier académique
   - **[A]nalyses** (Reports/Analytics section header)
   - Confidence: **Confirmed** (section labels exist) but **Inferred** (exact grouping of every module under each header) since the left edge of the sidebar was never fully captured.

2. **Top header bar**: Notification bell with count, "Besoin d'aide?" help button, **Language switcher** (FR flag shown, so multi-language UI — confirmed EN capability not directly seen but implied by the flag selector), **Academic Year switcher** (`2025/2026`), **Branch/Site switcher** (`GLS ONLINE` in early screenshots, `GLS MARRAKECH` in later ones — confirms multi-branch SaaS/franchise architecture), fullscreen toggle, user avatar menu. — **Confirmed**.

3. **Per-module tab bar**: nearly every module uses a horizontal tab strip immediately under the page title for its own sub-sections (e.g. Paiements has `Recherche multicritères | Chèques | Avances | Statistique | Audit financier`). This is a consistent, reused UI pattern across the whole app. — **Confirmed**.

---

## 2. Complete Module List

### 2.1 CRM / Lead Management — **Suivi**
- **Purpose:** Capture and work prospective students (leads) from first contact through conversion to enrollment.
- **Pages:** Prospects (list/search) · Suivi (follow-up) · Rendez-vous (appointments) · Échanges (communication log) · Historique · Statistique · Configuration.
- **Main actions:** Campagne WhatsApp (bulk WhatsApp campaign), Importer depuis Excel, Ajouter (manual add), Rapports.
- **Key fields (Prospect):** Nom, Prénom, Téléphone, Commercial (assigned salesperson), État du prospect (status), Source, two freetext "Info suppls" fields, Note, Date d'ajout.
- **Business rule confirmed:** dashboard actively surfaces an alert count of unassigned leads ("29 prospect(s) sans commercial"), implying a required-assignment SLA/workflow.
- **Related modules:** Feeds directly into **Inscriptions** — the enrollment-creation form has a dedicated path "Prospect en préinscription" that converts a lead straight into an enrolled student without a separate manual step.
- **Screenshot refs:** `193234`.
- **Confidence:** Confirmed (list view, filters, alert banner); Inferred (Rendez-vous/Échanges/Historique/Statistique/Configuration page contents — tab names only, not opened).

### 2.2 Enrollment Management — **Gestion des inscriptions**
- **Purpose:** The core join between a Student and a Group/Formation; tracks the full lifecycle of a paid course enrollment.
- **Pages:** Recherche multicritères · Prochainement expirées · Expirées · Archivées · Annulées · Conventions.
- **Main actions:** Ajouter (unified creation form, see §3), Rapports, "Envoyer un message collectif" (bulk WhatsApp).
- **Key fields:** Référence (`I###`), Étudiant, Formation, Statut (badge: Active, and by tab name also Expirée/Archivée/Annulée), Date d'inscription, Date de début, Date de fin.
- **Business logic confirmed:** Inscription creation form ("Ajouter un inscription") supports **three origination paths** via a single required dropdown "Inscription pour": *Nouvel étudiant* (brand-new student created inline), *Étudiant déjà inscrit* (existing student, new course), *Prospect en préinscription* (lead conversion). This is the single most important workflow in the whole system — three otherwise-separate business processes (new customer acquisition, upsell/repeat enrollment, lead conversion) are unified into one form and one resulting record type.
- **"Conventions" tab** — Inferred: agreements/contracts sub-feature, likely for corporate or batch client accounts (companies enrolling employees). Not opened.
- **Related modules:** Student, Group, Prospect (via conversion), Frais/Payment (an Inscription generates the Frais schedule that Payments/Recouvrements later track).
- **Screenshot refs:** `193357/193358` (list, 82 total inscriptions), `193508/193509` (creation modal).
- **Confidence:** Confirmed for list + creation form; Inferred for exact expiry/archival trigger logic and Conventions contents.

### 2.3 Group / Class Management — **Groupes / En formation**
- **Purpose:** The class/cohort entity that students are enrolled into; owns a teacher, a level, and a schedule.
- **Pages:** En formation · En inscription · Historique.
- **Main actions:** Copier (duplicate an existing group's config — a real time-saver for opening a new cohort at the same level), Ajouter.
- **Key fields:** Nom (often a teacher-nickname pattern, e.g. "Frau Ghita"), Formation (=Niveau scolaire), Enseignant, Classific (CEFR-style level code, e.g. B2.2), Étudiants (headcount), a statistic widget of filled/empty/waitlist-style counts.
- **Hierarchy confirmed:** Département (e.g. "GLS ONLINE") → Catégorie de formation (e.g. "GLS ONLINE - COURS D'ALLEMAND - ONLINE") → Formation/Niveau scolaire → Group. A four-level taxonomy, not a flat course list.
- **Related modules:** Séances (a Group has many dated sessions), Inscriptions (students join via a Group FK), Employees (Enseignant FK, and teachers ARE employees, not a separate person table — see §2.7).
- **Screenshot refs:** `193411/193413`.
- **Confidence:** Confirmed for list columns and hierarchy; Inferred for exact meaning of the "statistic widget" icon counts (read as filled-seats/empty-seats/waitlist but not opened to verify).

### 2.4 Student Management — **Gestion des étudiants**
- **Purpose:** Master record for the person taking classes, distinct from both Prospect (pre-conversion) and Utilisateur externe (portal login account).
- **Pages:** Recherche multicritères · Parents.
- **Main actions:** Importer depuis Excel, Ajouter, "Plus d'actions" menu.
- **Key fields:** Référence (`E###`, auto-incrementing), Nom/Prénom in **French AND Arabic** (four name fields total), Catégorie d'âge, Date de naissance, Sexe (binary: Masculin/Féminin), Niveau scolaire, Profession (FK lookup), Pays, Lieu de naissance, and three **separate** messaging channels (Téléphone, WhatsApp, Télégram) each individually captured with country code — not derived from one phone number.
- **Creation-modal sub-tabs:** Contact · **Quantité de mémorisation** · Ajouter un paiement (inline payment at creation time) · Parent · Autre informations.
  - "Quantité de mémorisation" — **Unknown**: name suggests some kind of memorization/vocabulary-tracking quantity, but the tab contents were never opened. Flagging rather than guessing.
- **Related modules:** Parents (linked sub-records, presumably for minors), Inscriptions (a student has many), Utilisateurs externes (1:1 portal account), Recouvrements/Payments (financial history).
- **Screenshot refs:** `193426/193427` (list), `193452/193453` (creation modal).
- **Confidence:** Confirmed for all listed fields and tabs; Unknown for "Quantité de mémorisation" contents specifically.

### 2.5 Session & Attendance Management — **Gestion des séances**
- **Purpose:** The operational heart of daily class delivery — individual dated class occurrences, attendance capture, and the recurring schedule that generates them.
- **Pages (row 1):** Gestion des séances · Calendrier des horaires · Saisir l'absence · Suivi des absences · Absence par group.
- **Pages (row 2):** Statistiques · Séances non traitées · Configuration.
- **Main actions:** Rapports, "Créer plusieurs séances" (bulk create), Ajouter.
- **Two-layer session model confirmed:**
  1. **Calendrier des horaires** (recurring weekly timetable template): per Group, defines Jour (day of week), start/end time, Enseignant, Salle, and a **"Type de Création de séance"** toggle — *Automatique* (auto-generates dated Séance rows every week) vs *Manuelle*. Also carries **"Lien de la séance" / "Lien de la classe"** — online meeting URLs (Zoom/Meet/Teams), confirming virtual delivery for the ONLINE department.
  2. **Gestion des séances** (actual dated instances): Date, Heure, Nbr d'heure, Statut (workflow state — "Brouillon"/Draft observed, implying a later validated state), FK Groupe, FK Enseignant.
- **Attendance ("Saisir l'absence"):** per-Séance, per-Student tri-state toggle set (Présent / Retard / Absent — mutually exclusive). Confirmed automated alert badge "Merci de contacter l'administration" appears next to specific students — **Inferred** to be a threshold-triggered retention/follow-up flag (e.g. N consecutive absences), not a manually-set field.
- **Cancellation workflow confirmed:** a configurable lookup table of cancellation reasons (Configuration tab) with real GLS entries: *Fin de formation, Match maroc, jour férié, Congée, empêchement personnel, Malade*. Bilingual (Nom + Nom en arabe), Statut Active/Inactive, Note.
- **"Séances non traitées"** (Unprocessed sessions queue) — **Inferred**: a required processing/validation step before a session is considered finalized, which plausibly feeds attendance-based teacher payroll (see §2.9, "Paiement prof").
- **Related modules:** Groups (FK), Employees/Teachers (FK), Students (attendance), Rooms/Salles (FK, includes a Matière/Subject FK too), Payroll (inferred link via unprocessed-sessions → teacher pay).
- **Screenshot refs:** `193436/193438`, `193533` (Ajouter une séance modal), `193608/193610` (attendance-entry UI), `193625/193626` + `193636` (cancellation-reason config), `193655/193656` (recurring-schedule modal), `193755/193756` (Emploi du temps alternate view).
- **Confidence:** Confirmed for the two-layer model, attendance tri-state, and cancellation reasons; Inferred for the exact trigger of the admin-contact alert badge and the exact processing rule behind "Séances non traitées."

### 2.6 Timetable Management — **Emploi du temps**
- Appears both as a tab inside Séances ("Calendrier des horaires") and as what looks like its own top-level sidebar entry ("Gestion d'emploi du temps," tabs: Emploi du temps | Paramétrage d'emploi du temps). **Inferred** to be the same underlying recurring-schedule entity surfaced through two different navigation entry points rather than two separate concepts — the field shapes (Groupe, Matière, Jour, Heure, Salle, Enseignant, Statut) match exactly.
- One UI defect noted for awareness (not to be replicated): the "Jour" filter dropdown showed a mislabeled placeholder ("Choisir une salle") — a copy-paste bug in the source app.
- **Screenshot refs:** `193755/193756`.
- **Confidence:** Confirmed fields; Inferred that this and "Calendrier des horaires" are the same entity.

### 2.7 Homework & Teaching Materials — **Gestion des devoirs / Doc. pédagogique**
- Seen only as sidebar labels in the grayed-out background of a modal screenshot; contents never opened.
- **Inferred purpose:** Gestion des devoirs = homework/assignment tracking per group or student; Doc. pédagogique = a teaching-materials/document library (likely tied to Matière and/or Niveau scolaire).
- **Screenshot refs:** `193855` (background only).
- **Confidence:** Unknown beyond the existence and name of these two modules.

### 2.8 Payment Management — **Gestion des paiements**
- **Purpose:** Transactional record of every payment collected, with sub-modules for cheques, advances, and financial auditing.
- **Pages:** Recherche multicritères · Chèques · Avances · Statistique · Audit financier.
- **Key fields:** Référence (`P###`), Étudiant/Payeur, Montant, Reste (remaining if partial), Type (Réglement confirmed; others implied), Méthode (TPE/card terminal, Espèces/cash confirmed; Chèque/Virement implied by sibling modules), Frais (which fee line this pays), Date, Agent (FK employee — full accountability trail), **La caisse** (which till/cash-register the money went into).
- **Payment-creation workflow confirmed:** select Étudiant → select Inscription → system lists every outstanding **Frais** line for that inscription with Date d'échéance/Montant/Reste à payer → cashier enters a per-line "Montant de paiement" and Méthode. This is a proper fee-schedule allocation UI, not a single lump-sum field — supports partial payment against a specific fee.
- **Avances (Advances) sub-type confirmed:** a Payment record with no Frais assigned yet (Reste = Montant, fully unallocated) — i.e. money collected ahead of a fee being generated, to be applied later.
- **Related modules:** Students, Inscriptions, Frais (FeeType lookup, see §4 for full Frais catalog), Caisse, Employees (Agent).
- **Screenshot refs:** `193844/193845` (list, 97 900 DH shown), `193855` (Ajouter un paiement modal), `194144` (Avances tab).
- **Confidence:** Confirmed for all fields and the allocation workflow.

### 2.9 Cheque Management — **Gestion des chèques**
- **Purpose:** Morocco-specific handling of post-dated cheques used as payment guarantees — a real and common local business practice, not a generic "attach a cheque image" feature.
- **Sub-tabs:** Gestion des chèques · Chèques à déposer à la banque · Chèques à encaisser · Chèques en retard.
- **Key fields:** Num Chèque, Propriétaire (drawer — Student or Parent, via a "Source" selector), Montant, Reste, Banque (real Moroccan banks seen: ATTIJARIWAFA BANK, BANK OF AFRICA), Type (**confirmed enum: "Garantie (À encaisser)" and "À déposer"** — only two values visible in the dropdown), Date de réception, Date d'échéance, Statut (badge "En possession" seen; Déposé/Encaissé/Retourné implied by the tab names), Note.
- **Full lifecycle implied by tab structure:** held in hand → deposit at bank → cash/encash → flag if overdue.
- **Related modules:** Students, Parents, Payments (a cheque functions as a payment instrument).
- **Screenshot refs:** `193901/193911` (list), `193913` (list continued), `193922/193923` + `194128/194142` (creation modal, two different branch contexts — GLS ONLINE and GLS MARRAKECH — confirming the modal is shared across branches).
- **Confidence:** Confirmed for fields and the two Type values actually opened in the dropdown; Inferred for the full Statut lifecycle (only "En possession" was directly observed as data, the rest inferred from tab names).

### 2.10 Cash Register — **Gestion de la caisse**
- **Purpose:** Per-till/per-agent cash-drawer tracking with a supervisor-approved transfer workflow.
- **Pages:** Ma caisse · Validation de transfert.
- **KPIs confirmed:** Encaissements (inflows), Dépenses (outflows), Solde (running balance = inflows − outflows).
- **Actions:** "transférer" (move cash between tills), Rapports.
- **Business rule confirmed:** transfers require a separate approval step ("Validation de transfert") — cash movement between tills isn't instant/unilateral.
- **Related modules:** Payments (inflows), Dépenses (outflows, including a dedicated "Transfert à une autre caisse" expense type that records the transfer as an expense line on the source till).
- **Screenshot refs:** `194214/194216`.
- **Confidence:** Confirmed.

### 2.11 Expense Management — **Gestion des dépenses**
- **Purpose:** All money leaving a Caisse, categorized by a hybrid system + custom Type lookup.
- **Pages:** Recherche multicritères · Gestion des remboursements · Type de dépenses.
- **Key fields:** Référence (`D###` implied by placeholder "ex:D19"), Montant, Date, Type (FK), Statut (Brouillon → **Validé** workflow confirmed via data), Classe/Groupe (optional cost-center tagging — an expense can be attributed to a level/group), La caisse, Mots-clés, Agent.
- **Expense Types confirmed as a hybrid enum** (this is an important architecture pattern to replicate): six **system-protected** types with no edit/delete affordance — *Paiement prof* (Teacher Payment), *Remboursement au client*, *Stock*, *Transfert à une autre caisse*, *Alimentation de caisse* (cash top-up), *Salaire* — versus freely admin-editable custom types — *Produits consommables, Femme de menage, Externalisation ou sous-traitance, Logistiques* and others.
  - **Direct relevance to GLS's own existing project:** "Paiement prof" being a first-class, system-protected Expense Type strongly validates and extends the user's own in-progress "Professor Payment Automation" work (see project memory) — WimSchool models teacher payouts as Dépense records, almost certainly auto-generated from Séance/attendance data given the adjacent "Séances non traitées" queue.
- **Auto-validation setting confirmed** in Settings > Générale: "Gestion des dépenses : Valider les dépenses automatiques" (Oui/Non toggle, observed set to Non) — controls whether new expenses require manual approval before the Brouillon→Validé transition.
- **Related modules:** Caisse (drains it), Employees (Agent, and Paiement-prof type implies Teacher FK too), Refunds (sibling sub-module).
- **Screenshot refs:** `194240/194242` (list), `194258/194300` (Types de dépenses config), `194724` (auto-validate setting).
- **Confidence:** Confirmed for fields, the Statut workflow, and the six protected vs. custom-type split.

### 2.12 Refund Management — **Gestion des remboursements**
- **Purpose:** A simpler, dedicated audit trail for money refunded to a Student or Parent, kept separate from generic Dépenses even though both drain the same Caisse.
- **Key fields:** ID, Bénéficiaire (Student or Parent), Date, Montant total, Agent.
- **Screenshot refs:** `194251/194252`.
- **Confidence:** Confirmed.

### 2.13 Collections / Dunning — **Gestion des recouvrements**
- **Purpose:** Accounts-receivable aging report and follow-up tool for overdue fees — a proper dunning module, not just a filtered payment list.
- **Pages:** Retards selon la durée · Retards selon les frais · Retards selon les critères · Suivi du recouvrement · Prévisions de paiement.
- **Aging buckets confirmed:** Dernier 1 jour · 7 jours · 15 jours · 30 jours · Plus 30 jours — a standard AR-aging structure.
- **Key fields:** Référence, Étudiant, Statut, Téléphone (+WhatsApp), Frais, Date d'échéance, Retard (days overdue, auto-calculated), Reste à payer.
- **Action:** "Envoyer un message collectif" — bulk WhatsApp payment reminders, directly from the aging list.
- **Related modules:** Frais, Payments, Students.
- **Screenshot refs:** `193819/193820`, `193828/193829`.
- **Confidence:** Confirmed.

### 2.14 Employee / HR Management — **Gestion des employées**
- **Purpose:** Master staff record. **Teachers are Employees, not a separate table** — Enseignant is one value of a Catégorie field alongside Assistante administrative, Directeur, etc. This is a significant architecture decision worth deliberately keeping or deliberately overriding in a rebuild.
- **Pages:** Recherche multicritères · Congés et absences (staff leave/absence tracking, separate from student attendance).
- **Key fields:** Référence (`PR###`), Prénom, Nom, Catégorie (role/job-title FK), Statut (Actif), three contact channels (phone/WhatsApp/Telegram), **Type: "Partagé" / "Non partagé"** — Inferred to mean "shared across branches" vs. "assigned to a single branch," consistent with the confirmed multi-branch architecture; a teacher working at multiple GLS campuses would be "Partagé."
- **Related modules:** Groups (Enseignant FK), Séances (Enseignant FK), Dépenses (Paiement-prof type), Paie/Payroll (sidebar section confirmed to exist, contents not opened).
- **Screenshot refs:** `194315/194316`.
- **Confidence:** Confirmed for fields; Inferred for the exact meaning of Partagé/Non partagé.

### 2.15 Portal / External Users — **Utilisateurs externes**
- **Purpose:** The authentication/login layer for non-staff users, kept as a separate entity from the Student business record — a clean separation of Person vs. Account.
- **Key fields:** Nom d'utilisateur (username, `e#####` — mirrors the Student `E###` numbering scheme), Prénom (full name shown), Téléphone, WhatsApp, Catégorie (only "Élève" observed; **Inferred** that "Parent" is a second possible value, not directly confirmed), Statut (Actif), with a direct WhatsApp-message shortcut icon in the Action column.
- **Significance:** confirms a **student/parent-facing self-service portal exists** alongside the admin backoffice — this must be scoped into any rebuild, not treated as backoffice-only.
- **Screenshot refs:** `194350/194351`.
- **Confidence:** Confirmed for the module's existence and fields; Inferred for the full set of Catégorie values and what the portal itself lets a student/parent actually do (never opened — only the admin-side user list was seen).

### 2.16 Settings / Branch Configuration — **Établissement**
- **Purpose:** The central configuration hub, itself organized into seven sub-areas, and the anchor of the confirmed multi-branch architecture.
- **Top sub-navigation:** Etablissement · Pédagogiques · Financiers · Calendrier académique · Inscriptions · Documents · Générale.
- **Établissement tab:** the branch/tenant record itself — Nom du centre (FR + AR), Pays, Ville, Address (FR + AR), Code Postal, Téléphone, Whatsapp, Email, and a **"Siège Social" (Head Office) Oui/Non flag** distinguishing the main office from satellite branches. Real GLS data observed: `GLS MARRAKECH`, Marrakech, `info@glssprachenzentrum.ma`, `+212 808 66 39 83`.
- **Établissement > Salles sub-tab:** Room/venue catalog — Nom, Effective (capacity), Statut. Confirmed dual use: physical rooms named after staff (e.g. "SALLE 01 ALAOUI") *and* thematically-named virtual rooms for the online department, branded after German cities (Wuppertal, Konstanz, Darmstadt, Ingolstadt) with real seat-capacity numbers.
- **Pédagogiques tab:** Structures pédagogiques · Matières (Subjects lookup) · Les cadences (pacing/rhythm config, contents unopened) · **Classification** — the master CEFR-style level catalog (B1.2, B2.3, B2.2, B2.1, B1.3, B1.1, A2.3, A2.2, A2.1, A1.2, +1 more on page 2 = 11 total), which is the same lookup that drives Group.Classific and Student.Niveau scolaire.
- **Financiers tab:** Types de frais (the full Fee Type catalog — see §4 for detail) · Modes de paiement (Payment Method lookup, unopened but implied to back the Méthode field seen throughout Payments/Cheques).
- **Calendrier académique tab:** Gestion des années scolaires (Academic Year — L'année scolaire, Année de début, Date de début/fin, Par défaut, Fermé, Inscription ouverte) · Périodes (terms) · Jours fériés (public holidays — feeds the Séance cancellation-reason list) · Vacances (school breaks).
- **Inscriptions, Documents, Générale tabs:** not opened in detail; Générale confirmed to hold at least one business-rule toggle (auto-validate expenses).
- **Screenshot refs:** `194128/194142` (branch switcher context), `194413/194414` (Salles list + sidebar fragment), `194427/194428` (Établissement detail), `194451/194452` (Classification), `194459/194500` (Types de frais), `194633/194634` (Academic Year), `194722/194724` (Générale toggle).
- **Confidence:** Confirmed for every field explicitly listed above; Inferred/Unknown flagged inline for Structures pédagogiques, Les cadences, Modes de paiement, Inscriptions tab, and Documents tab contents (names known, screens not opened).

---

## 3. Cross-Module Workflows

### 3.1 Lead-to-Enrollment Pipeline (Confirmed)
`Prospect (Suivi)` → *Ajouter un inscription, Inscription pour = "Prospect en préinscription"* → `Inscription` (with inline Student creation if needed) → `Group` assignment (Affectation tab) → `Frais` schedule generated → `Payments/Recouvrements` track collection.

### 3.2 Class Delivery & Attendance (Confirmed)
`Calendrier des horaires` (recurring template, Automatique/Manuelle) → generates → `Séance` (dated instance, Statut: Brouillon → …) → `Saisir l'absence` (per-student tri-state) → `Séances non traitées` queue (Inferred: a required processing gate) → (Inferred) feeds `Dépense: Paiement prof`.

### 3.3 Fee Collection & Dunning (Confirmed)
`Inscription` → `Frais` (monthly + one-off types, from the Types de frais catalog) → `Payment` (full or partial, per-line allocation) or `Avance` (unallocated prepayment) or `Chèque` (post-dated guarantee instrument) → if unpaid past due date → surfaces in `Recouvrements` aging buckets → `WhatsApp bulk reminder`.

### 3.4 Cash Flow (Confirmed)
`Payment` (inflow) and `Dépense` (outflow, including inter-till `Transfert`) both post against a specific `Caisse` → `Caisse.Solde` = running balance → inter-till transfers require `Validation de transfert` approval.

### 3.5 Multi-Branch Operation (Confirmed)
Every major screen carries a branch switcher (`GLS ONLINE`, `GLS MARRAKECH` both observed with real, different data) and an academic-year switcher. `Établissement` records carry a `Siège Social` flag. `Employee.Type` (Partagé/Non partagé) governs whether staff belong to one branch or float across several. Rooms, groups, and séances are branch-scoped.

---

## 4. User Roles & Permissions

- **Directly confirmed as a role/category value** (from the Employee Catégorie field): *Directeur*, *Assistante administrative*, *Enseignant* (Teacher).
- **Agent identities seen in transaction data** (Payments/Dépenses "Agent" columns): named individuals (`mustapha`, `saad01`, `latifa`) plus a generic account (`gls`) — Inferred to be either a shared/house login for legacy data or a role-based account rather than a person.
- **CRM-specific role:** "Commercial" (salesperson) referenced as a Prospect-assignment target — Inferred to be either a filtered view of Employee or a distinct sales-role tag.
- **No explicit permissions matrix or role-editor screen was captured.** A granular permission system (e.g. per-module CRUD flags per role) is **Unknown** — plausible for a system this size, but not evidenced. Do not assume Laravel-style spatie/permission granularity exists in WimSchool; only build what GLS's own rebuild needs (see `startup-mvp.md`).
- **Portal-side roles confirmed:** "Élève" (Student) category in Utilisateurs externes; "Parent" is Inferred, not confirmed.

---

## 5. Reports & Analytics (as referenced, not opened)

Every finance and CRM module carries a "Rapports" button and/or a "Statistique" tab (Suivi, Inscriptions, Séances, Paiements, Chèques, Dépenses/Type de dépenses all have one). None were opened in this screenshot set, so their actual content is **Unknown**. Their consistent placement across modules is itself a confirmed UI pattern worth replicating: every list-heavy module gets a dedicated reporting export point.

A left-sidebar "Analyses" section header was seen (fragment only) — Inferred to be a cross-module analytics/dashboard area separate from the per-module Rapports buttons.

---

## 6. What GLS Should Definitely Keep

- **The unified Inscription-creation form** (New Student / Existing Student / Prospect conversion in one form) — this single design decision eliminates a whole class of duplicate-data bugs and is the strongest idea in the whole system.
- **Frais as a configurable catalog, not free text** — monthly fee types with prorate/sort-key/salary-impact flags. This is what makes Recouvrements, Payments, and (per the "Paiement prof" type) payroll all work off one consistent source of truth.
- **Per-line fee allocation in the payment UI** (pick Inscription → see every outstanding Frais → pay against specific lines) rather than a single lump payment field.
- **Caisse (till) as a first-class entity** with inflow/outflow/balance and an approval-gated transfer flow — necessary the moment more than one cashier or more than one branch exists.
- **The two-layer session model** (recurring template generates dated instances) — this is the only sane way to run a weekly-recurring class schedule without hand-creating every session.
- **Teachers modeled as Employees**, not a bolt-on separate table — simplifies HR, payroll, and permission logic.
- **Cheque tracking as a first-class entity** — mandatory for the Moroccan market regardless of what GLS's rebuild looks like elsewhere.
- **WhatsApp as a first-class channel** (bulk campaigns from Prospects, bulk reminders from Recouvrements, per-student WhatsApp number stored everywhere) — this is clearly the dominant communication channel for this business and should not be an afterthought integration.

## 7. What Looks Optional

- **Telegram as a third messaging channel** on Student/Employee — captured everywhere but no evidence it's actually used operationally (no Telegram-based bulk-action button was seen anywhere, unlike WhatsApp).
- **The Arabic-name/Arabic-address duplicate fields** on every entity — worth keeping only if GLS's own back-office staff or documents genuinely need Arabic-language output; otherwise it doubles data-entry for a field that may sit empty on most records (several sample rows had it blank).
- **"Quantité de mémorisation"** on the student form — unclear enough in purpose that it may be legacy or low-usage; verify with GLS staff before reimplementing rather than guessing at its schema.
- **Doc. pédagogique / Gestion des devoirs** — plausible value (teaching materials, homework) but zero confirmed detail; treat as a Phase 2 candidate to be scoped properly later rather than guessed at now.

## 8. What Appears Over-Engineered (for a single-purpose language center)

- **Full AR-aging Recouvrements module with five separate tabs** (by duration / by fee / by criteria / follow-up / forecasts) is a lot of surface area for what could start as one filterable overdue-fees view with the same WhatsApp bulk-action.
- **Six system-protected + N custom Expense Types with per-type salary/prorate flags** is solid design but heavier than a school with one or two branches needs on day one — a flat Expense Type table without the "protected vs. custom" distinction is enough until the business genuinely needs to lock down types tied to code logic (payroll, till transfers).
- **Separate Utilisateurs externes entity distinct from Student** is the right long-term architecture but is pure overhead if the student portal isn't being built in Phase 1 — don't stand up the auth-account/business-record split until the portal itself is scoped.

## 9. What Should NOT Be Rebuilt for Version 1

- Multi-branch / multi-tenant switching (Établissement, Siège Social flag, per-branch Salles) — build for GLS's actual current branch count; do not build a franchise-ready abstraction speculatively.
- The full Cheque lifecycle UI (four sub-tabs: à déposer / à encaisser / en retard, plus the base list) — start with a single Cheque list with a Statut filter; split into dedicated tabs only once cheque volume justifies it.
- Congés et absences (staff leave tracking) — HR leave management is a distinct concern from school operations and can wait.
- Any reporting/analytics beyond basic per-module list exports — the "Statistique" and "Rapports" surfaces were never opened in this research and their value is unproven; do not invest in a reporting engine before the core data model is stable.
- Structures pédagogiques / Les cadences (pacing config) — their exact purpose is unconfirmed; do not build speculative schema for concepts that were never actually opened and inspected.

---

*End of architecture.md. See `database-schema.md` for the full entity/column reconstruction and `startup-mvp.md` for the phased build recommendation.*
