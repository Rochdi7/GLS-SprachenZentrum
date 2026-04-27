<?php
/**
 * WhatsApp Sender — standalone single-file router for XAMPP.
 *
 * Drop this folder into C:\xampp\htdocs\whatsappsender and open
 * http://localhost/whatsappsender/ — no composer, no npm.
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/runtime.php';
require __DIR__ . '/lib/automation.php';

$action = $_GET['action'] ?? 'index';
$id     = (int) ($_GET['id'] ?? 0);

// First-run guard: if no center is selected yet, force the installer to pick one.
// Allow the settings/save endpoints through so the setup screen can submit.
$center = currentCenter();
if (!$center && !in_array($action, ['settings', 'settings_save'], true)) {
    redirect('index.php?action=settings');
}

try {
    match ($action) {
        'index'         => actionIndex(),
        'create'        => actionCreate(),
        'store'         => actionStore(),
        'show'          => actionShow($id),
        'destroy'       => actionDestroy($id),
        'start'         => actionStart($id),
        'status'        => actionStatus($id),
        'log'           => actionLog($id),
        'pause'         => actionControl('pause'),
        'resume'        => actionControl('resume'),
        'stop'          => actionControl('stop'),
        'force_reset'   => actionForceReset(),
        'settings'      => actionSettings(),
        'settings_save' => actionSettingsSave(),
        'export'        => actionExport(),
        default         => actionIndex(),
    };
} catch (Throwable $e) {
    if (isAjax()) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    } else {
        http_response_code(500);
        echo '<h1>Erreur</h1><pre>' . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function isAjax(): bool
{
    return (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}

function json(mixed $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function render(string $view, array $data = []): void
{
    extract($data);
    ob_start();
    require __DIR__ . '/views/' . $view . '.php';
    $content = ob_get_clean();
    $title = $data['title'] ?? 'WhatsApp Sender';
    require __DIR__ . '/views/layout.php';
}

function flash(string $type, string $msg): void { $_SESSION['flash_' . $type] = $msg; }

function redirect(string $url): void { header('Location: ' . $url); exit; }

function assertPost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        if (isAjax()) json(['error' => 'POST required'], 405);
        http_response_code(405); echo 'POST required'; exit;
    }
}

// ---------------------------------------------------------------------------
// Actions
// ---------------------------------------------------------------------------

function actionIndex(): void
{
    $rows = db()->query('SELECT * FROM campaigns ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    render('list', ['campaigns' => $rows, 'title' => 'Campagnes']);
}

function actionCreate(): void
{
    render('create', ['old' => [], 'errors' => [], 'title' => 'Nouvelle campagne']);
}

function actionStore(): void
{
    assertPost();

    $errors = [];
    $name    = trim($_POST['name'] ?? '');
    $message = (string) ($_POST['message'] ?? '');
    $numbers = (string) ($_POST['numbers'] ?? '');
    $minD    = max(30, (int) ($_POST['delay_min']   ?? 45));
    $maxD    = max($minD + 10, (int) ($_POST['delay_max'] ?? 90));
    $launch  = max(3, min(30, (int) ($_POST['launch_wait'] ?? 7)));

    if ($name === '')    $errors[] = 'Nom requis.';
    if ($message === '') $errors[] = 'Message requis.';
    if (trim($numbers) === '') $errors[] = 'Liste de numéros requise.';

    $attachmentPath = null;
    if (!empty($_FILES['attachment']['name'] ?? '') && ($_FILES['attachment']['error'] ?? 0) === UPLOAD_ERR_OK) {
        $file = $_FILES['attachment'];
        if ($file['size'] > 20 * 1024 * 1024) {
            $errors[] = 'Pièce jointe trop lourde (max 20 Mo).';
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf','jpg','jpeg','png','webp','mp4'], true)) {
                $errors[] = 'Type de fichier non autorisé.';
            } else {
                $dir = __DIR__ . '/storage/attachments';
                if (!is_dir($dir)) @mkdir($dir, 0777, true);
                $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $file['name']);
                $target = $dir . DIRECTORY_SEPARATOR . time() . '_' . $safe;
                if (!move_uploaded_file($file['tmp_name'], $target)) {
                    $errors[] = 'Échec de l\'upload.';
                } else {
                    $attachmentPath = $target;
                }
            }
        }
    }

    $recipients = [];
    foreach (preg_split('/\r?\n/', trim($numbers)) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = array_map('trim', explode(',', $line));
        $recipients[] = [
            'phone'    => $parts[0],
            'business' => $parts[1] ?? '',
            'status'   => 'pending',
            'error'    => null,
            'sentAt'   => null,
        ];
    }
    if (!count($recipients)) $errors[] = 'Aucun numéro valide.';

    if ($errors) {
        render('create', ['old' => $_POST, 'errors' => $errors, 'title' => 'Nouvelle campagne']);
        return;
    }

    $center = currentCenter();
    $stmt = db()->prepare("
        INSERT INTO campaigns (center_code, center_name, name, message, attachment_path, status, total, delay_min, delay_max, launch_wait, recipients)
        VALUES (?, ?, ?, ?, ?, 'queued', ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $center['code'] ?? null, $center['name'] ?? null,
        $name, $message, $attachmentPath,
        count($recipients), $minD, $maxD, $launch,
        json_encode($recipients, JSON_UNESCAPED_UNICODE),
    ]);
    $id = (int) db()->lastInsertId();

    // Also write one normalized row per recipient for analytics/exports.
    insertMessagesForCampaign($id, $recipients, $center['code'] ?? null, $center['name'] ?? null);

    flash('success', 'Campagne créée. Cliquez sur "Démarrer" pour lancer l\'envoi.');
    redirect('index.php?action=show&id=' . $id);
}

function actionShow(int $id): void
{
    $campaign = loadCampaign($id);
    if (!$campaign) { http_response_code(404); echo 'Not found'; return; }
    render('show', ['campaign' => $campaign, 'title' => $campaign['name']]);
}

function actionDestroy(int $id): void
{
    assertPost();
    $campaign = loadCampaign($id);
    if (!$campaign) { flash('error', 'Campagne introuvable.'); redirect('index.php'); }

    $s = readStatus();
    $isThis = (int) ($s['campaignId'] ?? 0) === $id;

    if (!empty($s['running']) && $isThis) {
        if (($_POST['force'] ?? '') === '1') {
            writeControl(['paused' => false, 'aborted' => true]);
            clearStatus();
        } else {
            flash('error', 'Impossible de supprimer une campagne en cours. Arrêtez-la d\'abord.');
            redirect('index.php?action=show&id=' . $id);
        }
    } elseif ($isThis) {
        clearStatus();
    }

    if (!empty($campaign['attachment_path']) && is_file($campaign['attachment_path'])) {
        @unlink($campaign['attachment_path']);
    }
    db()->prepare('DELETE FROM campaigns WHERE id = ?')->execute([$id]);
    flash('success', 'Campagne supprimée.');
    redirect('index.php');
}

function actionStart(int $id): void
{
    assertPost();
    $campaign = loadCampaign($id);
    if (!$campaign) json(['error' => 'Campagne introuvable'], 404);

    $status = readStatus();
    if (!empty($status['running'])) {
        json([
            'error' => 'Une campagne est déjà en cours',
            'runningCampaignId' => $status['campaignId'] ?? null,
        ], 409);
    }
    if (!empty($status['stale'])) {
        clearStatus();
        db()->prepare("UPDATE campaigns SET status='paused' WHERE status='running'")->execute();
    }

    $attach = (string) ($campaign['attachment_path'] ?? '');
    if ($attach !== '' && !is_file($attach)) {
        json(['error' => "Fichier non trouvé: {$attach}"], 400);
    }

    $recipients = $campaign['recipients'];
    foreach ($recipients as &$r) {
        if (($r['status'] ?? '') === 'sending') $r['status'] = 'pending';
    }
    unset($r);

    $pending = 0;
    foreach ($recipients as $r) if (($r['status'] ?? '') === 'pending') $pending++;
    if ($pending === 0) json(['error' => 'Aucun message en attente'], 400);

    saveCampaign($id, ['recipients' => $recipients, 'status' => 'queued']);
    $campaign['recipients'] = $recipients;
    $campaign['status']     = 'queued';

    writeStatus([
        'running'        => true,
        'campaignId'     => $id,
        'name'           => $campaign['name'],
        'status'         => 'queued',
        'total'          => (int) $campaign['total'],
        'sent'           => (int) $campaign['sent'],
        'failed'         => (int) $campaign['failed'],
        'pending'        => $pending,
        'current'        => null,
        'nextSendAt'     => null,
        'attachmentPath' => $campaign['attachment_path'],
        'messages'       => $recipients,
    ]);
    writeControl(['paused' => false, 'aborted' => false]);

    try {
        spawnWorker($id);
    } catch (Throwable $e) {
        clearStatus();
        json(['error' => $e->getMessage()], 500);
    }

    json(['started' => true, 'campaignId' => $id, 'pending' => $pending]);
}

function actionStatus(int $id): void
{
    $campaign = loadCampaign($id);
    if (!$campaign) json(['error' => 'Campagne introuvable'], 404);

    $s = readStatus();
    $isThis = (int) ($s['campaignId'] ?? 0) === $id;

    if (!$isThis) {
        $pending = 0;
        foreach ($campaign['recipients'] as $r) if (($r['status'] ?? '') === 'pending') $pending++;
        json([
            'running'    => false,
            'campaignId' => $id,
            'name'       => $campaign['name'],
            'status'     => $campaign['status'],
            'total'      => (int) $campaign['total'],
            'sent'       => (int) $campaign['sent'],
            'failed'     => (int) $campaign['failed'],
            'pending'    => $pending,
            'current'    => null,
            'messages'   => $campaign['recipients'],
        ]);
    }

    $s['messages'] = $campaign['recipients'] ?: ($s['messages'] ?? []);
    if (!empty($s['nextSendAt'])) {
        $s['next_send_in'] = max(0, ((int) $s['nextSendAt']) - time());
    }
    $s['sent']   = (int) $campaign['sent'];
    $s['failed'] = (int) $campaign['failed'];
    $s['total']  = (int) $campaign['total'];

    $control = readControl();
    $s['paused']  = (bool) ($control['paused'] ?? false);
    $s['aborted'] = (bool) ($control['aborted'] ?? false);

    json($s);
}

function actionLog(int $id): void
{
    $path = __DIR__ . '/storage/logs/worker-' . $id . '.log';
    if (!is_file($path)) json(['exists' => false, 'lines' => [], 'size' => 0]);

    $size   = filesize($path);
    $max    = 64 * 1024;
    $offset = max(0, $size - $max);
    $fp = fopen($path, 'rb');
    if ($offset > 0) fseek($fp, $offset);
    $content = fread($fp, $max);
    fclose($fp);
    $lines = preg_split('/\r?\n/', (string) $content);
    $tail = array_slice(array_filter($lines, fn($l) => $l !== ''), -80);
    json(['exists' => true, 'size' => $size, 'lines' => array_values($tail)]);
}

function actionControl(string $mode): void
{
    assertPost();
    $c = readControl();
    switch ($mode) {
        case 'pause':  $c['paused']  = true;  break;
        case 'resume': $c['paused']  = false; break;
        case 'stop':   $c['aborted'] = true; $c['paused'] = false; break;
    }
    writeControl($c);
    json([$mode => true]);
}

function actionForceReset(): void
{
    assertPost();
    clearStatus();
    writeControl(['paused' => false, 'aborted' => false]);
    db()->prepare("UPDATE campaigns SET status='paused' WHERE status='running'")->execute();
    json(['reset' => true]);
}

function actionSettings(): void
{
    $current = currentCenter();
    render('settings', [
        'centers'  => GLS_CENTERS,
        'current'  => $current,
        'firstRun' => $current === null,
        'title'    => 'Paramètres du centre',
    ]);
}

function actionSettingsSave(): void
{
    assertPost();
    $code = (string) ($_POST['center_code'] ?? '');
    if (!isset(GLS_CENTERS[$code])) {
        flash('error', 'Centre invalide.');
        redirect('index.php?action=settings');
    }
    $isFirstTime = currentCenter() === null;
    settingSet('center_code', $code);
    settingSet('center_name', GLS_CENTERS[$code]);
    if ($isFirstTime) settingSet('installed_at', gmdate('c'));

    flash('success', 'Centre enregistré : ' . GLS_CENTERS[$code]);
    redirect('index.php');
}

/**
 * Export this center's campaigns + messages as two CSVs inside a ZIP.
 * The ZIP filename embeds the center code and date so the HQ merge tool can
 * consume all 7 center exports in one pass.
 */
function actionExport(): void
{
    $center = currentCenter();
    $code   = $center['code'] ?? 'unknown';
    $name   = $center['name'] ?? 'Unknown';
    $stamp  = date('Ymd_His');

    $tmp = tempnam(sys_get_temp_dir(), 'gls_export_') . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        http_response_code(500);
        echo 'Impossible de créer le fichier export.';
        return;
    }

    // campaigns.csv
    $campaignsCsv = fopen('php://temp', 'w+');
    fputcsv($campaignsCsv, [
        'center_code', 'center_name', 'id', 'name', 'message', 'attachment_path',
        'status', 'total', 'sent', 'failed', 'delay_min', 'delay_max',
        'launch_wait', 'started_at', 'finished_at', 'created_at',
    ]);
    $rows = db()->query("SELECT * FROM campaigns ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        fputcsv($campaignsCsv, [
            $r['center_code'] ?? $code,
            $r['center_name'] ?? $name,
            $r['id'], $r['name'], $r['message'], $r['attachment_path'],
            $r['status'], $r['total'], $r['sent'], $r['failed'],
            $r['delay_min'], $r['delay_max'], $r['launch_wait'],
            $r['started_at'], $r['finished_at'], $r['created_at'],
        ]);
    }
    rewind($campaignsCsv);
    $zip->addFromString('campaigns.csv', stream_get_contents($campaignsCsv));
    fclose($campaignsCsv);

    // messages.csv
    $messagesCsv = fopen('php://temp', 'w+');
    fputcsv($messagesCsv, [
        'center_code', 'center_name', 'id', 'campaign_id', 'phone', 'business',
        'status', 'error', 'attempts', 'sent_at', 'created_at',
    ]);
    $rows = db()->query("SELECT * FROM messages ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        fputcsv($messagesCsv, [
            $r['center_code'] ?? $code,
            $r['center_name'] ?? $name,
            $r['id'], $r['campaign_id'], $r['phone'], $r['business'],
            $r['status'], $r['error'], $r['attempts'], $r['sent_at'], $r['created_at'],
        ]);
    }
    rewind($messagesCsv);
    $zip->addFromString('messages.csv', stream_get_contents($messagesCsv));
    fclose($messagesCsv);

    // manifest.json — identifies the source center for the HQ merge tool.
    $manifest = [
        'center_code'  => $code,
        'center_name'  => $name,
        'exported_at'  => gmdate('c'),
        'installed_at' => settingGet('installed_at'),
        'campaigns'    => (int) db()->query("SELECT COUNT(*) FROM campaigns")->fetchColumn(),
        'messages'     => (int) db()->query("SELECT COUNT(*) FROM messages")->fetchColumn(),
    ];
    $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $zip->close();

    $filename = "gls_whatsapp_{$code}_{$stamp}.zip";
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tmp));
    readfile($tmp);
    @unlink($tmp);
    exit;
}

// ---------------------------------------------------------------------------
// Worker spawn (multi-strategy — whichever PHP function isn't disabled wins)
// ---------------------------------------------------------------------------

function spawnWorker(int $campaignId): void
{
    $workerLog = __DIR__ . '/storage/logs/worker-' . $campaignId . '.log';
    $spawnLog  = __DIR__ . '/storage/logs/spawn.log';
    $batPath   = __DIR__ . '/storage/logs/run-' . $campaignId . '.bat';

    $dir = dirname($workerLog);
    if (!is_dir($dir)) @mkdir($dir, 0777, true);

    // Locate the PHP CLI binary. IMPORTANT: under XAMPP's Apache module,
    // PHP_BINARY points at httpd.exe — we must NOT use that. Search known
    // locations instead.
    $php = findPhpCli();

    $worker = __DIR__ . '/worker.php';

    $bat  = "@echo off\r\n";
    $bat .= '"' . $php . '" "' . $worker . '" ' . $campaignId
          . ' > "' . $workerLog . '" 2>&1' . "\r\n";
    file_put_contents($batPath, $bat);

    $cmd    = 'cmd /c start "" /B "' . $batPath . '"';
    $method = null;
    $err    = null;

    try {
        if (funcEnabled('proc_open')) {
            $p = @proc_open($cmd, [
                0 => ['pipe', 'r'],
                1 => ['file', 'NUL', 'w'],
                2 => ['file', 'NUL', 'w'],
            ], $pipes, null, null, ['bypass_shell' => true]);
            if (is_resource($p)) {
                foreach ($pipes as $pipe) if (is_resource($pipe)) fclose($pipe);
                proc_close($p);
                $method = 'proc_open';
            }
        }

        if ($method === null && class_exists('COM')) {
            try {
                $shell = new COM('WScript.Shell');
                $shell->Run($cmd, 0, false);
                $method = 'wscript.shell';
            } catch (Throwable $e) { $err = 'COM failed: ' . $e->getMessage(); }
        }

        if ($method === null && funcEnabled('popen')) {
            $h = @popen($cmd, 'r');
            if (is_resource($h)) { pclose($h); $method = 'popen'; }
        }

        if ($method === null && funcEnabled('exec')) {
            @exec($cmd . ' > NUL 2>&1');
            $method = 'exec';
        }
    } catch (Throwable $e) {
        $err = $e->getMessage();
    }

    file_put_contents(
        $spawnLog,
        sprintf("[%s] spawn %s via=%s err=%s\n  php=%s\n  bat=%s\n  worker-log=%s\n",
            gmdate('c'), $campaignId, $method ?? 'NONE', $err ?? '-', $php, $batPath, $workerLog),
        FILE_APPEND | LOCK_EX
    );

    if ($method === null) {
        throw new RuntimeException(
            'Impossible de démarrer le worker : toutes les méthodes de spawn sont bloquées ' .
            '(proc_open/COM/popen/exec). Vérifiez php.ini → disable_functions.'
        );
    }
}

function funcEnabled(string $fn): bool
{
    if (!function_exists($fn)) return false;
    $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
    return !in_array($fn, $disabled, true);
}

/**
 * Locate the PHP CLI executable on a Windows XAMPP / WAMP / standalone
 * install. Under Apache mod_php, PHP_BINARY points at httpd.exe — useless
 * for spawning CLI scripts — so we try likely paths in order.
 */
function findPhpCli(): string
{
    // Env override always wins (set PHP_CLI=C:\path\to\php.exe in httpd.conf).
    $env = getenv('PHP_CLI');
    if ($env && is_file($env)) return $env;

    $candidates = [];

    // 1. Next to PHP_BINARY (works when PHP_BINARY *is* already php.exe/php-cgi.exe).
    $binDir = dirname(PHP_BINARY);
    $candidates[] = $binDir . DIRECTORY_SEPARATOR . 'php.exe';

    // 2. XAMPP default: C:\xampp\php\php.exe (when PHP_BINARY is httpd.exe).
    $binBase = basename($binDir);                // e.g. "bin"
    $apacheDir = dirname($binDir);               // e.g. "C:\xampp\apache"
    $xamppRoot = dirname($apacheDir);            // e.g. "C:\xampp"
    if (strcasecmp($binBase, 'bin') === 0) {
        $candidates[] = $xamppRoot . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'php.exe';
    }

    // 3. Common fixed locations.
    $candidates[] = 'C:\\xampp\\php\\php.exe';
    $candidates[] = 'C:\\wamp64\\bin\\php\\php.exe';
    $candidates[] = 'C:\\Program Files\\PHP\\php.exe';
    $candidates[] = 'C:\\php\\php.exe';

    foreach ($candidates as $c) {
        if (is_file($c)) return $c;
    }

    // 4. Fall back — better than nothing.
    return PHP_BINARY;
}
