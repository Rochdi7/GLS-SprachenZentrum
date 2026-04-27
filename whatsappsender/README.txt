WhatsApp Sender (local) — Installation & usage
==============================================

This is a standalone PHP application that drives WhatsApp Desktop to send
bulk WhatsApp messages from your own Windows PC. It is a lighter,
independent copy of the GLS Laravel backoffice "Campagnes WhatsApp" feature.

No Composer. No npm. No database server. Just PHP + SQLite.

------------------------------------------------------------
REQUIREMENTS
------------------------------------------------------------
- Windows 10 / 11
- XAMPP (PHP 8.1 or newer — 8.2+ recommended)
- WhatsApp Desktop installed and LOGGED IN with your phone
- PowerShell available (default on Windows)

------------------------------------------------------------
INSTALL (5 minutes)
------------------------------------------------------------
1. Copy the whole "whatsappsender" folder into:
      C:\xampp\htdocs\

   Final path: C:\xampp\htdocs\whatsappsender\

2. Open XAMPP Control Panel → Start Apache.

3. Open in your browser:
      http://localhost/whatsappsender/

   The database (storage/db.sqlite) is created automatically on first load.

------------------------------------------------------------
USE
------------------------------------------------------------
1. Click "+ Nouvelle campagne".
2. Fill in:
   - Name
   - Message (you can use {business}, {name}, {phone} placeholders)
   - Optional attachment (PDF / image / MP4, max 20 MB)
   - List of phone numbers, one per line. Format:
        +212600000000
        +212600000001, Optional Name
     Moroccan fax/landlines (05xx...) are automatically skipped.
3. Adjust delays (default 45–90s randomised between sends).
4. Click "Créer la campagne" → "Démarrer".
5. WhatsApp Desktop opens each chat, pastes the message, sends, waits, repeats.
   Keep WhatsApp Desktop VISIBLE — do not minimise it during a campaign,
   because SendKeys requires the window to have focus.

------------------------------------------------------------
CONTROLS
------------------------------------------------------------
- Pause / Resume / Stop: buttons on the campaign page.
- Force reset: if the "already running" lock is stuck (e.g. PC restarted
  mid-campaign), the Start button will offer to reset it automatically.
- Delete: works even while a campaign is running — it aborts the worker first.

------------------------------------------------------------
FILES
------------------------------------------------------------
  index.php              Web router + controllers
  worker.php             CLI worker (spawned per campaign)
  lib/
    db.php               SQLite connection + schema
    runtime.php          Status/control file I/O (stale-lock aware)
    automation.php       WhatsApp Desktop driver (PowerShell + SendKeys)
  views/
    layout.php           Shared HTML shell
    list.php, create.php, show.php
  storage/
    db.sqlite            Database (auto-created)
    attachments/         Uploaded files
    logs/                Worker logs + spawn.log (debugging)
    status.json          Current run state (written by worker ticks)
    control.json         Pause/abort flags (read by worker every 0.5s)

------------------------------------------------------------
TROUBLESHOOTING
------------------------------------------------------------
Symptom: "Démarrage échoué : toutes les méthodes de spawn sont bloquées"
  → Your XAMPP php.ini has proc_open/popen/exec disabled. Open
    C:\xampp\php\php.ini, find "disable_functions =", remove proc_open
    from the list, restart Apache.

Symptom: Worker log shows "powershell: command not found"
  → You're running the app on a Linux machine. This tool requires Windows.

Symptom: Button "Démarrer" spins forever, log empty
  → WhatsApp Desktop is not installed, or not logged in, or minimised.
    Open WhatsApp Desktop manually, scan the QR, and try again.

Symptom: Messages send to wrong contact / get truncated
  → WhatsApp Desktop window lost focus during sending. Do not touch the
    keyboard/mouse on the PC while a campaign is running.

------------------------------------------------------------
IMPORTANT — DON'T SPAM
------------------------------------------------------------
WhatsApp bans numbers that send many identical unsolicited messages to
people who did not save your contact. Keep batches small (< 50/day per
number), personalise with {name}, and only message people with a legitimate
reason to hear from you.
