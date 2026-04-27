<?php
/**
 * Windows / WhatsApp Desktop automation. Drives WhatsApp Desktop by:
 *   1. Putting the message / file on the Windows clipboard
 *   2. Launching whatsapp://send (or the UWP equivalent) to open the chat
 *   3. Forcing WhatsApp's window to the foreground
 *   4. Pasting + pressing Enter
 *
 * Supports both the classic desktop installer and the Microsoft Store UWP
 * version of WhatsApp.
 */

class Automation
{
    /** Run a PowerShell command, return true on exit 0. */
    private function ps(string $command, int $timeoutSeconds = 15): bool
    {
        $escaped = str_replace('"', '\\"', $command);
        $cmd = 'powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -Command "' . $escaped . '"';
        $out = [];
        $code = 0;
        @exec($cmd . ' 2>&1', $out, $code);
        return $code === 0;
    }

    /** Run a PowerShell command and return its stdout. */
    private function psOut(string $command): string
    {
        $escaped = str_replace('"', '\\"', $command);
        $cmd = 'powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -Command "' . $escaped . '"';
        $out = @shell_exec($cmd . ' 2>&1');
        return (string) $out;
    }

    /**
     * Run a PowerShell *script* (multi-line, with here-strings, etc.) by
     * dumping it to a temporary .ps1 file and invoking it via -File. Avoids
     * the double-escaping nightmare of embedding C# here-strings inside
     * `powershell -Command "..."`. Returns stdout.
     */
    private function psFile(string $script): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'wa_ps_') . '.ps1';
        file_put_contents($tmp, $script);
        $cmd = 'powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -File "' . $tmp . '"';
        $out = @shell_exec($cmd . ' 2>&1');
        @unlink($tmp);
        return (string) $out;
    }

    /**
     * Ensure WhatsApp Desktop has a real foreground window. UWP / Microsoft
     * Store WhatsApp sometimes runs in "background tile" mode where
     * MainWindowHandle is 0 — in that state SendKeys goes nowhere and the
     * whatsapp:// protocol activation wakes it up invisibly.
     *
     * This resolves the AppUserModelID dynamically (package name may change
     * between WhatsApp updates) and launches it via the Windows shell.
     */
    public function ensureWhatsAppWindow(int $maxWaitSeconds = 10): bool
    {
        $script = <<<'PS'
$existing = Get-Process | Where-Object {
    ($_.ProcessName -match 'WhatsApp' -or $_.MainWindowTitle -match 'WhatsApp') -and $_.MainWindowHandle -ne 0
} | Select-Object -First 1
if ($existing) { Write-Output 'ALREADY_VISIBLE'; exit 0 }

$app = Get-StartApps | Where-Object { $_.Name -match 'WhatsApp' } | Select-Object -First 1
if (-not $app) { Write-Output 'NOT_INSTALLED'; exit 2 }

Start-Process "explorer.exe" "shell:AppsFolder\$($app.AppID)"
Write-Output "LAUNCHED_$($app.AppID)"
exit 0
PS;
        $out = trim($this->psFile($script));
        if (str_contains($out, 'NOT_INSTALLED')) return false;
        if (str_contains($out, 'ALREADY_VISIBLE')) return true;

        // We launched — wait for the window to materialize.
        for ($i = 0; $i < $maxWaitSeconds * 2; $i++) {
            usleep(500_000);
            if ($this->hasWhatsAppWindow()) return true;
        }
        return false;
    }

    /** Quick check: is there a WhatsApp process with a visible window? */
    public function hasWhatsAppWindow(): bool
    {
        $out = trim($this->psFile(<<<'PS'
$p = Get-Process | Where-Object {
    ($_.ProcessName -match 'WhatsApp' -or $_.MainWindowTitle -match 'WhatsApp') -and $_.MainWindowHandle -ne 0
} | Select-Object -First 1
if ($p) { Write-Output 'YES' } else { Write-Output 'NO' }
PS));
        return str_contains($out, 'YES');
    }

    /**
     * Navigate the (already-open) WhatsApp window to a whatsapp://send URI.
     * Assumes ensureWhatsAppWindow() ran first.
     */
    public function launchUrl(string $url): bool
    {
        // Method 1: PowerShell Start-Process — works with UWP apps + protocol handlers.
        $escaped = str_replace("'", "''", $url);
        if ($this->ps("Start-Process '{$escaped}'")) return true;

        // Method 2: explorer.exe. UWP apps respond to this.
        @exec('explorer.exe "' . str_replace('"', '', $url) . '"', $o, $code);
        if ($code === 0 || $code === 1) {
            usleep(200_000);
            return true;
        }

        // Method 3: legacy rundll32 (classic WhatsApp installer).
        @exec('rundll32.exe url.dll,FileProtocolHandler "' . str_replace('"', '', $url) . '"', $o2, $code2);
        return $code2 === 0;
    }

    public function clipboardText(string $text): bool
    {
        $tmp = tempnam(sys_get_temp_dir(), 'wa_clip_') . '.txt';
        file_put_contents($tmp, $text);
        $escaped = str_replace("'", "''", $tmp);
        $ok = $this->ps(
            "\$t = [IO.File]::ReadAllText('{$escaped}', [Text.UTF8Encoding]::new(\$false)); " .
            "Set-Clipboard -Value \$t"
        );
        @unlink($tmp);
        return $ok;
    }

    public function clipboardFile(string $absolutePath): bool
    {
        if (!is_file($absolutePath)) return false;
        $escaped = str_replace("'", "''", $absolutePath);
        return $this->ps("Set-Clipboard -Path '{$escaped}'");
    }

    /**
     * Force the WhatsApp window to the foreground so SendKeys reaches it.
     * Tries process names: WhatsApp, WhatsApp.Root, WhatsAppDesktop.
     */
    public function focusWhatsApp(): bool
    {
        $ps = <<<'PS'
Add-Type @"
using System;
using System.Runtime.InteropServices;
public class Win {
    [DllImport("user32.dll")] public static extern bool SetForegroundWindow(IntPtr hWnd);
    [DllImport("user32.dll")] public static extern bool ShowWindow(IntPtr hWnd, int n);
    [DllImport("user32.dll")] public static extern bool IsIconic(IntPtr hWnd);
    [DllImport("user32.dll")] public static extern bool SwitchToThisWindow(IntPtr hWnd, bool alt);
    [DllImport("user32.dll")] public static extern IntPtr GetForegroundWindow();
    [DllImport("user32.dll")] public static extern uint GetWindowThreadProcessId(IntPtr hWnd, out uint pid);
    [DllImport("user32.dll")] public static extern bool AttachThreadInput(uint idAttach, uint idAttachTo, bool fAttach);
    [DllImport("kernel32.dll")] public static extern uint GetCurrentThreadId();
}
"@
$names = @('WhatsApp','WhatsApp.Root','WhatsAppDesktop')
$proc = $null
foreach ($n in $names) {
    $p = Get-Process -Name $n -ErrorAction SilentlyContinue |
         Where-Object { $_.MainWindowHandle -ne 0 } |
         Select-Object -First 1
    if ($p) { $proc = $p; break }
}
if (-not $proc) {
    # Fallback: any process with "WhatsApp" in the window title.
    $proc = Get-Process | Where-Object { $_.MainWindowTitle -match 'WhatsApp' } | Select-Object -First 1
}
if (-not $proc) { Write-Output 'NO_WINDOW'; exit 2 }

$h = $proc.MainWindowHandle
if ([Win]::IsIconic($h)) { [Win]::ShowWindow($h, 9) | Out-Null } # 9 = SW_RESTORE
[Win]::ShowWindow($h, 5) | Out-Null                              # 5 = SW_SHOW

# SetForegroundWindow is unreliable unless our thread is attached to the foreground thread.
$fg = [Win]::GetForegroundWindow()
$pidOut = 0
$fgThread = [Win]::GetWindowThreadProcessId($fg, [ref]$pidOut)
$ourThread = [Win]::GetCurrentThreadId()
[Win]::AttachThreadInput($fgThread, $ourThread, $true) | Out-Null
[Win]::SetForegroundWindow($h) | Out-Null
[Win]::SwitchToThisWindow($h, $true) | Out-Null
[Win]::AttachThreadInput($fgThread, $ourThread, $false) | Out-Null

Start-Sleep -Milliseconds 200
$fgAfter = [Win]::GetForegroundWindow()
if ($fgAfter -eq $h) { Write-Output 'OK'; exit 0 }
Write-Output 'NOT_FG'; exit 3
PS;
        $out = trim($this->psFile($ps));
        return str_contains($out, 'OK');
    }

    public function sendKeys(string $keys): bool
    {
        $escaped = str_replace("'", "''", $keys);
        return $this->ps(
            "Add-Type -AssemblyName System.Windows.Forms; " .
            "[System.Windows.Forms.SendKeys]::SendWait('{$escaped}')"
        );
    }

    public function primeChatInputFocus(): bool { return $this->sendKeys(' {BACKSPACE}'); }
    public function clearInputField(): bool     { return $this->sendKeys('^a{DELETE}'); }
    public function paste(): bool               { return $this->sendKeys('^v'); }
    public function pressEnter(): bool          { return $this->sendKeys('{ENTER}'); }

    public static function cleanPhone(string $raw): string
    {
        $s = preg_replace('/[\s\-\+\(\)]/', '', $raw) ?? '';
        if (strlen($s) === 10 && preg_match('/^0[567]/', $s)) {
            $s = '212' . substr($s, 1);
        }
        return $s;
    }

    /** Moroccan landlines/fax start with 05xxxxxxxx (or 2125xxxxxxxx). */
    public static function isFaxNumber(string $raw): bool
    {
        $s = preg_replace('/[\s\-\+\(\)]/', '', $raw) ?? '';
        if (preg_match('/^2125/', $s)) return true;
        if (strlen($s) === 10 && str_starts_with($s, '05')) return true;
        return false;
    }

    public static function buildWhatsAppUri(string $rawPhone): string
    {
        return 'whatsapp://send?phone=' . self::cleanPhone($rawPhone);
    }
}
