# WimSchool CRM — PlantUML Entity Diagrams

Companion diagrams for `database-schema.md`. Split into 4 domain diagrams (one giant diagram with all 26 tables is unreadable) — paste any block into [plantuml.com/plantuml](https://www.plantuml.com/plantuml) or a PlantUML IDE extension to render.

Legend used in every diagram:
- `PK` = primary key, `FK` = foreign key
- Fields marked `?` are **Strongly Inferred** (not directly labeled in a screenshot, required by the workflow)
- Entities with a dashed border are **Unknown-detail** tables (existence confirmed, own columns not confirmed)
- `--` = confirmed relationship, `..` = inferred relationship

---

## Diagram 1 — Core: Leads, Enrollment, Students, Groups

```plantuml
@startuml wimschool_core

skinparam linetype ortho
hide circle

entity "prospects" as prospects {
  * id : BIGINT <<PK>>
  --
  * prenom : VARCHAR(100)
  * nom : VARCHAR(100)
  * telephone : VARCHAR(20)
  * etat : VARCHAR(50)
  source : VARCHAR(100)
  commercial_id : BIGINT <<FK>>
  info_suppl_1 : TEXT
  info_suppl_2 : VARCHAR(255)
  note : VARCHAR(255)
  * date_ajout : DATETIME
  agence_id : BIGINT <<FK>> ?
}

entity "students" as students {
  * id : BIGINT <<PK>>
  --
  * reference : VARCHAR(20) "E###"
  * nom_fr : VARCHAR(100)
  * prenom_fr : VARCHAR(100)
  nom_ar : VARCHAR(100)
  prenom_ar : VARCHAR(100)
  categorie_age_id : BIGINT <<FK>>
  date_naissance : DATE
  * sexe : ENUM('M','F')
  niveau_scolaire_id : BIGINT <<FK>>
  profession_id : BIGINT <<FK>>
  pays : VARCHAR(100)
  lieu_naissance : VARCHAR(100)
  email : VARCHAR(255)
  telephone : VARCHAR(20)
  whatsapp : VARCHAR(20)
  telegram : VARCHAR(20)
  adresse : VARCHAR(255)
}

entity "parents" as parents <<unknown-detail>> {
  * id : BIGINT <<PK>> ?
  --
  student_id : BIGINT <<FK>> ?
  nom : VARCHAR(100) ?
  prenom : VARCHAR(100) ?
  telephone : VARCHAR(20) ?
  whatsapp : VARCHAR(20) ?
}

entity "inscriptions" as inscriptions {
  * id : BIGINT <<PK>>
  --
  * reference : VARCHAR(20) "I###"
  * student_id : BIGINT <<FK>>
  * group_id : BIGINT <<FK>>
  * statut : VARCHAR(30)
  * date_inscription : DATE
  date_debut : DATE
  date_fin : DATE
  converted_from_prospect_id : BIGINT <<FK>> ?
  offre_id : BIGINT <<FK>> ?
  academic_year_id : BIGINT <<FK>> ?
}

entity "groups" as groups {
  * id : BIGINT <<PK>>
  --
  * nom : VARCHAR(100)
  * departement_id : BIGINT <<FK>>
  * categorie_formation_id : BIGINT <<FK>>
  * niveau_scolaire_id : BIGINT <<FK>>
  * enseignant_id : BIGINT <<FK>>
  branch_id : BIGINT <<FK>> ?
  academic_year_id : BIGINT <<FK>> ?
}

entity "niveaux" as niveaux {
  * id : BIGINT <<PK>>
  --
  * nom : VARCHAR(20) "B2.2 etc"
  * statut : VARCHAR(20)
}

entity "departements" as departements <<unknown-detail>> {
  * id : BIGINT <<PK>> ?
  --
  nom : VARCHAR(100) ?
}

entity "categories_formation" as cat_formation <<unknown-detail>> {
  * id : BIGINT <<PK>> ?
  --
  departement_id : BIGINT <<FK>> ?
  nom : VARCHAR(200) ?
}

entity "employees" as employees {
  * id : BIGINT <<PK>>
  --
  * reference : VARCHAR(20) "PR###"
  * prenom : VARCHAR(100)
  * nom : VARCHAR(100)
  * categorie_id : BIGINT <<FK>>
  * statut : VARCHAR(20)
  * type : ENUM('Partagé','Non partagé')
  branch_id : BIGINT <<FK>> ?
}

prospects ||..o{ inscriptions : "converted_from_prospect_id\n(inferred)"
students ||--o{ inscriptions : student_id
students ||..o{ parents : "student_id\n(cardinality unconfirmed)"
groups ||--o{ inscriptions : group_id
groups }o--|| niveaux : niveau_scolaire_id
groups }o--|| cat_formation : categorie_formation_id
cat_formation }o--|| departements : departement_id
groups }o--|| employees : "enseignant_id"
prospects }o--o| employees : "commercial_id"

@enduml
```

---

## Diagram 2 — Scheduling & Attendance

```plantuml
@startuml wimschool_scheduling

skinparam linetype ortho
hide circle

entity "groups" as groups {
  * id : BIGINT <<PK>>
  --
  nom : VARCHAR(100)
}

entity "horaires" as horaires {
  * id : BIGINT <<PK>>
  --
  * group_id : BIGINT <<FK>>
  * matiere_id : BIGINT <<FK>>
  * jour : VARCHAR(10)
  * heure_debut : TIME
  * heure_fin : TIME
  enseignant_id : BIGINT <<FK>>
  salle_id : BIGINT <<FK>>
  * type_creation : ENUM('Automatique','Manuelle')
  lien_seance : VARCHAR(500)
  lien_classe : VARCHAR(500)
  statut : VARCHAR(30)
  academic_year_id : BIGINT <<FK>> ?
}

entity "seances" as seances {
  * id : BIGINT <<PK>>
  --
  * group_id : BIGINT <<FK>>
  * matiere_id : BIGINT <<FK>>
  * enseignant_id : BIGINT <<FK>>
  salle_id : BIGINT <<FK>>
  * date : DATE
  * heure_debut : TIME
  * heure_fin : TIME
  nbr_heure : DECIMAL(3,1)
  * statut : VARCHAR(30) "Brouillon..."
  note : TEXT
  lien_seance : VARCHAR(500)
  cancellation_reason_id : BIGINT <<FK>>
  horaire_id : BIGINT <<FK>>
  academic_year_id : BIGINT <<FK>> ?
  created_by : BIGINT <<FK>> ?
}

entity "presences" as presences {
  * id : BIGINT <<PK>>
  --
  * seance_id : BIGINT <<FK>>
  * student_id : BIGINT <<FK>>
  * statut : ENUM('Présent','Retard','Absent')
  recorded_by : BIGINT <<FK>> ?
  recorded_at : DATETIME ?
}

entity "students" as students {
  * id : BIGINT <<PK>>
}

entity "employees" as employees {
  * id : BIGINT <<PK>>
}

entity "matieres" as matieres <<unknown-detail>> {
  * id : BIGINT <<PK>> ?
  --
  nom : VARCHAR(100) ?
  nom_ar : VARCHAR(100) ?
  statut : VARCHAR(20) ?
}

entity "salles" as salles {
  * id : BIGINT <<PK>>
  --
  * nom : VARCHAR(100)
  effective : INT
  * statut : VARCHAR(20)
  branch_id : BIGINT <<FK>> ?
  nom_ar : VARCHAR(100) ?
}

entity "seance_cancellation_reasons" as cancel_reasons {
  * id : BIGINT <<PK>>
  --
  * nom : VARCHAR(100)
  nom_ar : VARCHAR(100)
  * statut : ENUM('Active','Inactive')
  note : TEXT
}

groups ||--o{ horaires : group_id
horaires ||--o{ seances : "horaire_id\n(if Automatique)"
groups ||--o{ seances : group_id
seances }o--|| matieres : matiere_id
seances }o--|| salles : salle_id
seances }o--|| employees : enseignant_id
seances }o--o| cancel_reasons : cancellation_reason_id
seances ||--o{ presences : seance_id
presences }o--|| students : student_id
horaires }o--|| matieres : matiere_id
horaires }o--o| salles : salle_id
horaires }o--o| employees : enseignant_id

@enduml
```

---

## Diagram 3 — Finance: Fees, Payments, Cheques, Caisse, Expenses

```plantuml
@startuml wimschool_finance

skinparam linetype ortho
hide circle

entity "inscriptions" as inscriptions {
  * id : BIGINT <<PK>>
}

entity "frais" as frais {
  * id : BIGINT <<PK>>
  --
  * nom : VARCHAR(100) "Frais de Juillet etc"
  * statut : VARCHAR(20)
  * calculable_dans_salaire : BOOLEAN
  * appliquer_prorata : BOOLEAN
  cle_de_tri : INT
}

entity "fee_schedules" as fee_schedules <<inferred-table>> {
  * id : BIGINT <<PK>> ?
  --
  * inscription_id : BIGINT <<FK>> ?
  * frais_type_id : BIGINT <<FK>> ?
  * date_echeance : DATE ?
  * montant : DECIMAL(10,2) ?
  reste_a_payer : DECIMAL(10,2) ?
}

entity "payments" as payments {
  * id : BIGINT <<PK>>
  --
  * reference : VARCHAR(20) "P###"
  * student_id : BIGINT <<FK>>
  parent_id : BIGINT <<FK>>
  * montant : DECIMAL(10,2)
  * reste : DECIMAL(10,2)
  * type : VARCHAR(30) "Réglement"
  categorie_paiement_id : BIGINT <<FK>>
  * methode : VARCHAR(30) "TPE / Espèces..."
  fee_schedule_id : BIGINT <<FK>> "NULL = Avance"
  * date : DATE
  * agent_id : BIGINT <<FK>>
  * caisse_id : BIGINT <<FK>>
  inscription_id : BIGINT <<FK>> ?
}

entity "cheques" as cheques {
  * id : BIGINT <<PK>>
  --
  * source : ENUM('Étudiant', ...)
  * proprietaire_id : BIGINT <<FK>>
  * num_cheque : VARCHAR(30)
  * montant : DECIMAL(10,2)
  * reste : DECIMAL(10,2)
  banque_id : BIGINT <<FK>>
  * date_reception : DATE
  * type : ENUM('Garantie (À encaisser)','À déposer')
  date_echeance : DATE
  * statut : VARCHAR(30) "En possession..."
  note : TEXT
  agent_id : BIGINT <<FK>> ?
}

entity "caisses" as caisses {
  * id : BIGINT <<PK>>
  --
  nom : VARCHAR(100)
  branch_id : BIGINT <<FK>>
  encaissements : DECIMAL(12,2) <<computed>>
  depenses : DECIMAL(12,2) <<computed>>
  solde : DECIMAL(12,2) <<computed>>
  agent_id : BIGINT <<FK>> ?
}

entity "depenses" as depenses {
  * id : BIGINT <<PK>>
  --
  * reference : VARCHAR(20) "D# / DP#"
  * type_depense_id : BIGINT <<FK>>
  * statut : VARCHAR(30) "Brouillon -> Validé"
  * date : DATE
  * montant_total : DECIMAL(10,2)
  groupe_id : BIGINT <<FK>>
  mots_cles : VARCHAR(255)
  * agent_id : BIGINT <<FK>>
  * caisse_id : BIGINT <<FK>>
  destination_caisse_id : BIGINT <<FK>> ?
  validated_by : BIGINT <<FK>> ?
}

entity "types_depenses" as types_depenses {
  * id : BIGINT <<PK>>
  --
  * nom : VARCHAR(100)
  * statut : VARCHAR(20)
  * is_system : BOOLEAN ?
}

entity "remboursements" as remboursements {
  * id : INT <<PK>>
  --
  * beneficiaire_id : BIGINT <<FK>>
  * date : DATE
  * montant_total : DECIMAL(10,2)
  * agent_id : BIGINT <<FK>>
}

entity "students" as students {
  * id : BIGINT <<PK>>
}

entity "employees" as employees {
  * id : BIGINT <<PK>>
}

inscriptions ||..o{ fee_schedules : "inscription_id (inferred table)"
fee_schedules }o--|| frais : frais_type_id
fee_schedules ||..o{ payments : "fee_schedule_id\n(NULL = Avance)"
payments }o--|| students : student_id
payments }o--o| parents_ref : parent_id
payments }o--|| employees : agent_id
payments }o--|| caisses : caisse_id
cheques }o--|| students : proprietaire_id
cheques ..> payments : "possible payment instrument\n(no confirmed FK)"
caisses ||--o{ payments : caisse_id
caisses ||--o{ depenses : caisse_id
depenses }o--|| types_depenses : type_depense_id
depenses }o..o| caisses : destination_caisse_id
remboursements }o--|| students : beneficiaire_id
remboursements }o--|| employees : agent_id

entity "parents_ref" as parents_ref <<unknown-detail>> {
  * id : BIGINT <<PK>> ?
}

@enduml
```

---

## Diagram 4 — HR, Settings & Lookup Tables

```plantuml
@startuml wimschool_settings

skinparam linetype ortho
hide circle

entity "etablissements" as etablissements {
  * id : BIGINT <<PK>>
  --
  * nom_centre : VARCHAR(150)
  nom_centre_ar : VARCHAR(150)
  * pays : VARCHAR(100)
  * ville : VARCHAR(100)
  address : VARCHAR(255)
  adresse_ar : VARCHAR(255)
  code_postal : VARCHAR(10)
  telephone : VARCHAR(20)
  whatsapp : VARCHAR(20)
  email : VARCHAR(255)
  * siege_social : BOOLEAN
}

entity "salles" as salles {
  * id : BIGINT <<PK>>
  --
  * nom : VARCHAR(100)
  effective : INT
  * statut : VARCHAR(20)
  branch_id : BIGINT <<FK>>
}

entity "employees" as employees {
  * id : BIGINT <<PK>>
  --
  * reference : VARCHAR(20) "PR###"
  * prenom : VARCHAR(100)
  * nom : VARCHAR(100)
  * categorie_id : BIGINT <<FK>>
  * statut : VARCHAR(20)
  telephone : VARCHAR(20)
  whatsapp : VARCHAR(20)
  telegram : VARCHAR(20)
  * type : ENUM('Partagé','Non partagé')
  note : TEXT
  branch_id : BIGINT <<FK>> ?
}

entity "categories_employes" as categories_employes <<unknown-detail>> {
  * id : BIGINT <<PK>> ?
  --
  nom : VARCHAR(100) "Directeur, Enseignant..." ?
}

entity "conges_absences" as conges <<unknown-detail>> {
  * id : BIGINT <<PK>> ?
  --
  employee_id : BIGINT <<FK>> ?
  date_debut : DATE ?
  date_fin : DATE ?
  type : VARCHAR(50) ?
  statut : VARCHAR(30) ?
}

entity "students" as students {
  * id : BIGINT <<PK>>
  --
  * reference : VARCHAR(20) "E###"
}

entity "external_users" as external_users {
  * id : BIGINT <<PK>>
  --
  * username : VARCHAR(20) "e#####"
  student_id : BIGINT <<FK>>
  * nom_complet : VARCHAR(200)
  telephone : VARCHAR(20)
  whatsapp : VARCHAR(20)
  * categorie : VARCHAR(30) "Élève / Parent?"
  * statut : VARCHAR(20)
}

entity "niveaux" as niveaux {
  * id : BIGINT <<PK>>
  --
  * nom : VARCHAR(20)
  * statut : VARCHAR(20)
}

entity "annees_scolaires" as annees {
  * id : BIGINT <<PK>>
  --
  * annee_scolaire : VARCHAR(20) "2025/2026"
  * annee_debut : INT
  * date_debut : DATE
  * date_fin : DATE
  * par_defaut : BOOLEAN
  * ferme : BOOLEAN
  * inscription_ouverte : BOOLEAN
}

entity "periodes" as periodes <<unknown-detail>> {
  * id : BIGINT <<PK>> ?
  --
  annee_scolaire_id : BIGINT <<FK>> ?
}

entity "jours_feries" as jours_feries <<unknown-detail>> {
  * id : BIGINT <<PK>> ?
  --
  date : DATE ?
  nom : VARCHAR(100) ?
}

entity "vacances" as vacances <<unknown-detail>> {
  * id : BIGINT <<PK>> ?
  --
  date_debut : DATE ?
  date_fin : DATE ?
}

entity "modes_paiement" as modes_paiement <<unknown-detail>> {
  * id : BIGINT <<PK>> ?
  --
  nom : VARCHAR(50) "Espèces, TPE..." ?
}

etablissements ||--o{ salles : branch_id
etablissements ||..o{ employees : "branch_id (if Non partagé)"
employees }o--|| categories_employes : categorie_id
employees ||..o{ conges : employee_id
students ||--o| external_users : "student_id (1:1 inferred)"
annees ||..o{ periodes : annee_scolaire_id
annees ..> jours_feries : "feeds cancellation reasons"
annees ..> vacances

@enduml
```

---

## Rendering notes

- **PlantUML online:** paste any single `@startuml ... @enduml` block into [plantuml.com/plantuml](https://www.plantuml.com/plantuml).
- **VS Code:** the "PlantUML" extension (jebbs.plantuml) renders these inline with `Alt+D` once installed — it needs either Java + Graphviz locally, or the extension's remote-render setting pointed at the public PlantUML server.
- **`<<unknown-detail>>` / `<<inferred-table>>` stereotypes** are cosmetic tags only (PlantUML doesn't style them specially by default) — they exist so the diagram visually flags, at a glance, which boxes are Confirmed vs. Unknown, matching the confidence key in `database-schema.md`. Add a `skinparam entity<<unknown-detail>> BackgroundColor LightGray` line under any `@startuml` if you want that visually distinct too.
- Diagram 3 has one deliberate simplification: `parents_ref` is a stub box standing in for the full `parents` entity (already detailed in Diagram 1) so the finance diagram doesn't have to duplicate its column list — link them mentally as the same table.

*Generated from `database-schema.md`. If that file is updated, regenerate these blocks to match — they are not auto-synced.*
