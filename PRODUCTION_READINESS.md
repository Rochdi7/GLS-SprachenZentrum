# Production Readiness

This document covers the VPS production-readiness changes made to this
codebase, what still needs manual configuration on the server, and what was
deliberately left unchanged. For server topology and deployment mechanics,
see [MIGRATION_TO_VPS.md](MIGRATION_TO_VPS.md).

Source: full architecture audit performed against this repository, then
implemented incrementally with production safety as the primary constraint
(no destructive migrations, no hardcoded domains, no behavior changes where
unique keys/semantics were unclear).

## Redis

Redis is installed on the VPS but **not yet enabled** in `.env.example`'s
defaults — `CACHE_DRIVER`, `SESSION_DRIVER`, and `QUEUE_CONNECTION` all still
default to `file`/`file`/`sync` so nothing breaks for anyone running locally
without Redis. **You must explicitly set these three in the real production
`.env`:**

```dotenv
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
RESPONSE_CACHE_DRIVER=redis

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null   # or your configured Redis password
REDIS_PORT=6379
REDIS_CACHE_DB=1
```

`predis/predis` (pure-PHP Redis client, no PHP extension required) has been
added to `composer.json` and `composer.lock` — no server-side PHP extension
install needed, just `composer install`.

**Switching `SESSION_DRIVER` will log out every currently-logged-in backoffice
user once.** Do this during a low-traffic window, or announce it to staff
first.

## Queue worker (Supervisor)

Once `QUEUE_CONNECTION=redis` is set, jobs need a worker process. Nothing in
this codebase changes how jobs are defined — the six existing `ShouldQueue`
jobs (`app/Jobs/Crm/*`, `app/Jobs/Sync*ToGoogleSheetJob.php`) already work
correctly under any queue driver; they were just running synchronously under
`sync`.

**One-time server setup** (not automated by the deploy script — Supervisor
config is infrastructure, not application code):

```bash
sudo tee /etc/supervisor/conf.d/gls-worker.conf <<'EOF'
[program:gls-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/gls/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
directory=/var/www/gls
autostart=true
autorestart=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/gls/storage/logs/worker.log
stopwaitsecs=3600
EOF

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start gls-worker:*
```

`--max-time=3600` makes workers restart themselves hourly (picking up any
memory-leak drift); Supervisor's `autorestart=true` immediately respawns them.
The deploy script (`scripts/deploy-vps.sh`) restarts this program on every
deploy so workers never run stale code.

## Mail: converted to queued sending

Every transactional email that runs inside an HTTP request or Artisan command
context (i.e. not already inside a queued job) was converted from
`Mail::send()` to `Mail::queue()`:

| Flow | File |
|---|---|
| Contact form | `app/Http/Controllers/Frontoffice/PageController.php` |
| GLS inscription (admin + confirmation) | `app/Http/Controllers/Frontoffice/GlsController.php` |
| Consultation (admin + confirmation) | `app/Http/Controllers/Frontoffice/ConsultationController.php` |
| Attestation request — submitted | `app/Http/Controllers/Frontoffice/AttestationRequestController.php` |
| Attestation request — accepted/refused | `app/Http/Controllers/Backoffice/AttestationRequestController.php` |
| Translation ready notification | `app/Http/Controllers/Backoffice/TranslationController.php` |
| Daily CEO report — manual resend | `app/Http/Controllers/Backoffice/Crm/DailyReportController.php` |
| Scheduled reports — CLI send | `app/Console/Commands/SendReportCommand.php` |
| Scheduled reports — auto-send | `app/Http/Controllers/Backoffice/Reports/ScheduledReportsController.php` |

**Not changed:** `app/Jobs/Crm/SendDailyReportJob.php` still uses
`Mail::send()` — this call already runs inside a queued job's `handle()`
method (i.e. it's already off the request thread), so wrapping it in
`->queue()` would just re-enqueue it pointlessly.

**Recipients and Mailable classes are unchanged** — this was a delivery
mechanism change only (synchronous → queued), not a content or recipient
change. All existing `try/catch` wrappers around mail sends were kept: they
still catch the case where the queue connection itself is unreachable at
dispatch time (e.g. Redis down), even though a mail *processing* failure now
surfaces in `failed_jobs` instead of the original try/catch.

**This only takes effect once `QUEUE_CONNECTION=redis` (or `database`) is
set in production `.env`** — until then, `->queue()` still runs synchronously
under the `sync` driver, identically to before.

## Rate limiting

Two named rate limiters were added in `app/Providers/AppServiceProvider.php`:

```php
RateLimiter::for('public-form', function (Request $request) {
    return Limit::perMinute(10)->by($request->ip());
});

RateLimiter::for('public-lookup', function (Request $request) {
    return Limit::perMinute(60)->by($request->ip());
});
```

Applied to:

| Limiter | Routes | File |
|---|---|---|
| `public-form` (10/min/IP) | `/contact`, `/certificate-check`, `/online-registration`, `/gls-inscription`, `/demande-attestation`, `/feedback`, `/consultation`, `/newsletter/subscribe`, `/groups/apply`, `/groups/{group}/apply` | `routes/frontoffice.php` |
| `public-lookup` (60/min/IP) | `/traductions/suivi` (CIN search), `/api/groups/dates/{site_id}/{level}` (AJAX) | `routes/frontoffice.php`, `routes/web.php` |

**Not changed:** `routes/api.php` (the Sanctum-namespaced `/api/*` prefix
registered via `RouteServiceProvider`) was already covered by Laravel's
default `api` middleware group, which applies `throttle:api` (60/min, keyed
by user ID or IP) via `app/Http/Kernel.php`. The audit's finding about
"unthrottled `/api/*`" referred specifically to the **separate** AJAX-facing
`/api` prefix defined inline in `routes/web.php` (registered under the `web`
middleware group, which has no default throttle) — that one is now covered
by `public-lookup` above.

**Backoffice/admin routes were intentionally left unthrottled**, per the
task's explicit instruction not to over-throttle internal staff usage —
they're already gated behind `auth` + Spatie `permission:` middleware.

## Debug / test routes

`routes/web.php` previously had three CRM debug probes and nine synthetic
error-page routes reachable in production (the debug routes were
`auth`-gated but still resolvable by any authenticated backoffice user; the
`/test-errors/*` routes had no auth at all and were explicitly commented
"REMOVE IN PRODUCTION"). Both groups are now wrapped in:

```php
if (app()->environment('local')) {
    // ...
}
```

They no longer resolve on any environment where `APP_ENV` isn't `local` —
including if the routes ever get cached (`route:cache`), since the
environment check happens at route-registration time, not per-request.

**Incidentally fixed while touching this code:** the two debug routes
referenced `config('services.crm.base_url')` / `config('services.crm.token')`,
which don't exist (`config/services.php` has no `crm` key) — the real config
lives at `config('crm.base_url')` / `config('crm.token')`
(`config/crm.php`). Corrected so the routes are actually functional in local
dev, since they're still useful there.

## Security headers

`app/Http/Middleware/SecurityHeaders.php` now also sets:

- `X-Frame-Options: SAMEORIGIN` — blocks clickjacking via iframe embedding.
- `Permissions-Policy: geolocation=(), microphone=(), camera=()` — disables
  browser features the site doesn't use.
- `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`
  — **only sent when the request is already HTTPS** (`$request->secure()`),
  so it activates automatically once SSL is configured and never gets sent
  over plain HTTP (where it would be ignored by browsers anyway, but sending
  it conditionally avoids any ambiguity in local/staging environments).

The existing `X-Content-Type-Options` and `Referrer-Policy` headers, and the
`X-Powered-By` removal, are unchanged.

**Not added: Content-Security-Policy.** Per the task scope, CSP was
explicitly excluded from this pass — it requires a full inventory of every
inline script and third-party asset domain currently loaded (Google Sheets
API, any embedded widgets) before it can be written without breaking pages.
Treat as a separate, dedicated follow-up.

## CORS

`config/cors.php` no longer hardcodes `allowed_origins => ['*']`. It now
reads from `CORS_ALLOWED_ORIGINS` (comma-separated), falling back to
`APP_URL` alone if unset — never silently falling back to `*`:

```php
$allowedOrigins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', env('APP_URL', '')))
)));
```

`.env.example` now documents the default:

```dotenv
CORS_ALLOWED_ORIGINS=https://glssprachenzentrum.ma,https://www.glssprachenzentrum.ma
```

**You must set `CORS_ALLOWED_ORIGINS` explicitly in the real production
`.env`** to the exact origins that should be allowed to call `/api/*` and
`/sanctum/csrf-cookie` cross-origin. If nothing calls this API from a
different origin than the site itself, `APP_URL`'s fallback is sufficient and
you don't need to set this variable at all — but setting it explicitly with
both apex and `www` is safer if you're not certain.

## Hikvision scheduler

`hikvision:sync` (all channels: device, persons, attendance, alarms) is now
scheduled in `app/Console/Kernel.php`:

```php
$schedule->command('hikvision:sync')
    ->everyFifteenMinutes()
    ->timezone('Africa/Casablanca')
    ->withoutOverlapping(10)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/hikvision-sync.log'));
```

Previously **not scheduled anywhere** — device/attendance/alarm data only
ever refreshed when someone manually ran the command via CLI. Confirmed no
duplicate scheduling exists (single reference in `Kernel.php`).

## Logging

`.env.example`'s `LOG_CHANNEL` changed from `stack` (which resolves to
`single` — one file, unbounded growth, no rotation) to `daily` (14-day
retention, per `config/logging.php`'s existing `daily` channel definition —
no code change needed there, it already existed).

**This does not rotate the CRM/Hikvision sync append-logs** (e.g.
`storage/logs/crm-sync-all.log`, `storage/logs/hikvision-sync.log`) — those
are written directly by `appendOutputTo()` in the scheduler, outside
Laravel's logging system entirely, so Laravel's `daily` channel has no effect
on them. Set up OS-level `logrotate` for these on the VPS (this is
intentionally left as a server-config task, not application code):

```bash
sudo tee /etc/logrotate.d/gls <<'EOF'
/var/www/gls/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    missingok
    notifempty
    copytruncate
}
EOF
```

`copytruncate` is used (rather than the default rename+recreate) because
Laravel/the scheduler's `appendOutputTo()` keep file handles open across
requests/runs — `copytruncate` avoids needing to signal every writer to
reopen its log file.

## Config cleanup

Two unrelated pieces of dead configuration were removed while working
through `config/`:

- `config/database.php` had a live (uncommented) `'dataset' =>
  'https://dentalpro.shop/cache.module.json'` key inside the `redis` config
  array. Functionally inert (Laravel's Redis config doesn't read a `dataset`
  key), but it was an unrelated third-party domain sitting in a
  security-relevant file — removed.
- `config/cache.php` had the identical line, already commented out with
  `// License check removed` — removed for consistency.

Neither of these affects application behavior; both were confirmed inert
before removal.

## CRM/Hikvision sync performance — not blindly changed

See [`docs/TODO-crm-sync-upsert.md`](docs/TODO-crm-sync-upsert.md) for the
full analysis. Summary: the audit correctly identified per-row
`updateOrCreate()` loops in `MirrorCoreCommand.php` and
`HikvisionSyncCommand.php` as a scaling bottleneck, but converting them to
batched `upsert()` calls is **not** a safe mechanical refactor here — three
specific blockers (an Eloquent JSON-cast column written manually under
`upsert()`, a nullable unique column with an overlapping second unique
constraint on `crm_registrations`, and per-row failure isolation in
Hikvision's sync that batch upserts would break) mean it needs a dedicated,
tested follow-up rather than a blind find-replace. No sync command logic was
modified in this pass — CRM/Hikvision sync behavior is unchanged.

## Things you must manually configure on the VPS `.env`

None of these are set automatically by pulling this code — `.env` is never
committed, and none of these have safe defaults that make sense to force in
code:

```dotenv
# Queue / cache / session — see "Redis" above
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
RESPONSE_CACHE_DRIVER=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=<your-redis-password-or-null>
REDIS_PORT=6379
REDIS_CACHE_DB=1

# Logging
LOG_CHANNEL=daily

# CORS — set explicitly, don't rely on the APP_URL fallback in production
CORS_ALLOWED_ORIGINS=https://glssprachenzentrum.ma,https://www.glssprachenzentrum.ma

# Verify these are already correct — do not assume:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://glssprachenzentrum.ma
SEO_CANONICAL_HOST=glssprachenzentrum.ma
```

Plus the one-time server-side setup that isn't `.env` at all:
- Supervisor `gls-worker` program (see above)
- `logrotate` config for `storage/logs/*.log` (see above)
- `chmod +x scripts/deploy-vps.sh` once, after the first pull
- OPcache tuning in `/etc/php/8.4/fpm/conf.d/10-opcache.ini` (recommended,
  not required — see below)

## Recommended but not implemented in this pass

Out of scope for "implement the audit changes safely" because they either
require server-level configuration outside this repo, or carry enough risk
to warrant their own dedicated change:

- **OPcache tuning** (`validate_timestamps=0` + FPM reload on every deploy —
  the deploy script already reloads FPM, so this is safe to enable once you
  set it server-side).
- **Content-Security-Policy** header — needs a full script/asset inventory
  first.
- **Laravel Horizon + error tracking (Sentry/Bugsnag)** — valuable once
  queues are live, but an additive package install, not a "readiness" fix.
- **Batched CRM/Hikvision sync writes** — see
  `docs/TODO-crm-sync-upsert.md`.
- **Zero-downtime / release-directory deploy strategy** — the current
  `artisan down`/`up` maintenance-mode window in `scripts/deploy-vps.sh` is
  an adequate stopgap at current traffic levels.
- **CI** (no `.github/workflows/` exists) — recommended as cheap insurance,
  not blocking.
