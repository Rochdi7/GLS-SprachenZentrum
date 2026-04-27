<?php
/**
 * @var array $centers     Fixed list of [code => label]
 * @var ?array $current    ['code'=>..., 'name'=>...] or null on first run
 * @var bool $firstRun     true on first-run flow (show explanation)
 */
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Campagnes</a></li>
        <li class="breadcrumb-item active">Paramètres</li>
    </ol>
</nav>

<div class="d-flex align-items-center mb-4">
    <h1 class="fs-page-title m-0">
        <i class="bi bi-geo-alt-fill text-danger"></i> Centre de ce poste
    </h1>
</div>

<?php if ($firstRun): ?>
    <div class="alert alert-info">
        <strong>Première utilisation de ce poste.</strong><br>
        Avant de créer une campagne, choisissez le centre GLS auquel ce PC appartient.
        Toutes les campagnes et messages envoyés depuis ce poste seront tagués avec ce centre,
        pour permettre la consolidation des statistiques au siège.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-building"></i> Sélectionner le centre</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?action=settings_save">
            <div class="mb-3">
                <label class="form-label">Centre GLS</label>
                <select name="center_code" class="form-select form-select-lg" required>
                    <option value="">— Choisir un centre —</option>
                    <?php foreach ($centers as $code => $label): ?>
                        <option value="<?= htmlspecialchars($code) ?>"
                            <?= ($current && $current['code'] === $code) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">
                    Cette valeur sera enregistrée localement dans <code>storage/db.sqlite</code>.
                    Chaque campagne créée sur ce poste portera le code de ce centre.
                </div>
            </div>

            <?php if (!$firstRun && $current): ?>
                <div class="alert alert-warning small">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Changer le centre n'affecte pas les campagnes déjà enregistrées — elles
                    gardent leur tag d'origine. Seules les <em>nouvelles</em> campagnes utiliseront la nouvelle valeur.
                </div>
            <?php endif; ?>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-wa btn-lg">
                    <i class="bi bi-check-lg"></i> Enregistrer
                </button>
                <?php if (!$firstRun): ?>
                    <a href="index.php" class="btn btn-light btn-lg">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
