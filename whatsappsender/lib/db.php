<?php
/**
 * SQLite connection + schema bootstrap.
 * Auto-creates the database and tables on first request.
 * Runs in-place migrations for older DBs so upgrades are safe.
 */

/** Fixed list of GLS centers. Installer picks one; used as a foreign tag on every campaign/message row. */
const GLS_CENTERS = [
    'kenitra'    => 'Kenitra',
    'marrakech'  => 'Marrakech',
    'sale'       => 'Salé',
    'rabat'      => 'Rabat',
    'casablanca' => 'Casablanca',
    'agadir'     => 'Agadir',
    'online'     => 'Online',
];

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;

    $path = __DIR__ . '/../storage/db.sqlite';
    $fresh = !is_file($path);

    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA foreign_keys = ON');

    if ($fresh) {
        createSchema($pdo);
    } else {
        migrateSchema($pdo);
    }

    return $pdo;
}

function createSchema(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE settings (
            key   TEXT PRIMARY KEY,
            value TEXT NOT NULL
        )
    ");

    $pdo->exec("
        CREATE TABLE campaigns (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            center_code TEXT,
            center_name TEXT,
            name TEXT NOT NULL,
            message TEXT NOT NULL,
            attachment_path TEXT,
            status TEXT NOT NULL DEFAULT 'queued',
            total INTEGER NOT NULL DEFAULT 0,
            sent INTEGER NOT NULL DEFAULT 0,
            failed INTEGER NOT NULL DEFAULT 0,
            delay_min INTEGER NOT NULL DEFAULT 45,
            delay_max INTEGER NOT NULL DEFAULT 90,
            launch_wait INTEGER NOT NULL DEFAULT 7,
            recipients TEXT NOT NULL DEFAULT '[]',
            started_at TEXT,
            finished_at TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $pdo->exec("CREATE INDEX idx_campaigns_status ON campaigns(status)");
    $pdo->exec("CREATE INDEX idx_campaigns_created ON campaigns(created_at DESC)");
    $pdo->exec("CREATE INDEX idx_campaigns_center ON campaigns(center_code)");

    $pdo->exec("
        CREATE TABLE messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            campaign_id INTEGER NOT NULL,
            center_code TEXT,
            center_name TEXT,
            phone TEXT NOT NULL,
            business TEXT,
            status TEXT NOT NULL DEFAULT 'pending',
            error TEXT,
            attempts INTEGER NOT NULL DEFAULT 0,
            sent_at TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
        )
    ");
    $pdo->exec("CREATE INDEX idx_messages_campaign ON messages(campaign_id)");
    $pdo->exec("CREATE INDEX idx_messages_status ON messages(status)");
    $pdo->exec("CREATE INDEX idx_messages_center ON messages(center_code)");
    $pdo->exec("CREATE INDEX idx_messages_sent_at ON messages(sent_at)");
}

/**
 * Idempotent migrations for DBs that predate the multi-center schema.
 * Safe to run on every request — each step checks for existence first.
 */
function migrateSchema(PDO $pdo): void
{
    $hasTable = function(string $t) use ($pdo): bool {
        $q = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
        $q->execute([$t]);
        return (bool) $q->fetchColumn();
    };
    $hasColumn = function(string $t, string $c) use ($pdo): bool {
        $rows = $pdo->query("PRAGMA table_info($t)")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) if (strcasecmp($r['name'], $c) === 0) return true;
        return false;
    };

    if (!$hasTable('settings')) {
        $pdo->exec("CREATE TABLE settings (key TEXT PRIMARY KEY, value TEXT NOT NULL)");
    }

    if ($hasTable('campaigns')) {
        if (!$hasColumn('campaigns', 'center_code')) {
            $pdo->exec("ALTER TABLE campaigns ADD COLUMN center_code TEXT");
        }
        if (!$hasColumn('campaigns', 'center_name')) {
            $pdo->exec("ALTER TABLE campaigns ADD COLUMN center_name TEXT");
        }
        try { $pdo->exec("CREATE INDEX IF NOT EXISTS idx_campaigns_center ON campaigns(center_code)"); } catch (Throwable $e) {}
    }

    if (!$hasTable('messages')) {
        $pdo->exec("
            CREATE TABLE messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                campaign_id INTEGER NOT NULL,
                center_code TEXT,
                center_name TEXT,
                phone TEXT NOT NULL,
                business TEXT,
                status TEXT NOT NULL DEFAULT 'pending',
                error TEXT,
                attempts INTEGER NOT NULL DEFAULT 0,
                sent_at TEXT,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
            )
        ");
        $pdo->exec("CREATE INDEX idx_messages_campaign ON messages(campaign_id)");
        $pdo->exec("CREATE INDEX idx_messages_status ON messages(status)");
        $pdo->exec("CREATE INDEX idx_messages_center ON messages(center_code)");
        $pdo->exec("CREATE INDEX idx_messages_sent_at ON messages(sent_at)");

        // Backfill: convert every existing campaign's JSON recipients into rows.
        $campaigns = $pdo->query("SELECT id, center_code, center_name, recipients FROM campaigns")->fetchAll(PDO::FETCH_ASSOC);
        $ins = $pdo->prepare("
            INSERT INTO messages (campaign_id, center_code, center_name, phone, business, status, error, sent_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($campaigns as $c) {
            $recipients = json_decode($c['recipients'] ?: '[]', true) ?: [];
            foreach ($recipients as $r) {
                $ins->execute([
                    (int) $c['id'],
                    $c['center_code'],
                    $c['center_name'],
                    (string) ($r['phone'] ?? ''),
                    (string) ($r['business'] ?? ''),
                    (string) ($r['status'] ?? 'pending'),
                    $r['error'] ?? null,
                    $r['sentAt'] ?? null,
                ]);
            }
        }
    }
}

// ---------------------------------------------------------------------------
// Settings helpers
// ---------------------------------------------------------------------------

function settingGet(string $key, ?string $default = null): ?string
{
    $s = db()->prepare('SELECT value FROM settings WHERE key = ?');
    $s->execute([$key]);
    $v = $s->fetchColumn();
    return $v !== false ? (string) $v : $default;
}

function settingSet(string $key, string $value): void
{
    db()->prepare('INSERT INTO settings(key,value) VALUES(?,?) ON CONFLICT(key) DO UPDATE SET value=excluded.value')
        ->execute([$key, $value]);
}

function currentCenter(): ?array
{
    $code = settingGet('center_code');
    if (!$code || !isset(GLS_CENTERS[$code])) return null;
    return ['code' => $code, 'name' => GLS_CENTERS[$code]];
}

// ---------------------------------------------------------------------------
// Campaign helpers
// ---------------------------------------------------------------------------

function loadCampaign(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM campaigns WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return null;
    $row['recipients'] = json_decode($row['recipients'] ?: '[]', true) ?: [];
    return $row;
}

function saveCampaign(int $id, array $fields): void
{
    if (isset($fields['recipients']) && is_array($fields['recipients'])) {
        $fields['recipients'] = json_encode($fields['recipients'], JSON_UNESCAPED_UNICODE);
    }
    $sets = [];
    $vals = [];
    foreach ($fields as $k => $v) {
        $sets[] = "$k = ?";
        $vals[] = $v;
    }
    $vals[] = $id;
    $sql = 'UPDATE campaigns SET ' . implode(', ', $sets) . ' WHERE id = ?';
    db()->prepare($sql)->execute($vals);
}

// ---------------------------------------------------------------------------
// Message helpers (normalized per-recipient rows — the source of truth for
// analytics. The JSON blob on campaigns.recipients is kept in sync for
// backward compatibility with the UI/worker, but exports read from here.)
// ---------------------------------------------------------------------------

function insertMessagesForCampaign(int $campaignId, array $recipients, ?string $centerCode, ?string $centerName): void
{
    $ins = db()->prepare("
        INSERT INTO messages (campaign_id, center_code, center_name, phone, business, status, error, sent_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    foreach ($recipients as $r) {
        $ins->execute([
            $campaignId,
            $centerCode,
            $centerName,
            (string) ($r['phone'] ?? ''),
            (string) ($r['business'] ?? ''),
            (string) ($r['status'] ?? 'pending'),
            $r['error'] ?? null,
            $r['sentAt'] ?? null,
        ]);
    }
}

/**
 * Worker calls this to update the per-recipient row after a send attempt.
 * Matches on (campaign_id, phone, ordinal) — ordinal = zero-based position
 * of the recipient in the campaign's recipients array, to handle duplicates.
 */
function updateMessageStatus(int $campaignId, int $ordinal, string $phone, string $status, ?string $error = null, ?string $sentAt = null): void
{
    $rows = db()->prepare("SELECT id FROM messages WHERE campaign_id = ? AND phone = ? ORDER BY id ASC");
    $rows->execute([$campaignId, $phone]);
    $ids = $rows->fetchAll(PDO::FETCH_COLUMN);
    if (!isset($ids[$ordinal])) return;

    $upd = db()->prepare("
        UPDATE messages
        SET status = ?, error = ?, sent_at = ?, attempts = attempts + 1
        WHERE id = ?
    ");
    $upd->execute([$status, $error, $sentAt, (int) $ids[$ordinal]]);
}
