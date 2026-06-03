# Hostinger Cron Job Setup — GLS CRM Sync

**Site:** gls-sprachzentrum.ma  
**Hostinger Panel:** Websites → gls-sprachzentrum.ma → Advanced → Cron Jobs

---

## Good News: You Already Have the Right Cron Entry

Looking at your current cron job list, you already have this entry running:

```
* * * * *
/usr/bin/php /home/u728601666/domains/gls-sprachzentrum.ma/laravel/artisan schedule:run
```

**This is exactly what you need.** No new cron job is required.

Laravel's scheduler fires every minute via this entry, then internally decides which
commands to run based on their configured schedule. `crm:sync-all` is now configured
to run every 2 hours — the scheduler handles that timing automatically.

---

## What You Already Have (Current Cron Jobs)

| Time | Command | Purpose |
|------|---------|---------|
| `* * * * *` | `artisan schedule:run` | Laravel scheduler — runs every minute, dispatches all scheduled commands |
| `*/5 * * * *` | `artisan queue:work --queue=google-sheets ...` | Google Sheets queue worker |

---

## What the Scheduler Now Does Automatically

Because `crm:sync-all` is registered in `Kernel.php` with `cron('0 */2 * * *')`,
every time the `* * * * *` cron fires `schedule:run`, Laravel checks:
- Is it the top of an even hour (00:00, 02:00, 04:00 ... 22:00)?
- Is it not already running (`withoutOverlapping`)?

If yes → it launches `crm:sync-all` in the background.
If no → it does nothing for that minute.

**You do not need to add any new cron job.**

---

## How to Verify It Is Working

### Step 1 — Check the scheduler sees your new command

Connect via SSH or run in terminal:

```bash
php /home/u728601666/domains/gls-sprachzentrum.ma/laravel/artisan schedule:list
```

You should see something like:

```
0 */2 * * *   crm:sync-all    Every 2 hours
07:00          crm:daily-report  Daily at 07:00
00:15          gls:generate-level-followups  Daily at 00:15
```

If `crm:sync-all` appears in the list, the scheduler is configured correctly.

### Step 2 — Watch the log after the next even hour

The next time the clock hits 00:00, 02:00, 04:00, etc. (Casablanca time),
the sync will run automatically. Check the log:

```bash
tail -f /home/u728601666/domains/gls-sprachzentrum.ma/laravel/storage/logs/crm-sync-all.log
```

You should see output like:

```
╔══════════════════════════════════════════════════════╗
║  CRM SYNC ALL  —  2026-06-04 02:00:00               ║
╚══════════════════════════════════════════════════════╝

┌─ [classes] Mirror classes, students, registrations
└─ [OK]   classes completed in 45.2s

┌─ [attendance] Sync session presence
└─ [OK]   attendance completed in 12.1s

...

╔══════════════════════════════════════════════════════╗
║  SYNC COMPLETE — all dashboards updated              ║
╚══════════════════════════════════════════════════════╝
```

### Step 3 — Verify tables have data after first sync

In phpMyAdmin or via SSH:

```sql
SELECT COUNT(*) FROM crm_payment_allocations;
SELECT COUNT(*) FROM crm_group_evolution_snapshot;
SELECT COUNT(*) FROM crm_presence_summary;
SELECT step, status, completed_at FROM crm_sync_log ORDER BY id;
```

All tables should have rows and all steps should show `status = done`.

---

## Before the First Automatic Sync — Run Once Manually

The cron will handle all future syncs, but you need to seed the tables once.
Run these commands via SSH (in order):

```bash
cd /home/u728601666/domains/gls-sprachzentrum.ma/laravel

# Step 1: Run the new migrations
php artisan migrate

# Step 2: Backfill normalized columns from existing raw_data
php artisan crm:backfill-columns

# Step 3: First full sync — populates all new tables
php artisan crm:sync-all
```

Step 3 takes approximately **10–15 minutes** the first time (API calls + aggregations).
After that, every 2-hour cron run is incremental and takes the same ~10–15 minutes.

---

## If You Need to Add a New Cron Job (For Reference Only)

You do NOT need this for the CRM sync. But if you ever need to add a new cron job,
here is how to do it in the Hostinger panel:

### In the Hostinger Cron Jobs panel:

| Field | Value |
|-------|-------|
| **Command to Run** | `/usr/bin/php /home/u728601666/domains/gls-sprachzentrum.ma/laravel/artisan YOUR:COMMAND` |
| **Minute** | `*` |
| **Hour** | `*` |
| **Day** | `*` |
| **Month** | `*` |
| **Weekday** | `*` |

### Common timing options:

| You want | Minute | Hour | Day | Month | Weekday | Cron expression |
|----------|--------|------|-----|-------|---------|-----------------|
| Every minute | `*` | `*` | `*` | `*` | `*` | `* * * * *` |
| Every 5 minutes | `*/5` | `*` | `*` | `*` | `*` | `*/5 * * * *` |
| Every hour | `0` | `*` | `*` | `*` | `*` | `0 * * * *` |
| Every 2 hours | `0` | `*/2` | `*` | `*` | `*` | `0 */2 * * *` |
| Daily at 02:00 | `0` | `2` | `*` | `*` | `*` | `0 2 * * *` |
| Daily at 07:00 | `0` | `7` | `*` | `*` | `*` | `0 7 * * *` |

**But again — for the CRM sync, you need no new cron job.**
The existing `* * * * * artisan schedule:run` handles everything.

---

## Your Full Cron Job Picture After This Setup

| Cron entry | What it does |
|------------|-------------|
| `* * * * *  artisan schedule:run` | **Existing.** Fires every minute. Laravel internally runs `crm:sync-all` every 2 hours, `crm:daily-report` daily at 07:00, etc. |
| `*/5 * * * *  artisan queue:work --queue=google-sheets` | **Existing.** Google Sheets queue worker. No change. |

That's it. Two cron entries, everything else is managed by Laravel.

---

## Troubleshooting

### "No output in the log after 2 hours"

Check if the scheduler cron is actually running:
```bash
# View Hostinger cron output history via panel:
# Cron Jobs → * * * * * artisan schedule:run → View Output
```

Or run schedule:run manually to test:
```bash
php /home/u728601666/domains/gls-sprachzentrum.ma/laravel/artisan schedule:run --verbose
```

### "crm:sync-all doesn't appear in schedule:list"

The new `Kernel.php` changes haven't been deployed yet. Make sure you:
```bash
git pull   # or upload the updated files
php artisan config:clear
php artisan cache:clear
```

### "Sync failed partway through"

Check which step failed:
```sql
SELECT step, status, last_error FROM crm_sync_log ORDER BY id;
```

Resume from the failed step:
```bash
php artisan crm:sync-all --resume
# or restart from a specific step:
php artisan crm:sync-all --from=allocations
```

### "Dashboard still shows old data"

The sync runs every 2 hours. If you just deployed, the first automatic sync
hasn't run yet. Either wait for the next even hour, or run manually:
```bash
php artisan crm:sync-all
```

---

## Summary

| Question | Answer |
|----------|--------|
| Do I need a new cron job? | **No.** `* * * * * artisan schedule:run` already exists. |
| When will crm:sync-all first run automatically? | At the next even hour (00:00, 02:00, 04:00...) Casablanca time |
| What do I need to run manually (once)? | `php artisan migrate` → `crm:backfill-columns` → `crm:sync-all` |
| Where is the sync log? | `storage/logs/crm-sync-all.log` |
| How do I check sync status? | `SELECT * FROM crm_sync_log` in phpMyAdmin |
