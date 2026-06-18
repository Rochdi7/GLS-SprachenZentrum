@extends('layouts.main')

@section('title', 'Centre aide - Documentation')
@section('breadcrumb-item', 'Aide')
@section('breadcrumb-item-active', 'Documentation')

@section('css')
    <style>
        .doc-hero {
            border: 1px solid #e7ecf3;
            border-radius: 28px;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(4, 169, 245, 0.18), transparent 28%),
                linear-gradient(135deg, #ffffff 0%, #f7fbff 55%, #f4f8fd 100%);
            box-shadow: 0 24px 48px rgba(18, 38, 63, 0.08);
        }

        .doc-hero__body {
            padding: 32px;
        }

        .doc-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: #eaf5ff;
            color: #0b72c7;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .doc-hero__title {
            margin: 16px 0 10px;
            font-size: 2rem;
            line-height: 1.1;
            color: #233044;
            font-weight: 800;
        }

        .doc-hero__text {
            max-width: 820px;
            color: #5c6b82;
            font-size: 1rem;
            margin-bottom: 0;
        }

        .doc-metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-top: 24px;
        }

        .doc-metric {
            border: 1px solid #e8eef5;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.92);
            padding: 18px;
        }

        .doc-metric__value {
            font-size: 1.35rem;
            font-weight: 800;
            color: #233044;
        }

        .doc-metric__label {
            margin-top: 6px;
            color: #718198;
            font-size: 0.85rem;
        }

        .doc-section {
            margin-top: 24px;
        }

        .doc-card {
            border: 1px solid #e7ecf3;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 14px 34px rgba(18, 38, 63, 0.05);
            height: 100%;
        }

        .doc-card__header {
            padding: 22px 24px 10px;
        }

        .doc-card__body {
            padding: 0 24px 24px;
        }

        .doc-card__icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 14px;
        }

        .doc-card__title {
            margin: 0;
            font-size: 1.12rem;
            font-weight: 800;
            color: #243042;
        }

        .doc-card__text {
            margin-top: 8px;
            margin-bottom: 0;
            color: #66768d;
        }

        .doc-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .doc-list li {
            position: relative;
            padding-left: 22px;
            margin-bottom: 12px;
            color: #475467;
        }

        .doc-list li::before {
            content: "";
            position: absolute;
            left: 0;
            top: 9px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #04a9f5;
        }

        .doc-list strong {
            color: #243042;
        }

        .doc-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .doc-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .doc-chipline {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .doc-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 999px;
            background: #f4f7fb;
            color: #526177;
            font-size: 0.85rem;
            font-weight: 700;
        }

        /* ── LANGUAGE SWITCH ───────────────────────────── */
        .doc-langbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 16px;
        }
        .doc-langbar__hint {
            color: #718198;
            font-size: .82rem;
            font-weight: 600;
            margin-right: auto;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .doc-langbtn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 999px;
            border: 1px solid #d8e2ef;
            background: #fff;
            color: #475467;
            font-weight: 700;
            font-size: .9rem;
            cursor: pointer;
            transition: all .2s ease;
        }
        .doc-langbtn:hover { border-color: #0b72c7; color: #0b72c7; }
        .doc-langbtn.is-active {
            background: #233044;
            border-color: #233044;
            color: #fff;
        }

        /* ── QUICK-JUMP TAG BAR ────────────────────────── */
        .doc-tagbar {
            position: sticky;
            top: 70px;
            z-index: 30;
            margin: 24px 0 8px;
            padding: 16px 20px;
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid #e7ecf3;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(18,38,63,.06);
        }
        .doc-tagbar__label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #5c6b82;
            margin-bottom: 10px;
            display: flex; align-items: center; gap: 8px;
        }
        .doc-tagbar__label i { color: #0b72c7; font-size: 1rem; }
        .doc-tagbar__list { display: flex; flex-wrap: wrap; gap: 8px; }
        .doc-tag {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 14px;
            background: #f4f8fd;
            border: 1px solid #e3ebf5;
            border-radius: 999px;
            color: #233044;
            font-size: .85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all .2s ease;
            white-space: nowrap;
        }
        .doc-tag i { font-size: 1rem; }
        .doc-tag:hover {
            background: #233044;
            border-color: #233044;
            color: #fff;
            transform: translateY(-2px);
            text-decoration: none;
        }
        .doc-tag--blue   { background: #eaf5ff; border-color: #cfe5ff; color: #0b72c7; }
        .doc-tag--green  { background: #e6f7ec; border-color: #c3ebcf; color: #0f8a58; }
        .doc-tag--purple { background: #f1ebff; border-color: #ddcfff; color: #6d44d8; }
        .doc-tag--pink   { background: #ffe9f1; border-color: #ffc6da; color: #c01e69; }
        .doc-tag--orange { background: #fff1e0; border-color: #ffd9b3; color: #c8651a; }
        .doc-tag--gold   { background: #fff7d6; border-color: #ffe8a3; color: #8a6d00; }
        .doc-tag--red    { background: #fde7e9; border-color: #f6c0c4; color: #b3252f; }
        .doc-tag--teal   { background: #e0f7f6; border-color: #b6ebe8; color: #0c8079; }

        .doc-hero__title[id],
        .doc-card__title[id],
        .doc-card[id] { scroll-margin-top: 160px; }

        .doc-workflow {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .doc-step {
            border: 1px solid #e8eef5;
            border-radius: 20px;
            padding: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f9fbfe 100%);
        }

        .doc-step__num {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eaf5ff;
            color: #0b72c7;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .doc-step__title {
            margin: 0 0 8px;
            font-size: 1rem;
            font-weight: 800;
            color: #243042;
        }

        .doc-step__text {
            margin: 0;
            color: #66768d;
            font-size: 0.92rem;
        }

        .doc-note {
            border: 1px dashed #c8d7ea;
            border-radius: 20px;
            padding: 18px 20px;
            background: #f8fbff;
            color: #4f6077;
        }

        .doc-note strong {
            color: #233044;
        }

        .bg-doc-blue { background: #eaf5ff; color: #0b72c7; }
        .bg-doc-green { background: #e9fbf3; color: #0f8a58; }
        .bg-doc-orange { background: #fff2e8; color: #d66b1f; }
        .bg-doc-purple { background: #f1ebff; color: #6d44d8; }
        .bg-doc-pink { background: #ffeef5; color: #d84a84; }
        .bg-doc-yellow { background: #fff7df; color: #b98000; }
        .bg-doc-teal { background: #e0f7f6; color: #0c8079; }

        /* ── ARABIC / RTL ──────────────────────────────── */
        #doc-ar { display: none; }
        body.doc-lang-ar #doc-fr { display: none; }
        body.doc-lang-ar #doc-ar { display: block; }

        #doc-ar,
        #doc-ar .doc-hero__body,
        #doc-ar .doc-card__body,
        #doc-ar .doc-tagbar {
            direction: rtl;
            text-align: right;
        }
        #doc-ar .doc-list li {
            padding-left: 0;
            padding-right: 22px;
        }
        #doc-ar .doc-list li::before {
            left: auto;
            right: 0;
        }
        #doc-ar .doc-step__num { margin-bottom: 12px; }
        #doc-ar .doc-tag,
        #doc-ar .doc-eyebrow,
        #doc-ar .doc-chip {
            flex-direction: row-reverse;
        }
        #doc-ar .doc-langbar { direction: ltr; }
        #doc-ar .doc-tagbar__label { flex-direction: row-reverse; }
        #doc-ar table { direction: rtl; }

        @media (max-width: 1199.98px) {
            .doc-metrics,
            .doc-workflow,
            .doc-grid-3 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .doc-hero__body {
                padding: 24px;
            }

            .doc-tagbar { padding: 14px 16px; }
            .doc-hero__title {
                font-size: 1.6rem;
            }

            .doc-langbar { flex-wrap: wrap; }
            .doc-langbar__hint { width: 100%; margin-bottom: 6px; }

            .doc-grid-2,
            .doc-grid-3,
            .doc-metrics,
            .doc-workflow {
                grid-template-columns: 1fr;
            }
        }

        /* ============================================================== */
        /*  DARK MODE  (theme toggles [data-pc-theme="dark"] on body)      */
        /*  Surfaces go dark; text is re-lightened so nothing disappears.  */
        /*  Colored pastel pills (doc-tag--*, bg-doc-*, eyebrow, icons)    */
        /*  already pair light bg + colored text, so they stay readable.   */
        /* ============================================================== */
        [data-pc-theme="dark"] {
            --doc-surface: #1f2731;     /* card / hero base */
            --doc-surface-2: #262f3a;   /* nested boxes */
            --doc-border: rgba(255, 255, 255, 0.09);
            --doc-heading: #f1f4f8;
            --doc-text: #b9c2cf;
            --doc-text-soft: #8b97a7;
        }

        /* Hero — inline style backgrounds need !important to be overridden */
        [data-pc-theme="dark"] .doc-hero {
            border-color: var(--doc-border);
            background:
                radial-gradient(circle at top right, rgba(4, 169, 245, 0.20), transparent 30%),
                var(--doc-surface) !important;
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.40);
        }

        [data-pc-theme="dark"] .doc-hero__title { color: var(--doc-heading); }
        [data-pc-theme="dark"] .doc-hero__text { color: var(--doc-text); }

        [data-pc-theme="dark"] .doc-metric {
            border-color: var(--doc-border);
            background: rgba(255, 255, 255, 0.04);
        }
        [data-pc-theme="dark"] .doc-metric__value { color: var(--doc-heading); }
        [data-pc-theme="dark"] .doc-metric__label { color: var(--doc-text-soft); }

        /* Cards */
        [data-pc-theme="dark"] .doc-card {
            border-color: var(--doc-border);
            background: var(--doc-surface);
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.35);
        }
        [data-pc-theme="dark"] .doc-card__title { color: var(--doc-heading); }
        [data-pc-theme="dark"] .doc-card__text { color: var(--doc-text); }

        /* Lists */
        [data-pc-theme="dark"] .doc-list li { color: var(--doc-text); }
        [data-pc-theme="dark"] .doc-list strong { color: var(--doc-heading); }

        /* Generic chips (neutral grey ones) */
        [data-pc-theme="dark"] .doc-chip {
            background: var(--doc-surface-2);
            color: var(--doc-text);
        }

        /* Language buttons */
        [data-pc-theme="dark"] .doc-langbtn {
            border-color: var(--doc-border);
            background: var(--doc-surface-2);
            color: var(--doc-text);
        }
        [data-pc-theme="dark"] .doc-langbtn:hover { border-color: #4aa3ff; color: #4aa3ff; }
        [data-pc-theme="dark"] .doc-langbtn.is-active {
            background: #4680ff;
            border-color: #4680ff;
            color: #fff;
        }

        /* Quick-jump tag bar (the sticky pill bar) */
        [data-pc-theme="dark"] .doc-tagbar {
            background: rgba(31, 39, 49, 0.92);
            border-color: var(--doc-border);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }
        [data-pc-theme="dark"] .doc-tagbar__label { color: var(--doc-text-soft); }
        /* Neutral (uncolored) tags only — colored doc-tag--* keep their pastel look */
        [data-pc-theme="dark"] .doc-tag:not([class*="doc-tag--"]) {
            background: var(--doc-surface-2);
            border-color: var(--doc-border);
            color: var(--doc-text);
        }

        /* Workflow steps */
        [data-pc-theme="dark"] .doc-step {
            border-color: var(--doc-border);
            background: rgba(255, 255, 255, 0.03);
        }
        [data-pc-theme="dark"] .doc-step__title { color: var(--doc-heading); }
        [data-pc-theme="dark"] .doc-step__text { color: var(--doc-text); }

        /* Notes */
        [data-pc-theme="dark"] .doc-note {
            border-color: rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.03);
            color: var(--doc-text);
        }
        [data-pc-theme="dark"] .doc-note strong { color: var(--doc-heading); }

        /* Tables inside docs */
        [data-pc-theme="dark"] .doc-card table,
        [data-pc-theme="dark"] .doc-hero table {
            color: var(--doc-text);
        }
    </style>
@endsection

@section('content')

    {{-- ── LANGUAGE SWITCH (shared) ──────────────────────── --}}
    <div class="doc-langbar">
        <span class="doc-langbar__hint">
            <i class="ph-duotone ph-translate"></i>
            Langue de la documentation / لغة التوثيق
        </span>
        <button type="button" class="doc-langbtn is-active" data-doc-lang="fr">
            <i class="ph-duotone ph-flag"></i> Français
        </button>
        <button type="button" class="doc-langbtn" data-doc-lang="ar">
            <i class="ph-duotone ph-flag"></i> العربية
        </button>
    </div>

    {{-- ================================================================== --}}
    {{-- ████████  VERSION FRANÇAISE  ████████                              --}}
    {{-- ================================================================== --}}
    <div id="doc-fr">

    <div class="row">
        <div class="col-12">
            <section class="doc-hero">
                <div class="doc-hero__body">
                    <div class="doc-eyebrow">
                        <i class="ph-duotone ph-book-open-text"></i>
                        Guide operateur
                    </div>
                    <h1 class="doc-hero__title">Documentation d'utilisation du backoffice GLS</h1>
                    <p class="doc-hero__text">
                        Cette page sert de mode d'emploi interne pour l'equipe GLS. Elle explique comment naviguer dans le portail,
                        quelles operations faire dans chaque module et dans quel ordre traiter les taches quotidiennes.
                    </p>

                    <div class="doc-metrics">
                        <div class="doc-metric">
                            <div class="doc-metric__value">8+</div>
                            <div class="doc-metric__label">zones du portail</div>
                        </div>
                        <div class="doc-metric">
                            <div class="doc-metric__value">1</div>
                            <div class="doc-metric__label">routine simple par jour</div>
                        </div>
                        <div class="doc-metric">
                            <div class="doc-metric__value">PDF</div>
                            <div class="doc-metric__label">exports disponibles</div>
                        </div>
                        <div class="doc-metric">
                            <div class="doc-metric__value">Equipe</div>
                            <div class="doc-metric__label">usage administratif</div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- ── QUICK-JUMP TAG BAR ───────────────────────────── --}}
    <div class="row">
        <div class="col-12">
            <div class="doc-tagbar">
                <div class="doc-tagbar__label">
                    <i class="ph-duotone ph-bookmark-simple"></i>
                    Acces rapide aux modules
                </div>
                <div class="doc-tagbar__list">
                    <a href="#doc-school" class="doc-tag doc-tag--blue"><i class="ph-duotone ph-buildings"></i> Gestion ecole</a>
                    <a href="#doc-translations" class="doc-tag doc-tag--red"><i class="ph-duotone ph-translate"></i> Traductions</a>
                    <a href="#doc-attestation-requests" class="doc-tag doc-tag--gold"><i class="ph-duotone ph-mail-forward"></i> Demandes attestation</a>
                    <a href="#doc-attestations" class="doc-tag doc-tag--green"><i class="ph-duotone ph-file-text"></i> Attestations</a>
                    <a href="#doc-certificates" class="doc-tag doc-tag--purple"><i class="ph-duotone ph-certificate"></i> Certificats</a>
                    <a href="#doc-studienkollegs" class="doc-tag doc-tag--orange"><i class="ph-duotone ph-graduation-cap"></i> Studienkollegs</a>
                    <a href="#doc-quizzes" class="doc-tag doc-tag--pink"><i class="ph-duotone ph-question"></i> Quizzes</a>
                    <a href="#doc-blog" class="doc-tag doc-tag--blue"><i class="ph-duotone ph-newspaper"></i> Blog</a>
                    <a href="#doc-leads" class="doc-tag doc-tag--pink"><i class="ph-duotone ph-address-book"></i> Leads &amp; Applications</a>
                    <a href="#doc-newsletter" class="doc-tag"><i class="ph-duotone ph-envelope"></i> Newsletter</a>
                    <a href="#doc-feedbacks" class="doc-tag doc-tag--teal"><i class="ph-duotone ph-star"></i> Avis (QR)</a>
                    <a href="#doc-planning" class="doc-tag doc-tag--blue"><i class="ph-duotone ph-calendar"></i> Planning</a>
                    <a href="#doc-rapport" class="doc-tag doc-tag--blue"><i class="ph-duotone ph-clipboard-text"></i> Rapport semaine</a>
                    <a href="#doc-users" class="doc-tag"><i class="ph-duotone ph-user-gear"></i> Utilisateurs &amp; Roles</a>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <div class="doc-card">
                    <div class="doc-card__header">
                        <div class="doc-card__icon bg-doc-blue">
                            <i class="ph-duotone ph-compass"></i>
                        </div>
                        <h5 class="doc-card__title">Structure du portail</h5>
                    </div>
                    <div class="doc-card__body">
                        <div class="doc-chipline">
                            <span class="doc-chip"><i class="ph-duotone ph-squares-four"></i> Dashboard</span>
                            <span class="doc-chip"><i class="ph-duotone ph-chart-line-up"></i> Pilotage</span>
                            <span class="doc-chip"><i class="ph-duotone ph-buildings"></i> Gestion ecole</span>
                            <span class="doc-chip"><i class="ph-duotone ph-address-book"></i> Admissions & leads</span>
                            <span class="doc-chip"><i class="ph-duotone ph-newspaper"></i> Contenu</span>
                            <span class="doc-chip"><i class="ph-duotone ph-user-gear"></i> Administration</span>
                        </div>
                        <p class="doc-card__text mt-3">
                            Le menu lateral est organise par mission. Ouvrez toujours le module qui correspond a l'action
                            que vous voulez faire au lieu de chercher page par page.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <div class="doc-card">
                    <div class="doc-card__header">
                        <div class="doc-card__icon bg-doc-green">
                            <i class="ph-duotone ph-lightning"></i>
                        </div>
                        <h5 class="doc-card__title">Demarrage rapide</h5>
                    </div>
                    <div class="doc-card__body">
                        <div class="doc-workflow">
                            <div class="doc-step">
                                <div class="doc-step__num">1</div>
                                <h6 class="doc-step__title">Ouvrir le dashboard</h6>
                                <p class="doc-step__text">Verifier les compteurs et les elements a traiter en priorite.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">2</div>
                                <h6 class="doc-step__title">Traiter les leads</h6>
                                <p class="doc-step__text">Verifier consultations, inscriptions et applications recues.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">3</div>
                                <h6 class="doc-step__title">Suivre les groupes</h6>
                                <p class="doc-step__text">Controler groupes, enseignants et suivi niveau en cours.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">4</div>
                                <h6 class="doc-step__title">Mettre a jour le contenu</h6>
                                <p class="doc-step__text">Publier blogs, quiz, certificats ou documents si besoin.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-3">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue">
                        <i class="ph-duotone ph-chart-line-up"></i>
                    </div>
                    <h5 class="doc-card__title">Pilotage</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Dashboard :</strong> vue d'ensemble des activites et indicateurs.</li>
                        <li><strong>Suivi niveau :</strong> suivre la progression des groupes, ouvrir le detail, exporter le PDF groupe et marquer un suivi termine.</li>
                        <li><strong>Bon usage :</strong> traiter d'abord les cartes marquees en cours puis verifier les echeances.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-orange">
                        <i class="ph-duotone ph-graduation-cap"></i>
                    </div>
                    <h5 class="doc-card__title">Gestion ecole</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Centres GLS :</strong> gerer les centres, coordonnees et informations de presentation.</li>
                        <li><strong>Enseignants :</strong> ajouter, modifier ou archiver les profils pedagogiques.</li>
                        <li><strong>Groupes :</strong> creer les groupes, choisir niveau, dates, centre et enseignant.</li>
                        <li><strong>Certificats :</strong> creer et exporter les certificats PDF.</li>
                        <li><strong>Studienkollegs / Quizzes :</strong> gerer l'offre academique et les tests.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-pink">
                        <i class="ph-duotone ph-users"></i>
                    </div>
                    <h5 class="doc-card__title">Admissions & leads</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Applications :</strong> suivre les candidatures rattachees aux groupes.</li>
                        <li><strong>Leads :</strong> consulter les demandes entrantes et supprimer les doublons ou erreurs.</li>
                        <li><strong>Routine :</strong> verifier ce bloc chaque jour avant de passer aux autres modules.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-yellow">
                        <i class="ph-duotone ph-newspaper"></i>
                    </div>
                    <h5 class="doc-card__title">Contenu</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Categories blog :</strong> structurer les sujets avant de publier.</li>
                        <li><strong>Articles blog :</strong> rediger, illustrer puis publier ou garder en brouillon.</li>
                        <li><strong>Conseil :</strong> definir la categorie avant l'article pour garder un backoffice propre.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-purple">
                        <i class="ph-duotone ph-shield-check"></i>
                    </div>
                    <h5 class="doc-card__title">Administration</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Utilisateurs :</strong> gerer les acces au backoffice.</li>
                        <li><strong>Mon compte :</strong> mettre a jour son profil et son mot de passe.</li>
                        <li><strong>Regle simple :</strong> chaque utilisateur doit avoir un acces personnel, jamais partage.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green">
                        <i class="ph-duotone ph-lifebuoy"></i>
                    </div>
                    <h5 class="doc-card__title">Support</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Centre d'aide :</strong> accessible depuis la sidebar.</li>
                        <li><strong>Blocage fonctionnel :</strong> noter le module, la page et l'action qui pose probleme.</li>
                        <li><strong>Support externe :</strong> utiliser le bouton de support en bas de page si necessaire.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue">
                        <i class="ph-duotone ph-list-checks"></i>
                    </div>
                    <h5 class="doc-card__title">Routine quotidienne recommandee</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>1.</strong> Verifier le dashboard et les alertes du jour.</li>
                        <li><strong>2.</strong> Ouvrir `Admissions & leads` pour traiter les nouvelles demandes.</li>
                        <li><strong>3.</strong> Controler `Groupes` puis `Suivi niveau` pour les classes en cours.</li>
                        <li><strong>4.</strong> Generer les certificats ou PDF groupes si besoin.</li>
                        <li><strong>5.</strong> Mettre a jour le blog, les quiz ou les contenus publics si prevu.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-orange">
                        <i class="ph-duotone ph-warning-circle"></i>
                    </div>
                    <h5 class="doc-card__title">Bonnes pratiques</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Verifier avant suppression :</strong> toute suppression doit etre volontaire et confirmee.</li>
                        <li><strong>Nommer clairement :</strong> centres, groupes, articles et certificats doivent etre faciles a retrouver.</li>
                        <li><strong>Utiliser les filtres :</strong> ne pas travailler en liste complete si un filtre centre ou statut existe.</li>
                        <li><strong>Preferer la page detail :</strong> pour un suivi niveau, les notes restent dans la vue detail du groupe.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    {{-- ================================================================== --}}
    {{-- AVIS / FEEDBACKS (QR public)                                       --}}
    {{-- ================================================================== --}}

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(12,128,121,0.16), transparent 28%), linear-gradient(135deg, #ffffff 0%, #f0fbfa 55%, #f4f8fd 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow" style="background:#e0f7f6;color:#0c8079;">
                            <i class="ph-duotone ph-star"></i>
                            Module Avis
                        </div>
                        <h2 id="doc-feedbacks" class="doc-hero__title">Avis etudiants — Formulaire QR</h2>
                        <p class="doc-hero__text">
                            Les etudiants laissent un avis en scannant un QR code affiche dans le centre. Les avis arrivent
                            dans le backoffice ou l'equipe peut les lire et les supprimer.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-teal"><i class="ph-duotone ph-qr-code"></i></div>
                    <h5 class="doc-card__title">Collecte par QR code</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Page QR :</strong> Avis &rarr; QR. Imprimer / afficher le QR dans le centre.</li>
                        <li>L'etudiant scanne, choisit son centre et laisse son nom + message.</li>
                        <li>Aucun compte requis : le formulaire est public.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-teal"><i class="ph-duotone ph-chat-dots"></i></div>
                    <h5 class="doc-card__title">Lecture des avis</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Liste :</strong> nom, centre, message, statut lu / non lu.</li>
                        <li>Ouvrir un avis pour voir le detail (le marque comme lu).</li>
                        <li>Supprimer un avis non pertinent.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>


    {{-- ================================================================== --}}
    {{-- RH — Mon planning (semaine)                                       --}}
    {{-- ================================================================== --}}

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(70,128,255,0.15), transparent 28%), linear-gradient(135deg, #ffffff 0%, #f2f6ff 55%, #f4f8fd 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow" style="background:#eaf5ff;color:#0b72c7;">
                            <i class="ph-duotone ph-clock"></i>
                            Module RH / Planning
                        </div>
                        <h2 id="doc-planning" class="doc-hero__title">Mon planning — Semaine</h2>
                        <p class="doc-hero__text">
                            Cette page permet de saisir les horaires de travail (debut / fin / pause) pour chaque jour de la
                            semaine. Le temps travaille est calcule <strong>en temps reel</strong> au fur et a mesure de la saisie,
                            en soustrayant automatiquement la pause. Un administrateur peut gerer le planning des autres membres
                            de l'equipe.
                        </p>
                        <div class="doc-metrics">
                            <div class="doc-metric">
                                <div class="doc-metric__value">Live</div>
                                <div class="doc-metric__label">calcul en temps reel</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">7 jours</div>
                                <div class="doc-metric__label">lundi a dimanche</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">Total</div>
                                <div class="doc-metric__label">semaine automatique</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">Admin</div>
                                <div class="doc-metric__label">gerer d'autres plannings</div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue">
                        <i class="ph-duotone ph-calculator"></i>
                    </div>
                    <h5 class="doc-card__title">Calcul du temps travaille</h5>
                </div>
                <div class="doc-card__body">
                    <p class="doc-card__text"><strong>Formule :</strong></p>
                    <ul class="doc-list">
                        <li><strong>Travaille = Fin - Debut - (Pause fin - Pause debut)</strong></li>
                        <li>Si la pause depasse les heures de travail, elle est coupee aux bornes Debut / Fin.</li>
                        <li>Si Debut, Fin sont vides ou que Fin &le; Debut, la ligne affiche « — » et ne compte pas dans le total.</li>
                        <li>La cellule <strong>Travaille</strong> et le <strong>Total semaine</strong> se mettent a jour instantanement a chaque modification d'heure.</li>
                    </ul>
                    <div class="doc-note mt-3">
                        <strong>Exemple :</strong> Debut 09:30, Fin 19:30, Pause 14:00 &rarr; 16:00.<br>
                        Travaille = 10h00 - 2h00 = <strong>8h00</strong>.
                    </div>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue">
                        <i class="ph-duotone ph-users-three"></i>
                    </div>
                    <h5 class="doc-card__title">Naviguer et gerer</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Precedente / Suivante :</strong> changer de semaine.</li>
                        <li><strong>Selecteur de date :</strong> sauter directement a une semaine donnee.</li>
                        <li><strong>Gerer le planning de (admin) :</strong> pour un administrateur, menu deroulant « Moi-meme / autre utilisateur » pour saisir le planning d'un collegue.</li>
                        <li><strong>Notes :</strong> champ libre par jour (500 caracteres max).</li>
                        <li><strong>Vider un jour :</strong> laisser Debut et Fin vides et enregistrer supprime l'entree du jour.</li>
                        <li><strong>Enregistrer la semaine :</strong> bouton en bas de page ; le calcul cote serveur est identique au calcul en direct.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- PILOTAGE — Rapport Semaine (Enseignants)                          --}}
    {{-- ================================================================== --}}

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(70,128,255,0.18), transparent 28%), linear-gradient(135deg, #ffffff 0%, #f3f7ff 55%, #f4f8fd 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow" style="background:#eaf5ff;color:#0b72c7;">
                            <i class="ph-duotone ph-calendar-check"></i>
                            Module Pilotage
                        </div>
                        <h2 id="doc-rapport" class="doc-hero__title">Rapport Semaine — Enseignants</h2>
                        <p class="doc-hero__text">
                            Cette page sert de carnet de bord : pour chaque jour de la semaine, on note ce que chaque enseignant
                            a fait (cours, intervention, absence justifiee, etc.). La vue semaine offre un calendrier lundi-vendredi
                            et un aper&ccedil;u mensuel accessible depuis un bouton dedie.
                        </p>
                        <div class="doc-metrics">
                            <div class="doc-metric">
                                <div class="doc-metric__value">Semaine</div>
                                <div class="doc-metric__label">calendrier lun-ven</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">Mois</div>
                                <div class="doc-metric__label">modale aper&ccedil;u</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">PDF</div>
                                <div class="doc-metric__label">export semaine</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">Mobile</div>
                                <div class="doc-metric__label">vue adaptative</div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue">
                        <i class="ph-duotone ph-pencil-simple-line"></i>
                    </div>
                    <h5 class="doc-card__title">Ajouter / modifier un rapport</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Cliquer sur une case jour</strong> (ou le bouton + au survol) pour ouvrir la modale d'ajout.</li>
                        <li><strong>Enseignant :</strong> choisir dans la liste des enseignants.</li>
                        <li><strong>Notes :</strong> decrire ce que l'enseignant a fait ce jour-la (2000 caracteres max).</li>
                        <li><strong>Modifier :</strong> cliquer sur un rapport existant (chip bleu) rouvre la modale pre-remplie avec un bouton <strong>Supprimer</strong>.</li>
                        <li><strong>Un couple Enseignant + Date :</strong> un seul rapport par enseignant par jour ; une nouvelle saisie met a jour l'existant.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue">
                        <i class="ph-duotone ph-calendar-blank"></i>
                    </div>
                    <h5 class="doc-card__title">Icone calendrier — Vue mensuelle</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Bouton calendrier</strong> a cote de la navigation semaine : ouvre une modale avec la grille du mois complet (6 lignes &times; 7 colonnes).</li>
                        <li><strong>Navigation mois :</strong> fleches precedent / suivant et bouton « Aujourd'hui ».</li>
                        <li><strong>Chaque case :</strong> numero du jour + jusqu'a 3 rapports visibles (nom enseignant + debut de la note). Au-dela, un indicateur « +N autres » apparait.</li>
                        <li><strong>Cliquer une case :</strong> saute a la semaine correspondante pour editer le jour.</li>
                        <li><strong>Mobile :</strong> les chips se resument a une pastille bleue + un compteur pour garder la grille lisible.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue">
                        <i class="ph-duotone ph-device-mobile"></i>
                    </div>
                    <h5 class="doc-card__title">Affichage adaptatif</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Desktop (&ge; 992 px) :</strong> calendrier tableau lundi a vendredi, une colonne par jour.</li>
                        <li><strong>Tablette / mobile :</strong> cartes empilees jour par jour, avec un bouton + dedie pour ajouter rapidement.</li>
                        <li><strong>Aujourd'hui :</strong> la case ou la carte du jour est mise en avant.</li>
                        <li><strong>Langue :</strong> les jours et mois s'affichent toujours en fran&ccedil;ais (<em>lundi, mardi, avril, mai...</em>) meme si l'interface passe en anglais.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue">
                        <i class="ph-duotone ph-file-pdf"></i>
                    </div>
                    <h5 class="doc-card__title">Export PDF</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>Bouton <strong>Export PDF</strong> en haut de la page : exporte la semaine visible au format paysage A4.</li>
                        <li>Contient : grille jour par jour et regroupement par enseignant.</li>
                        <li>Nomme automatiquement <code>rapport_semaine_YYYY-MM-DD_YYYY-MM-DD.pdf</code>.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>


    {{-- ================================================================== --}}
    {{-- Routine recapitulative (updated)                                   --}}
    {{-- ================================================================== --}}

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <div class="doc-card">
                    <div class="doc-card__header">
                        <div class="doc-card__icon bg-doc-orange">
                            <i class="ph-duotone ph-list-numbers"></i>
                        </div>
                        <h5 class="doc-card__title">Routine recapitulative (modules recents inclus)</h5>
                    </div>
                    <div class="doc-card__body">
                        <ul class="doc-list">
                            <li><strong>1. Dashboard :</strong> verifier les compteurs et alertes du jour.</li>
                            <li><strong>2. Admissions & leads :</strong> traiter consultations, inscriptions, applications.</li>
                            <li><strong>3. Rapport Semaine :</strong> noter les activites des enseignants (et consulter le mois via l'icone calendrier).</li>
                            <li><strong>4. Mon planning :</strong> saisir ses horaires du jour ; verifier le total semaine calcule en direct.</li>
                            <li><strong>7. Contenu :</strong> publier un blog, quiz ou certificat si prevu au planning editorial.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         TRADUCTIONS — Maroc / Allemagne
         ========================================================= --}}
    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(210,39,48,0.16), transparent 28%), linear-gradient(135deg, #ffffff 0%, #fff5f5 55%, #fffbe8 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow" style="background:#fde7e9;color:#b3252f;">
                            <i class="ph-duotone ph-translate"></i>
                            Module Traductions
                        </div>
                        <h2 id="doc-translations" class="doc-hero__title">Traductions — Suivi Maroc / Allemagne</h2>
                        <p class="doc-hero__text">
                            Gerer les commandes de traduction des etudiants. Une commande = un etudiant (CIN unique) avec plusieurs documents,
                            chacun ayant son nombre de pages et son prix. Le total est calcule automatiquement.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-list-bullets"></i></div>
                    <h5 class="doc-card__title">Operations principales</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Nouvelle commande :</strong> bouton en haut → modale → saisir CIN, nom, telephone, puis ajouter chaque document avec pages + prix par page.</li>
                        <li><strong>Modifier :</strong> icone crayon sur la ligne → modale d'edition. Ajouter/supprimer des lignes de documents en direct.</li>
                        <li><strong>Statut (1 clic) :</strong> bouton statut cycle Recu (GLS) → Chez Traducteur → Rendu a l'etudiant.</li>
                        <li><strong>Date de remise :</strong> input de date sur la ligne, change a la volee.</li>
                        <li><strong>Filtres :</strong> recherche libre (CIN, nom, document) + filtre par statut + total filtre affiche en bas.</li>
                        <li><strong>Export CSV :</strong> bouton vert, une ligne par document (ouvre Excel).</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green"><i class="ph-duotone ph-globe"></i></div>
                    <h5 class="doc-card__title">Cote etudiant (frontoffice)</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>URL publique :</strong> <code>/traductions/suivi</code> — l'etudiant entre son CIN.</li>
                        <li>Affiche toutes ses commandes avec leurs documents, prix et timeline (Recu → Traducteur → Rendu).</li>
                        <li>Etapes deja franchies en vert, etape actuelle en bleu pulse, prochaines etapes en gris.</li>
                        <li>Total de la commande affiche en vert quand le statut est rendu.</li>
                        <li>Aucune authentification — la confidentialite est garantie par le CIN unique.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         DEMANDES D'ATTESTATION — public form
         ========================================================= --}}
    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(245,180,10,0.18), transparent 28%), linear-gradient(135deg, #ffffff 0%, #fffbeb 55%, #fffbe8 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow" style="background:#fff7d6;color:#8a6d00;">
                            <i class="ph-duotone ph-mail-forward"></i>
                            Module Demandes d'attestation
                        </div>
                        <h2 id="doc-attestation-requests" class="doc-hero__title">Demandes d'attestation — Soumissions publiques</h2>
                        <p class="doc-hero__text">
                            Les anciens etudiants soumettent leur demande d'attestation depuis le site public. Ce module liste toutes les demandes
                            recues, permet de les valider (et generer une attestation) ou de les refuser avec motif (envoye par email).
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-orange"><i class="ph-duotone ph-list-checks"></i></div>
                    <h5 class="doc-card__title">Workflow de traitement</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>1. Liste :</strong> 3 onglets — En attente / Acceptees / Refusees + compteurs.</li>
                        <li><strong>2. Voir le detail :</strong> icone oeil → page detail avec les infos saisies par l'etudiant.</li>
                        <li><strong>3. Accepter :</strong> redirige vers le formulaire de creation d'attestation pre-rempli avec les donnees de la demande. Email automatique a l'etudiant.</li>
                        <li><strong>4. Refuser :</strong> motif obligatoire (min. 5 caracteres) → email automatique avec le motif a l'etudiant.</li>
                        <li><strong>5. Supprimer :</strong> icone poubelle si la demande est invalide.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-globe"></i></div>
                    <h5 class="doc-card__title">Cote etudiant (frontoffice)</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>URL publique :</strong> <code>/demande-attestation</code></li>
                        <li>Formulaire en ligne : nom, prenom, email, telephone, date/lieu de naissance, groupe, niveau, notes.</li>
                        <li>Apres envoi : page de remerciement + email a l'equipe GLS.</li>
                        <li>L'etudiant recoit un email a chaque etape (recue / acceptee / refusee).</li>
                        <li>Lien dans le footer du site et dans la dropdown "Ressources" du header public.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         GESTION ECOLE — Sites / Teachers / Groups / Certificates / Attestations
         ========================================================= --}}
    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(11,114,199,0.15), transparent 28%), linear-gradient(135deg, #ffffff 0%, #f4f8fd 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow"><i class="ph-duotone ph-buildings"></i> Gestion ecole</div>
                        <h2 id="doc-school" class="doc-hero__title">Centres, Enseignants, Groupes</h2>
                        <p class="doc-hero__text">
                            Le coeur operationnel : creer et tenir a jour les centres GLS, les enseignants, les groupes de cours
                            et les documents administratifs (certificats, attestations).
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-3">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-buildings"></i></div>
                    <h5 class="doc-card__title">Centres GLS</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>Creer / modifier les centres (Casablanca, Marrakech, etc.).</li>
                        <li>Renseigner adresse, contact, image, slug, visibilite.</li>
                        <li>Le slug est utilise dans les URLs publiques.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green"><i class="ph-duotone ph-chalkboard-teacher"></i></div>
                    <h5 class="doc-card__title">Enseignants</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>Profils des profs : nom, photo, centre principal, langues, certifications.</li>
                        <li>Le profil est lie aux groupes via le champ enseignant.</li>
                        <li>Les rapports semaine sont signes par un enseignant.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-purple"><i class="ph-duotone ph-users-three"></i></div>
                    <h5 class="doc-card__title">Groupes</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>Creer un groupe : niveau (A1/A2/B1/B2/C1), centre, enseignant, plage horaire.</li>
                        <li>Periode auto-detectee depuis <code>time_range</code> (matin / soir / etc.).</li>
                        <li>Une candidature (Application) se rattache toujours a un groupe.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-pink"><i class="ph-duotone ph-certificate"></i></div>
                    <h5 class="doc-card__title" id="doc-certificates">Certificats</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>Creer un certificat : nom etudiant, niveau, date examen, date emission.</li>
                        <li>Numero unique <code>GLS-XXXX</code> + token public + QR code.</li>
                        <li>Telechargement PDF, verification publique sur <code>/certificate-check</code>.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green"><i class="ph-duotone ph-file-text"></i></div>
                    <h5 class="doc-card__title" id="doc-attestations">Attestations</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>Attestations de participation (suivi de cours en cours ou termine).</li>
                        <li>Bilingue allemand / francais. Lien direct depuis "Demandes d'attestation".</li>
                        <li>Methodologie pedagogique editable (champ riche).</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-orange"><i class="ph-duotone ph-graduation-cap"></i></div>
                    <h5 class="doc-card__title" id="doc-studienkollegs">Studienkollegs</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>Catalogue des Studienkollegs allemands : ville, universite, niveau de langue, deadlines.</li>
                        <li>Chaque fiche : hero image, requirements, documents, courses, contact, lien d'application.</li>
                        <li>La page detail publique calcule automatiquement les jours restants avant la prochaine deadline.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         QUIZZES + BLOG + LEADS
         ========================================================= --}}
    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card" id="doc-quizzes">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-pink"><i class="ph-duotone ph-question"></i></div>
                    <h5 class="doc-card__title">Quizzes (QCM de niveau)</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>Creer un quiz, ajouter des questions, chaque question a plusieurs options.</li>
                        <li>Attache un niveau cible — le score determine le niveau de l'etudiant.</li>
                        <li>Utilise dans la page "Decouvrez votre niveau" du site public.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card" id="doc-blog">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-newspaper"></i></div>
                    <h5 class="doc-card__title">Blog (Categories &amp; Articles)</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Toujours</strong> creer la categorie avant l'article.</li>
                        <li>Article : titre, slug, image, contenu riche, statut (publie / brouillon).</li>
                        <li>Mots-cles SEO et meta description pour le referencement.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card" id="doc-leads">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-orange"><i class="ph-duotone ph-address-book"></i></div>
                    <h5 class="doc-card__title">Leads &amp; Applications</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Leads :</strong> consultations, inscriptions GLS, contacts entrants. Liste, filtres, suppression doublons.</li>
                        <li><strong>Applications :</strong> candidatures rattachees a un groupe — accepter, refuser, modifier.</li>
                        <li><strong>Stats leads :</strong> volume entrant, conversion, sources.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card" id="doc-newsletter">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green"><i class="ph-duotone ph-envelope"></i></div>
                    <h5 class="doc-card__title">Newsletter</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>Liste des abonnes a la newsletter (formulaire footer du site public).</li>
                        <li>Visualiser, filtrer, supprimer les abonnes.</li>
                        <li>Pas d'envoi de newsletter integre — exporter et utiliser un outil externe.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================
         ADMINISTRATION — Users + Roles
         ========================================================= --}}
    <div class="doc-section">
        <div class="doc-card" id="doc-users">
            <div class="doc-card__header">
                <div class="doc-card__icon bg-doc-purple"><i class="ph-duotone ph-user-gear"></i></div>
                <h5 class="doc-card__title">Utilisateurs &amp; Roles</h5>
            </div>
            <div class="doc-card__body">
                <ul class="doc-list">
                    <li><strong>Utilisateurs :</strong> creer, modifier, attribuer un role et un ou plusieurs centres.</li>
                    <li><strong>Roles disponibles :</strong> Super Admin (tout), Admin (sauf gestion utilisateurs/roles), Reception (operations centre, pas de suppression, pas de RH/comptes).</li>
                    <li><strong>Permissions :</strong> CRUD par module — voir, creer, editer, supprimer. Editables sur la page <code>/backoffice/roles</code>.</li>
                    <li><strong>Affectation centre obligatoire :</strong> les comptes non-admin sans centre attribue voient la page "Acces limite" et n'ont acces a aucune donnee.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="doc-section mb-4">
        <div class="doc-note">
            <strong>Besoin d'aide supplementaire ?</strong>
            Utilisez cette page comme reference interne. Si une action n'est pas couverte ou si un module change,
            la documentation doit etre mise a jour en meme temps que l'interface.
        </div>
    </div>

    </div>{{-- /#doc-fr --}}

    {{-- ================================================================== --}}
    {{-- ████████  النسخة العربية (ARABIC)  ████████                        --}}
    {{-- ================================================================== --}}
    <div id="doc-ar" dir="rtl" lang="ar">

    <div class="row">
        <div class="col-12">
            <section class="doc-hero">
                <div class="doc-hero__body">
                    <div class="doc-eyebrow">
                        <i class="ph-duotone ph-book-open-text"></i>
                        دليل المستخدم
                    </div>
                    <h1 class="doc-hero__title">دليل استخدام لوحة تحكم GLS</h1>
                    <p class="doc-hero__text">
                        هذه الصفحة دليل داخلي لفريق GLS. تشرح كيفية التنقل في البوابة، والعمليات التي يجب القيام بها
                        في كل وحدة، والترتيب الذي تُعالَج به المهام اليومية.
                    </p>

                    <div class="doc-metrics">
                        <div class="doc-metric">
                            <div class="doc-metric__value">8+</div>
                            <div class="doc-metric__label">وحدات البوابة</div>
                        </div>
                        <div class="doc-metric">
                            <div class="doc-metric__value">1</div>
                            <div class="doc-metric__label">روتين بسيط يومي</div>
                        </div>
                        <div class="doc-metric">
                            <div class="doc-metric__value">PDF</div>
                            <div class="doc-metric__label">تصدير متاح</div>
                        </div>
                        <div class="doc-metric">
                            <div class="doc-metric__value">الفريق</div>
                            <div class="doc-metric__label">استخدام إداري</div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- شريط الوصول السريع --}}
    <div class="row">
        <div class="col-12">
            <div class="doc-tagbar">
                <div class="doc-tagbar__label">
                    <i class="ph-duotone ph-bookmark-simple"></i>
                    وصول سريع إلى الوحدات
                </div>
                <div class="doc-tagbar__list">
                    <a href="#ar-school" class="doc-tag doc-tag--blue"><i class="ph-duotone ph-buildings"></i> إدارة المدرسة</a>
                    <a href="#ar-translations" class="doc-tag doc-tag--red"><i class="ph-duotone ph-translate"></i> الترجمات</a>
                    <a href="#ar-attestation-requests" class="doc-tag doc-tag--gold"><i class="ph-duotone ph-mail-forward"></i> طلبات الشهادات</a>
                    <a href="#ar-certificates" class="doc-tag doc-tag--purple"><i class="ph-duotone ph-certificate"></i> الشهادات</a>
                    <a href="#ar-quizzes" class="doc-tag doc-tag--pink"><i class="ph-duotone ph-question"></i> الاختبارات</a>
                    <a href="#ar-blog" class="doc-tag doc-tag--blue"><i class="ph-duotone ph-newspaper"></i> المدونة</a>
                    <a href="#ar-leads" class="doc-tag doc-tag--pink"><i class="ph-duotone ph-address-book"></i> العملاء والطلبات</a>
                    <a href="#ar-feedbacks" class="doc-tag doc-tag--teal"><i class="ph-duotone ph-star"></i> الآراء (QR)</a>
                    <a href="#ar-planning" class="doc-tag doc-tag--blue"><i class="ph-duotone ph-calendar"></i> الجدول</a>
                    <a href="#ar-rapport" class="doc-tag doc-tag--blue"><i class="ph-duotone ph-clipboard-text"></i> تقرير الأسبوع</a>
                    <a href="#ar-users" class="doc-tag"><i class="ph-duotone ph-user-gear"></i> المستخدمون والأدوار</a>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <div class="doc-card">
                    <div class="doc-card__header">
                        <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-compass"></i></div>
                        <h5 class="doc-card__title">هيكل البوابة</h5>
                    </div>
                    <div class="doc-card__body">
                        <div class="doc-chipline">
                            <span class="doc-chip"><i class="ph-duotone ph-squares-four"></i> لوحة القيادة</span>
                            <span class="doc-chip"><i class="ph-duotone ph-chart-line-up"></i> المتابعة</span>
                            <span class="doc-chip"><i class="ph-duotone ph-buildings"></i> إدارة المدرسة</span>
                            <span class="doc-chip"><i class="ph-duotone ph-address-book"></i> القبول والعملاء</span>
                            <span class="doc-chip"><i class="ph-duotone ph-wallet"></i> تتبع المدفوعات</span>
                            <span class="doc-chip"><i class="ph-duotone ph-chart-pie-slice"></i> المداخيل</span>
                            <span class="doc-chip"><i class="ph-duotone ph-newspaper"></i> المحتوى</span>
                            <span class="doc-chip"><i class="ph-duotone ph-user-gear"></i> الإدارة</span>
                        </div>
                        <p class="doc-card__text mt-3">
                            القائمة الجانبية منظمة حسب المهمة. افتح دائماً الوحدة المناسبة للعملية التي تريد القيام بها
                            بدل البحث صفحة صفحة.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <div class="doc-card">
                    <div class="doc-card__header">
                        <div class="doc-card__icon bg-doc-green"><i class="ph-duotone ph-lightning"></i></div>
                        <h5 class="doc-card__title">بداية سريعة</h5>
                    </div>
                    <div class="doc-card__body">
                        <div class="doc-workflow">
                            <div class="doc-step">
                                <div class="doc-step__num">1</div>
                                <h6 class="doc-step__title">افتح لوحة القيادة</h6>
                                <p class="doc-step__text">تحقق من العدادات والعناصر التي يجب معالجتها أولاً.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">2</div>
                                <h6 class="doc-step__title">عالج العملاء</h6>
                                <p class="doc-step__text">تحقق من الاستشارات والتسجيلات والطلبات الواردة.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">3</div>
                                <h6 class="doc-step__title">تابع المجموعات</h6>
                                <p class="doc-step__text">راقب المجموعات والأساتذة وتتبع المستوى الجاري.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">4</div>
                                <h6 class="doc-step__title">حدّث المحتوى</h6>
                                <p class="doc-step__text">انشر مقالات المدونة أو الاختبارات أو الشهادات عند الحاجة.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-3">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-chart-line-up"></i></div>
                    <h5 class="doc-card__title">المتابعة</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>لوحة القيادة:</strong> نظرة عامة على الأنشطة والمؤشرات.</li>
                        <li><strong>تتبع المستوى:</strong> متابعة تقدم المجموعات، فتح التفاصيل، تصدير PDF للمجموعة ووضع علامة "منتهٍ".</li>
                        <li><strong>الاستخدام الجيد:</strong> عالج أولاً البطاقات المعلّمة "جارية" ثم تحقق من المواعيد.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-orange"><i class="ph-duotone ph-graduation-cap"></i></div>
                    <h5 class="doc-card__title">إدارة المدرسة</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>مراكز GLS:</strong> إدارة المراكز وبيانات التواصل ومعلومات التقديم.</li>
                        <li><strong>الأساتذة:</strong> إضافة أو تعديل أو أرشفة الملفات التعليمية.</li>
                        <li><strong>المجموعات:</strong> إنشاء المجموعات واختيار المستوى والتواريخ والمركز والأستاذ.</li>
                        <li><strong>الشهادات:</strong> إنشاء وتصدير الشهادات بصيغة PDF.</li>
                        <li><strong>Studienkollegs / الاختبارات:</strong> إدارة العرض الأكاديمي والاختبارات.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-pink"><i class="ph-duotone ph-users"></i></div>
                    <h5 class="doc-card__title">القبول والعملاء</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>الطلبات:</strong> متابعة الترشيحات المرتبطة بالمجموعات.</li>
                        <li><strong>العملاء:</strong> الاطلاع على الطلبات الواردة وحذف التكرارات أو الأخطاء.</li>
                        <li><strong>الروتين:</strong> تحقق من هذا القسم يومياً قبل الانتقال إلى الوحدات الأخرى.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-yellow"><i class="ph-duotone ph-newspaper"></i></div>
                    <h5 class="doc-card__title">المحتوى</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>فئات المدونة:</strong> نظّم المواضيع قبل النشر.</li>
                        <li><strong>مقالات المدونة:</strong> اكتب وأضف الصور ثم انشر أو احفظ كمسودة.</li>
                        <li><strong>نصيحة:</strong> حدّد الفئة قبل المقال للحفاظ على لوحة تحكم مرتبة.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-purple"><i class="ph-duotone ph-shield-check"></i></div>
                    <h5 class="doc-card__title">الإدارة</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>المستخدمون:</strong> إدارة الوصول إلى لوحة التحكم.</li>
                        <li><strong>حسابي:</strong> تحديث الملف الشخصي وكلمة المرور.</li>
                        <li><strong>قاعدة بسيطة:</strong> لكل مستخدم وصول شخصي، لا يُشارك أبداً.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green"><i class="ph-duotone ph-lifebuoy"></i></div>
                    <h5 class="doc-card__title">الدعم</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>مركز المساعدة:</strong> متاح من القائمة الجانبية.</li>
                        <li><strong>مشكلة وظيفية:</strong> سجّل الوحدة والصفحة والإجراء الذي يسبب المشكلة.</li>
                        <li><strong>الدعم الخارجي:</strong> استعمل زر الدعم أسفل الصفحة عند الحاجة.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- المداخيل (AR) --}}
    <div class="doc-section">
        <div class="doc-grid-3">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-yellow"><i class="ph-duotone ph-cash-register"></i></div>
                    <h5 class="doc-card__title">المداخيل (التحصيلات)</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>إدخال مدخول يدوياً أو <strong>استيراد ملف Excel</strong> للمداخيل.</li>
                        <li>معاينة قبل الاستيراد: تحقق من الأسطر قبل الحفظ.</li>
                        <li>كل مدخول مرتبط بمركز وطريقة دفع وتاريخ.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-orange"><i class="ph-duotone ph-receipt"></i></div>
                    <h5 class="doc-card__title">المصاريف</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>تسجيل مصاريف المركز (الكراء، الأجور، اللوازم...).</li>
                        <li>استيراد المصاريف عبر Excel مع سجل الاستيرادات.</li>
                        <li>تعديل / حذف مصروف فردي.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green"><i class="ph-duotone ph-chart-donut"></i></div>
                    <h5 class="doc-card__title">لوحة القيادة والمردودية</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>لوحة القيادة:</strong> مجاميع المداخيل/المصاريف، التطور الشهري، التوزيع حسب طريقة الدفع.</li>
                        <li><strong>المردودية:</strong> الهامش لكل مركز (المداخيل - المصاريف).</li>
                        <li><strong>المشغّلون:</strong> الأداء حسب كل مشغّل إدخال.</li>
                        <li>تصفية حسب <strong>السنة</strong> و<strong>الشهر</strong> و<strong>المركز</strong>.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-pink"><i class="ph-duotone ph-hand-coins"></i></div>
                    <h5 class="doc-card__title">التحصيل والمتأخرات</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>استيراد قائمة <strong>المتأخرات</strong> المراد تحصيلها.</li>
                        <li>وضع علامة <strong>محصَّل</strong> بمجرد استلام المبلغ.</li>
                        <li>توليد <strong>مكافآت</strong> التحصيل تلقائياً.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-purple"><i class="ph-duotone ph-medal"></i></div>
                    <h5 class="doc-card__title">المكافآت</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>المكافآت <strong>تُولَّد تلقائياً</strong> (لا إنشاء يدوي).</li>
                        <li>صفحة <strong>الإعدادات</strong>: تحديد قواعد / نسب المكافأة.</li>
                        <li>الموافقة على مكافأة أو حذفها من القائمة.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-shield-warning"></i></div>
                    <h5 class="doc-card__title">الوصول والاحتياطات</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>بيانات حساسة: ترى الحسابات فقط المراكز المخصصة لها.</li>
                        <li>تحقق دائماً من المعاينة قبل تأكيد الاستيراد.</li>
                        <li>الاستيراد الخاطئ يُحذف من سجل الاستيرادات.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- الآراء QR (AR) --}}
    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(12,128,121,0.16), transparent 28%), linear-gradient(135deg, #ffffff 0%, #f0fbfa 55%, #f4f8fd 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow" style="background:#e0f7f6;color:#0c8079;">
                            <i class="ph-duotone ph-star"></i>
                            وحدة الآراء
                        </div>
                        <h2 id="ar-feedbacks" class="doc-hero__title">آراء الطلاب — نموذج QR</h2>
                        <p class="doc-hero__text">
                            يترك الطلاب رأياً بمسح رمز QR المعروض في المركز. تصل الآراء إلى لوحة التحكم حيث يمكن للفريق قراءتها وحذفها.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-teal"><i class="ph-duotone ph-qr-code"></i></div>
                    <h5 class="doc-card__title">الجمع عبر رمز QR</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>صفحة QR:</strong> الآراء &larr; QR. اطبع / اعرض الرمز في المركز.</li>
                        <li>يمسح الطالب الرمز، يختار مركزه، ويترك اسمه ورسالته.</li>
                        <li>لا يتطلب حساباً: النموذج عمومي.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-teal"><i class="ph-duotone ph-chat-dots"></i></div>
                    <h5 class="doc-card__title">قراءة الآراء</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>القائمة:</strong> الاسم، المركز، الرسالة، الحالة مقروء / غير مقروء.</li>
                        <li>افتح رأياً لعرض التفاصيل (يُعلَّم كمقروء).</li>
                        <li>احذف رأياً غير ملائم.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- واتساب (AR) --}}
    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <div class="doc-card">
                    <div class="doc-card__header">
                        <div class="doc-card__icon" style="background:#e6f9ee;color:#1a8f4c;"><i class="ph-duotone ph-arrows-left-right"></i></div>
                        <h5 class="doc-card__title">مسار الحملة</h5>
                    </div>
                    <div class="doc-card__body">
                        <div class="doc-workflow" style="margin-top:4px;">
                            <div class="doc-step">
                                <div class="doc-step__num">1</div>
                                <h6 class="doc-step__title">إنشاء الحملة</h6>
                                <p class="doc-step__text"><strong>التواصل &larr; حملات واتساب &larr; حملة جديدة</strong>. اختر الاسم والمركز وقائمة الأرقام والرسالة.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">2</div>
                                <h6 class="doc-step__title">التحقق من التكرار</h6>
                                <p class="doc-step__text">يقارن النظام مع كل الإرسالات «الناجحة» في الحملات السابقة ويتيح إزالة الأرقام التي سبق التواصل معها.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">3</div>
                                <h6 class="doc-step__title">بدء الإرسال</h6>
                                <p class="doc-step__text">في صفحة التفاصيل اضغط «بدء». يفتح عامل Windows واتساب ويرسل رسالة واحدة كل مرة بفاصل زمني عشوائي.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">4</div>
                                <h6 class="doc-step__title">المتابعة المباشرة</h6>
                                <p class="doc-step__text">يُحدَّث التقدم (مُرسل / فشل / منتظر) فورياً. يمكن <strong>الإيقاف المؤقت</strong> أو <strong>الاستئناف</strong> أو <strong>الإيقاف</strong> في أي وقت.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- الجدول (AR) --}}
    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(70,128,255,0.15), transparent 28%), linear-gradient(135deg, #ffffff 0%, #f2f6ff 55%, #f4f8fd 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow" style="background:#eaf5ff;color:#0b72c7;">
                            <i class="ph-duotone ph-clock"></i>
                            وحدة الموارد البشرية / الجدول
                        </div>
                        <h2 id="ar-planning" class="doc-hero__title">جدولي — الأسبوع</h2>
                        <p class="doc-hero__text">
                            تتيح هذه الصفحة إدخال ساعات العمل (بداية / نهاية / استراحة) لكل يوم من الأسبوع. يُحسب وقت العمل
                            <strong>فورياً</strong> أثناء الإدخال بطرح الاستراحة تلقائياً. يمكن للمدير إدارة جدول باقي أعضاء الفريق.
                        </p>
                        <div class="doc-metrics">
                            <div class="doc-metric"><div class="doc-metric__value">مباشر</div><div class="doc-metric__label">حساب فوري</div></div>
                            <div class="doc-metric"><div class="doc-metric__value">7 أيام</div><div class="doc-metric__label">الإثنين إلى الأحد</div></div>
                            <div class="doc-metric"><div class="doc-metric__value">المجموع</div><div class="doc-metric__label">أسبوعي تلقائي</div></div>
                            <div class="doc-metric"><div class="doc-metric__value">مدير</div><div class="doc-metric__label">إدارة جداول أخرى</div></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-calculator"></i></div>
                    <h5 class="doc-card__title">حساب وقت العمل</h5>
                </div>
                <div class="doc-card__body">
                    <p class="doc-card__text"><strong>الصيغة:</strong></p>
                    <ul class="doc-list">
                        <li><strong>العمل = النهاية - البداية - (نهاية الاستراحة - بداية الاستراحة)</strong></li>
                        <li>إذا تجاوزت الاستراحة ساعات العمل، تُقتطع عند حدود البداية / النهاية.</li>
                        <li>إذا كانت البداية أو النهاية فارغة أو النهاية &le; البداية، يعرض السطر «—» ولا يُحتسب.</li>
                        <li>تُحدَّث خانة <strong>العمل</strong> و<strong>مجموع الأسبوع</strong> فوراً عند كل تعديل.</li>
                    </ul>
                    <div class="doc-note mt-3">
                        <strong>مثال:</strong> بداية 09:30، نهاية 19:30، استراحة 14:00 &larr; 16:00.<br>
                        العمل = 10 ساعات - ساعتان = <strong>8 ساعات</strong>.
                    </div>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-users-three"></i></div>
                    <h5 class="doc-card__title">التنقل والإدارة</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>السابق / التالي:</strong> تغيير الأسبوع.</li>
                        <li><strong>منتقي التاريخ:</strong> القفز مباشرة إلى أسبوع معين.</li>
                        <li><strong>إدارة جدول (مدير):</strong> قائمة منسدلة «نفسي / مستخدم آخر» لإدخال جدول زميل.</li>
                        <li><strong>ملاحظات:</strong> حقل حر لكل يوم (500 حرف كحد أقصى).</li>
                        <li><strong>تفريغ يوم:</strong> ترك البداية والنهاية فارغتين والحفظ يحذف إدخال اليوم.</li>
                        <li><strong>حفظ الأسبوع:</strong> زر أسفل الصفحة؛ الحساب في الخادم مطابق للحساب المباشر.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- تقرير الأسبوع (AR) --}}
    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(70,128,255,0.18), transparent 28%), linear-gradient(135deg, #ffffff 0%, #f3f7ff 55%, #f4f8fd 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow" style="background:#eaf5ff;color:#0b72c7;">
                            <i class="ph-duotone ph-calendar-check"></i>
                            وحدة المتابعة
                        </div>
                        <h2 id="ar-rapport" class="doc-hero__title">تقرير الأسبوع — الأساتذة</h2>
                        <p class="doc-hero__text">
                            تعمل هذه الصفحة كدفتر متابعة: لكل يوم من الأسبوع نسجّل ما قام به كل أستاذ (درس، تدخل، غياب مبرر...).
                            يوفر عرض الأسبوع تقويماً من الإثنين إلى الجمعة ومعاينة شهرية عبر زر مخصص.
                        </p>
                        <div class="doc-metrics">
                            <div class="doc-metric"><div class="doc-metric__value">أسبوع</div><div class="doc-metric__label">تقويم إثنين-جمعة</div></div>
                            <div class="doc-metric"><div class="doc-metric__value">شهر</div><div class="doc-metric__label">نافذة معاينة</div></div>
                            <div class="doc-metric"><div class="doc-metric__value">PDF</div><div class="doc-metric__label">تصدير الأسبوع</div></div>
                            <div class="doc-metric"><div class="doc-metric__value">الهاتف</div><div class="doc-metric__label">عرض متجاوب</div></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-pencil-simple-line"></i></div>
                    <h5 class="doc-card__title">إضافة / تعديل تقرير</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>انقر على خانة يوم</strong> (أو زر + عند المرور) لفتح نافذة الإضافة.</li>
                        <li><strong>الأستاذ:</strong> اختر من قائمة الأساتذة.</li>
                        <li><strong>الملاحظات:</strong> صِف ما قام به الأستاذ ذلك اليوم (2000 حرف كحد أقصى).</li>
                        <li><strong>التعديل:</strong> النقر على تقرير موجود يعيد فتح النافذة معبأة مع زر <strong>حذف</strong>.</li>
                        <li><strong>أستاذ + تاريخ:</strong> تقرير واحد لكل أستاذ في اليوم؛ الإدخال الجديد يحدّث الموجود.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-calendar-blank"></i></div>
                    <h5 class="doc-card__title">أيقونة التقويم — العرض الشهري</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>زر التقويم</strong> بجانب تنقل الأسبوع: يفتح نافذة بشبكة الشهر الكامل.</li>
                        <li><strong>تنقل الشهر:</strong> أسهم السابق / التالي وزر «اليوم».</li>
                        <li><strong>كل خانة:</strong> رقم اليوم + حتى 3 تقارير ظاهرة. بعدها يظهر «+N أخرى».</li>
                        <li><strong>النقر على خانة:</strong> القفز إلى الأسبوع المقابل لتعديل اليوم.</li>
                        <li><strong>الهاتف:</strong> تُختصر التقارير إلى نقطة زرقاء + عداد للحفاظ على وضوح الشبكة.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>


    {{-- الترجمات (AR) --}}
    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(210,39,48,0.16), transparent 28%), linear-gradient(135deg, #ffffff 0%, #fff5f5 55%, #fffbe8 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow" style="background:#fde7e9;color:#b3252f;">
                            <i class="ph-duotone ph-translate"></i>
                            وحدة الترجمات
                        </div>
                        <h2 id="ar-translations" class="doc-hero__title">الترجمات — متابعة المغرب / ألمانيا</h2>
                        <p class="doc-hero__text">
                            إدارة طلبات ترجمة الطلاب. الطلب = طالب واحد (رقم بطاقة وطنية فريد) بعدة وثائق، لكل منها عدد صفحات وسعر.
                            يُحسب المجموع تلقائياً.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-list-bullets"></i></div>
                    <h5 class="doc-card__title">العمليات الأساسية</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>طلب جديد:</strong> زر بالأعلى → نافذة → إدخال البطاقة الوطنية والاسم والهاتف ثم إضافة كل وثيقة بصفحاتها وسعر الصفحة.</li>
                        <li><strong>تعديل:</strong> أيقونة القلم على السطر → نافذة تعديل. إضافة/حذف أسطر الوثائق مباشرة.</li>
                        <li><strong>الحالة (نقرة واحدة):</strong> مستلَم (GLS) → عند المترجم → سُلّم للطالب.</li>
                        <li><strong>تاريخ التسليم:</strong> حقل تاريخ على السطر يتغير فوراً.</li>
                        <li><strong>التصفية:</strong> بحث حر + تصفية حسب الحالة + المجموع المصفّى بالأسفل.</li>
                        <li><strong>تصدير CSV:</strong> زر أخضر، سطر لكل وثيقة (يفتح Excel).</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green"><i class="ph-duotone ph-globe"></i></div>
                    <h5 class="doc-card__title">جانب الطالب (الموقع العمومي)</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>الرابط العمومي:</strong> <code>/traductions/suivi</code> — يُدخل الطالب بطاقته الوطنية.</li>
                        <li>يعرض كل طلباته بوثائقها وأسعارها وخط الزمن (مستلَم → المترجم → سُلّم).</li>
                        <li>المراحل المنجزة بالأخضر، الحالية بالأزرق، القادمة بالرمادي.</li>
                        <li>مجموع الطلب يظهر بالأخضر عند التسليم.</li>
                        <li>لا حاجة لتسجيل دخول — السرية مضمونة بالبطاقة الوطنية الفريدة.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- طلبات الشهادات (AR) --}}
    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(245,180,10,0.18), transparent 28%), linear-gradient(135deg, #ffffff 0%, #fffbeb 55%, #fffbe8 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow" style="background:#fff7d6;color:#8a6d00;">
                            <i class="ph-duotone ph-mail-forward"></i>
                            وحدة طلبات الشهادات
                        </div>
                        <h2 id="ar-attestation-requests" class="doc-hero__title">طلبات الشهادات — تقديمات عمومية</h2>
                        <p class="doc-hero__text">
                            يقدّم الطلاب السابقون طلب شهادتهم من الموقع العمومي. تسرد هذه الوحدة كل الطلبات الواردة، وتتيح قبولها
                            (وإنشاء شهادة) أو رفضها مع سبب (يُرسل بالبريد).
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-orange"><i class="ph-duotone ph-list-checks"></i></div>
                    <h5 class="doc-card__title">مسار المعالجة</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>1. القائمة:</strong> 3 تبويبات — قيد الانتظار / مقبولة / مرفوضة + عدادات.</li>
                        <li><strong>2. عرض التفاصيل:</strong> أيقونة العين → صفحة تفاصيل بمعلومات الطالب.</li>
                        <li><strong>3. القبول:</strong> يحوّل إلى نموذج إنشاء شهادة معبأ ببيانات الطلب. بريد تلقائي للطالب.</li>
                        <li><strong>4. الرفض:</strong> السبب إلزامي (5 أحرف على الأقل) → بريد تلقائي بالسبب للطالب.</li>
                        <li><strong>5. الحذف:</strong> أيقونة السلة إذا كان الطلب غير صالح.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-globe"></i></div>
                    <h5 class="doc-card__title">جانب الطالب (الموقع العمومي)</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>الرابط العمومي:</strong> <code>/demande-attestation</code></li>
                        <li>نموذج: الاسم، النسب، البريد، الهاتف، تاريخ/مكان الازدياد، المجموعة، المستوى، ملاحظات.</li>
                        <li>بعد الإرسال: صفحة شكر + بريد لفريق GLS.</li>
                        <li>يستلم الطالب بريداً في كل مرحلة (مستلمة / مقبولة / مرفوضة).</li>
                        <li>رابط في تذييل الموقع وفي قائمة "الموارد" بالموقع العمومي.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- إدارة المدرسة (AR) --}}
    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(11,114,199,0.15), transparent 28%), linear-gradient(135deg, #ffffff 0%, #f4f8fd 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow"><i class="ph-duotone ph-buildings"></i> إدارة المدرسة</div>
                        <h2 id="ar-school" class="doc-hero__title">المراكز، الأساتذة، المجموعات</h2>
                        <p class="doc-hero__text">
                            القلب التشغيلي: إنشاء وتحديث مراكز GLS والأساتذة والمجموعات والوثائق الإدارية (الشهادات والإفادات).
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-3">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-buildings"></i></div>
                    <h5 class="doc-card__title">مراكز GLS</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>إنشاء / تعديل المراكز (الدار البيضاء، مراكش...).</li>
                        <li>إدخال العنوان والتواصل والصورة والـ slug والظهور.</li>
                        <li>يُستعمل الـ slug في الروابط العمومية.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green"><i class="ph-duotone ph-chalkboard-teacher"></i></div>
                    <h5 class="doc-card__title">الأساتذة</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>ملفات الأساتذة: الاسم، الصورة، المركز الرئيسي، اللغات، الشهادات.</li>
                        <li>الملف مرتبط بالمجموعات عبر حقل الأستاذ.</li>
                        <li>تقارير الأسبوع موقّعة باسم أستاذ.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-purple"><i class="ph-duotone ph-users-three"></i></div>
                    <h5 class="doc-card__title">المجموعات</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>إنشاء مجموعة: المستوى (A1/A2/B1/B2/C1)، المركز، الأستاذ، التوقيت.</li>
                        <li>تُكتشف الفترة تلقائياً من <code>time_range</code> (صباح / مساء...).</li>
                        <li>كل ترشيح (Application) مرتبط دائماً بمجموعة.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-pink"><i class="ph-duotone ph-certificate"></i></div>
                    <h5 class="doc-card__title" id="ar-certificates">الشهادات</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>إنشاء شهادة: اسم الطالب، المستوى، تاريخ الامتحان، تاريخ الإصدار.</li>
                        <li>رقم فريد <code>GLS-XXXX</code> + رمز عمومي + QR.</li>
                        <li>تحميل PDF، تحقق عمومي على <code>/certificate-check</code>.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green"><i class="ph-duotone ph-file-text"></i></div>
                    <h5 class="doc-card__title">الإفادات (Teilnahmebestätigung)</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>إفادات المشاركة (متابعة دورة جارية أو منتهية).</li>
                        <li>ثنائية اللغة ألماني / فرنسي. رابط مباشر من "طلبات الشهادات".</li>
                        <li>منهجية تعليمية قابلة للتعديل (حقل غني).</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-orange"><i class="ph-duotone ph-graduation-cap"></i></div>
                    <h5 class="doc-card__title">Studienkollegs</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>دليل الـ Studienkollegs الألمانية: المدينة، الجامعة، مستوى اللغة، المواعيد.</li>
                        <li>كل بطاقة: صورة، المتطلبات، الوثائق، الدورات، التواصل، رابط التقديم.</li>
                        <li>تحسب الصفحة العمومية تلقائياً الأيام المتبقية قبل الموعد القادم.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- اختبارات + مدونة + عملاء (AR) --}}
    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card" id="ar-quizzes">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-pink"><i class="ph-duotone ph-question"></i></div>
                    <h5 class="doc-card__title">الاختبارات (تحديد المستوى)</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>إنشاء اختبار، إضافة أسئلة، لكل سؤال عدة خيارات.</li>
                        <li>مرتبط بمستوى مستهدف — النتيجة تحدد مستوى الطالب.</li>
                        <li>يُستعمل في صفحة "اكتشف مستواك" بالموقع العمومي.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card" id="ar-blog">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-blue"><i class="ph-duotone ph-newspaper"></i></div>
                    <h5 class="doc-card__title">المدونة (الفئات والمقالات)</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>دائماً</strong> أنشئ الفئة قبل المقال.</li>
                        <li>المقال: العنوان، الـ slug، الصورة، محتوى غني، الحالة (منشور / مسودة).</li>
                        <li>كلمات SEO ووصف ميتا للأرشفة.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card" id="ar-leads">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-orange"><i class="ph-duotone ph-address-book"></i></div>
                    <h5 class="doc-card__title">العملاء والطلبات</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>العملاء:</strong> الاستشارات والتسجيلات والاتصالات الواردة. قائمة، تصفية، حذف تكرارات.</li>
                        <li><strong>الطلبات:</strong> الترشيحات المرتبطة بمجموعة — قبول، رفض، تعديل.</li>
                        <li><strong>إحصائيات:</strong> حجم الوارد، التحويل، المصادر.</li>
                    </ul>
                </div>
            </div>
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green"><i class="ph-duotone ph-envelope"></i></div>
                    <h5 class="doc-card__title">النشرة البريدية</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>قائمة المشتركين في النشرة (نموذج تذييل الموقع).</li>
                        <li>عرض، تصفية، حذف المشتركين.</li>
                        <li>لا إرسال نشرة مدمج — صدّر واستعمل أداة خارجية.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- المستخدمون والأدوار (AR) --}}
    <div class="doc-section">
        <div class="doc-card" id="ar-users">
            <div class="doc-card__header">
                <div class="doc-card__icon bg-doc-purple"><i class="ph-duotone ph-user-gear"></i></div>
                <h5 class="doc-card__title">المستخدمون والأدوار</h5>
            </div>
            <div class="doc-card__body">
                <ul class="doc-list">
                    <li><strong>المستخدمون:</strong> إنشاء، تعديل، تعيين دور ومركز أو عدة مراكز.</li>
                    <li><strong>الأدوار المتاحة:</strong> Super Admin (كل شيء)، Admin (ما عدا إدارة المستخدمين/الأدوار)، Reception (عمليات المركز، دون حذف، دون موارد بشرية/حسابات).</li>
                    <li><strong>الصلاحيات:</strong> CRUD لكل وحدة — عرض، إنشاء، تعديل، حذف. قابلة للتعديل على <code>/backoffice/roles</code>.</li>
                    <li><strong>تعيين المركز إلزامي:</strong> الحسابات غير الإدارية بدون مركز ترى صفحة "وصول محدود" ولا تصل لأي بيانات.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="doc-section mb-4">
        <div class="doc-note">
            <strong>هل تحتاج مساعدة إضافية؟</strong>
            استعمل هذه الصفحة كمرجع داخلي. إذا لم تُغطَّ عملية أو تغيّرت وحدة، يجب تحديث التوثيق في نفس وقت تحديث الواجهة.
        </div>
    </div>

    </div>{{-- /#doc-ar --}}

@endsection

@section('scripts')
    <script>
        (function () {
            var STORAGE_KEY = 'gls_doc_lang';
            var buttons = document.querySelectorAll('[data-doc-lang]');

            function applyLang(lang) {
                if (lang !== 'ar') lang = 'fr';
                document.body.classList.toggle('doc-lang-ar', lang === 'ar');
                buttons.forEach(function (btn) {
                    btn.classList.toggle('is-active', btn.getAttribute('data-doc-lang') === lang);
                });
                try { localStorage.setItem(STORAGE_KEY, lang); } catch (e) {}
            }

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    applyLang(btn.getAttribute('data-doc-lang'));
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });

            var saved = 'fr';
            try { saved = localStorage.getItem(STORAGE_KEY) || 'fr'; } catch (e) {}
            applyLang(saved);
        })();
    </script>
@endsection
