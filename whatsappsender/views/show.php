<?php /** @var array $campaign */
$statusMeta = [
    'queued'    => ['class' => 'bg-secondary',         'icon' => 'bi-hourglass-split',  'label' => 'En file'],
    'running'   => ['class' => 'bg-primary',           'icon' => 'bi-broadcast',        'label' => 'En cours'],
    'paused'    => ['class' => 'bg-warning text-dark', 'icon' => 'bi-pause-circle',     'label' => 'Pause'],
    'stopped'   => ['class' => 'bg-danger',            'icon' => 'bi-stop-circle',      'label' => 'Arrêtée'],
    'completed' => ['class' => 'bg-success',           'icon' => 'bi-check-circle',     'label' => 'Terminée'],
];
$meta = $statusMeta[$campaign['status']] ?? ['class' => 'bg-secondary', 'icon' => 'bi-circle', 'label' => $campaign['status']];
$total = (int) $campaign['total'];
$sent  = (int) $campaign['sent'];
$failed= (int) $campaign['failed'];
$pending = max(0, $total - $sent - $failed);
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Campagnes</a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($campaign['name']) ?></li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h1 class="fs-page-title mb-1"><?= htmlspecialchars($campaign['name']) ?></h1>
        <div class="text-muted small">
            <i class="bi bi-hash"></i><?= (int)$campaign['id'] ?>
            <span class="mx-1">·</span>
            <i class="bi bi-calendar3"></i> <?= htmlspecialchars($campaign['created_at']) ?>
        </div>
    </div>
    <span class="badge badge-status <?= $meta['class'] ?> fs-6 px-3 py-2" id="wa-status-badge">
        <i class="bi <?= $meta['icon'] ?>" id="wa-status-icon"></i>
        <span id="wa-status-label"><?= $meta['label'] ?></span>
    </span>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <!-- LIVE + CONTROLS -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="alert alert-info d-none mb-3" id="wa-live">
                    <div class="d-flex align-items-center gap-2">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        <span id="wa-live-text">…</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-4" id="wa-controls">
                    <button class="btn btn-wa btn-lg" id="wa-btn-start">
                        <i class="bi bi-play-fill"></i> Démarrer l'envoi
                    </button>
                    <button class="btn btn-warning d-none" id="wa-btn-pause">
                        <i class="bi bi-pause-fill"></i> Pause
                    </button>
                    <button class="btn btn-info text-white d-none" id="wa-btn-resume">
                        <i class="bi bi-play-fill"></i> Reprendre
                    </button>
                    <button class="btn btn-danger d-none" id="wa-btn-stop">
                        <i class="bi bi-stop-fill"></i> Arrêter
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary ms-auto">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                </div>

                <!-- Counter tiles -->
                <div class="row g-2 mb-3">
                    <div class="col-3">
                        <div class="stat-tile">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="stat-icon" style="background:#e0e7ff; color:#4338ca"><i class="bi bi-people-fill"></i></span>
                            </div>
                            <div class="stat-value" id="wa-total"><?= $total ?></div>
                            <div class="stat-label">Total</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="stat-tile">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="stat-icon" style="background:#d1fae5; color:#065f46"><i class="bi bi-send-check-fill"></i></span>
                            </div>
                            <div class="stat-value text-success" id="wa-sent"><?= $sent ?></div>
                            <div class="stat-label">Envoyés</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="stat-tile">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="stat-icon" style="background:#fee2e2; color:#991b1b"><i class="bi bi-x-octagon-fill"></i></span>
                            </div>
                            <div class="stat-value text-danger" id="wa-failed"><?= $failed ?></div>
                            <div class="stat-label">Échecs</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="stat-tile">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="stat-icon" style="background:#fef3c7; color:#92400e"><i class="bi bi-hourglass-split"></i></span>
                            </div>
                            <div class="stat-value" id="wa-pending"><?= $pending ?></div>
                            <div class="stat-label">En attente</div>
                        </div>
                    </div>
                </div>

                <div class="progress">
                    <div class="progress-bar bg-success" id="wa-progress-sent"
                         style="width: <?= $total > 0 ? round($sent * 100 / $total) : 0 ?>%"></div>
                    <div class="progress-bar bg-danger" id="wa-progress-failed"
                         style="width: <?= $total > 0 ? round($failed * 100 / $total) : 0 ?>%"></div>
                </div>
                <div class="d-flex justify-content-between mt-2 small text-muted">
                    <span><i class="bi bi-graph-up"></i> Progression</span>
                    <span><strong id="wa-pct"><?= $total > 0 ? round(($sent + $failed) * 100 / $total) : 0 ?></strong>%</span>
                </div>
            </div>
        </div>

        <!-- LOG -->
        <div class="card mb-3">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold">
                    <i class="bi bi-terminal text-muted"></i> Journal du worker
                </h6>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary" id="wa-log-refresh">
                        <i class="bi bi-arrow-clockwise"></i> Rafraîchir
                    </button>
                    <button class="btn btn-outline-primary" id="wa-log-toggle-auto">
                        <i class="bi bi-play-fill"></i> Auto
                    </button>
                </div>
            </div>
            <div class="card-body p-3">
                <pre class="log-pre" id="wa-log-pre">— Aucun journal pour le moment (la campagne n'a pas encore démarré) —</pre>
                <small class="text-muted d-block mt-2" id="wa-log-size"></small>
            </div>
        </div>

        <!-- RECIPIENTS -->
        <div class="card">
            <div class="card-header py-3">
                <h6 class="fw-semibold">
                    <i class="bi bi-people text-muted"></i> Destinataires
                    <span class="badge bg-light text-dark ms-1" style="font-weight:500"><?= $total ?></span>
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Téléphone</th>
                        <th>Nom</th>
                        <th>Statut</th>
                        <th>Envoyé à</th>
                    </tr>
                    </thead>
                    <tbody id="wa-recipients-tbody">
                    <?php foreach ($campaign['recipients'] as $i => $r):
                        $rstatus = $r['status'] ?? 'pending';
                        $rIcon = match($rstatus) {
                            'sent'    => 'bi-check-circle-fill',
                            'failed'  => 'bi-x-circle-fill',
                            'sending' => 'bi-arrow-repeat',
                            'skipped' => 'bi-skip-forward-fill',
                            default   => 'bi-circle',
                        };
                    ?>
                        <tr>
                            <td class="text-muted"><?= $i + 1 ?></td>
                            <td class="fw-semibold">
                                <i class="bi bi-telephone text-muted me-1"></i>
                                <?= htmlspecialchars($r['phone'] ?? '') ?>
                            </td>
                            <td>
                                <?php if (!empty($r['business']) || !empty($r['name'])): ?>
                                    <?= htmlspecialchars($r['business'] ?? ($r['name'] ?? '')) ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge status-<?= htmlspecialchars($rstatus) ?>">
                                    <i class="bi <?= $rIcon ?>"></i> <?= htmlspecialchars($rstatus) ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($r['sentAt'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header py-3">
                <h6 class="fw-semibold">
                    <i class="bi bi-chat-left-quote text-muted"></i> Message
                </h6>
            </div>
            <div class="card-body" style="background:#f8fafc; border-radius: 0 0 14px 14px;">
                <pre class="mb-0" style="white-space:pre-wrap; font-family:inherit; font-size:0.9rem; color:#374151"><?= htmlspecialchars($campaign['message']) ?></pre>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header py-3">
                <h6 class="fw-semibold">
                    <i class="bi bi-sliders text-muted"></i> Paramètres
                </h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-6 text-muted d-flex align-items-center gap-1">
                        <i class="bi bi-stopwatch"></i> Délai
                    </dt>
                    <dd class="col-6 text-end fw-semibold"><?= (int)$campaign['delay_min'] ?>–<?= (int)$campaign['delay_max'] ?> s</dd>

                    <dt class="col-6 text-muted d-flex align-items-center gap-1">
                        <i class="bi bi-hourglass"></i> Chargement
                    </dt>
                    <dd class="col-6 text-end fw-semibold"><?= (int)$campaign['launch_wait'] ?> s</dd>

                    <?php if (!empty($campaign['attachment_path'])): ?>
                        <dt class="col-6 text-muted d-flex align-items-center gap-1">
                            <i class="bi bi-paperclip"></i> Pièce jointe
                        </dt>
                        <dd class="col-6 text-end small text-truncate" title="<?= htmlspecialchars(basename($campaign['attachment_path'])) ?>">
                            <?= htmlspecialchars(basename($campaign['attachment_path'])) ?>
                        </dd>
                    <?php endif; ?>

                    <?php if (!empty($campaign['started_at'])): ?>
                        <dt class="col-6 text-muted d-flex align-items-center gap-1">
                            <i class="bi bi-play-circle"></i> Démarrée
                        </dt>
                        <dd class="col-6 text-end small"><?= htmlspecialchars($campaign['started_at']) ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($campaign['finished_at'])): ?>
                        <dt class="col-6 text-muted d-flex align-items-center gap-1">
                            <i class="bi bi-check-circle"></i> Terminée
                        </dt>
                        <dd class="col-6 text-end small"><?= htmlspecialchars($campaign['finished_at']) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <div class="card" style="border-left: 4px solid var(--gls-yellow)">
            <div class="card-body">
                <div class="d-flex gap-2 small">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                    <div>
                        <strong>Important</strong><br>
                        <span class="text-muted">Gardez WhatsApp Desktop <u>visible à l'écran</u> pendant l'envoi. Ne touchez pas au clavier/souris.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const CAMPAIGN_ID = <?= (int)$campaign['id'] ?>;
    const URL_START  = 'index.php?action=start&id='   + CAMPAIGN_ID;
    const URL_STATUS = 'index.php?action=status&id='  + CAMPAIGN_ID;
    const URL_LOG    = 'index.php?action=log&id='     + CAMPAIGN_ID;
    const URL_PAUSE  = 'index.php?action=pause';
    const URL_RESUME = 'index.php?action=resume';
    const URL_STOP   = 'index.php?action=stop';
    const URL_RESET  = 'index.php?action=force_reset';

    const btnStart  = document.getElementById('wa-btn-start');
    const btnPause  = document.getElementById('wa-btn-pause');
    const btnResume = document.getElementById('wa-btn-resume');
    const btnStop   = document.getElementById('wa-btn-stop');
    const live      = document.getElementById('wa-live');
    const liveText  = document.getElementById('wa-live-text');
    const tbody     = document.getElementById('wa-recipients-tbody');
    const badge     = document.getElementById('wa-status-badge');
    const badgeIcon = document.getElementById('wa-status-icon');
    const badgeLbl  = document.getElementById('wa-status-label');

    const elTotal   = document.getElementById('wa-total');
    const elSent    = document.getElementById('wa-sent');
    const elFailed  = document.getElementById('wa-failed');
    const elPending = document.getElementById('wa-pending');
    const elPct     = document.getElementById('wa-pct');
    const pgSent    = document.getElementById('wa-progress-sent');
    const pgFailed  = document.getElementById('wa-progress-failed');

    const STATUS_META = {
        queued:    {cls:'bg-secondary',         icon:'bi-hourglass-split', label:'En file'},
        running:   {cls:'bg-primary',           icon:'bi-broadcast',       label:'En cours'},
        paused:    {cls:'bg-warning text-dark', icon:'bi-pause-circle',    label:'Pause'},
        stopped:   {cls:'bg-danger',            icon:'bi-stop-circle',     label:'Arrêtée'},
        completed: {cls:'bg-success',           icon:'bi-check-circle',    label:'Terminée'},
    };
    const ROW_ICONS = {
        pending:'bi-circle', sending:'bi-arrow-repeat', sent:'bi-check-circle-fill',
        failed:'bi-x-circle-fill', skipped:'bi-skip-forward-fill',
    };

    function esc(s){ return String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]); }

    async function api(url, opts={}){
        const r = await fetch(url, Object.assign({headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}}, opts));
        let data=null; try { data = await r.json(); } catch(_) {}
        return {ok: r.ok, status: r.status, data};
    }
    function apiErr(res){
        if(!res) return 'erreur inconnue';
        if(res.data && res.data.error) return res.data.error;
        return 'HTTP '+res.status;
    }

    function uiRunning(){
        btnStart.classList.add('d-none');
        btnPause.classList.remove('d-none');
        btnResume.classList.add('d-none');
        btnStop.classList.remove('d-none');
        live.classList.remove('d-none');
    }
    function uiPaused(){
        btnPause.classList.add('d-none');
        btnResume.classList.remove('d-none');
    }
    function uiIdle(){
        btnStart.classList.remove('d-none');
        btnStart.disabled = false;
        btnPause.classList.add('d-none');
        btnResume.classList.add('d-none');
        btnStop.classList.add('d-none');
        live.classList.add('d-none');
    }

    function renderRecipients(list){
        if (!list || !tbody) return;
        tbody.innerHTML = list.map((r,i)=>{
            const st = r.status || 'pending';
            const icon = ROW_ICONS[st] || 'bi-circle';
            const name = r.business || r.name;
            return `
                <tr>
                    <td class="text-muted">${i+1}</td>
                    <td class="fw-semibold"><i class="bi bi-telephone text-muted me-1"></i>${esc(r.phone)}</td>
                    <td>${name ? esc(name) : '<span class="text-muted">—</span>'}</td>
                    <td><span class="badge status-${esc(st)}"><i class="bi ${icon}"></i> ${esc(st)}</span></td>
                    <td class="text-muted small">${esc(r.sentAt||'—')}</td>
                </tr>`;
        }).join('');
    }

    function updateCounters(s){
        const total = s.total || 0, sent = s.sent || 0, failed = s.failed || 0;
        const pending = Math.max(0, total - sent - failed);
        elTotal.textContent = total;
        elSent.textContent = sent;
        elFailed.textContent = failed;
        elPending.textContent = pending;
        const pctS = total > 0 ? Math.round(sent   * 100 / total) : 0;
        const pctF = total > 0 ? Math.round(failed * 100 / total) : 0;
        pgSent.style.width   = pctS + '%';
        pgFailed.style.width = pctF + '%';
        elPct.textContent    = pctS + pctF;

        if (s.status && STATUS_META[s.status]) {
            const m = STATUS_META[s.status];
            badgeLbl.textContent = m.label;
            badge.className = 'badge badge-status fs-6 px-3 py-2 ' + m.cls;
            badgeIcon.className = 'bi ' + m.icon;
        }
    }

    let pollTimer = null;
    async function poll(){
        const r = await api(URL_STATUS);
        if (!r.ok || !r.data) return;
        const s = r.data;
        updateCounters(s);
        renderRecipients(s.messages);

        if (s.running) {
            uiRunning();
            if (s.paused) {
                uiPaused();
                // Frozen countdown: show the remaining seconds but don't animate it.
                const remain = s.next_send_in != null ? s.next_send_in : null;
                liveText.innerHTML = '<i class="bi bi-pause-circle"></i> <strong>En pause</strong>'
                    + (remain != null ? ' — reprise dans ' + remain + ' s' : '');
                live.classList.remove('alert-info');
                live.classList.add('alert-warning');
            } else {
                live.classList.remove('alert-warning');
                live.classList.add('alert-info');
                if (s.current)                   liveText.innerHTML = '<i class="bi bi-send"></i> Envoi vers <strong>' + esc(s.current) + '</strong>…';
                else if (s.next_send_in != null) liveText.innerHTML = '<i class="bi bi-stopwatch"></i> Prochain envoi dans <strong>' + s.next_send_in + ' s</strong>';
                else                             liveText.innerHTML = '<i class="bi bi-gear-wide-connected"></i> Préparation…';
            }
        } else {
            uiIdle();
            if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
        }
    }
    function startPolling(){ if (pollTimer) return; poll(); pollTimer = setInterval(poll, 2000); }

    async function tryStart(){
        const r = await api(URL_START, {method:'POST'});
        if (r.ok) { uiRunning(); startPolling(); startLogAuto(); return true; }
        if (r.status === 409) {
            const otherId = r.data && r.data.runningCampaignId;
            const isSelf  = otherId && Number(otherId) === Number(CAMPAIGN_ID);
            const ok = await GlsUI.confirm({
                title: 'Verrou détecté',
                message: isSelf
                    ? "Cette campagne est marquée comme en cours, mais aucun worker n'est actif. Forcer la réinitialisation et redémarrer ?"
                    : "Une autre campagne est marquée comme en cours (ID " + (otherId ?? '?') + "). Forcer la réinitialisation ?",
                confirmText: 'Réinitialiser',
                confirmVariant: 'warning',
                type: 'warning',
            });
            if (ok) {
                const rr = await api(URL_RESET, {method:'POST'});
                if (!rr.ok) { GlsUI.toast({ type:'error', title:'Réinitialisation échouée', message: apiErr(rr) }); return false; }
                const r2 = await api(URL_START, {method:'POST'});
                if (r2.ok) { uiRunning(); startPolling(); startLogAuto(); return true; }
                GlsUI.toast({ type:'error', title:'Démarrage échoué après réinitialisation', message: apiErr(r2) });
                return false;
            }
            return false;
        }
        GlsUI.toast({ type:'error', title:'Démarrage échoué', message: apiErr(r) });
        return false;
    }

    btnStart.addEventListener('click', async ()=>{
        btnStart.disabled = true;
        const ok = await tryStart();
        if (!ok) btnStart.disabled = false;
    });
    btnPause.addEventListener('click', async ()=>{
        btnPause.disabled = true; uiPaused();
        await api(URL_PAUSE, {method:'POST'});
        btnPause.disabled = false; poll();
    });
    btnResume.addEventListener('click', async ()=>{
        btnResume.disabled = true;
        await api(URL_RESUME, {method:'POST'});
        btnResume.disabled = false; poll();
    });
    btnStop.addEventListener('click', async ()=>{
        const ok = await GlsUI.confirm({
            title: 'Arrêter la campagne ?',
            message: 'Vous pourrez la redémarrer plus tard — les messages déjà envoyés ne seront pas renvoyés.',
            confirmText: 'Arrêter',
            confirmVariant: 'danger',
            type: 'warning',
        });
        if (!ok) return;
        await api(URL_STOP, {method:'POST'});
    });

    // Log tail
    const logPre        = document.getElementById('wa-log-pre');
    const logSize       = document.getElementById('wa-log-size');
    const btnLogRefresh = document.getElementById('wa-log-refresh');
    const btnLogAuto    = document.getElementById('wa-log-toggle-auto');
    let logTimer = null;

    function fmtBytes(n){ if(n<1024)return n+' o'; if(n<1048576)return (n/1024).toFixed(1)+' Ko'; return (n/1048576).toFixed(2)+' Mo'; }

    async function refreshLog(){
        const r = await api(URL_LOG);
        if (!r.ok || !r.data) return;
        if (!r.data.exists) {
            logPre.textContent = '— Aucun journal pour le moment (la campagne n\'a pas encore démarré) —';
            logSize.textContent = '';
            return;
        }
        logPre.textContent = (r.data.lines || []).join('\n') || '(vide)';
        logSize.innerHTML = '<i class="bi bi-file-earmark-text"></i> ' + fmtBytes(r.data.size || 0);
        logPre.scrollTop = logPre.scrollHeight;
    }
    function startLogAuto(){
        if (logTimer) return;
        btnLogAuto.innerHTML = '<i class="bi bi-pause-fill"></i> Auto';
        btnLogAuto.dataset.auto = '1';
        refreshLog();
        logTimer = setInterval(refreshLog, 3000);
    }
    function stopLogAuto(){
        if (logTimer) { clearInterval(logTimer); logTimer = null; }
        btnLogAuto.innerHTML = '<i class="bi bi-play-fill"></i> Auto';
        btnLogAuto.dataset.auto = '0';
    }
    btnLogRefresh.addEventListener('click', refreshLog);
    btnLogAuto.addEventListener('click', ()=>{
        if (btnLogAuto.dataset.auto === '1') stopLogAuto(); else startLogAuto();
    });

    // Boot
    (async ()=>{
        const r = await api(URL_STATUS);
        if (!r.ok || !r.data) return;
        updateCounters(r.data);
        if (r.data.running) {
            uiRunning();
            startPolling();
            startLogAuto();
        }
    })();
})();
</script>
