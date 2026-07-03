# Migrating GLS Sprachenzentrum: Hostinger Shared Hosting → Hostinger VPS (KVM4)

Goal: move the live site + database from shared hosting to your new KVM4 VPS with **zero data loss**, then upgrade the CRM sync job from a 2-hour cron poll to a **continuous background loop** (KVM4 gives you full root access + dedicated CPU/RAM, which shared hosting never allowed).

This is a one-time cutover, not a permanent dual-live setup: old shared hosting stays untouched as a cold backup, new VPS becomes the only live site after DNS switches.

---

## 0. What you need before starting

- Your Hostinger **VPS (KVM4)** already provisioned, with its IP address and root password/SSH key from the Hostinger hPanel → VPS → Overview page.
- **PuTTY** + **PuTTYgen** + **WinSCP** installed on Windows (SSH terminal, key generation, and drag-and-drop file transfer).
- Access to Hostinger **hPanel** for the shared hosting account (file manager, phpMyAdmin, cron jobs, DNS zone).
- Your domain's DNS management access (usually also in hPanel, under Domains → DNS Zone).

---

## 1. Back up everything on the OLD shared hosting first

Never touch the new VPS until you have a verified backup of the old site sitting on your own PC.

### 1.1 Database backup

1. hPanel → shared hosting → **Databases → phpMyAdmin**.
2. Select your GLS database → **Export** → format: SQL → **Custom** export → make sure "Add DROP TABLE" and "structure + data" are both checked → Go.
3. Save the `.sql` file. Also take a second copy using SSH if your shared plan allows SSH access:
   ```bash
   mysqldump -u DB_USER -p DB_NAME > gls_backup_$(date +%Y%m%d).sql
   gzip gls_backup_$(date +%Y%m%d).sql
   ```
4. Download that file to your PC via WinSCP or the hPanel File Manager. Do not rely on it staying on the server.

### 1.2 File backup

1. hPanel → **File Manager**, go to your domain's root (usually `public_html` or `domains/yourdomain.com/public_html`).
2. Select everything → **Compress** → `.zip` (or `.tar.gz`).
   - Important: `storage/app/public` and any Spatie Media Library uploads must be included — these are real user-uploaded files (certificates, images, PDFs), not code, and are NOT in git.
   - Also grab `.env` separately — it has your production DB credentials, mail credentials, and app key. You cannot regenerate this from git.
3. Download the archive to your PC via WinSCP/File Manager.
4. Verify the zip isn't corrupted: unzip it locally and spot-check that `storage/app/public/...` files and `.env` are actually present.

**Do not proceed until you have, on your own PC:** a `.sql` (or `.sql.gz`) database dump, a full site archive including `.env` and `storage/`, confirmed by opening/extracting them locally.

---

## 2. Connect to the new VPS (PuTTY + WinSCP)

### 2.1 Generate/convert an SSH key (recommended over password login)

1. Open **PuTTYgen** → Generate → move mouse to create randomness → Save private key as `gls-vps.ppk` somewhere safe (e.g. `Documents\ssh\`).
2. Copy the public key text shown at the top of PuTTYgen.
3. In Hostinger hPanel → VPS → **SSH Keys**, paste the public key (or, if you must, skip this and use the root password Hostinger emailed you — key-based is safer for a production box).

### 2.2 First login with PuTTY

1. Open PuTTY → **Host Name**: your VPS IP → Port 22 → Connection type: SSH.
2. Left tree → Connection → SSH → Auth → Credentials → browse to `gls-vps.ppk`.
3. Save this session (Session screen → give it a name like `gls-vps` → Save) so you don't retype the IP every time.
4. Open → log in as `root` (or the sudo user Hostinger created).

### 2.3 Basic VPS hardening (do this before anything else, it's a fresh box exposed to the internet)

```bash
apt update && apt upgrade -y
adduser deploy                # non-root user for day-to-day work
usermod -aG sudo deploy
ufw allow OpenSSH
ufw allow 80,443/tcp
ufw enable
```

Set up a matching PuTTY session for `deploy` once created, and prefer using that over root for routine tasks.

### 2.4 Install the stack

Match what your project needs (Laravel 11 / PHP 8.2+ / MySQL, per this repo's `composer.json`):

```bash
apt install -y nginx mysql-server php8.2 php8.2-fpm php8.2-cli php8.2-mysql \
  php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd \
  php8.2-intl unzip git composer

curl -fsSL https://deb.nodesource.com/setup_lts.x | bash -
apt install -y nodejs
```

Configure MySQL (`mysql_secure_installation`), create the production DB + user:

```sql
CREATE DATABASE gls_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'gls_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON gls_production.* TO 'gls_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 3. Move the data to the VPS (still with the domain pointing at the OLD host — no downtime yet)

### 3.1 Upload your backups via WinSCP

1. Open WinSCP → New session → SFTP → host = VPS IP → username `deploy` → advanced → SSH → Authentication → point to `gls-vps.ppk`.
2. Drag your site archive and `.sql.gz` dump into `/home/deploy/migration/` on the VPS.

### 3.2 Restore the database

```bash
cd /home/deploy/migration
gunzip gls_backup_YYYYMMDD.sql.gz
mysql -u gls_user -p gls_production < gls_backup_YYYYMMDD.sql
```

### 3.3 Deploy the code

Two options — pick based on what's simpler for you:

**Option A — from your git repo (recommended, since this project is already version-controlled):**
```bash
mkdir -p /var/www/gls
cd /var/www/gls
git clone <your-repo-url> .
```
Then copy `storage/app/public/*` and `.env` from your extracted backup archive into place (these are the only two things git doesn't have).

**Option B — straight from the shared-hosting archive:**
```bash
cd /var/www/gls
unzip /home/deploy/migration/site_backup.zip
```

Either way, then:

```bash
cd /var/www/gls
composer install --optimize-autoloader --no-dev
npm install && npm run build
cp .env.example .env   # skip if you restored the real .env already
php artisan key:generate   # ONLY if you don't have the original .env — otherwise keep the original APP_KEY or old encrypted DB columns become unreadable
```

Edit `.env`: set `DB_DATABASE=gls_production`, `DB_USERNAME=gls_user`, `DB_PASSWORD=...`, `APP_URL=https://yourdomain.com`.

```bash
php artisan storage:link
chown -R www-data:www-data /var/www/gls
chmod -R 775 storage bootstrap/cache
```

### 3.4 Configure Nginx + PHP-FPM, then test before touching DNS

Point Nginx's `server_name` at your domain but test via the VPS IP with a hosts-file override on your PC first, or a temporary subdomain, so you can verify the whole site works **before** DNS cutover:

```
# Windows: C:\Windows\System32\drivers\etc\hosts
VPS_IP  yourdomain.com
```

Browse the site, log into the backoffice, check the payroll/CRM pages, confirm data matches the old site. Remove that hosts-file line once done testing.

### 3.5 Cutover (only step with real downtime, keep it short)

1. On the OLD shared host, put the site in maintenance/read-only mode (or just note the exact time) so no new writes land there after your DB dump.
2. Re-run steps 3.2–3.3 with a **fresh** dump/upload if any time passed since your first backup, so the VPS has the latest data.
3. In hPanel → DNS Zone, change the A record to the VPS IP. Propagation is usually minutes, can take up to 24-48h depending on old TTL.
4. Once propagated, get an SSL cert on the VPS:
   ```bash
   apt install -y certbot python3-certbot-nginx
   certbot --nginx -d yourdomain.com -d www.yourdomain.com
   ```
5. Set up Laravel's scheduler cron (needed regardless of the continuous-sync change below — reports, monthly resnapshot etc. still run on schedule):
   ```bash
   crontab -e -u www-data
   # add:
   * * * * * /usr/bin/php /var/www/gls/artisan schedule:run >> /dev/null 2>&1
   ```
6. Keep the old shared-hosting account **untouched and unpaused** for at least a few weeks as a fallback. Don't cancel it yet.

---

## 4. Turning the CRM sync from "every 2 hours" into continuous

### What "every 2h" actually is in this codebase

It's not a file/DB backup — it's [app/Console/Kernel.php](app/Console/Kernel.php), where `crm:sync-all` and several follow-on jobs (`crm:daily-report`, `gls:generate-level-followups`, `wimschool:sync-attendance`, the stats self-heal, `crm:nightly-resync`) all run on `cron('X */2 * * *')`. Shared hosting's cron only fires once a minute at best and Hostinger shared plans often throttle or queue overlapping cron hits, which is why everything was batched into a 2-hour cycle.

Wimschool CRM is a third-party system you **pull from** — it has no webhook to push changes at you. So "continuous" here means: instead of firing every 2 hours, run `crm:sync-all` in a **supervised loop that polls every 1–2 minutes**, kept alive forever by a process supervisor (not by cron). This is exactly what a KVM4 VPS unlocks that shared hosting couldn't: a real long-running background worker.

### 4.1 Install Supervisor

```bash
apt install -y supervisor
```

### 4.2 Create the continuous CRM sync worker

This replaces the `crm:sync-all` cron line (and effectively the :00/:50 sync jobs) with an always-running loop:

```ini
# /etc/supervisor/conf.d/gls-crm-sync.conf
[program:gls-crm-sync]
process_name=%(program_name)s
command=/bin/bash -c "while true; do /usr/bin/php /var/www/gls/artisan crm:sync-all >> /var/www/gls/storage/logs/crm-sync-all.log 2>&1; sleep 90; done"
autostart=true
autorestart=true
user=www-data
numprocs=1
stdout_logfile=/var/www/gls/storage/logs/crm-sync-supervisor.log
stopwaitsecs=30
```

- `sleep 90` = a new sync starts ~90 seconds after the previous one finishes → effectively continuous, adjust to 60 or 120 depending on how Wimschool's API responds to load (watch for rate-limit errors in the log first day).
- `autorestart=true` means if the sync command crashes or PHP dies, Supervisor immediately restarts the loop — no more waiting for the next cron tick.

Apply it:

```bash
supervisorctl reread
supervisorctl update
supervisorctl start gls-crm-sync
supervisorctl status   # confirm RUNNING
```

### 4.3 What stays on cron vs what moves to the continuous worker

Not everything in `Kernel.php` should become a tight loop — only the actual data-freshness-sensitive pulls. Reports, monthly resnapshots, and weekly emails are fine on their existing schedule and would just spam duplicates if run every 90 seconds.

| Job | Old schedule | New approach |
|---|---|---|
| `crm:sync-all` | every 2h | **Supervisor loop, ~90s** (section 4.2) |
| `wimschool:sync-attendance` | every 2h (:40) | Move into the same continuous loop or its own Supervisor program if it needs to run independently of sync-all |
| `crm:daily-report`, level-followups, stats self-heal | every 2h (:20/:30/:45) | Keep on cron, but you can tighten to hourly now that sync-all runs continuously and data is always fresh |
| `crm:nightly-resync` (3-month deep resync) | every 2h | Keep as-is on cron — it's a deep catch-up pass, not meant to run every 90s |
| `crm:snapshot-payments` (monthly), `crm:weekly-report`, `reports:send *` | monthly/weekly | Leave untouched in `Kernel.php` — these are already correct as periodic jobs, not candidates for "continuous" |

So in practice: **remove or comment out** the `crm:sync-all` cron entry in [Kernel.php](app/Console/Kernel.php) (lines 22-28) since Supervisor now owns it, keep the rest of the file as-is, and keep the Laravel scheduler cron line from step 3.5 running for everything else.

### 4.4 (Recommended alongside this) Move queued jobs to a real queue worker too

If any part of the app dispatches Laravel jobs (`ShouldQueue`) and currently relies on `QUEUE_CONNECTION=sync` or a cron-driven `queue:work --stop-when-empty`, KVM4 lets you run a proper persistent queue worker the same way:

```ini
# /etc/supervisor/conf.d/gls-queue-worker.conf
[program:gls-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/gls/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
stdout_logfile=/var/www/gls/storage/logs/queue-worker.log
stopwaitsecs=3600
```

```bash
supervisorctl reread && supervisorctl update && supervisorctl start gls-queue-worker:*
```

This makes mail (consultation confirmations, GLS inscription notifications) and any other queued work fire within seconds instead of waiting on cron.

### 4.5 Monitoring the continuous jobs

```bash
supervisorctl status                          # is it running?
tail -f /var/www/gls/storage/logs/crm-sync-all.log   # live output
journalctl -u supervisor -f                   # supervisor-level events
```

If Wimschool's API starts rate-limiting or timing out with 90s cycles, increase the `sleep` value first — don't just let it retry-storm.

---

## 5. Post-migration checklist

- [ ] Old shared hosting backup files + `.sql.gz` kept safely off-server (your PC / external drive), not deleted.
- [ ] VPS firewall (`ufw`) enabled, only 22/80/443 open.
- [ ] SSL cert installed and auto-renewing (`certbot renew --dry-run`).
- [ ] Laravel scheduler cron running (`* * * * * php artisan schedule:run`).
- [ ] `gls-crm-sync` Supervisor job status = RUNNING, log shows successful runs every ~90s.
- [ ] `crm:sync-all` cron entry removed from [Kernel.php](app/Console/Kernel.php) so it's not double-running via both cron and Supervisor.
- [ ] Backoffice login, payroll dashboard, and CRM stats pages all show live/matching data on the VPS.
- [ ] `.env` `APP_ENV=production`, `APP_DEBUG=false` confirmed on the VPS.
- [ ] Old shared hosting account kept active (not cancelled) for a few weeks as rollback safety net.
