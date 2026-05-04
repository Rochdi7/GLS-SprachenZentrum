@extends('layouts.main')

@section('title', 'Campagne — ' . $campaign->name)
@section('breadcrumb-item', 'Communication')
@section('breadcrumb-item-active', $campaign->name)

@section('css')
<style>
    :root { --wa-primary:#25d366; --wa-ok:#16a34a; --wa-danger:#ef4444; --wa-warn:#f59e0b; --wa-info:#0369a1; }

    .wa-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;letter-spacing:.3px;color:#fff}
    .wa-b-sent{background:var(--wa-ok)} .wa-b-failed{background:var(--wa-danger)} .wa-b-skipped{background:#6b7280}
    .wa-b-pending{background:#9ca3af} .wa-b-sending{background:var(--wa-info)} .wa-b-fax{background:#ef6c00}

    @keyframes wa-pulse-row {
        0%{background:rgba(37,211,102,.25);box-shadow:inset 3px 0 0 var(--wa-primary)}
        50%{background:rgba(37,211,102,.55);box-shadow:inset 6px 0 0 var(--wa-primary)}
        100%{background:rgba(37,211,102,.25);box-shadow:inset 3px 0 0 var(--wa-primary)}
    }
    .wa-row-sending{animation:wa-pulse-row 1.2s ease-in-out infinite}
    @keyframes wa-sent-flash{
        0%{background:rgba(22,163,74,.55);transform:scale(1)}
        40%{background:rgba(22,163,74,.35)}
        100%{background:transparent;transform:scale(1)}
    }
    .wa-row-sent-flash{animation:wa-sent-flash .9s ease-out 1}

    @keyframes wa-bounce-dot{0%,80%,100%{transform:scale(0);opacity:.4}40%{transform:scale(1);opacity:1}}
    .wa-dots{display:inline-flex;gap:4px;margin-left:8px;vertical-align:middle}
    .wa-dots span{width:6px;height:6px;background:var(--wa-primary);border-radius:50%;display:inline-block;animation:wa-bounce-dot 1.2s infinite ease-in-out both}
    .wa-dots span:nth-child(1){animation-delay:-.32s}
    .wa-dots span:nth-child(2){animation-delay:-.16s}

    @keyframes wa-slide-in{from{transform:translateY(-10px);opacity:0}to{transform:translateY(0);opacity:1}}
    #wa-live{display:none;align-items:center;gap:10px;padding:12px 16px;margin-bottom:12px;
        background:linear-gradient(90deg,#dcfce7,#a7f3d0);border:1px solid #86efac;
        border-radius:8px;font-weight:600;color:#166534;font-size:13px;animation:wa-slide-in .3s ease-out}
    #wa-live .wa-spin{width:16px;height:16px;border:2.5px solid var(--wa-primary);border-top-color:transparent;border-radius:50%;animation:wa-spin .8s linear infinite}
    @keyframes wa-spin{to{transform:rotate(360deg)}}

    .wa-sent-toast{position:fixed;top:80px;right:20px;z-index:9999;background:#16a34a;color:#fff;
        padding:10px 16px;border-radius:6px;font-weight:600;font-size:13px;
        box-shadow:0 4px 12px rgba(0,0,0,.2);animation:wa-ripple 2.2s ease-out forwards}
    @keyframes wa-ripple{
        0%{transform:translateX(40px);opacity:0}
        12%{transform:translateX(0);opacity:1}
        80%{transform:translateX(0);opacity:1}
        100%{transform:translateX(40px);opacity:0}
    }

    .wa-progress{background:#f3f4f6;height:10px;border-radius:5px;overflow:hidden;margin:8px 0}
    .wa-progress > div{height:100%;background:var(--wa-primary);transition:width .3s}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $campaign->name }}</h5>
                <span id="wa-status-badge" class="badge bg-secondary">{{ strtoupper($campaign->status) }}</span>
            </div>
            <div class="card-body">
                <div id="wa-live">
                    <div class="wa-spin"></div>
                    <div id="wa-live-text">Préparation…</div>
                </div>

                <div class="d-flex gap-2 flex-wrap mb-3">
                    <button class="btn btn-success" id="wa-btn-start">
                        <i class="ph-duotone ph-play me-1"></i> Démarrer
                    </button>
                    <button class="btn btn-warning" id="wa-btn-pause" style="display:none">
                        <i class="ph-duotone ph-pause me-1"></i> Pause
                    </button>
                    <button class="btn btn-success" id="wa-btn-resume" style="display:none">
                        <i class="ph-duotone ph-play me-1"></i> Reprendre
                    </button>
                    <button class="btn btn-danger" id="wa-btn-stop" style="display:none">
                        <i class="ph-duotone ph-stop me-1"></i> Arrêter
                    </button>
                    <a href="{{ route('backoffice.whatsapp_campaigns.index') }}" class="btn btn-light">
                        <i class="ph-duotone ph-arrow-left me-1"></i> Retour
                    </a>
                </div>

                <div id="wa-progress-wrap">
                    <div id="wa-progress-text" class="fw-semibold mb-1">
                        {{ $campaign->sent + $campaign->failed }}/{{ $campaign->total }}
                        ({{ $campaign->sent }} envoyés, {{ $campaign->failed }} échecs)
                    </div>
                    <div class="wa-progress"><div id="wa-progress-fill" style="width:0%"></div></div>
                    <div id="wa-next-send-in" class="text-muted small"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Journal du worker</h5>
                <div>
                    <span id="wa-log-size" class="badge bg-light-secondary"></span>
                    <button type="button" class="btn btn-sm btn-light-primary" id="wa-log-refresh">
                        <i class="ph-duotone ph-arrow-clockwise"></i> Rafraîchir
                    </button>
                    <button type="button" class="btn btn-sm btn-light" id="wa-log-toggle-auto" data-auto="1">
                        <i class="ph-duotone ph-play"></i> Auto
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <pre id="wa-log-pre" style="background:#0b1120;color:#a7f3d0;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12px;margin:0;padding:14px;max-height:260px;overflow:auto;white-space:pre-wrap">— En attente du premier lancement —</pre>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Destinataires</h5></div>
            <div class="card-body p-0">
                <div style="max-height:520px;overflow-y:auto">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Téléphone</th>
                                <th>Nom</th>
                                <th>Statut</th>
                                <th>Envoyé à</th>
                            </tr>
                        </thead>
                        <tbody id="wa-recipients-tbody">
                            {{-- Filled by JS on first status poll --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Message</h5></div>
            <div class="card-body">
                <pre class="mb-0" style="white-space:pre-wrap;font-family:inherit;font-size:13px">{{ $campaign->message }}</pre>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Paramètres</h5></div>
            <div class="card-body small">
                <div class="mb-2"><strong>Centre :</strong> {{ $campaign->site?->name ?? '—' }}</div>
                <div class="mb-2"><strong>Créée par :</strong> {{ $campaign->user?->name ?? '—' }}</div>
                <div class="mb-2"><strong>Total :</strong> {{ $campaign->total }}</div>
                <div class="mb-2"><strong>Délai :</strong> {{ $campaign->delay_min }}–{{ $campaign->delay_max }} s</div>
                <div class="mb-2"><strong>Attente chargement :</strong> {{ $campaign->launch_wait }} s</div>
                @if ($campaign->attachment_path)
                    <div class="mb-2"><strong>Pièce jointe :</strong><br>
                        <i class="ph-duotone ph-paperclip me-1"></i>
                        {{ basename($campaign->attachment_path) }}
                    </div>
                @endif
                <div class="mb-2"><strong>Créée le :</strong> {{ $campaign->created_at?->format('d/m/Y H:i') }}</div>
                @if ($campaign->finished_at)
                    <div class="mb-2"><strong>Terminée le :</strong> {{ $campaign->finished_at?->format('d/m/Y H:i') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function(){
    const CAMPAIGN_ID = {{ $campaign->id }};
    const URL_START  = @json(route('backoffice.whatsapp_campaigns.start', $campaign));
    const URL_STATUS = @json(route('backoffice.whatsapp_campaigns.status', $campaign));
    const URL_LOG    = @json(route('backoffice.whatsapp_campaigns.log', $campaign));
    const URL_PAUSE  = @json(route('backoffice.whatsapp_campaigns.pause'));
    const URL_RESUME = @json(route('backoffice.whatsapp_campaigns.resume'));
    const URL_STOP   = @json(route('backoffice.whatsapp_campaigns.stop'));
    const URL_RESET  = @json(route('backoffice.whatsapp_campaigns.force_reset'));

    const btnStart  = document.getElementById('wa-btn-start');
    const btnPause  = document.getElementById('wa-btn-pause');
    const btnResume = document.getElementById('wa-btn-resume');
    const btnStop   = document.getElementById('wa-btn-stop');
    const live      = document.getElementById('wa-live');
    const liveText  = document.getElementById('wa-live-text');
    const tbody     = document.getElementById('wa-recipients-tbody');
    const badge     = document.getElementById('wa-status-badge');

    function normPhone(p){ let s=String(p||'').replace(/[\s\-+\(\)]/g,''); if(s.length===10&&/^0[567]/.test(s))s='212'+s.slice(1); return s; }
    function isFax(p){ const s=String(p||'').replace(/[\s\-+\(\)]/g,''); if(/^2125/.test(s))return true; return s.length===10&&s.startsWith('05'); }
    function esc(s){ return String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]); }

    const CSRF_TOKEN = (document.querySelector('meta[name="csrf-token"]')||{}).content || '';
    async function api(url, opts={}){
        const headers = Object.assign({'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, opts.headers||{});
        if (CSRF_TOKEN && (opts.method||'GET').toUpperCase() !== 'GET') {
            headers['X-CSRF-TOKEN'] = CSRF_TOKEN;
        }
        let body = opts.body;
        if (body && typeof body === 'object' && !(body instanceof FormData)){
            headers['Content-Type']='application/json'; body=JSON.stringify(body);
        }
        const r = await fetch(url, Object.assign({}, opts, {headers, body, credentials:'same-origin'}));
        let data=null;
        const ct = r.headers.get('content-type')||'';
        if (ct.includes('application/json')) { try{data=await r.json();}catch(_){ } }
        else { const t=await r.text(); data={_nonJson:true,_status:r.status,_text:t.slice(0,500)}; }
        return {ok:r.ok,status:r.status,data};
    }
    function apiErr(res){
        if(!res) return 'erreur inconnue';
        if(res.data && res.data._nonJson) return 'Réponse non-JSON (HTTP '+res.status+')';
        if(res.data && res.data.error) return res.data.error;
        if(res.data && res.data.message) return res.data.message;
        return 'HTTP '+res.status;
    }

    const toasted = new Set();
    function showToast(phone, business){
        const el = document.createElement('div');
        el.className = 'wa-sent-toast';
        el.textContent = '✓ Envoyé à ' + (business || phone);
        document.body.appendChild(el);
        setTimeout(()=>el.remove(), 2300);
    }

    const BADGE_CLASS = {
        queued:'bg-secondary', running:'bg-info', paused:'bg-warning',
        completed:'bg-success', stopped:'bg-dark',
    };
    function updateBadge(status){
        badge.className = 'badge ' + (BADGE_CLASS[status]||'bg-light-secondary');
        badge.textContent = (status||'').toUpperCase();
    }

    let recipients = [];
    function renderRecipients(currentPhone){
        if (!recipients.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">— aucun destinataire —</td></tr>';
            return;
        }
        const curN = currentPhone ? normPhone(currentPhone) : '';
        tbody.innerHTML = recipients.map((r,i)=>{
            const fax = isFax(r.phone);
            const isSending = (r.status==='sending') || (curN && normPhone(r.phone)===curN);
            const justSent = !!r._flashSent; if (justSent) r._flashSent = false;
            const rowClass = isSending ? 'wa-row-sending' : (justSent ? 'wa-row-sent-flash' : '');
            const st = fax ? 'skipped' : (r.status || 'pending');
            const dots = isSending ? ' <span class="wa-dots"><span></span><span></span><span></span></span>' : '';
            const faxB = fax ? ' <span class="wa-badge wa-b-fax">FAX</span>' : '';
            return `<tr class="${rowClass}">
                <td>${i+1}</td>
                <td class="font-monospace">${esc(r.phone)}${faxB}${dots}</td>
                <td>${esc(r.business||r.name||'—')}</td>
                <td><span class="wa-badge wa-b-${st}">${st}</span></td>
                <td class="text-muted small">${esc(r.sentAt||r.time||'—')}</td>
            </tr>`;
        }).join('');
    }

    let pollTimer = null;
    let pollTicksWithoutRunning = 0;
    let sawRunning = false;

    function uiRunning(isPaused){
        btnStart.disabled = true;
        btnPause.style.display  = isPaused ? 'none' : '';
        btnResume.style.display = isPaused ? ''     : 'none';
        btnStop.style.display   = '';
    }
    function uiPaused(){
        btnStart.disabled = true;
        btnPause.style.display = 'none';
        btnResume.style.display = '';
        btnStop.style.display = '';
    }
    function uiIdle(){
        btnStart.disabled = false;
        btnPause.style.display = 'none';
        btnResume.style.display = 'none';
        btnStop.style.display = 'none';
        live.style.display = 'none';
    }

    function startPolling(){
        if (pollTimer) clearInterval(pollTimer);
        pollTicksWithoutRunning = 0;
        sawRunning = false;
        pollTimer = setInterval(poll, 1500);
        poll();
    }

    async function poll(){
        try {
            const r = await api(URL_STATUS);
            if (!r.ok || !r.data) return;
            const d = r.data;

            updateBadge(d.status);

            const total = d.total || recipients.length || 0;
            const processed = (d.sent||0) + (d.failed||0);
            const pct = total ? Math.round(processed*100/total) : 0;

            document.getElementById('wa-progress-text').textContent =
                `${processed}/${total} (${d.sent||0} envoyés, ${d.failed||0} échecs)`;
            document.getElementById('wa-progress-fill').style.width = pct+'%';

            const nextTxt = d.next_send_in != null ? `Prochain dans ${d.next_send_in}s` : '';
            const curTxt  = d.current ? ` · envoi à ${d.current}` : '';
            document.getElementById('wa-next-send-in').textContent = nextTxt + curTxt;

            if (d.running) {
                live.style.display = 'flex';
                if (d.paused) {
                    liveText.textContent = `⏸ Campagne en pause (${processed}/${total})`;
                } else if (d.current) {
                    liveText.textContent = `Envoi vers ${d.current} (${processed}/${total})`;
                } else if (d.next_send_in != null) {
                    liveText.textContent = `Attente ${d.next_send_in}s avant le prochain envoi (${processed}/${total})`;
                } else {
                    liveText.textContent = `Préparation… (${processed}/${total})`;
                }
            } else {
                live.style.display = 'none';
            }

            if (Array.isArray(d.messages) && d.messages.length) {
                if (!recipients.length) {
                    recipients = d.messages.map(m=>({
                        phone:m.phone, business:m.business||'', status:m.status||'pending', sentAt:m.sentAt||''
                    }));
                } else {
                    for (const m of d.messages) {
                        const key = normPhone(m.phone);
                        let match = recipients.find(r=>normPhone(r.phone)===key);
                        if (!match) {
                            match = {phone:m.phone, business:m.business||'', status:'pending', sentAt:''};
                            recipients.push(match);
                        }
                        const prev = match.status;
                        const next = m.status || 'pending';
                        if (prev !== 'sent' && next === 'sent' && !toasted.has(key)) {
                            toasted.add(key);
                            match._flashSent = true;
                            showToast(m.phone, m.business||match.business);
                        }
                        match.status = next;
                        match.sentAt = m.sentAt || match.sentAt || '';
                        if (m.business) match.business = m.business;
                    }
                }
                renderRecipients(d.current);
            }

            if (d.running) {
                sawRunning = true;
                pollTicksWithoutRunning = 0;
                uiRunning(!!d.paused);
            } else {
                pollTicksWithoutRunning++;
                const finished = sawRunning || pollTicksWithoutRunning >= 10;
                if (finished) {
                    clearInterval(pollTimer); pollTimer = null;
                    uiIdle();
                    stopLogAuto();
                    loadLog();
                    if (!sawRunning) {
                        console.warn('Worker did not start. Check storage/app/wa-spawn.log');
                    }
                }
            }
        } catch(e){ /* ignore */ }
    }

    async function tryStart(){
        const r = await api(URL_START, {method:'POST'});
        if (r.ok) {
            uiRunning();
            startPolling();
            startLogAuto();
            return true;
        }
        // 409 = another campaign (or stale lock) is marked as running.
        if (r.status === 409) {
            const otherId = r.data && r.data.runningCampaignId;
            const isSelf  = otherId && Number(otherId) === Number(CAMPAIGN_ID);
            const prompt = isSelf
                ? 'Le verrou indique que cette campagne tourne déjà, mais aucun worker n\'est actif. Forcer la réinitialisation et redémarrer ?'
                : 'Une autre campagne est marquée comme en cours (ID ' + (otherId ?? '?') + '). Si elle est bloquée, forcer la réinitialisation ?';
            if (confirm(prompt)) {
                const rr = await api(URL_RESET, {method:'POST'});
                if (!rr.ok) { alert('Réinitialisation échouée : ' + apiErr(rr)); return false; }
                const r2 = await api(URL_START, {method:'POST'});
                if (r2.ok) {
                    uiRunning();
                    startPolling();
                    startLogAuto();
                    return true;
                }
                alert('Démarrage échoué après réinitialisation : ' + apiErr(r2));
                return false;
            }
            return false;
        }
        alert('Démarrage échoué : ' + apiErr(r));
        return false;
    }

    btnStart.addEventListener('click', async ()=>{
        btnStart.disabled = true;
        const ok = await tryStart();
        if (!ok) btnStart.disabled = false;
    });
    btnPause.addEventListener('click', async ()=>{
        btnPause.disabled = true;
        uiPaused();
        await api(URL_PAUSE, {method:'POST'});
        btnPause.disabled = false;
        poll();
    });
    btnResume.addEventListener('click', async ()=>{
        btnResume.disabled = true;
        uiRunning(false);
        await api(URL_RESUME, {method:'POST'});
        btnResume.disabled = false;
        poll();
    });
    btnStop.addEventListener('click', async ()=>{
        if (!confirm('Arrêter la campagne ? Vous pourrez la redémarrer plus tard.')) return;
        await api(URL_STOP, {method:'POST'});
    });

    // ---- Log tail -----------------------------------------------------------
    const logPre       = document.getElementById('wa-log-pre');
    const logSize      = document.getElementById('wa-log-size');
    const btnLogRefresh= document.getElementById('wa-log-refresh');
    const btnLogAuto   = document.getElementById('wa-log-toggle-auto');
    let logTimer = null;

    function formatBytes(n){
        if (!n) return '0 B';
        const units = ['B','KB','MB'];
        let i=0; while(n>=1024 && i<units.length-1){ n/=1024; i++; }
        return n.toFixed(i?1:0) + ' ' + units[i];
    }

    async function loadLog(){
        try {
            const r = await api(URL_LOG);
            if (!r.ok || !r.data) return;
            if (!r.data.exists) {
                logPre.textContent = '— Aucun journal pour le moment (la campagne n\'a pas encore démarré) —';
                logSize.textContent = '';
                return;
            }
            const lines = r.data.lines || [];
            logPre.textContent = lines.length ? lines.join('\n') : '(journal vide)';
            logSize.textContent = formatBytes(r.data.size);
            // Auto-scroll to bottom on fresh load
            logPre.scrollTop = logPre.scrollHeight;
        } catch(_){}
    }
    function startLogAuto(){
        if (logTimer) return;
        btnLogAuto.dataset.auto = '1';
        btnLogAuto.innerHTML = '<i class="ph-duotone ph-pause"></i> Auto';
        btnLogAuto.classList.remove('btn-light');
        btnLogAuto.classList.add('btn-light-success');
        loadLog();
        logTimer = setInterval(loadLog, 2500);
    }
    function stopLogAuto(){
        if (logTimer) { clearInterval(logTimer); logTimer = null; }
        btnLogAuto.dataset.auto = '0';
        btnLogAuto.innerHTML = '<i class="ph-duotone ph-play"></i> Auto';
        btnLogAuto.classList.remove('btn-light-success');
        btnLogAuto.classList.add('btn-light');
    }
    btnLogRefresh.addEventListener('click', loadLog);
    btnLogAuto.addEventListener('click', ()=>{
        if (btnLogAuto.dataset.auto === '1') stopLogAuto(); else startLogAuto();
    });
    loadLog();

    // Load initial state — if the campaign is already running (page reload), resume polling.
    (async()=>{
        const r = await api(URL_STATUS);
        if (r.ok && r.data) {
            if (Array.isArray(r.data.messages) && r.data.messages.length) {
                recipients = r.data.messages.map(m=>({
                    phone:m.phone, business:m.business||'', status:m.status||'pending', sentAt:m.sentAt||''
                }));
                renderRecipients(r.data.current);
            }
            updateBadge(r.data.status);
            if (r.data.running) {
                uiRunning(!!r.data.paused);
                startPolling();
                startLogAuto();
            }
        }
    })();
})();
</script>
@endsection
