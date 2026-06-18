# GLS Sprachenzentrum — Platform Management System

A full-stack **Laravel 11** web application powering GLS Sprachenzentrum, a multi-site German language learning center in Morocco. The platform combines a public-facing website with a complete back-office management suite covering everything from student enrollment and payroll to CRM integration and WhatsApp campaigns.

---

## Table of Contents

- [Tech Stack](#tech-stack)
- [Architecture Overview](#architecture-overview)
- [Public Website (Frontoffice)](#public-website-frontoffice)
- [Admin Dashboard (Backoffice)](#admin-dashboard-backoffice)
  - [Core Resources](#core-resources)
  - [Student Enrollment & Leads](#student-enrollment--leads)
  - [Finance & Revenue](#finance--revenue)
  - [Payroll System](#payroll-system)
  - [CRM Integration (Wimschool API)](#crm-integration-wimschool-api)
  - [HR & Scheduling](#hr--scheduling)
  - [Weekly Reports](#weekly-reports)
  - [Level Followup](#level-followup)
  - [Certificates & Attestations](#certificates--attestations)
  - [Quizzes](#quizzes)
  - [WhatsApp Campaigns](#whatsapp-campaigns)
  - [Translation Orders](#translation-orders)
  - [Scheduled Reports](#scheduled-reports)
  - [Users, Roles & Permissions](#users-roles--permissions)
- [API Endpoints](#api-endpoints)
- [Artisan Commands](#artisan-commands)
- [Email Notifications](#email-notifications)
- [Installation & Setup](#installation--setup)
- [Environment Variables](#environment-variables)

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 11, PHP 8.2+, MySQL |
| ORM | Eloquent |
| Frontend | Blade templates, Tailwind CSS v4, Bootstrap 5, SCSS |
| Build Tool | Vite 5 |
| Auth | Laravel Sanctum + built-in auth + email verification |
| Permissions | Spatie Laravel Permission (roles + granular permissions) |
| File Uploads | Spatie Media Library |
| Response Cache | Spatie Response Cache |
| PDF Generation | Barryvdh DOMPdf |
| Excel Import/Export | Maatwebsite Excel |
| QR Codes | SimpleSoftwareIO Simple QRCode |
| SEO | Artesaos SEOTools + Spatie Schema.org |
| Multi-language | mcamara Laravel Localization (FR / EN) |
| HTTP Client | Guzzle (CRM API integration) |
| Google Sheets | Google API Client |
| Testing | PHPUnit |

---

## Architecture Overview

```
app/
├── Http/Controllers/
│   ├── Frontoffice/        # Public website controllers
│   ├── Backoffice/         # Admin panel controllers
│   │   └── Crm/            # CRM integration controllers
│   ├── Auth/               # Authentication controllers
│   └── Api/                # JSON API controllers
├── Models/                 # Eloquent models
├── Mail/                   # Mailable classes
└── Console/Commands/       # Artisan scheduled commands

routes/
├── web.php                 # Entry point (imports sub-route files)
├── frontoffice.php         # Public pages (cached, localized)
├── backoffice.php          # Admin panel (auth-protected)
└── api.php                 # Sanctum-authenticated API

resources/
├── views/
│   ├── frontoffice/        # Public page templates
│   ├── backoffice/         # Admin templates
│   ├── emails/             # Transactional email templates
│   └── layouts/            # Shared layout templates
├── scss/                   # SCSS stylesheets
└── js/                     # Vanilla JS + Axios
```

**Key patterns:**
- **Multi-site**: Users, teachers, and resources are scoped to one or multiple centers (sites) via pivot tables
- **Multi-language**: FR/EN route prefixes via `mcamara/laravel-localization`; models use `_fr`, `_en`, `_ar`, `_de` column suffixes for multilingual content
- **CRM mirroring**: External Wimschool API data is synced to local mirror tables (nightly + on-demand), so dashboards never depend on live API latency
- **Excel pipeline**: Payroll and finance data flows through Excel imports → versioned snapshots → normalized records → reports

---

## Public Website (Frontoffice)

All public routes are cached (Spatie Response Cache) and support FR/EN localization.

### Pages & Features

| Page | Route | Description |
|---|---|---|
| Homepage | `/` | Hero, course overview, why-choose-us, stats bar, FAQ, CTA |
| About | `/about` | Organization story and team |
| Contact | `/contact` | Contact form with admin email notification |
| FAQ | `/faq` | Frequently asked questions |
| Pricing | `/pricing` | Course pricing |
| GLS Inscription | `/gls-inscription` | Student enrollment form with confirmation email |
| Free Consultation | (POST) | Request a free consultation session |
| Online Registration | `/online-registration` | Register for online courses |
| Online Courses | `/online-courses` | Online course information |
| Intensive Courses | `/intensive-courses` | Intensive course information |
| Discover Your Level | `/discover-your-level` | Interactive placement quiz landing |
| Level Quiz | `/discover-your-level/quiz` | AJAX-powered level quiz |
| Level Pages | `/niveaux/a1` → `b2` | Per-level course details (A1, A2, B1, B2) |
| Blog | `/blog`, `/blog/{slug}` | Blog listing and article detail |
| Student Stories | `/student-stories` | Testimonials |
| Centers | `/sites/{slug}` | Individual center detail pages |
| Exams | `/exams/gls`, `/exams/osd`, `/exams/goethe` | Exam information pages |
| Studienkollegs | `/studienkollegs`, `/studienkollegs/{slug}` | Preparatory college pages |
| Certificate Check | `/certificate-check` | Verify certificate authenticity by token |
| Attestation Request | `/demande-attestation` | Request a participation certificate (frontoffice form) |
| Feedback (QR) | `/feedback` | Student feedback form (reached via QR code) |
| Translation Tracking | `/traductions/suivi` | Track translation order status by CIN |
| Newsletter Subscribe | (POST) | Newsletter opt-in |
| Landing Pages | `/lp/meta`, `/lp/google` | Ad landing pages (bypasses response cache) |
| Legal | `/terms`, `/privacy` | Terms and Privacy Policy |

### Group Application Flow

Students can apply to a group directly from the public site:
- `POST /groups/apply` — apply via query parameters (group pre-selected from URL)
- `POST /groups/{group}/apply` — legacy per-group apply route

---

## Admin Dashboard (Backoffice)

All backoffice routes are protected by the `auth` middleware. Permissions are enforced per resource using Spatie Laravel Permission.

### Core Resources

#### Sites (Teaching Centers)

Full CRUD for GLS branch locations. Each site stores:
- Name, slug, city, address, phone, email
- A promo video URL
- `crm_store_id` and `crm_token` for Wimschool CRM integration
- Active/inactive status

#### Teachers

Full CRUD for instructors, including:
- Assignment to one or multiple sites (multi-site pivot)
- `crm_teacher_id` for CRM sync
- `payment_per_student` for payroll calculation
- Bio, speciality, profile photo (Spatie Media Library)

#### Groups (Classes)

Full CRUD for language groups:
- Level (A1–B2), time range, period label, status
- Start and end dates (`date_debut`, `date_fin`)
- Linked to a site and teacher
- `crm_class_id` for CRM class matching
- Multilingual name (`name_fr`, `name_en`, `name_ar`, `name_de`)
- View submitted group applications per group

#### Blog

- **Blog Categories**: Full CRUD
- **Blog Posts**: Full CRUD with featured image upload, category assignment, publish status

#### Studienkollegs

Full CRUD for preparatory college programs with slug-based public detail pages.

---

### Student Enrollment & Leads

#### Leads Dashboard

Central inbox for all incoming student interest:
- **Consultations**: Free consultation requests from the public site
- **GLS Inscriptions**: Student enrollment form submissions
- **Group Applications**: Direct group enrollment requests
- Statistics overview for all lead types

#### Group Applications (Admin View)

- Full CRUD for standalone management
- Approve or reject each application
- Upload ID card documents (Spatie Media Library)
- Resync application to CRM

#### Attestation Requests

Manage frontoffice attestation requests:
- View pending / approved / refused requests
- Accept or refuse with one click
- Automatically sends confirmation or refusal email to the student

#### Student Feedback

- View all feedback submitted via the public QR code form
- QR code management for each center

#### Newsletter Subscribers

- List all newsletter signups
- Delete subscribers

---

### Finance & Revenue

#### Encaissements (Payments)

Full revenue tracking per site:

- **Excel Import**: Upload monthly payment exports from external POS or banking sources. Preview before confirming import.
- **Manual Entry**: Create individual payment records
- **Payment Methods**: Cash (`especes`), TPE (card terminal), bank transfer, cheque
- **Fee Types**: Course registration (A1–B2), monthly tuition, OSD exam, other
- **Dashboard**: Revenue overview with operator analytics and site-level breakdown
- **Rentabilité (Profitability)**: Revenue vs. expenses comparison per site

#### Site Expenses

- Full CRUD for per-site operating costs
- Excel import for bulk expense uploads
- CRM-synced expenses (`crm_expense_id`)

#### Primes (Bonuses)

- Auto-generated from collection data
- Read-only view (generated via recouvrement module)
- Config management

#### Impayés (Unpaid Invoices)

- Excel import of unpaid invoice lists
- Mark individual unpaid invoices as recovered
- Version tracking per import

#### Recouvrement (Collections)

- Dashboard for collections status
- Auto-generate primes based on collected amounts

---

### Payroll System

A versioned, Excel-driven payroll pipeline for calculating teacher payments.

#### Group Import (Enrollment-Based Payroll)

1. **Upload** a student roster Excel file for a group
2. System creates a versioned **GroupImport snapshot** with all student records
3. Track **student lifecycle**: enrolled → transferred / cancelled
4. **Monthly payment calculation** per student (fixed `payment_per_student` × active students)
5. **Compare imports**: diff two versions side-by-side to see additions/removals
6. **Monthly analysis**: view per-student payment breakdown for any month

#### Presence Import (Attendance-Based Payroll)

1. **Upload** a weekly attendance Excel file (or sync from Wimschool CRM)
2. System parses per-student, per-week attendance counts
3. Define **weekly threshold** and **rate percentage** for partial-week adjustments
4. **Calculated payments** per student based on actual attendance
5. Approve import after review
6. **Export Excel** of final payment summary

#### CRM Payroll (Wimschool Auto-Sync)

- Pull attendance directly from Wimschool API for any class
- Preview computed payments before saving
- Replaces manual Excel uploads for CRM-connected classes

---

### CRM Integration (Wimschool API)

A full read-and-mirror integration with the Wimschool student management system. Data is synced to local mirror tables and surfaced through a rich dashboard suite.

#### Student Directory

- Browse all registered students from CRM
- Search by name, email, phone
- View individual session presence history
- View registration history per student

#### Payments Module

- **Payment Checks**: Verify payment status per student
- **Allocations**: Payment allocation breakdown
- **Collections**: Outstanding balance tracking with drill-down per student

#### Classes & Groups

- **Classes Listing**: All CRM classes with teacher and enrollment count
- **Level Sessions**: Session details per level
- **Payment Matrix**: Export payment matrix to Excel
- **Subscription Services**: Track subscription-based services
- **Salaries**: Teacher salary records from CRM

#### Insights & Analytics

- **Cash Handlers**: Who collected cash and when
- **Reconciliation**: Payment vs. enrollment reconciliation
- **Retention Analysis**: Student retention rates over time
- **Payment Activity History**: Full timeline of payment events
- **Group Evolution**: Enrollment and revenue trend snapshots per group
- **Forecast**: Revenue forecasting based on current enrollment

#### Presence Suivi (Anti-Fraud Attendance)

- Interactive attendance calendar per class
- Detect anomalies (gaps, duplicates, suspiciously uniform records)
- Session stats (average attendance, peak days)

#### Statistics

- **Center Performance**: KPIs per site (revenue, enrollment, retention)
- **Teacher Performance**: Teaching hours, student counts, payment totals per teacher
- **Annual Summary**: Full-year revenue and enrollment summary
- **Collections Dashboard**: Drill-down from site → class → student

#### Agent Dashboard (Call Center)

- Task list for sales agents
- Student follow-ups queue
- Unpaid student contact tracking

#### CRM Expenses

- Expense records synced from Wimschool
- Read-only view linked to local site expenses

#### Daily / Weekly CEO Reports

- Auto-generated narrative reports summarizing the day's / week's KPIs
- Sent via email to management
- Regenerate and resend from the admin panel

---

### HR & Scheduling

#### Employee Schedules

- **Self-service weekly view** (`/schedules/week`): Staff members view and submit their own schedule
- **Admin management view** (`/schedules/manage`): Admins approve, edit, batch-publish schedules
- **PDF export**: Generate PDF planning by employee or by site

---

### Weekly Reports (Rapport Semaine)

Per-group weekly reports filled out by teachers:

- Create / edit report content (rich text)
- Attach files (photos, exercises)
- Batch sync multiple reports at once
- **PDF export** of individual reports
- **Event calendar** view of submitted reports
- Status tracking (draft, submitted, validated)

The system supports a **5-skill grid** (Lesen / Hören / Grammatik / Schreiben / Sprechen) with per-skill notes when a teacher covers multiple groups in the same week.

---

### Level Followup (Suivi Niveau)

Track student level progression across groups:

- Sync level data from CRM
- View per-group followup status
- Add notes per student or group
- Mark followup as complete
- **PDF export** of followup summaries
- Tracks `date_fin` (end date) from group record for scheduling followups

---

### Certificates & Attestations

#### Certificates

- Full CRUD for student certificates
- **Token-based public download link** — students receive a unique URL
- **QR code** embedded in certificate pointing to download URL
- **PDF generation** (DOMPdf) for printable certificates
- File storage via Spatie Media Library

#### Attestations (Teilnahmebestätigung)

- Full CRUD for participation attestations (separate from certificates)
- Supports `methodology_text`, `erfolg` (result), `is_ongoing` flag
- **PDF generation** for printable attestations
- Legacy flag for migrated historical records

#### Public Certificate Download

- `GET /certificates/download/{token}` — Publicly accessible, no auth required
- Token is unique per certificate record

---

### Quizzes

Level placement quizzes for prospective students:

- **Admin**: Full CRUD for quizzes, questions, and answer options (nested)
- **Frontoffice**: AJAX-powered quiz interface at `/discover-your-level/quiz`
- Mark correct options, calculate score, recommend level to student
- Multiple quizzes can be active simultaneously

---

### WhatsApp Campaigns

Bulk WhatsApp messaging for marketing and follow-ups:

- Create campaign with title, message template, and target phone list
- Schedule start/end dates
- **Live campaign controls**: Start, Pause, Resume, Stop, Force-Reset
- Real-time status monitoring
- Track contacted phone numbers (prevent duplicate sends)
- Campaign run logs

---

### Translation Orders

Track document translation orders (Morocco ↔ Germany):

- Log document type, source language, target language, ordered-by
- Status tracking through workflow stages
- Handover management
- Public tracking page at `/traductions/suivi` — students enter their CIN to check status

---

### Scheduled Reports

Automated email reports sent to management:

- **Weekly**: Center performance, group performance, presence summary, professor payments, unpaid students
- **Monthly**: Revenue summary
- **Daily**: CEO operational report
- Resend any report from the admin panel
- Full send log with timestamps and recipients

---

### Users, Roles & Permissions

- Full user management with role assignment
- Spatie Laravel Permission: granular `view`, `create`, `edit`, `delete` permissions per module
- Staff can be restricted to their assigned site(s)
- Admin-only modules: Studienkollegs, Users, Roles, System Config
- Profile self-editing: name, email, password

---

## API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/centers` | List all active teaching centers (cached 12h) |
| `GET` | `/api/groups/{site_id}` | Groups available at a center (cached 1h) |
| `GET` | `/api/groups/dates/{group_id}` | Start/end dates for a group |
| `GET` | `/api/groups/time/{group_id}` | Time range for a group |
| `GET` | `/api/groups/dates/{site_id}/{level}` | Available dates for a level at a site |
| `GET` | `/api/user` | Authenticated user (Sanctum) |

---

## Artisan Commands

### CRM Synchronization

```bash
php artisan crm:sync-centers            # Sync teaching centers from Wimschool
php artisan crm:sync-registrations      # Sync student registrations
php artisan crm:sync-attendance         # Sync attendance records
php artisan crm:sync-collections        # Sync collection data
php artisan crm:sync-expenses           # Sync expenses
php artisan crm:sync-payment-allocations
php artisan crm:sync-all                # Batch sync everything
php artisan crm:nightly-resync          # Scheduled nightly full resync
php artisan crm:ping                    # Test CRM API connectivity
php artisan crm:backfill                # Backfill historical data
php artisan crm:mirror-core             # Mirror core CRM tables
php artisan crm:snapshot-payments       # Snapshot current payment status
```

### Payroll & Analytics

```bash
php artisan payroll:build-group-evolution   # Build group growth snapshots
php artisan payroll:build-presence-summary  # Calculate presence payment summaries
php artisan payroll:compute-churn-scores    # Calculate student churn risk
```

### Reporting

```bash
php artisan reports:generate-daily      # Generate daily CEO report
php artisan reports:send                # Send scheduled reports
php artisan sitemap:generate            # Regenerate public XML sitemap
```

### Data Management

```bash
php artisan weekly-reports:generate     # Auto-generate weekly reports
php artisan level-followups:generate    # Generate level followup records
php artisan inscriptions:resync         # Resync GLS inscriptions
php artisan columns:backfill-normalized # Normalize legacy data columns
```

### Marketing

```bash
php artisan whatsapp:run-campaign {id}  # Execute a WhatsApp campaign
```

---

## Email Notifications

| Trigger | Recipient | Mail Class |
|---|---|---|
| Student submits attestation request | Admin | `AttestationRequestSubmittedMail` |
| Admin approves attestation request | Student | `AttestationRequestAcceptedMail` |
| Admin refuses attestation request | Student | `AttestationRequestRefusedMail` |
| Free consultation submitted | Student | `ConsultationConfirmationMail` |
| Free consultation submitted | Admin | `ConsultationAdminMail` |
| Contact form submitted | Admin | `ContactMessageMail` |
| GLS inscription submitted | Student | `GlsInscriptionConfirmation` |
| GLS inscription submitted | Admin | `GlsInscriptionMail` |
| Daily CEO report | Management | `DailyCeoReportMail` |
| Weekly center performance | Management | `WeeklyCenterPerformanceReportMail` |
| Weekly group performance | Management | `WeeklyGroupPerformanceReportMail` |
| Weekly presence summary | Management | `WeeklyPresenceReportMail` |
| Weekly professor payments | Management | `WeeklyProfPaymentReportMail` |
| Weekly unpaid students | Management | `WeeklyUnpaidStudentsReportMail` |
| Monthly revenue report | Management | `MonthlyRevenueReportMail` |

All emails use a shared `EmbedsBrandLogo` concern for consistent GLS branding.

---

## Installation & Setup

```bash
# Clone the repository
git clone <repo-url>
cd gls

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file and configure
cp .env.example .env
php artisan key:generate

# Run database migrations and seeders
php artisan migrate --seed

# Build frontend assets
npm run build

# Start development servers
php artisan serve       # http://127.0.0.1:8000
npm run dev             # Vite dev server
```

---

## Environment Variables

Key variables to configure in `.env`:

```env
APP_NAME="GLS Sprachenzentrum"
APP_URL=http://127.0.0.1:8000
APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gls
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=your@gmail.com
MAIL_FROM_NAME="GLS Sprachenzentrum"

# Wimschool CRM API
CRM_BASE_URL=https://your-crm-api.com
CRM_TOKEN=your-crm-token

# Google Sheets (for payroll export)
GOOGLE_APPLICATION_CREDENTIALS=/path/to/credentials.json
```

---

## License

Private — GLS Sprachenzentrum internal platform. All rights reserved.
