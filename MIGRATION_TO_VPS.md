# Migration to VPS

GLS Sprachenzentrum moved from Hostinger shared hosting to a dedicated Ubuntu
VPS. This document is the server-facing reference: what the box looks like,
what runs on it, and how deploys work. For the *why* behind specific
production-readiness changes (queues, rate limiting, security headers, CORS),
see [PRODUCTION_READINESS.md](PRODUCTION_READINESS.md).

## Server stack

| Component | Version / detail |
|---|---|
| OS | Ubuntu 24.04 |
| Web server | Nginx |
| PHP | 8.4 (PHP-FPM) |
| Database | MySQL 8 |
| Cache / queue / session broker | Redis (installed; see [PRODUCTION_READINESS.md](PRODUCTION_READINESS.md) for enabling it in `.env`) |
| Process supervision | Supervisor (`gls-worker` program for `queue:work`) |
| Scheduler | Laravel Scheduler via a single cron entry (`schedule:run` every minute) |
| Frontend build | Node + Vite |
| App framework | Laravel 12 |

## Project path

```
/var/www/gls
```

Public web root: `/var/www/gls/public`

## Production domain

```
https://glssprachenzentrum.ma
```

Enforced as the canonical host by `App\Http\Middleware\ForceCanonicalHost`
(registered globally in `app/Http/Kernel.php`). Never hardcode this domain in
application code — use `config('app.url')` or the `route()`/`url()` helpers,
both of which read `APP_URL` from `.env`.

> **Known inconsistency to verify:** `.env.example` currently ships `APP_URL`
> and `SEO_CANONICAL_HOST` as `gls-sprachzentrum.ma` (hyphenated) — an older
> value. Confirm the **real** production `.env` has the correct
> non-hyphenated domain (`glssprachenzentrum.ma`) set for both. If it doesn't,
> `route()`/`url()` output, canonical tags, sitemap URLs, and outgoing email
> links will all be wrong until corrected.

## DNS notes

- `A` record for the apex domain (`glssprachenzentrum.ma`) → VPS IP.
- `A` (or `CNAME`) record for `www.glssprachenzentrum.ma` → same target,
  since `CORS_ALLOWED_ORIGINS` (see below) and `ForceCanonicalHost` both
  assume both hosts resolve, even though the canonical host middleware will
  redirect `www` → apex (or vice versa, whichever is configured as canonical).
- Confirm TTLs are reasonable (300–3600s) before any future IP changes so
  cutover doesn't hang on stale caches.

## SSL notes

- Use Let's Encrypt via `certbot` with the Nginx plugin for auto-renewal:
  ```bash
  sudo apt install certbot python3-certbot-nginx
  sudo certbot --nginx -d glssprachenzentrum.ma -d www.glssprachenzentrum.ma
  ```
- Certbot installs a renewal cron/systemd timer automatically — verify with
  `systemctl list-timers | grep certbot`.
- `SecurityHeaders` middleware (`app/Http/Middleware/SecurityHeaders.php`)
  sends `Strict-Transport-Security` **only when the request is already
  HTTPS** (`$request->secure()`), so HSTS activates automatically once SSL is
  live — no separate toggle needed.

## Queue worker

Managed by Supervisor, not run manually. See
[PRODUCTION_READINESS.md § Queue worker](PRODUCTION_READINESS.md#queue-worker-supervisor)
for the full `gls-worker.conf` and setup steps. Quick reference:

```bash
sudo supervisorctl status gls-worker:*
sudo supervisorctl restart gls-worker:*
```

## Scheduler cron

Single crontab entry (edit with `crontab -e` as the deploy user, or set up as
a system cron under `/etc/cron.d/` running as `www-data`):

```
* * * * * cd /var/www/gls && php artisan schedule:run >> /dev/null 2>&1
```

All actual scheduling logic (frequencies, overlap protection, backgrounding)
lives in `app/Console/Kernel.php` — this cron entry never needs to change
when adding/removing scheduled commands.

## Redis configuration

Redis is installed on the VPS. See
[PRODUCTION_READINESS.md § Redis](PRODUCTION_READINESS.md#redis) for the
`.env` values to set and what each driver switch (`CACHE_DRIVER`,
`SESSION_DRIVER`, `QUEUE_CONNECTION`, `RESPONSE_CACHE_DRIVER`) controls.

## Deployment commands

Use the deploy script — do not run the steps by hand except when
troubleshooting:

```bash
cd /var/www/gls
./scripts/deploy-vps.sh
```

See [scripts/deploy-vps.sh](scripts/deploy-vps.sh) for exactly what it runs
and why each step exists (maintenance mode, dependency install, asset build,
migrations, cache rebuild, PHP-FPM/Nginx reload, queue worker restart,
permission fix).

## Rollback notes

There is currently no automated release/rollback tooling (no
release-directory symlink pattern, no CI). Rollback today is manual:

1. **Code rollback** — identify the last known-good commit, then:
   ```bash
   cd /var/www/gls
   php artisan down --render="errors::503"
   git log --oneline -10          # find the commit to roll back to
   git reset --hard <good-commit> # ⚠ discards commits after this point locally
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   sudo systemctl reload php8.4-fpm
   sudo supervisorctl restart gls-worker:*
   php artisan up
   ```
   `git reset --hard` is destructive to local server-side history — only run
   it on the server checkout, never on your local dev machine, and confirm
   there are no server-only uncommitted changes first (`git status`).

2. **Migration rollback** — only if the bad deploy included a migration:
   ```bash
   php artisan migrate:rollback --step=1
   ```
   Review the migration first; per project policy, never run a destructive
   rollback against production without confirming what it drops.

3. **Queue worker code drift** — always restart workers after any rollback
   (`supervisorctl restart gls-worker:*`) — a worker process holds the PHP
   code it started with in memory until restarted, so a code rollback alone
   does not un-stick already-running workers.

Given there's no CI or staged release directory yet, the safest rollback
strategy is prevention: test on a local/staging copy before pushing to
`main`, and keep deploys small and frequent rather than large and risky. A
symlink-based release strategy (e.g. via [Deployer](https://deployer.org/))
is a reasonable future improvement — tracked as a low-priority item, not
blocking current operations.

## CRM API recommendations

The CRM integration (`app/Services/Crm/`, `app/Console/Commands/*Crm*`,
`app/Console/Commands/MirrorCoreCommand.php`) is the highest-priority system
in this project. Current state and recommendations:

- **Already solid:** `WimschoolClient` (`app/Services/Crm/WimschoolClient.php`)
  has genuinely good pagination, 429/rate-limit handling with `Retry-After`
  honoring, and a cooldown flag — don't touch this without reason.
- **Sync orchestration:** `crm:sync-all` runs every 2 hours via the scheduler
  with both a Kernel-level `withoutOverlapping()` guard and an app-level
  cache lock (`CrmSyncAllCommand.php`) — two layers of overlap protection,
  intentional and correct.
- **Known gap — no transactions around sync writes:** none of the CRM mirror
  sync loops (`MirrorCoreCommand`, `SyncCrmAttendanceCommand`) wrap their
  per-page writes in `DB::transaction()`. A network failure mid-page leaves a
  partially-written page with no rollback. See
  `docs/TODO-crm-sync-upsert.md` for the full analysis and a recommended
  next step.
- **Known gap — per-row `updateOrCreate()` instead of batched `upsert()`:**
  flagged as a real bottleneck at scale, but **not** converted automatically
  in this pass — the unique-key semantics on `crm_registrations` (nullable
  `crm_id` plus a second overlapping unique constraint) and the JSON-cast
  `raw_data` column make a blind conversion risky. Full analysis, including
  which two sync methods *are* safe to convert today, is in
  [`docs/TODO-crm-sync-upsert.md`](docs/TODO-crm-sync-upsert.md).
- **Hikvision integration** is intentionally isolated from the CRM domain
  (`config/hikvision.php`) and now runs on its own 15-minute schedule
  (`hikvision:sync` in `app/Console/Kernel.php`) — previously unscheduled
  entirely, so device/attendance/alarm data never refreshed automatically.
- **Scale target:** design any new CRM/Homeschool/Hikvision sync work assumed
  to serve thousands of students — prefer chunked/paginated reads, queued
  jobs over inline execution, and batched writes over per-row writes, per the
  patterns above.
