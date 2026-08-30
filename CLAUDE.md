# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

GLS Sprachenzentrum — a Laravel 11 web application for a language learning center. Dual-purpose platform: public website (frontoffice) for course info and student enrollment, plus an admin dashboard (backoffice) for management.

## Tech Stack

- **Backend:** Laravel 11, PHP 8.2+, MySQL, Eloquent ORM
- **Frontend:** Blade templates, Vite 5, Tailwind CSS + Bootstrap 5, SCSS, vanilla JS + Axios
- **Auth:** Laravel Sanctum + built-in auth with email verification
- **Key packages:** Spatie Media Library (uploads), Spatie Response Cache, Artesaos SEOTools, Barryvdh DOMPdf, Simple QRCode, mcamara Laravel Localization (FR/EN routing)

## Commands

```bash
# Install dependencies
composer install
npm install

# Dev server (run both)
php artisan serve          # Laravel at http://127.0.0.1:8000
npm run dev                # Vite dev server

# Production build
npm run build              # Cleans public/build/ then runs vite build

# Database
php artisan migrate
php artisan db:seed

# Tests
./vendor/bin/phpunit                    # All tests
./vendor/bin/phpunit --filter=TestName  # Single test

# Linting
./vendor/bin/pint           # Laravel Pint (PHP code style)

# Cache management
php artisan cache:clear
php artisan config:cache
php artisan view:cache

# WARNING: NEVER run `php artisan route:cache` on this project.
# Frontoffice routes are wrapped in LaravelLocalization::setLocale() (routes/web.php:89),
# which freezes one locale prefix at cache-build time and 404s the ENTIRE site
# (/fr, /fr/contact, all pages). Recovery: php artisan route:clear

# Sitemap
php artisan sitemap:generate
```

## Architecture

### Controller Organization

- `app/Http/Controllers/Frontoffice/` — Public-facing pages (home, courses, blog, exams, certificates, quiz, consultations, inscriptions)
- `app/Http/Controllers/Backoffice/` — Admin panel (CRUD for groups, teachers, sites, blog, certificates, quizzes, studienkollegs, applications)
- `app/Http/Controllers/Auth/` — Authentication (login, register, password reset, email verification)
- `app/Http/Controllers/Api/` — API endpoints (group dates, centers)

### Route Files

- `routes/web.php` — Main router: imports backoffice/frontoffice routes, auth routes, API endpoints, certificate downloads
- `routes/backoffice.php` — All admin routes, protected by `auth` middleware
- `routes/frontoffice.php` — All public routes, wrapped with localization middleware and response caching
- `routes/api.php` — Sanctum-authenticated API routes

### Localization

Default locale is **French** (`config/app.php`). Routes are localized via `mcamara/laravel-localization` with FR/EN support. Translation files live in `resources/lang/fr/` and `resources/lang/en/`. Models with multilingual content use suffixed columns (e.g., `name_fr`, `name_en`, `name_ar`, `name_de`).

### Views Structure

- `resources/views/frontoffice/` — Public pages organized by feature (blog, certificates, exams, niveaux, sites, quiz, legal, etc.)
- `resources/views/backoffice/` — Admin pages (dashboard, blog, groups, certificates, quizzes)
- `resources/views/layouts/` — Base layout templates
- `resources/views/emails/` — Email notification templates
- `resources/views/frontoffice/partials/` — Reusable Blade components

### Assets Pipeline (Vite)

Entry points defined in `vite.config.js`. SCSS files in `resources/scss/`, JS in `resources/js/`. Vite copies static assets (plugins, fonts, images, JSON) to `public/build/`. CSS code splitting is enabled.

### Key Models & Relationships

- **Group** belongs to Site and Teacher; has auto-detected period from `time_range`
- **BlogPost** belongs to BlogCategory
- **Certificate** has token-based public download links with QR codes
- **Quiz** has many QuizQuestions, each with QuizOptions
- **GlsInscription**, **Consultation**, **GroupApplication** — student-facing form submissions
- **User** and other entities support media uploads via Spatie Media Library

### Email Notifications

Mailable classes in `app/Mail/` handle: consultation confirmations, GLS inscription notifications, contact form messages. Configured with Gmail SMTP via `.env`.
