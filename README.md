<div align="center">

# 🎓 GLS Sprachenzentrum

### The all-in-one platform for a modern language learning center

*A public bilingual website for prospective students — powered by a full-featured admin suite for enrollment, teaching, payroll, CRM, and finance.*

<br>

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-5-646CFF?style=for-the-badge&logo=vite&logoColor=white)
![Tailwind](https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

</div>

---

## ✨ Overview

**GLS Sprachenzentrum** is a dual-purpose web application built for a German language school. It combines a polished, marketing-ready **public website** with a powerful, role-based **administration dashboard** that runs the day-to-day operations of the center — from the first ad click all the way through to a student's certificate.

Everything a language center needs lives in one place: courses and enrollment, teaching workflows, attendance, payroll automation, a full CRM, and financial reporting.

---

## 🌍 Public Website (Frontoffice)

The student-facing experience — fast, cached, bilingual, and conversion-focused.

### 🗣️ Bilingual by design
- Full **French / English** localization with automatic locale routing
- Multilingual content across the entire site (course names, levels, blog, and more)

### 📚 Courses & Levels
- Rich level pages for **A1 → C1** explaining what each CEFR level covers
- **Intensive** and **online** course presentations
- Dedicated **center (site) pages** listing live group schedules and availability
- **Studienkolleg** program pages for students preparing to study in Germany
- Transparent **pricing** page

### 🎯 Lead capture & enrollment
- **Online registration** and **GLS inscription** forms with instant email confirmations
- **Group application** flow — students apply directly to a specific open group
- **Free consultation booking** for prospective students
- **Newsletter subscription**
- **Ad landing pages** (Meta, Google, TikTok) kept uncached so conversion tracking always fires

### 🧠 Interactive tools
- **"Discover your level"** interactive placement **quiz** that guides a visitor to their CEFR level
- **Certificate verification** — anyone can check a certificate's authenticity
- **Attestation requests** — students request official attendance/enrollment documents online
- **Translation tracking** — clients follow the status of an official translation by reference

### 📰 Content & trust
- Full **blog** with categories and SEO-friendly article pages
- **Student stories** and testimonials
- **Exam information** hubs (GLS, ÖSD, Goethe)
- **Feedback** submission for students to share their experience
- Legal pages (terms, privacy) and partner pages

### ⚡ Performance & SEO
- **Response caching** for lightning-fast public pages
- Automatic **sitemap generation**
- Built-in **SEO meta tags** and **Schema.org** structured data
- Anti-spam **rate limiting** on all public form submissions

---

## 🛠️ Admin Dashboard (Backoffice)

A comprehensive, permission-controlled control center for the entire organization.

### 🔐 Access & security
- Secure authentication with **email verification** and password reset
- **Role & permission** system — each staff member sees only what they should
- **Session-conflict protection** to prevent duplicate logins
- Data automatically **scoped to a user's assigned centers**

### 👩‍🏫 Academic management
- Manage **groups, teachers, and centers (sites)**
- Build and publish **class schedules & planning** (exportable to PDF)
- **Weekly teaching reports** with a per-skill grid (Lesen · Hören · Grammatik · Schreiben · Sprechen) and file attachments
- **Level follow-up** tracking to monitor each group's progression
- **Certificate generation** with unique QR-coded, token-based public download links
- **Attestations** — issue official student documents on demand

### 📝 Content & marketing
- Full **blog CMS** (posts & categories) with media uploads
- **Quiz builder** — create placement quizzes with questions and options
- **Newsletter subscriber** management
- **WhatsApp campaign** management for outreach
- **Translation management** for official document translation orders

### 📋 Applications & requests
- Central inbox for **group applications**, **attestation requests**, and **feedback**
- **Lead management** to track and convert prospects

### 💼 CRM
A dedicated CRM module that keeps the whole student lifecycle in view:
- **Students, classes, registrations, and attendance** synced from external school systems
- **Payment tracking** with snapshots, allocations, and collections follow-up
- **Churn scoring** to flag at-risk students
- **Agent dashboards**, follow-ups, and daily reports
- **Group evolution** and presence tracking over time
- **Insights & statistics** dashboards
- Timezone-correct syncing (Casablanca) so evening sessions never land on the wrong day

### 💰 Payroll automation
Turns manual attendance-based pay calculations into an automated pipeline:
- **Import attendance & presence** data and auto-calculate what each professor is owed
- **Professor, hourly-period, and CRM staff** payroll runs
- **Status logging** and audit trail for every payroll cycle
- **Bonus (prime)** management

### 🧾 Finance (Encaissement)
- Import and track **revenue (encaissements)** and **expenses**
- Manage **unpaid invoices (impayés)** and **debt recovery (recouvrement)**
- Track **per-site expenses**
- A finance **dashboard** consolidating cash flow at a glance

### 🎥 Access control integration (Hikvision)
- Native integration with **Hikvision** devices for physical **attendance & access control**
- Device, person, and attendance management
- **Alarm** monitoring, webhooks, and activity logs
- Dedicated device dashboard and settings

### 📊 Reporting & dashboards
- Role-specific **staff & admin dashboards**
- **Scheduled automated reports** delivered on a recurring basis
- Send-log tracking for every dispatched report

---

## 🧰 Tech Stack

| Layer | Technologies |
|------|-------------|
| **Backend** | Laravel 11 · PHP 8.2+ · MySQL · Eloquent ORM |
| **Frontend** | Blade · Vite 5 · Tailwind CSS · Bootstrap 5 · SCSS · Vanilla JS + Axios |
| **Auth** | Laravel Sanctum · built-in auth + email verification · Spatie Permission |
| **Media** | Spatie Media Library (uploads & conversions) |
| **Docs** | DOMPDF (PDF export) · Simple QRCode · Maatwebsite Excel (import/export) |
| **SEO** | Artesaos SEOTools · Spatie Sitemap · Spatie Schema.org |
| **i18n** | mcamara Laravel Localization (FR / EN) |
| **Performance** | Spatie Response Cache · Predis (Redis) |
| **Integrations** | Google API Client · Hikvision devices · WhatsApp campaigns |

---

## 🚀 Getting Started

```bash
# 1. Install dependencies
composer install
npm install

# 2. Set up environment
cp .env.example .env
php artisan key:generate

# 3. Prepare the database
php artisan migrate --seed

# 4. Run the app (two terminals)
php artisan serve      # backend  → http://127.0.0.1:8000
npm run dev            # frontend → Vite dev server
```

### 🏗️ Production build

```bash
npm run build          # cleans & builds optimized assets
php artisan config:cache
php artisan route:cache
php artisan sitemap:generate
```

---

## 🧪 Quality & Tooling

```bash
./vendor/bin/phpunit                    # run the test suite
./vendor/bin/phpunit --filter=TestName  # run a single test
./vendor/bin/pint                       # Laravel Pint code style
php artisan cache:clear                 # clear caches
```

---

<div align="center">

### 🌟 Built with Laravel — engineered for a real language center.

*One platform. Every workflow — from the first click to the final certificate.*

</div>
