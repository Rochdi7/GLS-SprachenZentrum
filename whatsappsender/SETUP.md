# WhatsApp Sender — Setup Guide (Local / Another PC)

Complete install guide for running the WhatsApp Sender on a fresh Windows PC. Follow the steps in order.

**This app is designed to be installed on all 7 GLS centers:** Kenitra, Marrakech, Salé, Rabat, Casablanca, Agadir, Online. Each PC stores its own local database and can export its data so HQ can consolidate stats (see [Step 7 — Exporting & consolidation at HQ](#step-7--exporting--consolidation-at-hq)).

---

## Requirements

- **Windows 10 or 11**
- **XAMPP** with PHP 8.2+ — [download](https://www.apachefriends.org/download.html)
- **WhatsApp Desktop** installed from the Microsoft Store and **logged in** (phone linked, QR scanned) — [download](https://www.whatsapp.com/download)
- **PowerShell** (built into Windows)
- Administrator rights on the PC (for the Apache setup step)

---

## Step 1 — Install XAMPP

1. Download and install XAMPP to the default path: `C:\xampp\`.
2. Open **XAMPP Control Panel**.
3. Start **Apache** and **MySQL** once to confirm they work, then stop Apache (we will start it a different way in Step 4).

---

## Step 2 — Copy the project

Copy the entire `whatsappsender` folder into:

```
C:\xampp\htdocs\whatsappsender\
```

After copying, the folder structure should look like:

```
C:\xampp\htdocs\whatsappsender\
├── index.php
├── worker.php
├── lib\
├── views\
├── storage\
└── public\
```

---

## Step 3 — Verify `php.ini` allows process spawning

The worker spawns PHP CLI processes to send messages. If `proc_open` / `exec` are disabled, nothing will start.

1. Open `C:\xampp\php\php.ini` in a text editor.
2. Find the line starting with `disable_functions =`.
3. Make sure **none** of these are in the list: `proc_open`, `popen`, `exec`, `shell_exec`.
4. Save and close.

---

## Step 4 — CRITICAL: Run Apache as your user, not as a Windows service

**This is the most important step. Skipping it will cause the fatal error:**

> `FATAL: WhatsApp Desktop window could not be opened. Installed?`

### Why

By default, XAMPP installs Apache as a **Windows service running under `LocalSystem` in Session 0**. WhatsApp Desktop runs in **your interactive session (Session 1+)**. Windows isolates Session 0 from interactive sessions for security — so a service cannot see, focus, or send keystrokes to your WhatsApp window.

**The fix:** uninstall the Apache service and run `httpd.exe` directly as your logged-in user.

### How

Open **PowerShell as Administrator** and run:

```powershell
# Stop the Apache service if it is running
Stop-Service Apache2.4 -Force -ErrorAction SilentlyContinue

# Uninstall it as a Windows service (XAMPP control panel will no longer start it in Session 0)
& "C:\xampp\apache\bin\httpd.exe" -k uninstall
```

You should see: *"The 'Apache2.4' service has been removed successfully."*

### Start Apache in your session

From now on, **do NOT use the XAMPP Control Panel "Start" button for Apache** — it will reinstall it as a service.

Instead, start Apache one of these ways:

**Option A — one-time (current session only):**
```powershell
Start-Process -FilePath "C:\xampp\apache\bin\httpd.exe" -WorkingDirectory "C:\xampp\apache" -WindowStyle Hidden
```

**Option B — create a shortcut on your desktop** (recommended for daily use):
1. Right-click desktop → New → Shortcut.
2. Target: `C:\xampp\apache_start.bat`
3. Name it "Start Apache (WhatsApp Sender)".
4. Right-click the shortcut → Properties → Advanced → tick **Run as administrator**.

Double-click the shortcut whenever you need to send campaigns.

### Verify it worked

```powershell
Get-Process httpd | Select-Object Id, SessionId, @{n='User';e={ (Get-WmiObject Win32_Process -Filter "ProcessId=$($_.Id)").GetOwner().User }}
```

The `User` column must show **your Windows username** (e.g. `Outlaw`), **NOT** `Système` or `SYSTEM`. The `SessionId` must be **greater than 0**.

If it still shows `SYSTEM` / session 0, repeat Step 4 — something else reinstalled the service.

---

## Step 5 — Open the app

1. Make sure **WhatsApp Desktop is open, logged in, and visible** on your screen.
2. Go to: [http://localhost/whatsappsender/](http://localhost/whatsappsender/)
3. The SQLite database (`storage/db.sqlite`) is created automatically on first load.

---

## Step 6 — Identify this PC's center (one-time)

The first time you open the app, you will be **automatically redirected to a "Paramètres du centre" screen**. You cannot create a campaign until this is done.

Select which GLS center this PC belongs to from the dropdown:

- Kenitra
- Marrakech
- Salé
- Rabat
- Casablanca
- Agadir
- Online

Click **Enregistrer**. The center is saved in the local SQLite database and shown as a red badge in the top-right corner of every page going forward.

**Every campaign and every individual message sent from this PC will be tagged with this center**, so HQ can later see:
- which center sent the most campaigns
- delivery success rate per center
- which messages failed and why, per center
- activity timeline across all 7 centers

> **Important:** on each of the 7 PCs, pick the **correct** center. If you pick the wrong one, all the data on that PC will be mis-attributed. You can change it later from the gear icon in the top-right, but past campaigns keep their original tag.

---

## Step 7 — Create and run a campaign

1. Click **+ Nouvelle campagne**.
2. Fill in:
   - **Name** — internal label
   - **Message** — use `{name}`, `{phone}`, `{business}` placeholders if you want personalization
   - **Attachment** (optional) — PDF / image / MP4, max 20 MB
   - **Recipients** — one per line, format:
     ```
     +212600000000
     +212600000001, Optional Name
     ```
     Moroccan landlines (starting with `05`) are automatically skipped.
3. Adjust delays (default 45–90s randomized between sends). Slower = safer against bans.
4. Click **Créer la campagne** → **Démarrer**.
5. **Keep WhatsApp Desktop visible and foreground.** Do NOT touch the mouse or keyboard during sending — SendKeys needs the WhatsApp window to have focus, and any interaction can redirect keystrokes to the wrong window.

---

## Step 8 — Exporting & consolidation at HQ

Each of the 7 PCs runs its own local database. Here's how HQ gets a global view of all centers.

### What's stored locally on each PC

SQLite database at `storage/db.sqlite`. Every campaign creates:

- **1 row in `campaigns`** — campaign name, message template, attachment, status, per-center tag, creation date, start/finish timestamps, totals (sent / failed / pending), delay settings.
- **1 row in `messages` per recipient** — phone, business/name, status (pending / sending / sent / failed / skipped), error message if any, sent-at timestamp, number of attempts, per-center tag.

This means every single message has its own row with full history — you can query how many messages each center sent last month, the most common failure reasons, which campaign had the worst delivery rate, etc.

### Exporting from a center PC

On each center's PC, click the **Export** button (download icon in the top-right nav).

You'll get a ZIP file named like:
```
gls_whatsapp_casablanca_20260423_161200.zip
```

It contains:
- `campaigns.csv` — all campaigns with the center tag
- `messages.csv` — all messages with the center tag
- `manifest.json` — center identity + export timestamp

Send this ZIP to HQ (email, USB stick, Google Drive, whatever works for you).

### Consolidating at HQ

At the head office PC, install the same `whatsappsender` folder (Steps 1–5). You do **not** need to pick a center — just use it as the consolidation point.

Then:

1. Create a folder `C:\xampp\htdocs\whatsappsender\exports\`.
2. Drop all 7 center ZIPs (`gls_whatsapp_*.zip`) into that folder.
3. Open [http://localhost/whatsappsender/merge.php](http://localhost/whatsappsender/merge.php).

You'll get a dashboard showing:

- **Top KPIs** — total centers reporting, total campaigns, total messages sent, total failures
- **Ranking table** — centers sorted by volume, with delivery rate % and visual bar chart
- **Exports received** — which ZIPs have been loaded and when
- **Top failure reasons** — the 10 most common error messages across all centers
- **30 most recent campaigns** across all centers, chronologically

Click **"Recharger les exports"** whenever you drop a fresh ZIP into the folder.

The consolidated database lives at `storage/merged.sqlite` — you can open it with [DB Browser for SQLite](https://sqlitebrowser.org/) for custom queries (e.g. "messages per center per month", "top 100 phone numbers that failed", etc.).

### Export cadence suggestion

Have each center export **weekly** on Friday afternoon and email the ZIP to HQ. HQ drops the 7 ZIPs in `exports/` and reloads `merge.php` on Monday morning for the weekly report.

---

## Troubleshooting

### `FATAL: WhatsApp Desktop window could not be opened. Installed?`

**99% of the time, this means Apache is running as a Windows service (LocalSystem) instead of as your user.** Re-do Step 4.

To confirm:
```powershell
Get-Process httpd | Select-Object Id, SessionId, @{n='User';e={ (Get-WmiObject Win32_Process -Filter "ProcessId=$($_.Id)").GetOwner().User }}
```
If `User` is `Système` / `SYSTEM` → Apache is in the wrong session. Run the uninstall command from Step 4 again.

Only if Apache is confirmed running as your user and the error persists:
- Make sure WhatsApp Desktop actually launches when you double-click it
- Re-install WhatsApp Desktop from the Microsoft Store
- Log into WhatsApp (scan the QR with your phone)

### "Démarrage échoué : toutes les méthodes de spawn sont bloquées"

`disable_functions` in `php.ini` is blocking `proc_open` / `exec`. See Step 3.

### Worker log shows `powershell: command not found`

You are on Linux / Mac. This tool is Windows-only — it drives the native WhatsApp Desktop app via PowerShell and SendKeys.

### Messages go to the wrong contact, or text gets truncated

WhatsApp Desktop lost focus mid-send. Don't touch the keyboard or mouse, don't let other apps steal focus, don't let the screensaver kick in. Disable Windows notifications during campaigns if possible.

### Start button spins forever, log is empty

WhatsApp Desktop is minimized, not logged in, or not installed. Open it manually and make sure a chat list is visible on screen, then retry.

### "Already running" lock is stuck after a PC restart

The Start button will offer to reset it automatically — click yes.

---

## Important — don't get banned

WhatsApp bans numbers that blast identical unsolicited messages to non-contacts. Rules of thumb:

- **< 50 messages per day per number**, especially for new accounts
- **Personalize** with `{name}` — identical messages trigger spam detection faster
- **Randomize delays** — never send faster than the default 45–90s
- **Warm up new numbers gradually** — start with 10–20/day for the first week
- Only message people with a **legitimate reason** to hear from you (customers, students, opt-ins)

---

## Reminder: every time you reboot the PC

1. Start XAMPP MySQL from the Control Panel (that one is fine as a service).
2. Start Apache from your shortcut / `apache_start.bat` — **NOT** from the XAMPP Control Panel "Start" button for Apache.
3. Open WhatsApp Desktop and wait for the chat list to appear.
4. Open [http://localhost/whatsappsender/](http://localhost/whatsappsender/).
