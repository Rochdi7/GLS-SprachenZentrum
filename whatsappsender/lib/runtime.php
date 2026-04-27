<?php
/**
 * Status + control files, with stale-lock detection identical to the Laravel
 * version. Shared between the web layer and the worker.
 */

const STALE_AFTER_SECONDS = 180;

function statusPath(): string  { return __DIR__ . '/../storage/status.json'; }
function controlPath(): string { return __DIR__ . '/../storage/control.json'; }

function readStatus(): array
{
    if (!is_file(statusPath())) return ['running' => false];
    $raw = @file_get_contents(statusPath());
    $d = $raw ? json_decode($raw, true) : null;
    if (!is_array($d)) return ['running' => false];

    if (!empty($d['running'])) {
        $last = (int) ($d['lastTick'] ?? 0);
        if ($last > 0 && (time() - $last) > STALE_AFTER_SECONDS) {
            $d['running'] = false;
            $d['stale']   = true;
        }
    }
    return $d;
}

function writeStatus(array $status): void
{
    $dir = dirname(statusPath());
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
    $status['lastTick'] = time();
    file_put_contents(statusPath(), json_encode($status, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function clearStatus(): void
{
    @unlink(statusPath());
}

function readControl(): array
{
    if (!is_file(controlPath())) return ['paused' => false, 'aborted' => false];
    $raw = @file_get_contents(controlPath());
    $d = $raw ? json_decode($raw, true) : null;
    return is_array($d)
        ? $d + ['paused' => false, 'aborted' => false]
        : ['paused' => false, 'aborted' => false];
}

function writeControl(array $control): void
{
    $dir = dirname(controlPath());
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
    file_put_contents(controlPath(), json_encode($control), LOCK_EX);
}
