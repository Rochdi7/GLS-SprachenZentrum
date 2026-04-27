<?php
/**
 * WhatsApp Desktop worker. Runs as a detached CLI process spawned by
 * index.php when the user clicks "Démarrer".
 *
 * Usage: php worker.php <campaign_id>
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "CLI only";
    exit(1);
}

require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/runtime.php';
require __DIR__ . '/lib/automation.php';

$id = (int) ($argv[1] ?? 0);
if ($id <= 0) {
    fwrite(STDERR, "Usage: php worker.php <campaign_id>\n");
    exit(1);
}

$campaign = loadCampaign($id);
if (!$campaign) {
    fwrite(STDERR, "Campaign {$id} not found\n");
    exit(1);
}

$auto = new Automation();

writeControl(['paused' => false, 'aborted' => false]);
saveCampaign($id, ['status' => 'running', 'started_at' => date('c')]);
$campaign['status'] = 'running';
tick($campaign, null);

// Make sure WhatsApp Desktop has a real window before we start. If the app is
// minimised to tray / running as a UWP background tile, SendKeys will fail.
echo "Launching WhatsApp Desktop…\n";
if (!$auto->ensureWhatsAppWindow(15)) {
    echo "FATAL: WhatsApp Desktop window could not be opened. Installed?\n";
    saveCampaign($id, ['status' => 'paused']);
    writeStatus(['running' => false, 'campaignId' => $id, 'status' => 'paused']);
    writeControl(['paused' => false, 'aborted' => false]);
    exit(2);
}
echo "WhatsApp window ready.\n";

$minD = max(30, (int) $campaign['delay_min']);
$maxD = max($minD + 10, (int) $campaign['delay_max']);
$launchWait = max(3, (int) $campaign['launch_wait']);
$attachPath = (string) ($campaign['attachment_path'] ?? '');
$message    = (string) $campaign['message'];

$recipients = $campaign['recipients'];
$total = count($recipients);

for ($i = 0; $i < $total; $i++) {
    $control = readControl();
    if (!empty($control['aborted'])) { echo "Aborted\n"; break; }
    while (!empty($control['paused']) && empty($control['aborted'])) {
        usleep(500_000);
        $control = readControl();
    }
    if (!empty($control['aborted'])) break;

    $entry = $recipients[$i];
    $status = $entry['status'] ?? 'pending';
    if (in_array($status, ['sent', 'failed', 'skipped'], true)) continue;

    $phone = (string) ($entry['phone'] ?? '');

    // Count duplicates of this phone *before* the current index, so we pass
    // the correct ordinal to updateMessageStatus() and don't overwrite an
    // earlier recipient with the same number.
    $ordinal = 0;
    for ($k = 0; $k < $i; $k++) {
        if (($recipients[$k]['phone'] ?? '') === $phone) $ordinal++;
    }

    if (Automation::isFaxNumber($phone)) {
        $recipients[$i]['status'] = 'skipped';
        $recipients[$i]['error']  = 'fax/landline (no WhatsApp)';
        $campaign['failed']++;
        $campaign['recipients'] = $recipients;
        saveCampaign($id, ['failed' => $campaign['failed'], 'recipients' => $recipients]);
        updateMessageStatus($id, $ordinal, $phone, 'skipped', 'fax/landline (no WhatsApp)');
        continue;
    }

    $recipients[$i]['status'] = 'sending';
    $campaign['recipients'] = $recipients;
    saveCampaign($id, ['recipients' => $recipients]);
    updateMessageStatus($id, $ordinal, $phone, 'sending');
    tick($campaign, $phone);
    echo sprintf("[%d/%d] -> %s\n", $i + 1, $total, $phone);

    try {
        $filledMessage = fillPlaceholders($message, $entry);

        if ($attachPath !== '') {
            if (!$auto->clipboardFile($attachPath)) throw new RuntimeException('file clipboard failed');
        } else {
            if (!$auto->clipboardText($filledMessage)) throw new RuntimeException('clipboard text failed');
        }

        // Safety: WhatsApp may have been closed between sends. Re-open if needed.
        if (!$auto->hasWhatsAppWindow()) {
            if (!$auto->ensureWhatsAppWindow(10)) {
                throw new RuntimeException('WhatsApp Desktop window disappeared and could not be re-opened');
            }
        }

        if (!$auto->launchUrl(Automation::buildWhatsAppUri($phone))) {
            throw new RuntimeException('URI launch failed — whatsapp:// protocol handler missing');
        }

        sleep($launchWait);

        // Force the WhatsApp window to the foreground. Retry up to 8 times
        // with a 1s gap — on a cold start the chat view can take a few
        // seconds to render after the URI activation.
        $focused = false;
        for ($attempt = 0; $attempt < 8 && !$focused; $attempt++) {
            if ($auto->focusWhatsApp()) { $focused = true; break; }
            sleep(1);
        }
        if (!$focused) {
            throw new RuntimeException('WhatsApp window could not be focused — keep WhatsApp Desktop visible and do not click away while the campaign runs');
        }
        usleep(700_000);

        if (!$auto->primeChatInputFocus()) throw new RuntimeException('prime focus failed');
        usleep(400_000);
        if (!$auto->clearInputField()) throw new RuntimeException('clear input failed');
        usleep(300_000);
        if (!$auto->paste()) throw new RuntimeException('paste failed');

        if ($attachPath !== '') {
            sleep(2);
            if (!$auto->clipboardText($filledMessage)) throw new RuntimeException('caption clipboard failed');
            usleep(300_000);
            if (!$auto->paste()) throw new RuntimeException('caption paste failed');
            usleep(800_000);
        } else {
            usleep(800_000);
        }

        // Re-focus WhatsApp right before Enter — browsers / other apps often
        // steal focus during the paste wait, which swallows the Enter.
        $auto->focusWhatsApp();
        usleep(250_000);
        if (!$auto->pressEnter()) throw new RuntimeException('enter failed');
        // Small wait + a second Enter as insurance for slow UWP renders.
        usleep(500_000);

        $sentAtIso = gmdate('c');
        $recipients[$i]['status'] = 'sent';
        $recipients[$i]['sentAt'] = $sentAtIso;
        $campaign['sent']++;
        updateMessageStatus($id, $ordinal, $phone, 'sent', null, $sentAtIso);
    } catch (Throwable $e) {
        $recipients[$i]['status'] = 'failed';
        $recipients[$i]['error']  = $e->getMessage();
        $campaign['failed']++;
        updateMessageStatus($id, $ordinal, $phone, 'failed', $e->getMessage());
    }

    $campaign['recipients'] = $recipients;
    saveCampaign($id, [
        'sent'       => $campaign['sent'],
        'failed'     => $campaign['failed'],
        'recipients' => $recipients,
    ]);

    $pending = 0;
    foreach ($recipients as $r) if (($r['status'] ?? '') === 'pending') $pending++;
    if ($pending > 0) {
        $wait = random_int($minD, $maxD);
        $nextAt = time() + $wait;
        tick($campaign, null, $nextAt);
        echo "  wait {$wait}s\n";

        $wasPaused = false;
        $pauseLogStart = 0;
        $lastTickAt = time();
        $lastPauseBump = 0;

        while (time() < $nextAt) {
            $c = readControl();
            if (!empty($c['aborted'])) break 2;

            if (!empty($c['paused'])) {
                // On entering pause, freeze "remaining" = nextAt - now.
                if (!$wasPaused) {
                    $wasPaused = true;
                    $pauseLogStart = time();
                    // Write PAUSED status so UI shows it immediately.
                    tick($campaign, null, $nextAt);
                }
                // Bump nextAt by exactly the elapsed wall-clock second so
                // the remaining countdown stays flat. (time() has 1s
                // granularity, so this runs at most once per second.)
                $now = time();
                if ($lastPauseBump === 0) $lastPauseBump = $now;
                if ($now > $lastPauseBump) {
                    $nextAt += ($now - $lastPauseBump);
                    $lastPauseBump = $now;
                }
                // Re-tick every 2s so the UI's next_send_in stays flat.
                if ($now - $lastTickAt >= 2) {
                    tick($campaign, null, $nextAt);
                    $lastTickAt = $now;
                }
                usleep(500_000);
                continue;
            }

            // Just resumed: one tick so UI updates the countdown anchor.
            if ($wasPaused) {
                $wasPaused = false;
                $lastPauseBump = 0;
                echo "  resumed after " . (time() - $pauseLogStart) . "s pause\n";
                tick($campaign, null, $nextAt);
                $lastTickAt = time();
            }

            // Refresh the tick every 5s during normal wait so the UI stays
            // in sync even if the page was opened mid-wait.
            if (time() - $lastTickAt >= 5) {
                tick($campaign, null, $nextAt);
                $lastTickAt = time();
            }
            usleep(500_000);
        }
    }
}

$pending = 0;
foreach ($recipients as $r) if (($r['status'] ?? '') === 'pending') $pending++;
$control = readControl();
if (!empty($control['aborted'])) {
    $finalStatus = 'stopped';
} elseif ($pending > 0) {
    $finalStatus = 'paused';
} else {
    $finalStatus = 'completed';
}

$update = ['status' => $finalStatus, 'recipients' => $recipients];
if ($finalStatus === 'completed') $update['finished_at'] = date('c');
saveCampaign($id, $update);

writeStatus([
    'running'    => false,
    'campaignId' => $id,
    'status'     => $finalStatus,
]);
writeControl(['paused' => false, 'aborted' => false]);

echo "Finished: {$finalStatus}\n";
exit(0);

function fillPlaceholders(string $tmpl, array $entry): string
{
    $business = (string) ($entry['business'] ?? ($entry['name'] ?? $entry['phone'] ?? ''));
    $phone    = (string) ($entry['phone'] ?? '');
    return str_replace(
        ['{business}', '{phone}', '{name}'],
        [$business, $phone, $business],
        $tmpl
    );
}

function tick(array $campaign, ?string $currentPhone, ?int $nextSendAt = null): void
{
    $recipients = $campaign['recipients'] ?? [];
    $pending = 0;
    foreach ($recipients as $r) if (($r['status'] ?? '') === 'pending') $pending++;
    writeStatus([
        'running'        => true,
        'campaignId'     => (int) $campaign['id'],
        'name'           => $campaign['name'],
        'status'         => $campaign['status'],
        'total'          => (int) $campaign['total'],
        'sent'           => (int) $campaign['sent'],
        'failed'         => (int) $campaign['failed'],
        'pending'        => $pending,
        'current'        => $currentPhone,
        'nextSendAt'     => $nextSendAt,
        'attachmentPath' => $campaign['attachment_path'],
        'messages'       => $recipients,
    ]);
}
