<?php /** @var array $campaigns */
$statusMeta = [
    'queued'    => ['class' => 'bg-secondary',         'icon' => 'bi-hourglass-split',  'label' => 'En file'],
    'running'   => ['class' => 'bg-primary',           'icon' => 'bi-broadcast',        'label' => 'En cours'],
    'paused'    => ['class' => 'bg-warning text-dark', 'icon' => 'bi-pause-circle',     'label' => 'Pause'],
    'stopped'   => ['class' => 'bg-danger',            'icon' => 'bi-stop-circle',      'label' => 'Arrêtée'],
    'completed' => ['class' => 'bg-success',           'icon' => 'bi-check-circle',     'label' => 'Terminée'],
];
// Stats
$totals = array_reduce($campaigns, function($acc, $c) {
    $acc['campaigns']++;
    $acc['recipients'] += (int)$c['total'];
    $acc['sent']       += (int)$c['sent'];
    $acc['failed']     += (int)$c['failed'];
    return $acc;
}, ['campaigns' => 0, 'recipients' => 0, 'sent' => 0, 'failed' => 0]);
?>

<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h1 class="fs-page-title mb-1">
            <i class="bi bi-chat-dots-fill text-success"></i> Campagnes WhatsApp
        </h1>
        <p class="text-muted mb-0">Gérez et suivez vos campagnes d'envoi en masse</p>
    </div>
    <a href="index.php?action=create" class="btn btn-wa btn-lg">
        <i class="bi bi-plus-circle-fill me-1"></i> Nouvelle campagne
    </a>
</div>

<!-- Stats row -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-tile">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="stat-icon" style="background:#e0e7ff; color:#4338ca"><i class="bi bi-collection"></i></span>
            </div>
            <div class="stat-value"><?= $totals['campaigns'] ?></div>
            <div class="stat-label">Campagnes</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-tile">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="stat-icon" style="background:#e0f2fe; color:#0369a1"><i class="bi bi-people-fill"></i></span>
            </div>
            <div class="stat-value"><?= $totals['recipients'] ?></div>
            <div class="stat-label">Destinataires</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-tile">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="stat-icon" style="background:#d1fae5; color:#065f46"><i class="bi bi-send-check-fill"></i></span>
            </div>
            <div class="stat-value text-success"><?= $totals['sent'] ?></div>
            <div class="stat-label">Envoyés</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-tile">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="stat-icon" style="background:#fee2e2; color:#991b1b"><i class="bi bi-x-octagon-fill"></i></span>
            </div>
            <div class="stat-value text-danger"><?= $totals['failed'] ?></div>
            <div class="stat-label">Échecs</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header py-3">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="fw-semibold mb-0">
                <i class="bi bi-list-ul me-1 text-muted"></i> Toutes les campagnes
            </h6>
            <span class="text-muted small"><?= count($campaigns) ?> résultat<?= count($campaigns) > 1 ? 's' : '' ?></span>
        </div>
    </div>

    <?php if (!count($campaigns)): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h5 class="mt-3 mb-1">Aucune campagne</h5>
            <p class="mb-3">Créez votre première campagne pour commencer à envoyer.</p>
            <a href="index.php?action=create" class="btn btn-wa">
                <i class="bi bi-plus-lg me-1"></i> Nouvelle campagne
            </a>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th style="width:60px">#</th>
                <th>Nom</th>
                <th>Statut</th>
                <th style="min-width:220px">Progression</th>
                <th>Créée le</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($campaigns as $c):
                $meta = $statusMeta[$c['status']] ?? ['class' => 'bg-secondary', 'icon' => 'bi-circle', 'label' => $c['status']];
                $total = (int)$c['total']; $sent = (int)$c['sent']; $failed = (int)$c['failed'];
                $pctS = $total > 0 ? round($sent   * 100 / $total) : 0;
                $pctF = $total > 0 ? round($failed * 100 / $total) : 0;
            ?>
                <tr>
                    <td class="text-muted fw-semibold">#<?= (int)$c['id'] ?></td>
                    <td>
                        <a href="index.php?action=show&amp;id=<?= (int)$c['id'] ?>" class="fw-semibold text-decoration-none text-dark">
                            <?= htmlspecialchars($c['name']) ?>
                        </a>
                    </td>
                    <td>
                        <span class="badge badge-status <?= $meta['class'] ?>">
                            <i class="bi <?= $meta['icon'] ?>"></i> <?= $meta['label'] ?>
                        </span>
                    </td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: <?= $pctS ?>%"></div>
                            <div class="progress-bar bg-danger" style="width: <?= $pctF ?>%"></div>
                        </div>
                        <small class="text-muted d-block mt-1">
                            <span class="text-success"><i class="bi bi-check-circle-fill"></i> <?= $sent ?></span>
                            <span class="mx-1">·</span>
                            <span class="text-danger"><i class="bi bi-x-circle-fill"></i> <?= $failed ?></span>
                            <span class="mx-1">/</span>
                            <span><?= $total ?></span>
                        </small>
                    </td>
                    <td class="text-muted small">
                        <i class="bi bi-calendar3"></i> <?= htmlspecialchars($c['created_at']) ?>
                    </td>
                    <td class="text-end">
                        <a href="index.php?action=show&amp;id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Voir
                        </a>
                        <form action="index.php?action=destroy&amp;id=<?= (int)$c['id'] ?>" method="POST" class="d-inline js-destroy-form"
                              data-campaign-name="<?= htmlspecialchars($c['name'], ENT_QUOTES) ?>">
                            <input type="hidden" name="force" value="1">
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer" type="submit">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.js-destroy-form').forEach((form) => {
    form.addEventListener('submit', async (e) => {
        if (form.dataset.confirmed === '1') return; // second pass after confirm
        e.preventDefault();
        const name = form.dataset.campaignName || 'cette campagne';
        const ok = await GlsUI.confirm({
            title: 'Supprimer la campagne ?',
            message: `« ${name} » sera supprimée définitivement. Si elle tourne, elle sera d'abord arrêtée.`,
            confirmText: 'Supprimer',
            confirmVariant: 'danger',
            type: 'warning',
        });
        if (ok) {
            form.dataset.confirmed = '1';
            form.submit();
        }
    });
});
</script>
