{{--
    Reusable Bootstrap modal that displays a CRM row in a human-friendly format.

    The table partial injects a `data-row-json="…"` attribute into each row's
    eye button. The JS at the bottom of this partial:
      1. Catches clicks on .crm-row-view buttons
      2. Parses the JSON
      3. Re-renders Classes / Payments / Students rows into clean tabs/lists
      4. Falls back to a generic property list for unknown row shapes
--}}

<div class="modal fade" id="crmRowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="crmRowModalTitle">
                    <i class="ti ti-info-circle text-primary me-1"></i>
                    Détails
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="crmRowModalBody">
                <div class="text-center text-muted py-4">
                    <i class="ti ti-loader spin"></i> Chargement...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

{{-- Dedicated XL modal for the "click on a count pill" student-table view --}}
<div class="modal fade" id="crmStudentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="crmStudentsModalTitle">
                    <i class="ti ti-users text-primary me-1"></i> La liste d'étudiants
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="crmStudentsModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Subtle student-list rows in the modal */
    .crm-student-row { padding: .4rem .6rem; border-radius: .35rem; }
    .crm-student-row:hover { background: var(--bs-light); }
    .crm-student-row .crm-ref { font-size: .7rem; color: var(--bs-secondary); }
    .crm-student-row a.tel { text-decoration: none; }
    .crm-tabs .nav-link { padding: .4rem .9rem; font-size: .85rem; }
    .crm-students-view { cursor: pointer; transition: filter .15s; }
    .crm-students-view:hover { filter: brightness(0.94); }
    .crm-service-row { display: flex; justify-content: space-between; padding: .35rem .5rem; border-bottom: 1px solid var(--bs-border-color-translucent); }
    .crm-service-row:last-child { border-bottom: 0; }
</style>

<script>
// Wait for DOM + Bootstrap JS (loaded by layouts.footerjs after this partial).
function crmRowModalInit() {
    if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
        // Bootstrap not loaded yet — retry on next tick.
        return setTimeout(crmRowModalInit, 50);
    }

    const modalEl         = document.getElementById('crmRowModal');
    const titleEl         = document.getElementById('crmRowModalTitle');
    const bodyEl          = document.getElementById('crmRowModalBody');
    const modal           = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;

    if (!modal) {
        console.warn('[CRM] crmRowModal element not found — skipping init');
        return;
    }

    // Default avatar served for students without a SMALL_AVATAR_PATH or when
    // the remote image fails to load.
    const DEFAULT_AVATAR = @json(asset('build/images/user/avatar-1.jpg'));

    // Map of CRM store id → human-readable center name (from the sites table).
    const STORE_ID_NAMES = @json(app(\App\Services\Crm\CenterContext::class)->storeIdToName());

    // Friendly French labels per column.
    const COLUMN_LABELS = {
        ID: 'Identifiant',
        REFERENCE: 'Référence',
        CNE: 'CNE',
        CIN: 'CIN',
        IDENTITY_ID: 'Identité',
        FIRST_NAME: 'Prénom',
        LAST_NAME: 'Nom',
        SEXE: 'Sexe',
        BIRTHDAY: 'Date de naissance',
        ADDRESS: 'Adresse',
        EMAIL: 'Email',
        PHONE_NUMBER: 'Téléphone',
        WHATSAPP_NUMBER: 'WhatsApp',
        TELEGRAM_NUMBER: 'Telegram',
        CATEGORY_NAME: 'Catégorie',
        NOTE: 'Note',
        STR_STORE_ID: 'Centre',
        SCHOOL_YEAR_ID: 'Année scolaire',
        STATUS_NAME: 'Statut',
        CLASSIFICATION_NAME: 'Niveau',
        SCHOOL_LEVEL_NAME: 'Formation',
        EMPLOYEE_TEACHER_FULL_NAME: 'Enseignant',
        USER_CREATION_FULL_NAME: 'Créé par',
        USER_UPDATE_FULL_NAME: 'Mis à jour par',
        DATE_CREATION: 'Créé le',
        DATE_UPDATE: 'Mis à jour le',
        START_DATE: 'Date de début',
        END_DATE: 'Date de fin',
        EFFECTIVE_DATE: 'Date effective',
        REGISTRATION_DATE: 'Date d\'inscription',
        AMOUNT: 'Montant',
        OPEN_AMOUNT: 'Reste',
        TOTAL_PRICE: 'Total',
        REST_AMOUNT: 'Reste à payer',
        STUDENT_FULL_NAME: 'Étudiant',
        TUTOR_LEGAL_FULL_NAME: 'Tuteur légal',
        PAYMENT_METHOD_NAME: 'Méthode',
        PAYMENT_TYPE_NAME: 'Type',
        PAYMENTS_STATUS_NAME: 'Statut paiement',
        CASH_BOX_ACCOUNT_DESIGNATION: 'Compte caisse',
        ITEMS_NAME: 'Articles',
        ORIGIN: 'Origine',
        DESCRIPTION: 'Description',
        ACTIVE: 'Actif',
        IS_AVANCE: 'Avance',
        // Used inside nested object-list tables
        NAME: 'Nom',
        LEVEL_SESSION_ID: 'Session',
        DUE_DATE: 'Échéance',
        SERVICE_ID: 'Service',
        SERVICE_TYPE_ID: 'Type service',
        SERVICE_TYPE_NAME: 'Service',
        PRICE: 'Prix',
        // Used inside LIST_STUDENT_* arrays
        STUDENT_ID: 'Étudiant',
        STUDENT_REFERENCE: 'Réf.',
        STUDENT_FIRST_NAME: 'Prénom',
        STUDENT_LAST_NAME: 'Nom',
        STUDENT_CIN: 'CIN',
        STUDENT_SEXE: 'Sexe',
        STUDENT_BIRTHDAY: 'Naissance',
        STUDENT_PHONE_NUMBER: 'Téléphone',
        STUDENT_WHATSAPP_NUMBER: 'WhatsApp',
        STUDENT_GRADE_NAME: 'Niveau scolaire',
        OFFER_NAME: 'Offre',
        REGISTRATION_START_DATE: 'Date d\'inscription',
    };

    const labelFor = (key) => {
        if (COLUMN_LABELS[key]) return COLUMN_LABELS[key];
        // Strip trailing _AR / _ID, prettify the rest.
        const isAr = key.endsWith('_AR');
        const base = isAr ? key.slice(0, -3) : key;
        const pretty = base.toLowerCase().replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        return isAr ? `${pretty} (AR)` : pretty;
    };

    // --- helpers -----------------------------------------------------------

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));

    const avatarHtml = (url, size = 64) => {
        const src = url || DEFAULT_AVATAR;
        // Wrap in a hard-clamped span so any aspect ratio fits the circle.
        return `<span style="display:inline-block;width:${size}px;height:${size}px;min-width:${size}px;min-height:${size}px;border-radius:50%;background:#f8f9fa;border:1px solid var(--bs-border-color,#dee2e6);overflow:hidden;vertical-align:middle;">
                    <img src="${esc(src)}"
                         onerror="this.onerror=null;this.src='${DEFAULT_AVATAR}';"
                         alt="photo"
                         style="width:100%;height:100%;object-fit:cover;display:block;">
                </span>`;
    };

    const fmtDate = (s) => {
        if (!s) return '—';
        const d = new Date(s);
        return isNaN(d) ? esc(s) : d.toISOString().slice(0, 10);
    };

    const fmtMoney = (n) => {
        if (n === null || n === undefined || n === '') return '—';
        const v = Number(n);
        if (isNaN(v)) return esc(n);
        return v.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' DH';
    };

    const safeParse = (s) => {
        if (s === null || s === undefined || s === '') return [];
        if (typeof s === 'object') return s;
        try { return JSON.parse(s); } catch { return []; }
    };

    // The API returns student lists with leading `null` placeholders to keep
    // sparse indexes; filter them out.
    const cleanStudents = (arr) => Array.isArray(arr) ? arr.filter(x => x && typeof x === 'object' && x.STUDENT_ID) : [];

    const studentName = (s) => [s.STUDENT_FIRST_NAME, s.STUDENT_LAST_NAME].filter(Boolean).join(' ').trim() || '—';

    // --- renderers ---------------------------------------------------------

    const renderStudentList = (students, emptyMsg = 'Aucun étudiant.') => {
        const clean = cleanStudents(students);
        if (!clean.length) return `<div class="text-muted small py-3 text-center">${esc(emptyMsg)}</div>`;
        return `
            <div class="small text-muted mb-2">${clean.length} étudiant(s)</div>
            <div>
                ${clean.map(s => `
                    <div class="crm-student-row d-flex align-items-center gap-2">
                        <span class="badge bg-light-secondary text-muted" style="width: 2.4rem; text-align: right;">
                            ${esc(s.STUDENT_REFERENCE || '—')}
                        </span>
                        <span class="badge ${s.STUDENT_SEXE === 'F' ? 'bg-light-danger text-danger' : 'bg-light-info text-info'}">
                            ${esc(s.STUDENT_SEXE || '?')}
                        </span>
                        <div class="flex-grow-1">
                            <div>${esc(studentName(s))}</div>
                            <div class="crm-ref">
                                ${s.STUDENT_CIN ? `CIN ${esc(s.STUDENT_CIN)} · ` : ''}
                                Inscrit le ${fmtDate(s.REGISTRATION_START_DATE)}
                            </div>
                        </div>
                        ${s.STUDENT_PHONE_NUMBER
                            ? `<a class="small tel" href="tel:${esc(s.STUDENT_PHONE_NUMBER)}"><i class="ti ti-phone"></i> ${esc(s.STUDENT_PHONE_NUMBER)}</a>`
                            : ''}
                        ${s.STUDENT_WHATSAPP_NUMBER && s.STUDENT_WHATSAPP_NUMBER !== s.STUDENT_PHONE_NUMBER
                            ? `<a class="small tel text-success" target="_blank" href="https://wa.me/${esc(s.STUDENT_WHATSAPP_NUMBER)}"><i class="ti ti-brand-whatsapp"></i></a>`
                            : ''}
                    </div>
                `).join('')}
            </div>
        `;
    };

    const renderServices = (services) => {
        const clean = Array.isArray(services) ? services.filter(Boolean) : [];
        if (!clean.length) return '<div class="text-muted small py-3 text-center">Aucun service.</div>';
        const total = clean.reduce((sum, x) => sum + Number(x.PRICE || 0), 0);
        return `
            <div>
                ${clean.map(s => `
                    <div class="crm-service-row">
                        <div>
                            <div>${esc(s.SERVICE_TYPE_NAME || '—')}</div>
                            <div class="text-muted small">Échéance : ${fmtDate(s.DUE_DATE)}</div>
                        </div>
                        <div class="fw-medium">${fmtMoney(s.PRICE)}</div>
                    </div>
                `).join('')}
                <div class="crm-service-row fw-semibold bg-light">
                    <div>Total</div>
                    <div>${fmtMoney(total)}</div>
                </div>
            </div>
        `;
    };

    const renderClass = (row) => {
        titleEl.innerHTML = `<i class="ti ti-school text-primary me-1"></i> ${esc(row.NAME || 'Classe')}`;

        const active   = safeParse(row.LIST_STUDENT_ACTIVE);
        const archived = safeParse(row.LIST_STUDENT_ARCHIVED);
        const canceled = safeParse(row.LIST_STUDENT_CANCELED);
        const services = safeParse(row.SERVICE_LIST);

        const countA = cleanStudents(active).length;
        const countR = cleanStudents(archived).length;
        const countC = cleanStudents(canceled).length;

        return `
            <div class="mb-3 p-2 bg-light rounded">
                <div class="row g-2 small">
                    <div class="col-6 col-md-3">
                        <div class="text-muted">Référence</div>
                        <div class="fw-medium">${esc(row.REFERENCE || '—')}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted">Niveau</div>
                        <div class="fw-medium">${esc(row.CLASSIFICATION_NAME || '—')}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted">Enseignant</div>
                        <div class="fw-medium">${esc(row.EMPLOYEE_TEACHER_FULL_NAME || '—')}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted">Statut</div>
                        <div class="fw-medium">${esc(row.STATUS_NAME || '—')}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted">Début</div>
                        <div class="fw-medium">${fmtDate(row.START_DATE)}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted">Fin</div>
                        <div class="fw-medium">${fmtDate(row.END_DATE)}</div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs crm-tabs mb-3" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-active" type="button">
                    <span class="badge bg-light-success text-success me-1">${countA}</span> En formation
                </button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-archived" type="button">
                    <span class="badge bg-light-secondary text-muted me-1">${countR}</span> Archivés
                </button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-canceled" type="button">
                    <span class="badge bg-light-danger text-danger me-1">${countC}</span> Annulés
                </button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-services" type="button">
                    <i class="ti ti-cash me-1"></i> Services & frais
                </button></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-active">${renderStudentList(active, 'Aucun étudiant en formation.')}</div>
                <div class="tab-pane fade" id="tab-archived">${renderStudentList(archived, 'Aucun étudiant archivé.')}</div>
                <div class="tab-pane fade" id="tab-canceled">${renderStudentList(canceled, 'Aucun étudiant annulé.')}</div>
                <div class="tab-pane fade" id="tab-services">${renderServices(services)}</div>
            </div>
        `;
    };

    // Columns that look like dates and should be rendered as YYYY-MM-DD.
    const DATE_KEYS = new Set([
        'DATE_CREATION', 'DATE_UPDATE', 'START_DATE', 'END_DATE',
        'EFFECTIVE_DATE', 'BIRTHDAY', 'REGISTRATION_DATE', 'DUE_DATE',
    ]);
    // Columns rendered as monetary amounts.
    const AMOUNT_KEYS = new Set(['AMOUNT', 'OPEN_AMOUNT', 'TOTAL_PRICE', 'REST_AMOUNT', 'PRICE']);

    const renderGeneric = (row) => {
        const fullName = [row.FIRST_NAME, row.LAST_NAME].filter(Boolean).join(' ').trim()
            || row.STUDENT_FULL_NAME
            || row.NAME
            || row.REFERENCE
            || 'Détails';
        titleEl.innerHTML = `<i class="ti ti-info-circle text-primary me-1"></i> ${esc(fullName)}`;

        // Top header card with avatar + key facts when this looks like a person.
        const hasAvatar = 'SMALL_AVATAR_PATH' in row;
        const header = hasAvatar
            ? `
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded mb-3">
                    ${avatarHtml(row.SMALL_AVATAR_PATH, 72)}
                    <div>
                        <div class="h5 mb-1">${esc(fullName)}</div>
                        <div class="small text-muted">
                            ${row.REFERENCE ? `Réf. ${esc(row.REFERENCE)} · ` : ''}
                            ${row.SEXE ? `Sexe ${esc(row.SEXE)} · ` : ''}
                            ${row.BIRTHDAY ? `Né(e) le ${fmtDate(row.BIRTHDAY)}` : ''}
                        </div>
                        ${row.PHONE_NUMBER ? `<div class="small mt-1"><i class="ti ti-phone me-1"></i><a class="text-decoration-none" href="tel:${esc(row.PHONE_NUMBER)}">${esc(row.PHONE_NUMBER)}</a></div>` : ''}
                        ${row.EMAIL ? `<div class="small"><i class="ti ti-mail me-1"></i><a class="text-decoration-none" href="mailto:${esc(row.EMAIL)}">${esc(row.EMAIL)}</a></div>` : ''}
                    </div>
                </div>
              `
            : '';

        // Build the set of *_ID columns to hide when a *_NAME companion exists.
        // (e.g. drop PAYMENT_METHOD_ID if PAYMENT_METHOD_NAME is present.)
        const keys = Object.keys(row);
        const redundantIds = new Set();
        for (const k of keys) {
            if (!k.endsWith('_ID')) continue;
            const stem = k.slice(0, -3); // PAYMENT_METHOD
            const candidates = [`${stem}_NAME`, `${stem}_FULL_NAME`, `${stem}_DESIGNATION`, `${stem}_NAME_FR`];
            if (candidates.some(c => keys.includes(c) && row[c])) {
                redundantIds.add(k);
            }
        }

        // Two-column property list, hiding noisy/redundant fields.
        const skip = new Set([
            'TNT_MODULE_ID', 'USER_CREATION', 'USER_UPDATE',
            'LIST_STUDENT','LIST_STUDENT_ACTIVE','LIST_STUDENT_ARCHIVED','LIST_STUDENT_CANCELED','SERVICE_LIST',
            // Already shown in the header card
            'SMALL_AVATAR_PATH','FIRST_NAME','LAST_NAME','REFERENCE','SEXE','BIRTHDAY','PHONE_NUMBER','EMAIL',
            // Hide every *_NAME_AR and *_FULL_NAME_AR; we render the non-AR one.
        ]);

        // --- helpers for the inner list table ---
        const innerCellFor = (col, val) => {
            if (val === null || val === '') return '<span class="text-muted">—</span>';
            // ACTIVE / IS_AVANCE → Oui/Non pill
            if ((col === 'ACTIVE' || col === 'IS_AVANCE') && (val === 'Y' || val === 'N')) {
                return `<span class="badge bg-light-${val === 'Y' ? 'success text-success' : 'secondary text-muted'}">${val === 'Y' ? 'Oui' : 'Non'}</span>`;
            }
            // Store id → center name
            if (col === 'STR_STORE_ID' && STORE_ID_NAMES[val]) {
                return `<span class="badge bg-light-primary text-primary">${esc(STORE_ID_NAMES[val])}</span>`;
            }
            // Amount-ish columns
            if (['PRICE','AMOUNT','TOTAL','REST_AMOUNT'].includes(col) && !isNaN(Number(val))) {
                return Number(val).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            // Dates
            if (col === 'DUE_DATE' || col === 'START_DATE' || col === 'END_DATE' || col === 'BIRTHDAY' || col === 'REGISTRATION_START_DATE') {
                return fmtDate(val);
            }
            // ID columns dimmed
            if (col.endsWith('_ID') || col === 'ID') {
                return `<span class="text-muted small">#${esc(val)}</span>`;
            }
            return esc(val);
        };

        /**
         * Render an array of objects as a clean Bootstrap table.
         * Skips _AR columns and any column where every value is null.
         */
        const renderObjectList = (arr) => {
            const clean = arr.filter(x => x && typeof x === 'object');
            if (clean.length === 0) return '<div class="text-muted small">Liste vide.</div>';

            // Collect all keys, then drop _AR and all-null columns
            const allKeys = Array.from(new Set(clean.flatMap(o => Object.keys(o))));
            const cols = allKeys.filter(k => {
                if (k.endsWith('_AR')) return false;
                // drop the column entirely if every row has null/empty there
                return clean.some(o => o[k] !== null && o[k] !== '' && o[k] !== undefined);
            });

            const headers = cols.map(c => `<th class="small">${esc(labelFor(c))}</th>`).join('');
            const rows = clean.map(o =>
                '<tr>' + cols.map(c => `<td class="small">${innerCellFor(c, o[c])}</td>`).join('') + '</tr>'
            ).join('');

            return `
                <div class="small text-muted mb-2">${clean.length} élément(s)</div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr>${headers}</tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        };

        /**
         * Try to interpret a value as a list-of-objects. Accepts:
         *   - an actual array
         *   - a JSON string starting with [ that decodes to an array of objects
         * Returns the parsed array or null.
         */
        const asObjectList = (v) => {
            if (Array.isArray(v) && v.length && typeof v[0] === 'object') return v;
            if (typeof v === 'string' && v.trim().startsWith('[')) {
                try {
                    const parsed = JSON.parse(v);
                    if (Array.isArray(parsed) && parsed.some(x => x && typeof x === 'object')) return parsed;
                } catch {}
            }
            return null;
        };

        const formatValue = (k, v) => {
            // Centre lookup
            if (k === 'STR_STORE_ID') {
                const name = STORE_ID_NAMES[v];
                return name
                    ? `<span class="badge bg-light-primary text-primary"><i class="ti ti-building me-1"></i>${esc(name)}</span> <span class="text-muted small">(#${esc(v)})</span>`
                    : `<span class="text-muted small">#${esc(v)}</span>`;
            }
            if (DATE_KEYS.has(k)) {
                return `<span class="text-nowrap">${fmtDate(v)}</span>`;
            }
            if (AMOUNT_KEYS.has(k) && !isNaN(Number(v))) {
                return `<span class="fw-medium">${Number(v).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>`;
            }
            if (k === 'ACTIVE' || k === 'IS_AVANCE') {
                if (v === 'Y' || v === 'N') {
                    return `<span class="badge bg-light-${v === 'Y' ? 'success text-success' : 'secondary text-muted'}">${v === 'Y' ? 'Oui' : 'Non'}</span>`;
                }
            }
            // List-of-objects (e.g. LEVEL_SESSION_PACKAGE_LIST, SERVICE_LIST, LIST_STUDENT_*)
            const list = asObjectList(v);
            if (list) return renderObjectList(list);

            if (typeof v === 'object') return `<code class="small">${esc(JSON.stringify(v))}</code>`;
            if (typeof v === 'string' && v.length > 200) {
                return `<details><summary class="small text-muted">Voir (${v.length} chars)</summary><pre class="small bg-light p-2 rounded">${esc(v)}</pre></details>`;
            }
            return esc(v);
        };

        // Columns that always render full-width because they contain tables
        const FULL_WIDTH_COLS = new Set([
            'LEVEL_SESSION_PACKAGE_LIST',
            'SERVICE_LIST',
            'LIST_STUDENT', 'LIST_STUDENT_ACTIVE', 'LIST_STUDENT_ARCHIVED', 'LIST_STUDENT_CANCELED',
        ]);

        const rows = Object.entries(row)
            .filter(([k, v]) =>
                !skip.has(k)
                && !redundantIds.has(k)
                && !k.endsWith('_AR')
                && v !== null && v !== ''
            )
            .map(([k, v]) => {
                const isWide = FULL_WIDTH_COLS.has(k) || asObjectList(v) !== null;
                const colClass = isWide ? 'col-12' : 'col-12 col-md-6';
                return `<div class="${colClass} mb-2">
                    <div class="text-muted small">${esc(labelFor(k))}</div>
                    <div>${formatValue(k, v)}</div>
                </div>`;
            }).join('');

        return header + `<div class="row">${rows || '<div class="text-muted">Aucune donnée affichable.</div>'}</div>`;
    };

    // ---- Students-table modal (clicked from a count pill) -----------------
    const studentsModalEl    = document.getElementById('crmStudentsModal');
    const studentsModalTitle = document.getElementById('crmStudentsModalTitle');
    const studentsModalBody  = document.getElementById('crmStudentsModalBody');
    const studentsModal      = studentsModalEl ? bootstrap.Modal.getOrCreateInstance(studentsModalEl) : null;

    const fmtDateFR = (s) => {
        if (!s) return '—';
        const d = new Date(s);
        if (isNaN(d)) return esc(s);
        const dd = String(d.getDate()).padStart(2, '0');
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        return `${dd}/${mm}/${d.getFullYear()}`;
    };

    const renderStudentsTable = (students, opts = {}) => {
        const clean = cleanStudents(students);
        if (!clean.length) {
            return `<div class="alert alert-info mb-0"><i class="ti ti-info-circle me-1"></i> ${esc(opts.emptyMsg || 'Aucun étudiant.')}</div>`;
        }
        const rows = clean.map(s => {
            const phone    = s.STUDENT_PHONE_NUMBER || '';
            const whatsapp = s.STUDENT_WHATSAPP_NUMBER || '';
            return `
                <tr>
                    <td class="text-nowrap"><span class="badge bg-light-secondary text-muted">${esc(s.STUDENT_REFERENCE || '—')}</span></td>
                    <td>${esc(s.STUDENT_FIRST_NAME || '—')}</td>
                    <td>${esc(s.STUDENT_LAST_NAME || '—')}</td>
                    <td class="small text-muted">${esc(s.STUDENT_CIN || '—')}</td>
                    <td class="small">
                        ${phone ? `<a href="tel:${esc(phone)}" class="text-decoration-none"><i class="ti ti-phone me-1"></i>${esc(phone)}</a>` : '—'}
                        ${whatsapp && whatsapp !== phone ? `<a href="https://wa.me/${esc(whatsapp)}" target="_blank" class="ms-2 text-success text-decoration-none" title="WhatsApp ${esc(whatsapp)}"><i class="ti ti-brand-whatsapp"></i></a>` : ''}
                    </td>
                    <td class="small">${fmtDateFR(s.STUDENT_BIRTHDAY)}</td>
                    <td class="small">${esc(s.STUDENT_GRADE_NAME || '—')}</td>
                    <td class="small">${esc(s.OFFER_NAME || '—')}</td>
                    <td class="small">${fmtDateFR(s.REGISTRATION_START_DATE)}</td>
                </tr>
            `;
        }).join('');

        return `
            <div class="small text-muted mb-2">${clean.length} étudiant(s)</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Référence</th>
                            <th class="small">Prénom</th>
                            <th class="small">Nom</th>
                            <th class="small">CIN</th>
                            <th class="small">Téléphone</th>
                            <th class="small">Date de naissance</th>
                            <th class="small">Niveau scolaire</th>
                            <th class="small">Offre</th>
                            <th class="small">Date d'inscription</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    };

    // --- data lookup -------------------------------------------------------
    // Each table emits a <script type="application/json" id="crm-rows-data-{tableId}">
    // and tags each button with data-table-id + data-row-index. We use that to
    // pull the full row object (avoids 100KB+ data attributes on every button).
    const rowCache = new Map();
    const getRow = (tableId, rowIndex) => {
        const cacheKey = tableId + ':' + rowIndex;
        if (rowCache.has(cacheKey)) return rowCache.get(cacheKey);
        const dataEl = document.getElementById('crm-rows-data-' + tableId);
        if (!dataEl) return null;
        try {
            const rows = JSON.parse(dataEl.textContent);
            const row = rows[Number(rowIndex)] || null;
            rowCache.set(cacheKey, row);
            return row;
        } catch (err) {
            console.error('[CRM] failed to parse rows JSON for table', tableId, err);
            return null;
        }
    };

    // --- click handlers ----------------------------------------------------

    document.addEventListener('click', function (e) {
        // Row "view details" eye button
        const btn = e.target.closest('.crm-row-view');
        if (btn) {
            e.preventDefault();
            const row = getRow(btn.dataset.tableId, btn.dataset.rowIndex);
            if (!row) {
                bodyEl.innerHTML = `<div class="alert alert-danger">Données introuvables (table=${esc(btn.dataset.tableId)}, index=${esc(btn.dataset.rowIndex)}).</div>`;
                modal.show();
                return;
            }
            const kind = btn.dataset.rowKind || 'generic';
            bodyEl.innerHTML = (kind === 'class') ? renderClass(row) : renderGeneric(row);
            modal.show();
            return;
        }

        // Count pill → "Liste d'étudiants" XL table modal
        const pill = e.target.closest('.crm-students-view');
        if (pill) {
            e.preventDefault();
            if (!studentsModal) {
                console.error('[CRM] crmStudentsModal not in DOM');
                return;
            }
            const row = getRow(pill.dataset.tableId, pill.dataset.rowIndex);
            if (!row) {
                studentsModalBody.innerHTML = `<div class="alert alert-danger">Données introuvables.</div>`;
                studentsModal.show();
                return;
            }
            const bucket = pill.dataset.bucket || 'active';
            const map = {
                active:   { label: 'En formation', field: 'LIST_STUDENT_ACTIVE',   empty: 'Aucun étudiant en formation.' },
                archived: { label: 'Archivés',     field: 'LIST_STUDENT_ARCHIVED', empty: 'Aucun étudiant archivé.'    },
                canceled: { label: 'Annulés',      field: 'LIST_STUDENT_CANCELED', empty: 'Aucun étudiant annulé.'     },
            };
            const cfg = map[bucket] || map.active;
            const list = safeParse(row[cfg.field]);

            const className = row.NAME ? ` — ${esc(row.NAME)}` : '';
            studentsModalTitle.innerHTML = `<i class="ti ti-users text-primary me-1"></i> La liste d'étudiants <span class="badge bg-light-primary text-primary ms-2">${esc(cfg.label)}</span>${className}`;
            studentsModalBody.innerHTML = renderStudentsTable(list, { emptyMsg: cfg.empty });
            studentsModal.show();
        }
    });
}

// Fire after DOMContentLoaded; the function will retry if Bootstrap isn't ready yet.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', crmRowModalInit);
} else {
    crmRowModalInit();
}
</script>
