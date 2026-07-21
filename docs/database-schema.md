# WimSchool CRM — Database Schema Reconstruction

Reconstructed entirely from field labels, table columns, filter dropdowns, and real sample data visible across 65 screenshots. No database was accessed. Column types are **estimated** from displayed values (e.g. a date-picker showing `DD/MM/YYYY` → `DATE`; a `###DH` amount → `DECIMAL`).

**Confidence key:**
- **Confirmed column** — the field label or table header was directly visible.
- **Strongly inferred column** — not directly labeled, but required by the confirmed relationships/workflow (e.g. every join table needs its two foreign keys even if the FK column itself wasn't screenshotted).
- **Unknown** — plausible but not evidenced; listed separately per table so it's never confused with the first two categories.

Reference-number prefixes observed and preserved below because they reveal separate auto-increment sequences per entity: `E###` Students, `I###` Inscriptions, `P###` Payments, `PR###` Employees, `D#` Expenses (placeholder only), `e#####` portal usernames.

---

## 1. `prospects` (Leads)

**Purpose:** Pre-conversion contact captured by the CRM before becoming a paying student.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| prenom | VARCHAR(100) | No | |
| nom | VARCHAR(100) | No | |
| telephone | VARCHAR(20) | No | seen with `+212` prefix |
| etat | VARCHAR(50) / enum-like | No | "État du prospect"; value seen: "Nouveau" |
| source | VARCHAR(100) | Yes | value seen: "Google Sheet" |
| commercial_id / responsable_id | BIGINT FK → employees | Yes | dashboard explicitly flags NULL as a problem state ("29 prospect(s) sans commercial") |
| info_suppl_1 | TEXT | Yes | freetext, seen holding a long WhatsApp-contact narrative |
| info_suppl_2 | VARCHAR(255) | Yes | short value, e.g. "online" |
| note | VARCHAR(255) | Yes | |
| date_ajout | DATETIME | No | seen with time component (`17/07/2026 04:48`) |

**Strongly inferred columns:** `agence_id` (checkbox filter "Pas agence" implies an Agence entity, not otherwise evidenced — flagged, not fabricated as its own table), `created_by`, `updated_at`.

**Unknown:** exact contents of "Filtre spécifique" filter dropdown; schema behind Rendez-vous/Échanges/Historique tabs (each is plausibly its own related table — `prospect_appointments`, `prospect_communications` — but zero columns were observed).

**Relationships:** `commercial_id` → `employees.id`. Converts into `inscriptions` (and transitively `students`) via the enrollment form; no confirmed FK from `inscriptions` back to `prospects` was seen, but the conversion workflow implies one is needed (see §2, inferred `converted_from_prospect_id`).

**Indexes to create:** `(commercial_id)`, `(etat)`, `(telephone)`, `(date_ajout)`.

**Confidence:** Confirmed core fields; Inferred FK-back-reference on conversion; Unknown sub-tab schemas.

**Evidence screenshots:** `193234`.

---

## 2. `inscriptions` (Enrollments)

**Purpose:** The central join between a Student and a Group, representing one paid course enrollment with its own lifecycle.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| reference | VARCHAR(20) | No | format `I###`, e.g. `I206` |
| student_id | BIGINT FK → students | No | |
| group_id | BIGINT FK → groups | No | set in the "Affectation" tab |
| statut | VARCHAR(30) | No | values implied by tabs: Active, Prochainement expirée, Expirée, Archivée, Annulée |
| date_inscription | DATE | No | defaults to today on creation |
| date_debut | DATE | Yes | |
| date_fin | DATE | Yes | |

**Strongly inferred columns:** `converted_from_prospect_id` (BIGINT FK → prospects, nullable — required by the "Prospect en préinscription" creation path), `offre_id` (BIGINT FK → offres — the "Offres" filter dropdown seen on both this module and Recouvrements implies a pricing-plan entity), `academic_year_id` (every module in the app is scoped by the top-nav year switcher).

**Unknown:** the exact schema of `conventions` (contract/agreement records) — tab exists, never opened; whether "Prochainement expirée" is a computed view (date_fin within N days) or a stored status value.

**Relationships:** belongs to Student, belongs to Group, optionally originates from a Prospect, has many Frais (fee schedule generated on creation — inferred, not a directly-labeled FK but required by the payment-allocation UI which lists Frais per Inscription), has many Payments (via Frais), has many Cheques (via the "Choisir l'inscription" field on the payment form... actually Cheques link to Student/Parent directly, not Inscription — see §9).

**Indexes to create:** `(student_id)`, `(group_id)`, `(statut)`, `(reference)` unique, `(date_fin)` for expiry-detection queries.

**Confidence:** Confirmed core fields and the three-origin creation workflow; Inferred FK columns as noted.

**Evidence screenshots:** `193357/193358`, `193508/193509`.

---

## 3. `groups` (Classes / Cohorts)

**Purpose:** A scheduled class cohort at a given level, taught by a given teacher, that students enroll into.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| nom | VARCHAR(100) | No | e.g. "Frau Ghita", "AMINE B2" |
| departement_id | BIGINT FK → departements | No | e.g. "GLS ONLINE" |
| categorie_formation_id | BIGINT FK → categories_formation | No | e.g. "GLS ONLINE - COURS D'ALLEMAND - ONLINE" |
| formation_id / niveau_scolaire_id | BIGINT FK → niveaux (Classification) | No | drives the "Classific" column shown |
| enseignant_id | BIGINT FK → employees | No | |
| etudiants_count | INT | computed | displayed count, likely a query not a stored column |

**Strongly inferred columns:** `capacite_max` (a numeric statistic widget of filled/empty/waitlist counts was seen but exact column breakdown unverified — flagged as Unknown below rather than guessed), `academic_year_id`, `branch_id` (établissement scoping).

**Unknown:** precise meaning/columns behind the filled/empty/red-badge statistic icons in the group list (read as attendance or capacity-vs-waitlist, never opened to confirm exact numbers' source).

**Relationships:** belongs to Département, belongs to Catégorie de formation, belongs to Niveau (Classification), belongs to Employee (teacher), has many Inscriptions, has many Séances, has many Horaires (recurring schedule rows).

**Indexes to create:** `(departement_id)`, `(categorie_formation_id)`, `(formation_id)`, `(enseignant_id)`.

**Confidence:** Confirmed fields and four-level hierarchy; Unknown on the statistic-widget breakdown.

**Evidence screenshots:** `193411/193413`.

---

## 4. `students`

**Purpose:** Master record for a person taking classes — distinct from `prospects` (pre-conversion) and `external_users` (portal login).

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| reference | VARCHAR(20) | No | format `E###`, auto-increment, e.g. `E337` |
| nom_fr | VARCHAR(100) | No | required |
| prenom_fr | VARCHAR(100) | No | required |
| nom_ar | VARCHAR(100) | Yes | |
| prenom_ar | VARCHAR(100) | Yes | |
| categorie_age_id | BIGINT FK → categories_age | Yes | e.g. "Adultes" |
| date_naissance | DATE | Yes | |
| sexe | ENUM('M','F') | No | required, binary only — no third option observed |
| niveau_scolaire_id | BIGINT FK → niveaux | Yes | |
| profession_id | BIGINT FK → professions | Yes | |
| pays | VARCHAR(100) | Yes | default "Morocco" |
| lieu_naissance | VARCHAR(100) | Yes | city dropdown |
| email | VARCHAR(255) | Yes | |
| telephone | VARCHAR(20) | Yes | `+212` prefix default |
| whatsapp | VARCHAR(20) | Yes | separately captured from telephone |
| telegram | VARCHAR(20) | Yes | separately captured |
| adresse | VARCHAR(255) | Yes | free text |

**Strongly inferred columns:** `age` is computed from `date_naissance`, not stored. `created_at`/`updated_at` standard timestamps.

**Unknown:** exact schema behind "Quantité de mémorisation" tab on the creation modal — name suggests a numeric vocabulary/memorization-tracking field but contents were never opened; do not invent a table for this without confirming with GLS staff.

**Relationships:** has many Inscriptions, has many Parents (linked sub-records — see §5), has one External User account (portal login), has many Payments/Cheques/Remboursements as payer or beneficiary.

**Indexes to create:** `(reference)` unique, `(nom_fr, prenom_fr)`, `(telephone)`, `(niveau_scolaire_id)`, `(categorie_age_id)`.

**Confidence:** Confirmed for all listed fields (both list view and full creation-modal form were captured). Unknown flagged explicitly for memorization field.

**Evidence screenshots:** `193426/193427` (list), `193452/193453` (full creation form).

---

## 5. `parents` (or `student_parents`)

**Purpose:** Guardian/parent record linked to a Student, presumably for minors and/or as an alternate payer.

**Confirmed columns:** none directly — the "Parent" tab exists on the Student creation modal, and `Parent` appears as a filterable/selectable field across Payments, Cheques, Remboursements, and the standalone "Parents" tab on the Student list module, but the Parent record's own field list was never opened.

**Strongly inferred columns:** `id`, `student_id` (FK, or a many-to-many `student_parent` pivot if one parent can have multiple children — plausible but unconfirmed), `nom`, `prenom`, `telephone`, `whatsapp` (Parent phone numbers were shown as selectable/searchable elsewhere, implying these columns exist).

**Unknown:** whether Parent is its own top-level entity with its own reference-number series (like Student's `E###`) or a lightweight sub-record with no independent identity; relationship cardinality (1 parent : many students, vs. many parents : many students for separated/blended families).

**Relationships:** linked to Student (cardinality unconfirmed); usable as a payer on Payments and as a Propriétaire/Source on Cheques; usable as a Bénéficiaire on Remboursements.

**Indexes to create:** `(student_id)` if a simple FK model is chosen.

**Confidence:** Unknown for the full schema — this table's existence is Confirmed (referenced constantly as a selectable entity) but its own field structure was never directly screenshotted. Any real rebuild should verify the Parent model directly in a live WimSchool session before finalizing columns.

**Evidence screenshots:** `193452/193453` (Parent tab reference only), plus every Payments/Cheques/Remboursements filter screen showing a "Parent" dropdown.

---

## 6. `seances` (Class Sessions — dated instances)

**Purpose:** One actual, dated occurrence of a class meeting.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| group_id | BIGINT FK → groups | No | |
| matiere_id | BIGINT FK → matieres | No | required on creation form |
| enseignant_id | BIGINT FK → employees | No | required |
| salle_id | BIGINT FK → salles | Yes | optional, default room observed |
| date | DATE | No | defaults to today |
| heure_debut | TIME | No | |
| heure_fin | TIME | No | |
| nbr_heure | DECIMAL(3,1) | computed or stored | displayed as "2h" |
| statut | VARCHAR(30) | No | "Brouillon" (Draft) observed; other states inferred (Validé/Traité) |
| note | TEXT | Yes | |
| lien_seance | VARCHAR(500) | Yes | online meeting URL, seen on the recurring-template form specifically — may live only on `horaires`, not per-séance; flagged |
| cancellation_reason_id | BIGINT FK → seance_cancellation_reasons | Yes | only populated if the session was cancelled |
| horaire_id | BIGINT FK → horaires | Yes | link back to the recurring template that generated it, if Automatique |

**Strongly inferred columns:** `academic_year_id`, `created_by`.

**Unknown:** the exact set of Statut values beyond "Brouillon"; whether `lien_seance`/`lien_classe` live on `seances`, on `horaires`, or both (they were only directly observed on the recurring-template creation form).

**Relationships:** belongs to Group, belongs to Matière, belongs to Employee (teacher), belongs to Salle, optionally belongs to a Horaire template, optionally belongs to a cancellation reason, has many Présences (attendance rows).

**Indexes to create:** `(group_id, date)`, `(enseignant_id, date)`, `(statut)`, `(date)` for the "Séances non traitées" queue.

**Confidence:** Confirmed core fields (creation form + list view both captured); Inferred/Unknown flagged for statut enum completeness and lien_* placement.

**Evidence screenshots:** `193436/193438` (list), `193533` (creation modal).

---

## 7. `horaires` (Recurring Timetable Templates)

**Purpose:** The weekly recurring schedule definition per Group that auto-generates dated `seances` rows.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| group_id | BIGINT FK → groups | No | |
| matiere_id | BIGINT FK → matieres | No | |
| jour | VARCHAR(10) / TINYINT | No | day of week |
| heure_debut | TIME | No | |
| heure_fin | TIME | No | |
| enseignant_id | BIGINT FK → employees | Yes | |
| salle_id | BIGINT FK → salles | Yes | |
| type_creation | ENUM('Automatique','Manuelle') | No | drives whether séances auto-generate |
| lien_seance | VARCHAR(500) | Yes | online meeting link |
| lien_classe | VARCHAR(500) | Yes | online classroom link (persistent, vs. per-session link) |
| statut | VARCHAR(30) | Yes | "Active" seen |

**Strongly inferred columns:** `academic_year_id`.

**Relationships:** belongs to Group, generates many Séances.

**Indexes to create:** `(group_id, jour)`, `(enseignant_id)`.

**Confidence:** Confirmed — full creation-modal form captured, plus a real-data list view ("Herr Zakaria," recurring Mon–Fri).

**Evidence screenshots:** `193655/193656` (creation modal), `193755/193756` (list, alternate "Emploi du temps" entry point).

---

## 8. `presences` (Attendance)

**Purpose:** Per-student, per-session tri-state attendance record.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| seance_id | BIGINT FK → seances | No | |
| student_id | BIGINT FK → students | No | |
| statut | ENUM('Présent','Retard','Absent') | No | three-way toggle in the UI, mutually exclusive |

**Strongly inferred columns:** `recorded_by` (employee who took attendance), `recorded_at`.

**Unknown:** the exact rule generating the "Merci de contacter l'administration" alert badge — plausibly a computed flag (e.g. N absences in a rolling window) rather than a stored column; do not model it as a stored boolean without confirming the threshold logic with GLS.

**Relationships:** belongs to Séance, belongs to Student.

**Indexes to create:** `(seance_id, student_id)` unique composite, `(student_id, statut)` for absence-threshold queries.

**Confidence:** Confirmed core structure; Unknown on the alert-trigger mechanism.

**Evidence screenshots:** `193608/193610`.

---

## 9. `frais` (Fee Types — catalog, not instances)

**Purpose:** The configurable catalog of fee types (monthly recurring + one-off), each with payroll and proration flags. This is the source table; actual per-student fee line items (what's owed, due date, amount, remaining) are a separate concept observed only through the payment-allocation UI (see Unknown note below).

**Confirmed columns (catalog/type level):**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| nom | VARCHAR(100) | No | e.g. "Frais de Juillet", "Frais annuel", "Frais dexam ÖSD B1" |
| statut | VARCHAR(20) | No | "Active" |
| calculable_dans_salaire | BOOLEAN | No | "Calculable dans le salaire" — whether this fee factors into payroll/commission calc |
| appliquer_prorata | BOOLEAN | No | whether partial-period enrollment prorates this fee |
| cle_de_tri | INT | Yes | sort order, populated on monthly types (11–15 seen for Août–Décembre) |

**Strongly inferred columns for the per-student fee-line-item concept** (observed indirectly via the Payment-creation UI and the Recouvrements aging report, which both display Date d'échéance / Montant / Reste à payer per student per fee — this is almost certainly a separate table, tentatively `fee_schedules` or `inscription_frais`):
- `id`, `inscription_id` (FK), `frais_type_id` (FK → this table), `date_echeance` (DATE), `montant` (DECIMAL), `reste_a_payer` (DECIMAL, computed as montant − sum of allocated payments).

**Unknown:** the exact table name/structure the app uses internally for the per-student fee-line concept — flagged as inferred rather than confirmed because no screen displayed a "Frais" CRUD list at the instance level, only at the type/catalog level (this table) and indirectly through Payments/Recouvrements views.

**Relationships:** referenced by Payments (`frais` column), referenced by Recouvrements aging rows, referenced by Expense Types indirectly (both "Frais" catalog and "Types de dépenses" catalog share the protected-vs-custom pattern conceptually but are separate tables).

**Indexes to create:** `(nom)`, `(cle_de_tri)`.

**Confidence:** Confirmed at the catalog/type level (full config screen captured, 18 total types, 10 visible); Strongly inferred at the per-instance/schedule level (structure required by observed UI behavior but never directly screenshotted as its own table).

**Evidence screenshots:** `194459/194500` (Types de frais config), plus indirect evidence throughout `193855` (payment modal), `193819/193820`, `193828/193829` (Recouvrements).

---

## 10. `payments`

**Purpose:** Transactional record of money collected from a student/parent.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| reference | VARCHAR(20) | No | format `P###` (also seen as `P10##`, `P12##`, `P27##` — same series, gaps expected) |
| student_id | BIGINT FK → students | No | "Étudiant/Payeur" |
| parent_id | BIGINT FK → parents | Yes | alternate payer |
| montant | DECIMAL(10,2) | No | |
| reste | DECIMAL(10,2) | No | 0 if fully allocated |
| type | VARCHAR(30) | No | "Réglement" confirmed; "Avance" behavior implied (see below) |
| categorie_paiement_id | BIGINT FK → categories_paiement | Yes | separate from Type |
| methode | VARCHAR(30) | No | confirmed values: TPE (card), Espèces (cash); Chèque/Virement implied |
| fee_schedule_id | BIGINT FK → (per-student fee line, §9) | Yes | NULL when the payment is an unallocated Avance |
| date | DATE | No | |
| agent_id | BIGINT FK → employees | No | cashier/processor, full accountability trail |
| caisse_id | BIGINT FK → caisses | No | which till the money went into |

**Strongly inferred columns:** `inscription_id` (the creation modal requires selecting an Inscription before Frais lines populate) — likely stored directly or derivable via `fee_schedule_id → inscription_id`.

**Unknown:** whether "Avance" is a distinct `type` enum value or simply any Payment row where `fee_schedule_id IS NULL` — the Avances tab's displayed columns (Type="Réglement" even on advance rows) suggest the latter (NULL-Frais pattern) rather than a dedicated type value, but this should be verified against real API responses before finalizing.

**Relationships:** belongs to Student, optionally belongs to Parent, optionally belongs to a fee-schedule line, belongs to Employee (agent), belongs to Caisse.

**Indexes to create:** `(reference)` unique, `(student_id)`, `(date)`, `(caisse_id)`, `(agent_id)`.

**Confidence:** Confirmed for list columns and the creation-modal allocation workflow; Inferred for the Avance mechanism specifically.

**Evidence screenshots:** `193844/193845` (list), `193855` (creation modal), `194144` (Avances tab).

---

## 11. `cheques`

**Purpose:** Morocco-specific post-dated cheque tracking, used both as payment instruments and as payment guarantees.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| source | ENUM('Étudiant', ...) | No | "Source" field — Étudiant confirmed as the only value actually seen selected, dropdown implies more options exist |
| proprietaire_id | BIGINT FK → students (or parents, per source) | No | "Propriétaire" |
| num_cheque | VARCHAR(30) | No | e.g. "682158" |
| montant | DECIMAL(10,2) | No | |
| reste | DECIMAL(10,2) | No | |
| banque_id | BIGINT FK → banques | Yes | real values seen: ATTIJARIWAFA BANK, BANK OF AFRICA |
| date_reception | DATE | No | defaults to today |
| type | ENUM('Garantie (À encaisser)','À déposer') | No | confirmed as the full dropdown list — only two values |
| date_echeance | DATE | Yes | maturity date |
| statut | VARCHAR(30) | No | "En possession" observed; Déposé/Encaissé/Retourné inferred from tab names |
| note | TEXT | Yes | |

**Strongly inferred columns:** `agent_id` (who received it), `academic_year_id`.

**Unknown:** the full Statut enum beyond "En possession" — only inferred from the four sub-tab names (Gestion des chèques / à déposer / à encaisser / en retard), never confirmed as actual stored values via a status-change screen.

**Relationships:** belongs to Student or Parent (polymorphic via `source` + `proprietaire_id`), optionally functions as a Payment instrument (relationship to `payments` not directly confirmed — the two modules were shown as sibling tabs under Paiements, not with a visible FK between them).

**Indexes to create:** `(num_cheque)`, `(statut)`, `(date_echeance)`, `(proprietaire_id)`.

**Confidence:** Confirmed for all fields via two separate creation-modal captures (different branches) and the list view; Unknown flagged for full Statut enum.

**Evidence screenshots:** `193901/193911`, `193913`, `193922/193923`, `194128/194142`.

---

## 12. `caisses` (Till / Cash Register)

**Purpose:** Per-agent or per-branch cash drawer with running balance.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| nom | VARCHAR(100) | Yes | "Ma caisse" seen as a default/personal label |
| branch_id | BIGINT FK → etablissements | Yes | till is branch-scoped, inferred from multi-branch confirmation |
| encaissements | DECIMAL(12,2) | computed | sum of linked Payments |
| depenses | DECIMAL(12,2) | computed | sum of linked Dépenses |
| solde | DECIMAL(12,2) | computed | encaissements − depenses |

**Strongly inferred columns:** `agent_id` (owner/assigned cashier) — the "Aucune caisse n'a été trouvée" empty state and per-agent Payment tracking strongly imply a till is assigned to a specific user, not just a branch.

**Relationships:** has many Payments, has many Dépenses, subject of `caisse_transfers` (see below).

**Indexes to create:** `(agent_id)`, `(branch_id)`.

**Confidence:** Confirmed KPI structure (Encaissements/Dépenses/Solde); Inferred ownership model.

**Evidence screenshots:** `194214/194216`.

### 12a. `caisse_transfers` (Strongly Inferred sub-table)

**Purpose:** Approval-gated movement of cash between two Caisses.

**Strongly inferred columns:** `id`, `source_caisse_id`, `destination_caisse_id`, `montant`, `statut` (pending/validated — the "Validation de transfert" tab name implies an approval gate), `requested_by`, `validated_by`, `date`.

**Relationship to `depenses`:** a transfer generates a paired Dépense row of type "Transfert à une autre caisse" on the source till (confirmed via real data: `DP4, Type=Transfert à une autre caisse, Montant=21600DH, Statut=Validé`) — so this may not need its own table at all; it may simply BE a Dépense row with a `destination_caisse_id` column. Flagging both possibilities rather than committing to one, since the actual FK link between the Validation-de-transfert approval screen and the Dépense record was never directly shown.

**Confidence:** Inferred — table existence and purpose are logical necessities of the confirmed UI, but no dedicated schema was ever displayed.

**Evidence screenshots:** `194214/194216` (Validation de transfert tab name only), `194240/194242` (the resulting Dépense row).

---

## 13. `depenses` (Expenses)

**Purpose:** All money leaving a Caisse.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| reference | VARCHAR(20) | No | format `D#` implied by placeholder "ex:D19"; real data shows `DP4` |
| type_depense_id | BIGINT FK → types_depenses | No | |
| statut | VARCHAR(30) | No | "Validé" observed; "Brouillon" implied as the pre-approval state |
| date | DATE | No | |
| montant_total | DECIMAL(10,2) | No | |
| groupe_id / niveau_id | BIGINT FK → groups (nullable) | Yes | optional cost-center tagging, filter labeled "Classe" |
| mots_cles | VARCHAR(255) | Yes | keyword tagging |
| agent_id | BIGINT FK → employees | No | |
| caisse_id | BIGINT FK → caisses | No | |

**Strongly inferred columns:** `destination_caisse_id` (nullable, populated only for "Transfert à une autre caisse" type rows — see §12a discussion), `validated_by`, `validated_at`.

**Relationships:** belongs to Type de dépense, belongs to Caisse, optionally belongs to Group/Level, belongs to Employee (agent).

**Indexes to create:** `(reference)` unique, `(caisse_id)`, `(type_depense_id)`, `(statut)`, `(date)`.

**Confidence:** Confirmed core fields and the Statut workflow; Inferred transfer-destination column.

**Evidence screenshots:** `194240/194242`.

### 13a. `types_depenses` (Expense Type catalog)

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| nom | VARCHAR(100) | No | |
| statut | VARCHAR(20) | No | "Active" |
| is_system | BOOLEAN | No | **inferred column name** for the confirmed behavior: 6 types have no edit/delete action (Paiement prof, Remboursement au client, Stock, Transfert à une autre caisse, Alimentation de caisse, Salaire), the rest do |

**Confirmed full type list (10 of what is likely a larger set — table showed no pagination control, unlike Frais):** Paiement prof (system), Remboursement au client (system), Stock (system), Transfert à une autre caisse (system), Alimentation de caisse (system), Salaire (system), Produits consommables (custom), Femme de menage (custom), Externalisation ou sous-traitance (custom), Logistiques (custom).

**Confidence:** Confirmed — full list with view/edit/delete action visibility directly observed.

**Evidence screenshots:** `194258/194300`.

---

## 14. `remboursements` (Refunds)

**Purpose:** Simplified, dedicated refund record — separate audit trail from generic Dépenses.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | INT PK | No | shown as `#1, #2, #25...` — small sequential ID, possibly its own table rather than sharing the Dépense sequence |
| beneficiaire_id | BIGINT FK → students (or parents) | No | "Bénéficiaire" |
| date | DATE | No | |
| montant_total | DECIMAL(10,2) | No | |
| agent_id | BIGINT FK → employees | No | |

**Unknown:** whether this is truly a separate table or a filtered view of `depenses` where `type_depense = 'Remboursement au client'` — the distinct simple ID sequence (`#1`–`#41` seen, low numbers relative to Dépenses' `D`-prefix) suggests a separate table, but this is Inferred, not confirmed.

**Relationships:** belongs to Student or Parent (beneficiary), belongs to Employee (agent).

**Indexes to create:** `(beneficiaire_id)`, `(date)`.

**Confidence:** Confirmed columns; Unknown on table independence vs. Dépense-subtype.

**Evidence screenshots:** `194251/194252`.

---

## 15. `employees`

**Purpose:** Master staff record — includes teachers, administrators, and directors under one table with a role/category field.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| reference | VARCHAR(20) | No | format `PR###` |
| prenom | VARCHAR(100) | No | |
| nom | VARCHAR(100) | No | |
| categorie_id | BIGINT FK → categories_employes | No | values seen: Assistante administrative, Directeur, Enseignant |
| statut | VARCHAR(20) | No | "Actif" |
| telephone | VARCHAR(20) | Yes | |
| whatsapp | VARCHAR(20) | Yes | |
| telegram | VARCHAR(20) | Yes | |
| type | ENUM('Partagé','Non partagé') | No | inferred meaning: shared-across-branches vs. single-branch |
| note | TEXT | Yes | |
| sexe | ENUM('M','F') | No | inferred from the gender-icon column shown, not a labeled field, but consistently rendered like the Student list |

**Strongly inferred columns:** `branch_id` (nullable if Type='Partagé', required if 'Non partagé').

**Relationships:** has many Groups (as Enseignant), has many Séances/Horaires (as Enseignant), has many Payments/Dépenses (as Agent), has many Congés (leave/absence records — sub-tab confirmed, contents unopened).

**Indexes to create:** `(reference)` unique, `(categorie_id)`, `(statut)`.

**Confidence:** Confirmed for all listed fields.

**Evidence screenshots:** `194315/194316`.

### 15a. `conges_absences` (Employee Leave — Unknown detail)

**Purpose:** Confirmed to exist as a sub-tab of Employees ("Congés et absences"), contents never opened.

**Strongly inferred columns:** `id`, `employee_id`, `date_debut`, `date_fin`, `type` (leave/sick/etc.), `statut` (approved/pending).

**Confidence:** Unknown beyond existence and name.

**Evidence screenshots:** `194315/194316` (tab label only).

---

## 16. `external_users` (Portal Accounts)

**Purpose:** Login/authentication layer for non-staff users (students, likely parents), kept separate from the `students` business record.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| username | VARCHAR(20) | No | format `e#####`, mirrors student numbering |
| student_id | BIGINT FK → students | Yes | inferred link, not a labeled column, but the username numbering pattern strongly implies a 1:1 link |
| nom_complet | VARCHAR(200) | No | displayed as one field in this list, though likely composed from the linked Student's own name fields |
| telephone | VARCHAR(20) | Yes | |
| whatsapp | VARCHAR(20) | Yes | can differ from telephone (one sample row showed different numbers for each) |
| categorie | VARCHAR(30) | No | "Élève" confirmed; "Parent" inferred as a second possible value |
| statut | VARCHAR(20) | No | "Actif" |

**Unknown:** password/auth-token storage (obviously exists but never visible in a UI screenshot, correctly); what a logged-in student/parent actually sees in the portal (never opened — only the admin-side account list was captured).

**Relationships:** belongs to Student (inferred 1:1 or 1:many if a Parent account also uses this table).

**Indexes to create:** `(username)` unique, `(student_id)`, `(statut)`.

**Confidence:** Confirmed for list-view fields; Inferred for the Student FK link; Unknown for auth internals and portal-side functionality.

**Evidence screenshots:** `194350/194351`.

---

## 17. `salles` (Rooms)

**Purpose:** Physical and virtual room/venue catalog, branch-scoped.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| nom | VARCHAR(100) | No | e.g. "SALLE 01 ALAOUI", "Wuppertal" |
| effective | INT | Yes | capacity, populated on the virtual/online room rows in the sample (36, 30, 46, 22), empty on the three physical rows shown |
| statut | VARCHAR(20) | No | "Active" |

**Strongly inferred columns:** `branch_id`, `nom_ar` (Arabic room label, seen as a default value "1 القاعة" on a séance-creation dropdown, implying rooms carry an Arabic name field consistent with the rest of the schema's FR/AR pattern).

**Relationships:** referenced by Séances and Horaires.

**Indexes to create:** `(branch_id)`, `(statut)`.

**Confidence:** Confirmed core columns (7 total rooms shown for one branch); Inferred `nom_ar` and `branch_id`.

**Evidence screenshots:** `194413/194414`.

---

## 18. `matieres` (Subjects)

**Purpose:** Lookup table for subject/language taught per session — referenced constantly (Séance creation requires it, attendance screen displays "allemand") but its own config screen was never opened.

**Strongly inferred columns:** `id`, `nom` (FR), `nom_ar`, `statut`.

**Confidence:** Table existence Confirmed (FK usage seen repeatedly, config tab name "Matières" seen under Settings > Pédagogiques); own column list Unknown/Inferred by pattern-matching against every other lookup table in the system (all of which share the Nom/Nom-arabe/Statut/Note shape).

**Evidence screenshots:** `193533` (as a required FK on Séance form), `193608/193610` (displayed value "allemand"), `194451/194452` (sibling tab name only).

---

## 19. `niveaux` (Classification — CEFR Level Catalog)

**Purpose:** Master lookup for student/group proficiency levels.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| nom | VARCHAR(20) | No | e.g. "B2.2", "A1.1" |
| statut | VARCHAR(20) | No | "Active" |

**Confirmed data (11 total, page 1 of 2 captured):** B1.2, B2.3, B2.2, B2.1, B1.3, B1.1, A2.3, A2.2, A2.1, A1.2 (+1 more, likely A1.1, on the uncaptured page 2).

**Relationships:** referenced by `students.niveau_scolaire_id`, `groups.formation_id`.

**Indexes to create:** `(nom)` unique.

**Confidence:** Confirmed.

**Evidence screenshots:** `194451/194452`.

---

## 20. `departements`, `categories_formation` (Training Hierarchy — Unknown detail)

**Purpose:** The two upper levels of the confirmed four-level hierarchy (Département → Catégorie de formation → Niveau → Group).

**Confirmed:** their existence as filterable dropdowns on the Groups list, and real values ("GLS ONLINE" département; "GLS ONLINE - COURS D'ALLEMAND - ONLINE" catégorie).

**Unknown:** own column lists — no dedicated config screen for either was opened (the Settings > Pédagogiques > "Structures pédagogiques" tab is the likely home for this config but was never clicked into).

**Confidence:** Table existence Confirmed via consistent dropdown usage; internal schema Unknown.

**Evidence screenshots:** `193411/193413`.

---

## 21. `annees_scolaires` (Academic Years)

**Purpose:** Defines the school-year periods that scope almost every other module via the top-nav switcher.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| annee_scolaire | VARCHAR(20) | No | label, e.g. "2025/2026" |
| annee_debut | INT | No | e.g. 2025 |
| date_debut | DATE | No | e.g. 01/09/2025 |
| date_fin | DATE | No | e.g. 31/08/2026 |
| par_defaut | BOOLEAN | No | "Oui" |
| ferme | BOOLEAN | No | "Fermé" — archival/lock flag |
| inscription_ouverte | BOOLEAN | No | whether new enrollments are accepted |

**Relationships:** referenced (inferred) by nearly every scoped entity — Inscriptions, Groups, Séances, Frais schedules.

**Indexes to create:** `(par_defaut)`, `(annee_debut)`.

**Confidence:** Confirmed — full config screen captured with real data.

**Evidence screenshots:** `194633/194634`.

### 21a. `periodes`, `jours_feries`, `vacances` (sibling config tables — Unknown detail)

**Confirmed:** existence as sibling tabs to Gestion des années scolaires (Périodes / Jours fériés / Vacances).

**Strongly inferred:** `jours_feries` feeds the Séance cancellation-reason list ("jour férié" is one of the six confirmed cancellation reasons); `vacances` plausibly blocks Séance auto-generation during school breaks.

**Unknown:** own column lists — none of the three tabs were opened.

**Confidence:** Existence Confirmed; schema Unknown.

**Evidence screenshots:** `194633/194634` (tab names only).

---

## 22. `etablissements` (Branches / Centers)

**Purpose:** The branch/tenant record anchoring the confirmed multi-branch architecture.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| nom_centre | VARCHAR(150) | No | real value: "GLS MARRAKECH" |
| nom_centre_ar | VARCHAR(150) | Yes | real value: "GLS" (short/untranslated in sample) |
| pays | VARCHAR(100) | No | "Morocco" |
| ville | VARCHAR(100) | No | "Marrakech" |
| address | VARCHAR(255) | Yes | real value captured in full |
| adresse_ar | VARCHAR(255) | Yes | empty in sample |
| code_postal | VARCHAR(10) | Yes | "40000" |
| telephone | VARCHAR(20) | Yes | real value captured |
| whatsapp | VARCHAR(20) | Yes | empty in sample |
| email | VARCHAR(255) | Yes | real value: "info@glssprachenzentrum.ma" |
| siege_social | BOOLEAN | No | "Oui" — head-office flag |

**Relationships:** has many Salles, has many Employees (Non-partagé subset), has many Groups/Séances (branch-scoped), referenced by the top-nav branch switcher.

**Indexes to create:** `(siege_social)`.

**Confidence:** Confirmed — full detail record captured with real GLS data.

**Evidence screenshots:** `194427/194428`.

---

## 23. `frais_categories_paiement`, `modes_paiement` (Payment lookup tables — Unknown detail)

**Purpose:** Confirmed existence as Settings > Financiers sibling tabs ("Modes de paiement") and as a Payment-form filter ("Catégorie de paiement"), backing the Méthode values seen in use (TPE, Espèces) elsewhere.

**Unknown:** own column structure — assumed to follow the universal Nom/Statut lookup-table pattern seen everywhere else in the system, but never directly opened.

**Confidence:** Existence Confirmed; schema Inferred-by-pattern only.

**Evidence screenshots:** `193844/193845` (as Payment filter fields), `194459/194500` (sibling tab name only).

---

## 24. `seance_cancellation_reasons`

**Purpose:** Configurable lookup for why a Séance was cancelled.

**Confirmed columns:**
| Column | Type (est.) | Nullable | Notes |
|---|---|---|---|
| id | BIGINT PK | No | |
| nom | VARCHAR(100) | No | |
| nom_ar | VARCHAR(100) | Yes | |
| statut | ENUM('Active','Inactive') | No | |
| note | TEXT | Yes | |

**Confirmed full data set (6 real GLS entries):** Fin de formation, Match maroc, jour férié, Congée, empêchement personnel, Malade.

**Confidence:** Confirmed — full config list and creation modal both captured.

**Evidence screenshots:** `193625/193626`, `193636`.

---

## 25. Relationship Diagram (textual, Confirmed + Strongly Inferred links only)

```
prospects ──(convert)──> inscriptions
students ──1:M──> inscriptions ──M:1──> groups
groups ──M:1──> niveaux (Classification)
groups ──M:1──> employees (enseignant)
groups ──M:1──> categories_formation ──M:1──> departements
groups ──1:M──> horaires ──1:M──> seances
seances ──M:1──> matieres, salles, employees
seances ──1:M──> presences ──M:1──> students
inscriptions ──1:M──> (fee schedule lines) ──M:1──> frais (catalog)
(fee schedule lines) ──1:M──> payments ──M:1──> employees (agent), caisses
students / parents ──1:M──> cheques
students / parents ──1:M──> remboursements
caisses ──1:M──> payments, depenses
depenses ──M:1──> types_depenses
students ──1:1──> external_users
students ──1:M──> parents (cardinality unconfirmed)
employees ──M:1──> categories_employes; employees ──M:1──> etablissements (if Non partagé)
etablissements ──1:M──> salles
annees_scolaires ──1:M──> (scopes nearly every table above, via inferred academic_year_id FKs)
```

---

*End of database-schema.md. All tables and columns above are drawn from or directly inferred against real WimSchool screenshots; anything not evidenced is explicitly marked Unknown rather than invented. See `startup-mvp.md` for which of these tables to actually build first.*
