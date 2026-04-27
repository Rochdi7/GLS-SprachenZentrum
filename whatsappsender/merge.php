<?php
/**
 * GLS WhatsApp Sender — HQ consolidation tool.
 *
 * Usage (at the head office):
 *   1. Collect the ZIP export from each of the 7 centers (produced by the
 *      "Export" button in the app).
 *   2. Drop them all into the same folder as this script (or into ./exports/).
 *   3. Open http://localhost/whatsappsender/merge.php
 *
 * Produces:
 *   - Consolidated SQLite database at storage/merged.sqlite
 *   - HTML dashboard with per-center stats, rankings, delivery rates, etc.
 *
 * Nothing on any center is modified. This script only reads the uploaded
 * ZIPs and writes to its own merged.sqlite.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(300);

$root       = __DIR__;
$exportsDir = is_dir($root . '/exports') ? $root . '/exports' : $root;
$mergedPath = $root . '/storage/merged.sqlite';

// Rebuild on ?rebuild=1 (or if file missing).
$rebuild = isset($_GET['rebuild']) || !is_file($mergedPath);

if ($rebuild) {
    @unlink($mergedPath);
    if (!is_dir(dirname($mergedPath))) @mkdir(dirname($mergedPath), 0777, true);

    $pdo = new PDO('sqlite:' . $mergedPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA journal_mode = WAL');

    $pdo->exec("CREATE TABLE campaigns (
        center_code TEXT, center_name TEXT, local_id INTEGER,
        name TEXT, message TEXT, attachment_path TEXT, status TEXT,
        total INTEGER, sent INTEGER, failed INTEGER,
        delay_min INTEGER, delay_max INTEGER, launch_wait INTEGER,
        started_at TEXT, finished_at TEXT, created_at TEXT,
        PRIMARY KEY (center_code, local_id)
    )");
    $pdo->exec("CREATE INDEX mc_center ON campaigns(center_code)");
    $pdo->exec("CREATE INDEX mc_created ON campaigns(created_at)");

    $pdo->exec("CREATE TABLE messages (
        center_code TEXT, center_name TEXT, local_id INTEGER,
        campaign_id INTEGER, phone TEXT, business TEXT,
        status TEXT, error TEXT, attempts INTEGER,
        sent_at TEXT, created_at TEXT,
        PRIMARY KEY (center_code, local_id)
    )");
    $pdo->exec("CREATE INDEX mm_center ON messages(center_code)");
    $pdo->exec("CREATE INDEX mm_campaign ON messages(campaign_id)");
    $pdo->exec("CREATE INDEX mm_status ON messages(status)");
    $pdo->exec("CREATE INDEX mm_sent ON messages(sent_at)");

    $pdo->exec("CREATE TABLE sources (
        center_code TEXT PRIMARY KEY,
        center_name TEXT, installed_at TEXT, exported_at TEXT,
        campaigns_count INTEGER, messages_count INTEGER,
        file TEXT, imported_at TEXT
    )");

    $loaded = loadZips($pdo, $exportsDir);
} else {
    $pdo = new PDO('sqlite:' . $mergedPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $loaded = [];
}

$sources   = $pdo->query("SELECT * FROM sources ORDER BY center_name")->fetchAll(PDO::FETCH_ASSOC);
$perCenter = $pdo->query("
    SELECT
        center_code,
        COALESCE(center_name, center_code) AS center_name,
        COUNT(*) AS campaigns,
        SUM(total)  AS total_msgs,
        SUM(sent)   AS sent_msgs,
        SUM(failed) AS failed_msgs,
        MAX(created_at) AS last_campaign_at
    FROM campaigns
    GROUP BY center_code, center_name
    ORDER BY sent_msgs DESC
")->fetchAll(PDO::FETCH_ASSOC);

$totals = $pdo->query("
    SELECT
        COUNT(DISTINCT center_code) AS centers,
        COUNT(*) AS campaigns,
        SUM(total)  AS total_msgs,
        SUM(sent)   AS sent_msgs,
        SUM(failed) AS failed_msgs
    FROM campaigns
")->fetch(PDO::FETCH_ASSOC);

$topErrors = $pdo->query("
    SELECT error, COUNT(*) AS n
    FROM messages
    WHERE status = 'failed' AND error IS NOT NULL AND error != ''
    GROUP BY error ORDER BY n DESC LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

$recentCampaigns = $pdo->query("
    SELECT center_name, name, status, total, sent, failed, created_at
    FROM campaigns ORDER BY created_at DESC LIMIT 30
")->fetchAll(PDO::FETCH_ASSOC);


// ---------------------------------------------------------------------------
function loadZips(PDO $pdo, string $dir): array
{
    $loaded = [];
    $files  = glob($dir . '/gls_whatsapp_*.zip') ?: [];
    $pdo->beginTransaction();
    try {
        foreach ($files as $file) {
            $zip = new ZipArchive();
            if ($zip->open($file) !== true) continue;

            $manifest = json_decode($zip->getFromName('manifest.json') ?: '{}', true) ?: [];
            $code = (string) ($manifest['center_code'] ?? basename($file));
            $name = (string) ($manifest['center_name'] ?? $code);

            $pdo->prepare("INSERT OR REPLACE INTO sources
                (center_code, center_name, installed_at, exported_at, campaigns_count, messages_count, file, imported_at)
                VALUES (?,?,?,?,?,?,?,?)")->execute([
                $code, $name,
                $manifest['installed_at'] ?? null,
                $manifest['exported_at'] ?? null,
                (int) ($manifest['campaigns'] ?? 0),
                (int) ($manifest['messages'] ?? 0),
                basename($file),
                gmdate('c'),
            ]);

            // campaigns.csv
            $csv = $zip->getFromName('campaigns.csv');
            if ($csv !== false) {
                $rows = parseCsv($csv);
                $ins = $pdo->prepare("INSERT OR REPLACE INTO campaigns
                    (center_code, center_name, local_id, name, message, attachment_path, status,
                     total, sent, failed, delay_min, delay_max, launch_wait,
                     started_at, finished_at, created_at)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                foreach ($rows as $r) {
                    $ins->execute([
                        $r['center_code'] ?: $code,
                        $r['center_name'] ?: $name,
                        (int) $r['id'],
                        $r['name'], $r['message'], $r['attachment_path'], $r['status'],
                        (int) $r['total'], (int) $r['sent'], (int) $r['failed'],
                        (int) $r['delay_min'], (int) $r['delay_max'], (int) $r['launch_wait'],
                        $r['started_at'], $r['finished_at'], $r['created_at'],
                    ]);
                }
            }

            // messages.csv
            $csv = $zip->getFromName('messages.csv');
            if ($csv !== false) {
                $rows = parseCsv($csv);
                $ins = $pdo->prepare("INSERT OR REPLACE INTO messages
                    (center_code, center_name, local_id, campaign_id, phone, business,
                     status, error, attempts, sent_at, created_at)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                foreach ($rows as $r) {
                    $ins->execute([
                        $r['center_code'] ?: $code,
                        $r['center_name'] ?: $name,
                        (int) $r['id'],
                        (int) $r['campaign_id'],
                        $r['phone'], $r['business'],
                        $r['status'], $r['error'], (int) $r['attempts'],
                        $r['sent_at'], $r['created_at'],
                    ]);
                }
            }

            $zip->close();
            $loaded[] = basename($file);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    return $loaded;
}

function parseCsv(string $content): array
{
    $fp = fopen('php://temp', 'r+');
    fwrite($fp, $content);
    rewind($fp);
    $header = fgetcsv($fp);
    $rows = [];
    while (($row = fgetcsv($fp)) !== false) {
        if (count($row) !== count($header)) continue;
        $rows[] = array_combine($header, $row);
    }
    fclose($fp);
    return $rows;
}

function fmt(?int $n): string { return number_format((int) $n, 0, ',', ' '); }
function pct(int $num, int $den): string { return $den > 0 ? sprintf('%.1f %%', $num / $den * 100) : '—'; }
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Consolidation WhatsApp · GLS HQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,600,700,900&display=swap" rel="stylesheet">
    <style>
        :root {
            --font-body: 'Inter', system-ui, sans-serif;
            --font-heading: 'Satoshi', 'Inter', system-ui, sans-serif;
        }
        * { -webkit-font-smoothing: antialiased; }
        body { background:#f4f5f8; font-family: var(--font-body); color:#14181f; }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); font-weight:700; letter-spacing:-.015em; }
        .card { border:1px solid #e9ebef; border-radius:14px; box-shadow:0 1px 2px rgba(16,24,40,.04); background:#fff; }
        .kpi .val { font-family: var(--font-heading); font-size:1.9rem; font-weight:900; letter-spacing:-.025em; }
        .kpi .lbl { text-transform:uppercase; font-size:.7rem; letter-spacing:.08em; color:#6b7280; font-weight:700; }
        .rank { font-family: var(--font-heading); font-weight:900; font-size:1.1rem; color:#6b7280; width:32px; text-align:center; }
        .bar { height:8px; background:#eef0f4; border-radius:999px; overflow:hidden; }
        .bar > span { display:block; height:100%; background:linear-gradient(90deg,#25D366,#128C7E); transition:width .4s ease; }
        code { background:#f3f4f6; padding:2px 6px; border-radius:4px; font-size:.85em; font-family: 'JetBrains Mono', Consolas, monospace; }
        .table thead th { font-size:.72rem; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; font-weight:700; }
    </style>
</head>
<body>

<header class="bg-white border-bottom py-3 mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <h4 class="m-0"><i class="bi bi-diagram-3 text-success"></i> Consolidation WhatsApp · <span class="text-muted">GLS HQ</span></h4>
        <a href="?rebuild=1" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-clockwise"></i> Recharger les exports
        </a>
    </div>
</header>

<main class="container pb-5">

<?php if ($rebuild): ?>
    <?php if (empty($loaded)): ?>
        <div class="alert alert-warning">
            <strong>Aucun export trouvé.</strong><br>
            Déposez les fichiers <code>gls_whatsapp_*.zip</code> (un par centre) dans le dossier
            <code><?= htmlspecialchars(basename($exportsDir)) ?>/</code> puis rechargez cette page.
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            <strong><?= count($loaded) ?> export(s) chargés :</strong>
            <?= implode(', ', array_map(fn($f) => '<code>'.htmlspecialchars($f).'</code>', $loaded)) ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card kpi p-3"><div class="lbl">Centres</div><div class="val text-success"><?= fmt((int) $totals['centers']) ?>/7</div></div></div>
    <div class="col-md-3"><div class="card kpi p-3"><div class="lbl">Campagnes</div><div class="val"><?= fmt((int) $totals['campaigns']) ?></div></div></div>
    <div class="col-md-3"><div class="card kpi p-3"><div class="lbl">Messages envoyés</div><div class="val text-success"><?= fmt((int) $totals['sent_msgs']) ?></div></div></div>
    <div class="col-md-3"><div class="card kpi p-3"><div class="lbl">Échecs</div><div class="val text-danger"><?= fmt((int) $totals['failed_msgs']) ?></div></div></div>
</div>

<!-- Per-center ranking -->
<div class="card mb-4">
    <div class="card-header bg-white"><h5 class="m-0"><i class="bi bi-trophy"></i> Classement par centre</h5></div>
    <div class="card-body p-0">
        <table class="table m-0 align-middle">
            <thead class="small text-muted">
                <tr>
                    <th></th><th>Centre</th><th class="text-end">Campagnes</th>
                    <th class="text-end">Envoyés</th><th class="text-end">Échecs</th>
                    <th class="text-end">Taux</th>
                    <th style="width:200px">Part d'envois</th>
                    <th>Dernière campagne</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $maxSent = max(array_column($perCenter, 'sent_msgs') ?: [1]);
            foreach ($perCenter as $i => $r):
                $sent = (int) $r['sent_msgs']; $failed = (int) $r['failed_msgs'];
                $width = $maxSent > 0 ? round($sent / $maxSent * 100) : 0;
            ?>
                <tr>
                    <td class="rank">#<?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($r['center_name']) ?></strong>
                        <div class="text-muted small"><?= htmlspecialchars($r['center_code']) ?></div></td>
                    <td class="text-end"><?= fmt((int) $r['campaigns']) ?></td>
                    <td class="text-end text-success fw-bold"><?= fmt($sent) ?></td>
                    <td class="text-end text-danger"><?= fmt($failed) ?></td>
                    <td class="text-end"><?= pct($sent, $sent + $failed) ?></td>
                    <td><div class="bar"><span style="width:<?= $width ?>%"></span></div></td>
                    <td class="small text-muted"><?= htmlspecialchars((string) $r['last_campaign_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4">
    <!-- Sources ingested -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white"><h6 class="m-0"><i class="bi bi-hdd-stack"></i> Exports reçus</h6></div>
            <div class="card-body p-0">
                <?php if (!$sources): ?>
                    <p class="text-muted p-3 m-0">Aucun fichier.</p>
                <?php else: ?>
                <table class="table m-0 small">
                    <thead><tr><th>Centre</th><th>Exporté le</th><th>Campagnes</th><th>Messages</th></tr></thead>
                    <tbody>
                    <?php foreach ($sources as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['center_name']) ?></strong></td>
                            <td class="text-muted"><?= htmlspecialchars((string) $s['exported_at']) ?></td>
                            <td><?= fmt((int) $s['campaigns_count']) ?></td>
                            <td><?= fmt((int) $s['messages_count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top failure reasons -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white"><h6 class="m-0"><i class="bi bi-exclamation-diamond"></i> Causes d'échec les plus fréquentes</h6></div>
            <div class="card-body p-0">
                <?php if (!$topErrors): ?>
                    <p class="text-muted p-3 m-0">Aucun échec enregistré.</p>
                <?php else: ?>
                <table class="table m-0 small">
                    <thead><tr><th>Erreur</th><th class="text-end">Occurrences</th></tr></thead>
                    <tbody>
                    <?php foreach ($topErrors as $e): ?>
                        <tr>
                            <td class="text-truncate" style="max-width:400px" title="<?= htmlspecialchars((string) $e['error']) ?>">
                                <?= htmlspecialchars((string) $e['error']) ?>
                            </td>
                            <td class="text-end fw-bold"><?= fmt((int) $e['n']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent campaigns across all centers -->
<div class="card mt-4">
    <div class="card-header bg-white"><h6 class="m-0"><i class="bi bi-clock-history"></i> 30 dernières campagnes (tous centres)</h6></div>
    <div class="card-body p-0">
        <table class="table m-0 small align-middle">
            <thead><tr><th>Créée le</th><th>Centre</th><th>Nom</th><th>Statut</th><th class="text-end">Total</th><th class="text-end">Envoyés</th><th class="text-end">Échecs</th></tr></thead>
            <tbody>
            <?php foreach ($recentCampaigns as $c): ?>
                <tr>
                    <td class="text-muted"><?= htmlspecialchars((string) $c['created_at']) ?></td>
                    <td><?= htmlspecialchars((string) $c['center_name']) ?></td>
                    <td><strong><?= htmlspecialchars((string) $c['name']) ?></strong></td>
                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars((string) $c['status']) ?></span></td>
                    <td class="text-end"><?= fmt((int) $c['total']) ?></td>
                    <td class="text-end text-success"><?= fmt((int) $c['sent']) ?></td>
                    <td class="text-end text-danger"><?= fmt((int) $c['failed']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<p class="text-muted small mt-4">
    Base consolidée : <code><?= htmlspecialchars($mergedPath) ?></code> —
    vous pouvez l'ouvrir avec DB Browser for SQLite pour des requêtes personnalisées.
</p>

</main>
</body>
</html>
