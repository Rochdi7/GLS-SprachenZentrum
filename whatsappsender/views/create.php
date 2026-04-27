<?php /** @var array $old @var array $errors */ ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Campagnes</a></li>
        <li class="breadcrumb-item active">Nouvelle campagne</li>
    </ol>
</nav>

<h1 class="fs-page-title mb-4">
    <i class="bi bi-plus-circle text-success"></i> Nouvelle campagne
</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>
                <strong>Erreurs de validation :</strong>
                <ul class="mb-0 mt-1">
                    <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<form action="index.php?action=store" method="POST" enctype="multipart/form-data">
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header py-3">
                    <h6 class="fw-semibold">
                        <span class="badge bg-success rounded-circle me-1" style="width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center;">1</span>
                        Configuration
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-tag"></i> Nom de la campagne *
                        </label>
                        <input type="text" name="name" class="form-control" required
                               placeholder="Ex : Promo rentrée septembre"
                               value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-chat-left-text"></i> Message *
                        </label>
                        <textarea name="message" class="form-control" rows="6" required
                                  placeholder="Bonjour {name}, …"><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
                        <div class="mt-2 p-2 rounded" style="background:#f8fafc; border:1px solid #eef0f4; font-size:0.85rem;">
                            <i class="bi bi-info-circle text-primary"></i>
                            <strong>Variables disponibles :</strong>
                            <code class="mx-1 bg-white px-2 py-1 rounded border">{business}</code>
                            <code class="mx-1 bg-white px-2 py-1 rounded border">{name}</code>
                            <code class="mx-1 bg-white px-2 py-1 rounded border">{phone}</code>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">
                            <i class="bi bi-paperclip"></i> Pièce jointe <span class="text-muted">(optionnel)</span>
                        </label>
                        <input type="file" name="attachment" class="form-control"
                               accept=".pdf,.jpg,.jpeg,.png,.webp,.mp4">
                        <div class="form-text">
                            <i class="bi bi-file-earmark"></i> PDF, images (JPG/PNG/WEBP) ou vidéo MP4 · 20 Mo max
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header py-3">
                    <h6 class="fw-semibold">
                        <span class="badge bg-success rounded-circle me-1" style="width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center;">2</span>
                        Destinataires
                    </h6>
                </div>
                <div class="card-body">
                    <label class="form-label">
                        <i class="bi bi-telephone"></i> Numéros (un par ligne) *
                    </label>
                    <textarea name="numbers" class="form-control" rows="10" required
                              style="font-family:'JetBrains Mono', Consolas, monospace; font-size:13px;"
                              placeholder="+212600000000&#10;+212600000001, Nom du contact&#10;+212600000002, Autre contact"><?= htmlspecialchars($old['numbers'] ?? '') ?></textarea>
                    <div class="form-text">
                        <i class="bi bi-info-circle"></i>
                        Format : <code>numéro</code> ou <code>numéro, nom</code>.
                        Les numéros fixes/fax (05…) sont <strong>automatiquement ignorés</strong>.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header py-3">
                    <h6 class="fw-semibold">
                        <span class="badge bg-success rounded-circle me-1" style="width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center;">3</span>
                        Paramètres d'envoi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-stopwatch"></i> Délai min (s)
                        </label>
                        <input type="number" name="delay_min" class="form-control" min="30"
                               value="<?= (int)($old['delay_min'] ?? 45) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-stopwatch-fill"></i> Délai max (s)
                        </label>
                        <input type="number" name="delay_max" class="form-control" min="40"
                               value="<?= (int)($old['delay_max'] ?? 90) ?>">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">
                            <i class="bi bi-hourglass"></i> Attente chargement (s)
                        </label>
                        <input type="number" name="launch_wait" class="form-control" min="3" max="30"
                               value="<?= (int)($old['launch_wait'] ?? 7) ?>">
                        <div class="form-text">Temps avant de taper le message (chargement WhatsApp).</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3" style="border-left: 4px solid var(--gls-yellow)">
                <div class="card-body">
                    <div class="d-flex gap-2 small">
                        <i class="bi bi-lightbulb-fill text-warning fs-5"></i>
                        <div>
                            <strong>Bonne pratique</strong><br>
                            <span class="text-muted">Gardez des délais aléatoires (45–90s) pour paraître humain et éviter le bannissement.</span>
                        </div>
                    </div>
                </div>
            </div>

            <button class="btn btn-wa btn-lg w-100" type="submit">
                <i class="bi bi-check2-circle me-1"></i> Créer la campagne
            </button>
            <a href="index.php" class="btn btn-link w-100 mt-1 text-muted text-decoration-none">
                <i class="bi bi-arrow-left"></i> Annuler
            </a>
        </div>
    </div>
</form>
