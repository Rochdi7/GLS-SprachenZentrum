# Hikvision Device Setup — What's Happening and What To Do

## The core problem, in plain terms

Your Hikvision terminal has **two completely separate login systems**. You've been
using one (Hik-Connect) but the code needs the other (the device's own local login).
Knowing the password to one does not help you log into the other — they are
unrelated accounts, like your email password not working on your bank account.

| | Hik-Connect (`hik-connect.com`) | Device local login (`192.168.100.163/doc/page/login.asp`) |
|---|---|---|
| What it is | Hikvision's cloud app/website, for viewing your device remotely from anywhere | The device's own built-in webpage, only reachable when you're on the same local network (WiFi/router) as the device |
| You log in with | Phone number + a password you created when you registered the Hik-Connect account | A username (often `admin`) + a password that was set **on the device itself**, usually during its first-time setup |
| What we need it for | Nothing — this project does not use Hik-Connect at all | **This is what the Laravel app needs** to pull attendance/employee/alarm data |

The code we built (`HikvisionClient`, `hikvision:sync` command) only talks to the
**device local login** system. It has no way to use your Hik-Connect phone/password
— that's a different Hikvision product with a different API entirely.

## Why your login attempt failed

You typed something into the device's local login page
(`192.168.100.163/doc/page/login.asp`) and got:

> "Incorrect user name or password. The device will be locked after 4 failed
> login attempts."

That's the device telling you the username/password you tried does not match
**its own local account** — which makes sense, since the credentials you have
(phone number + Hik-Connect password) were never meant for this page.

**Important: stop guessing passwords on that page right now.** The device warned
it locks itself out after 4 wrong attempts (usually for 30+ minutes, sometimes
longer). If you keep trying random things, you'll lock yourself out even from a
correct guess later.

## What determines your next step

### Step 1 — Find out if you're already locked out

Open `http://192.168.100.163/doc/page/login.asp` in a browser again.

- If it lets you type a username/password again → you still have attempts left.
- If it shows a lockout message / countdown timer → you're locked out and must
  wait it out before trying anything else (don't keep refreshing/retrying).

### Step 2 — Try to find the real local admin credentials

Before touching the login page again, check these sources for the device's
**local** admin username/password (not Hik-Connect):

1. **A sticker/label on the device itself** — some Hikvision terminals print a
   default password on a label on the back or bottom.
2. **Whoever physically installed the device** — an installer, IT contractor,
   or previous employee may have set a local admin password during setup and
   written it down somewhere (setup notes, a password manager, an email).
3. **The box/manual it came in** — sometimes has a default printed inside.
4. **Common defaults** — many older Hikvision devices default to username
   `admin`. Newer devices (2015+) usually force you to set a custom password on
   first activation and have **no** universal default — so if this device is
   newer, there may be no "default" to try at all.

Do **not** paste any password you find into this chat. Just try it directly on
the device's login page yourself.

### Step 3 — If nobody knows the password (factory reset)

If the local admin password is truly unknown to everyone, the only way back in
is a **factory reset of the device itself** (a physical button or procedure on
the terminal — not something done through a browser). The exact steps depend on
the specific terminal model (e.g. DS-K1T series, DS-K1F series, etc.) — look
for a model number printed on the device (usually on a label on the back/bottom)
and search "[your exact model] factory reset" or check Hikvision's official
support site for that model's manual. After a factory reset, you'll set a brand
new local admin username/password yourself, and that's what goes into `.env`.

## Once you have working local credentials

Update `.env` (replace the old key/secret lines):

```env
HIKVISION_BASE_URL=http://192.168.100.163
HIKVISION_USERNAME=<the device's local admin username>
HIKVISION_PASSWORD=<the device's local admin password>
```

Then tell me and I'll run a connection test
(`php artisan hikvision:sync device`) from this project — it only reports
success/failure and the device's name, so you never need to share the password
with me directly.

## Security note

A real password was visible in a screenshot shared earlier in this session
(your Hik-Connect account password). Please change that Hik-Connect account
password now, since it passed through this conversation and should be treated
as exposed, even though it isn't the credential this project actually needs.
