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

            .doc-grid-2,
            .doc-grid-3,
            .doc-metrics,
            .doc-workflow {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
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
                            <div class="doc-metric__value">7</div>
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
                    <a href="#doc-crm" class="doc-tag doc-tag--green"><i class="ph-duotone ph-wallet"></i> Suivi paiement</a>
                    <a href="#doc-presence" class="doc-tag doc-tag--purple"><i class="ph-duotone ph-user-check"></i> Paiement profs</a>
                    <a href="#doc-whatsapp" class="doc-tag doc-tag--green"><i class="ph-duotone ph-whatsapp-logo"></i> WhatsApp</a>
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
                            <span class="doc-chip"><i class="ph-duotone ph-wallet"></i> Suivi Paiement</span>
                            <span class="doc-chip"><i class="ph-duotone ph-user-check"></i> Paiement Professeurs</span>
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
    {{-- SUIVI PAIEMENT — Documentation complète                          --}}
    {{-- ================================================================== --}}

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(15,138,88,0.15), transparent 28%), linear-gradient(135deg, #ffffff 0%, #f2faf6 55%, #f4f8fd 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow" style="background:#e9fbf3;color:#0f8a58;">
                            <i class="ph-duotone ph-wallet"></i>
                            Module Suivi Paiement
                        </div>
                        <h2 id="doc-crm" class="doc-hero__title">Suivi Paiement — Import CRM</h2>
                        <p class="doc-hero__text">
                            Ce module permet de suivre les paiements des etudiants vers GLS. Il importe les fichiers Excel du CRM
                            contenant les montants payes par chaque etudiant chaque mois, et analyse leur cycle de vie
                            (nouveau, actif, perdu, retourne, annule, transfere).
                        </p>
                        <div class="doc-metrics">
                            <div class="doc-metric">
                                <div class="doc-metric__value">Excel</div>
                                <div class="doc-metric__label">fichier source CRM</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">Versions</div>
                                <div class="doc-metric__label">imports versionnés</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">Lifecycle</div>
                                <div class="doc-metric__label">analyse automatique</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">Comparaison</div>
                                <div class="doc-metric__label">entre versions</div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <div class="doc-card">
                    <div class="doc-card__header">
                        <div class="doc-card__icon bg-doc-green">
                            <i class="ph-duotone ph-arrows-left-right"></i>
                        </div>
                        <h5 class="doc-card__title">Flux de donnees : Etudiant &rarr; GLS</h5>
                    </div>
                    <div class="doc-card__body">
                        <p class="doc-card__text">Le Suivi Paiement traque l'argent que les <strong>etudiants versent a GLS</strong> chaque mois. C'est le sens Etudiant &rarr; GLS.</p>
                        <div class="doc-workflow" style="margin-top:16px;">
                            <div class="doc-step">
                                <div class="doc-step__num">1</div>
                                <h6 class="doc-step__title">Exporter le fichier CRM</h6>
                                <p class="doc-step__text">Le responsable exporte le fichier Excel depuis le CRM ou Google Sheets. Il contient les noms des etudiants et leurs montants payes par mois.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">2</div>
                                <h6 class="doc-step__title">Importer dans GLS</h6>
                                <p class="doc-step__text">Aller dans <strong>Suivi Paiement &rarr; Importer CRM</strong>. Selectionner le groupe, le mois de debut, le taux par etudiant et le fichier Excel.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">3</div>
                                <h6 class="doc-step__title">Analyse automatique</h6>
                                <p class="doc-step__text">Le systeme detecte automatiquement les colonnes de mois, les frais d'inscription, les couleurs (rouge = annule, gris = transfere) et calcule le lifecycle.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">4</div>
                                <h6 class="doc-step__title">Consulter et comparer</h6>
                                <p class="doc-step__text">Voir le detail de chaque import, comparer avec la version precedente, analyser les mouvements etudiants mois par mois.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green">
                        <i class="ph-duotone ph-file-xls"></i>
                    </div>
                    <h5 class="doc-card__title">Format du fichier Excel CRM</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Colonne etudiants :</strong> noms des etudiants (detecte automatiquement via les mots-cles : etudiant, nom, stagiaire, eleve).</li>
                        <li><strong>Colonnes mensuelles :</strong> chaque colonne de mois contient le montant paye (ex: « Frais de mars », « Frais d'avril »). Formats acceptes : « 1300.00 DH », « 1300,00 », nombres simples.</li>
                        <li><strong>Colonnes de frais :</strong> inscription A1/A2, inscription B2, etc. Detectees automatiquement.</li>
                        <li><strong>Couleurs de cellules :</strong> rouge = etudiant annule, gris = transfere, vert/blanc = actif.</li>
                        <li><strong>Formats acceptes :</strong> .xlsx, .xls, .csv (maximum 10 Mo).</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green">
                        <i class="ph-duotone ph-arrows-clockwise"></i>
                    </div>
                    <h5 class="doc-card__title">Cycle de vie etudiant (Lifecycle)</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Initial :</strong> premier mois de paiement = mois de debut du groupe.</li>
                        <li><strong>Nouveau :</strong> premier paiement apres le mois de debut (inscription tardive).</li>
                        <li><strong>Actif :</strong> continue de payer normalement.</li>
                        <li><strong>Perdu :</strong> a arrete de payer (pas de montant ce mois).</li>
                        <li><strong>Retourne :</strong> a repris le paiement apres une periode d'absence.</li>
                        <li><strong>Annule / Transfere :</strong> detecte par la couleur de la ligne dans le fichier Excel.</li>
                        <li><strong>Inactif :</strong> jamais paye depuis le debut.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-3">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green">
                        <i class="ph-duotone ph-squares-four"></i>
                    </div>
                    <h5 class="doc-card__title">Tableau de bord</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>Vue d'ensemble de tous les groupes avec imports.</li>
                        <li>Nombre de versions, dernier import, taux par etudiant.</li>
                        <li>Acces rapide vers l'historique, l'analyse mensuelle et le suivi etudiants.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green">
                        <i class="ph-duotone ph-git-diff"></i>
                    </div>
                    <h5 class="doc-card__title">Comparaison de versions</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>Compare deux imports successifs pour le meme groupe.</li>
                        <li>Met en evidence : etudiants ajoutes, supprimes, changements de montants, changements de statut.</li>
                        <li>Utile pour verifier les corrections apportees entre deux exports CRM.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-green">
                        <i class="ph-duotone ph-chart-bar"></i>
                    </div>
                    <h5 class="doc-card__title">Analyse mensuelle</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>Resume par mois : combien d'etudiants initiaux, nouveaux, actifs, perdus, retournes.</li>
                        <li>Montant total encaisse par mois.</li>
                        <li>Timeline complete du groupe depuis son ouverture.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- PAIEMENT PROFESSEURS — Documentation complète                     --}}
    {{-- ================================================================== --}}

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(109,68,216,0.15), transparent 28%), linear-gradient(135deg, #ffffff 0%, #f5f1ff 55%, #f4f8fd 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow" style="background:#f1ebff;color:#6d44d8;">
                            <i class="ph-duotone ph-user-check"></i>
                            Module Paiement Professeurs
                        </div>
                        <h2 id="doc-presence" class="doc-hero__title">Paiement Professeurs — Import Presence</h2>
                        <p class="doc-hero__text">
                            Ce module calcule automatiquement le salaire du professeur en se basant sur la presence des etudiants.
                            Chaque etudiant est classe dans une categorie (Complet, 3/4, 1/2, 1/4, Zero) selon le nombre de semaines
                            ou il etait present. Le salaire du professeur = somme de toutes les contributions ponderees.
                        </p>
                        <div class="doc-metrics">
                            <div class="doc-metric">
                                <div class="doc-metric__value">Pro-rata</div>
                                <div class="doc-metric__label">calcul proportionnel</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">5</div>
                                <div class="doc-metric__label">categories etudiants</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">Auto</div>
                                <div class="doc-metric__label">detection presence</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">Override</div>
                                <div class="doc-metric__label">ajustement manuel</div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <div class="doc-card">
                    <div class="doc-card__header">
                        <div class="doc-card__icon bg-doc-purple">
                            <i class="ph-duotone ph-arrows-left-right"></i>
                        </div>
                        <h5 class="doc-card__title">Flux de donnees : GLS &rarr; Professeur</h5>
                    </div>
                    <div class="doc-card__body">
                        <p class="doc-card__text">Le Paiement Professeurs calcule l'argent que <strong>GLS verse au professeur</strong>. C'est le sens inverse : GLS &rarr; Professeur. Il se base sur la feuille de presence.</p>
                        <div class="doc-workflow" style="margin-top:16px;">
                            <div class="doc-step">
                                <div class="doc-step__num">1</div>
                                <h6 class="doc-step__title">Remplir la feuille de presence</h6>
                                <p class="doc-step__text">Le responsable remplit la feuille de presence (papier ou tableur) chaque jour avec P (Present) et Q (Absent) pour chaque etudiant.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">2</div>
                                <h6 class="doc-step__title">Importer le fichier</h6>
                                <p class="doc-step__text">Aller dans <strong>Paiement Professeurs &rarr; Importer Presence</strong>. Selectionner le groupe, le mois, le taux par etudiant et le fichier Excel.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">3</div>
                                <h6 class="doc-step__title">Calcul automatique</h6>
                                <p class="doc-step__text">Le systeme classe chaque etudiant (Complet, 3/4, 1/2, 1/4, Zero), calcule le montant par etudiant et affiche le total du paiement professeur.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">4</div>
                                <h6 class="doc-step__title">Verifier et approuver</h6>
                                <p class="doc-step__text">Le responsable peut ajuster la categorie d'un etudiant si necessaire, puis approuver le paiement final.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-purple">
                        <i class="ph-duotone ph-calculator"></i>
                    </div>
                    <h5 class="doc-card__title">Logique de calcul</h5>
                </div>
                <div class="doc-card__body">
                    <p class="doc-card__text"><strong>Principe :</strong> Le professeur est paye en fonction du nombre d'etudiants et de leur taux de presence. Chaque etudiant contribue differemment selon le nombre de semaines ou il etait present.</p>

                    <p class="doc-card__text mt-3"><strong>Etape 1 — Diviser le mois en 4 quartiers (semaines) :</strong></p>
                    <ul class="doc-list">
                        <li><strong>Quartier 1 :</strong> Jours 1 a 5 (Semaine 1)</li>
                        <li><strong>Quartier 2 :</strong> Jours 6 a 10 (Semaine 2)</li>
                        <li><strong>Quartier 3 :</strong> Jours 11 a 15 (Semaine 3)</li>
                        <li><strong>Quartier 4 :</strong> Jours 16 a 20+ (Semaine 4)</li>
                    </ul>

                    <p class="doc-card__text mt-3"><strong>Etape 2 — Classer chaque etudiant :</strong> Un etudiant est « actif » dans un quartier s'il a au moins 1 jour de presence.</p>
                    <ul class="doc-list">
                        <li><strong>4 quartiers actifs</strong> &rarr; Complet (100%)</li>
                        <li><strong>3 quartiers actifs</strong> &rarr; 3/4 (75%)</li>
                        <li><strong>2 quartiers actifs</strong> &rarr; 1/2 (50%)</li>
                        <li><strong>1 quartier actif</strong> &rarr; 1/4 (25%)</li>
                        <li><strong>0 quartier actif</strong> &rarr; Zero (0%)</li>
                    </ul>

                    <p class="doc-card__text mt-3"><strong>Etape 3 — Calculer le montant :</strong></p>
                    <ul class="doc-list">
                        <li>Montant par etudiant = <strong>floor(prix_base x fraction)</strong></li>
                        <li>Total professeur = somme de tous les montants etudiants</li>
                    </ul>

                    <div class="doc-note mt-3">
                        <strong>Arrondi :</strong> Le systeme utilise floor() (arrondi vers le bas). Exemple : 550 x 0.75 = 412.5 &rarr; <strong>412 DH</strong> (pas 413).
                    </div>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-purple">
                        <i class="ph-duotone ph-coins"></i>
                    </div>
                    <h5 class="doc-card__title">Grille de tarifs</h5>
                </div>
                <div class="doc-card__body">
                    <p class="doc-card__text"><strong>Groupes A1 — Prix de base : 500 DH</strong></p>
                    <table class="table table-sm table-bordered mt-2" style="max-width:320px;">
                        <thead class="table-light"><tr><th>Categorie</th><th class="text-end">Montant</th></tr></thead>
                        <tbody>
                            <tr><td>Complet (1/1)</td><td class="text-end">500 DH</td></tr>
                            <tr><td>3/4</td><td class="text-end">375 DH</td></tr>
                            <tr><td>1/2</td><td class="text-end">250 DH</td></tr>
                            <tr><td>1/4</td><td class="text-end">125 DH</td></tr>
                            <tr><td>Zero</td><td class="text-end">0 DH</td></tr>
                        </tbody>
                    </table>

                    <p class="doc-card__text mt-3"><strong>Groupes B1 — Prix de base : 550 DH</strong></p>
                    <table class="table table-sm table-bordered mt-2" style="max-width:320px;">
                        <thead class="table-light"><tr><th>Categorie</th><th class="text-end">Montant</th></tr></thead>
                        <tbody>
                            <tr><td>Complet (1/1)</td><td class="text-end">550 DH</td></tr>
                            <tr><td>3/4</td><td class="text-end">412 DH</td></tr>
                            <tr><td>1/2</td><td class="text-end">275 DH</td></tr>
                            <tr><td>1/4</td><td class="text-end">137 DH</td></tr>
                            <tr><td>Zero</td><td class="text-end">0 DH</td></tr>
                        </tbody>
                    </table>

                    <div class="doc-note mt-3">
                        <strong>Note :</strong> Le taux par etudiant est configurable dans le profil de l'enseignant ou lors de l'import. Les tarifs ci-dessus sont des exemples courants pour les centres GLS.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-purple">
                        <i class="ph-duotone ph-file-xls"></i>
                    </div>
                    <h5 class="doc-card__title">Format du fichier de presence</h5>
                </div>
                <div class="doc-card__body">
                    <p class="doc-card__text"><strong>Le fichier Excel doit contenir :</strong></p>
                    <ul class="doc-list">
                        <li><strong>Colonne etudiants :</strong> Les noms des etudiants (detectee automatiquement via : Etudiant, Nom, Prenom, Stagiaire, Eleve).</li>
                        <li><strong>Colonnes de dates :</strong> Une colonne par jour de cours. Les dates peuvent etre au format dd/mm/yyyy, des numeros de jours (1, 2, 3...) ou des abbreviations (MO, DI, MI, DO, FR).</li>
                        <li><strong>Valeurs de presence :</strong>
                            <br>&bull; <span style="color:#155724;font-weight:700;">P</span> ou <span style="color:#155724;font-weight:700;">V</span> = Present
                            <br>&bull; <span style="color:#721c24;font-weight:700;">Q</span> ou <span style="color:#721c24;font-weight:700;">A</span> = Absent
                            <br>&bull; Cellule vide = pas de donnee
                        </li>
                        <li><strong>Couleurs :</strong> Lignes rouges = etudiant annule, lignes grises = transfere.</li>
                        <li><strong>Formats acceptes :</strong> .xlsx, .xls, .csv (maximum 10 Mo).</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-purple">
                        <i class="ph-duotone ph-sliders-horizontal"></i>
                    </div>
                    <h5 class="doc-card__title">Ajustement manuel et approbation</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Categorie automatique :</strong> Le systeme calcule automatiquement la categorie de chaque etudiant en analysant ses jours de presence par semaine.</li>
                        <li><strong>Override manuel :</strong> Le responsable peut changer la categorie d'un etudiant via le menu deroulant dans la colonne « Categorie ». Les etudiants modifies sont marques avec un badge « modifie ».</li>
                        <li><strong>Recalcul :</strong> Apres un ajustement, le systeme recalcule automatiquement le montant total du paiement professeur.</li>
                        <li><strong>Approbation :</strong> Cliquer sur « Approuver » pour valider definitivement le paiement. Le statut passe a « Approuve » et ne peut plus etre modifie sans recalcul.</li>
                        <li><strong>Historique :</strong> Chaque import cree une version (v1, v2, v3...). Les anciennes versions restent consultables.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <div class="doc-card">
                    <div class="doc-card__header">
                        <div class="doc-card__icon bg-doc-purple">
                            <i class="ph-duotone ph-math-operations"></i>
                        </div>
                        <h5 class="doc-card__title">Exemples de calcul</h5>
                    </div>
                    <div class="doc-card__body">
                        <div class="doc-grid-3">
                            <div class="doc-step">
                                <h6 class="doc-step__title">Groupe B1 — 10h00</h6>
                                <p class="doc-step__text">
                                    25 x 550 = 13 750 DH<br>
                                    3 x 412 = 1 236 DH<br>
                                    <strong>Total = 14 986 DH</strong>
                                </p>
                            </div>
                            <div class="doc-step">
                                <h6 class="doc-step__title">Groupe B1 — 16h00</h6>
                                <p class="doc-step__text">
                                    21 x 550 = 11 550 DH<br>
                                    2 x 412 = 824 DH<br>
                                    1 x 0 = 0 DH<br>
                                    <strong>Total = 12 374 DH</strong>
                                </p>
                            </div>
                            <div class="doc-step">
                                <h6 class="doc-step__title">Groupe A1 — 18h30</h6>
                                <p class="doc-step__text">
                                    10 x 500 = 5 000 DH<br>
                                    2 x 250 = 500 DH<br>
                                    1 x 375 = 375 DH<br>
                                    <strong>Total = 5 875 DH</strong>
                                </p>
                            </div>
                            <div class="doc-step">
                                <h6 class="doc-step__title">Groupe A1 — 18h30</h6>
                                <p class="doc-step__text">
                                    9 x 500 = 4 500 DH<br>
                                    2 x 375 = 750 DH<br>
                                    2 x 250 = 500 DH<br>
                                    <strong>Total = 5 750 DH</strong>
                                </p>
                            </div>
                            <div class="doc-step">
                                <h6 class="doc-step__title">Groupe B1 — 18h30</h6>
                                <p class="doc-step__text">
                                    19 x 550 = 10 450 DH<br>
                                    1 x 275 = 275 DH<br>
                                    1 x 137 = 137 DH<br>
                                    <strong>Total = 10 862 DH</strong>
                                </p>
                            </div>
                            <div class="doc-step">
                                <h6 class="doc-step__title">Groupe A1 — 16h00</h6>
                                <p class="doc-step__text">
                                    14 x 500 = 7 000 DH<br>
                                    2 x 375 = 750 DH<br>
                                    <strong>Total = 7 750 DH</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- COMPARAISON DES DEUX MODULES                                      --}}
    {{-- ================================================================== --}}

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <div class="doc-card">
                    <div class="doc-card__header">
                        <div class="doc-card__icon bg-doc-blue">
                            <i class="ph-duotone ph-swap"></i>
                        </div>
                        <h5 class="doc-card__title">Difference entre les deux modules</h5>
                    </div>
                    <div class="doc-card__body">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th></th>
                                    <th class="text-center" style="background:#e9fbf3;">Suivi Paiement</th>
                                    <th class="text-center" style="background:#f1ebff;">Paiement Professeurs</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Direction de l'argent</strong></td>
                                    <td class="text-center">Etudiant &rarr; GLS</td>
                                    <td class="text-center">GLS &rarr; Professeur</td>
                                </tr>
                                <tr>
                                    <td><strong>Fichier source</strong></td>
                                    <td class="text-center">Excel CRM (montants mensuels)</td>
                                    <td class="text-center">Excel Presence (P/Q par jour)</td>
                                </tr>
                                <tr>
                                    <td><strong>Donnees traquees</strong></td>
                                    <td class="text-center">Montants payes par etudiant</td>
                                    <td class="text-center">Jours de presence par etudiant</td>
                                </tr>
                                <tr>
                                    <td><strong>Resultat</strong></td>
                                    <td class="text-center">Lifecycle etudiant (nouveau, actif, perdu...)</td>
                                    <td class="text-center">Salaire professeur (somme ponderee)</td>
                                </tr>
                                <tr>
                                    <td><strong>Calcul</strong></td>
                                    <td class="text-center">Somme directe des montants</td>
                                    <td class="text-center">Pro-rata : floor(prix x fraction)</td>
                                </tr>
                                <tr>
                                    <td><strong>Menu sidebar</strong></td>
                                    <td class="text-center"><i class="ph-duotone ph-wallet"></i> Suivi Paiement</td>
                                    <td class="text-center"><i class="ph-duotone ph-user-check"></i> Paiement Professeurs</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- PAGES DU MODULE PRESENCE — Guide page par page                    --}}
    {{-- ================================================================== --}}

    <div class="doc-section">
        <div class="doc-grid-3">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-purple">
                        <i class="ph-duotone ph-squares-four"></i>
                    </div>
                    <h5 class="doc-card__title">Tableau de bord Presence</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li>Liste tous les groupes avec au moins un import de presence.</li>
                        <li>Affiche le taux par etudiant, le dernier paiement calcule et le statut (en attente ou approuve).</li>
                        <li>Bouton rapide pour voir le dernier import ou l'historique complet.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-purple">
                        <i class="ph-duotone ph-upload-simple"></i>
                    </div>
                    <h5 class="doc-card__title">Page d'import</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Groupe :</strong> selectionner le groupe concerne.</li>
                        <li><strong>Mois :</strong> le mois couvert par la feuille de presence.</li>
                        <li><strong>Taux :</strong> pre-rempli avec le taux de l'enseignant. Modifiable si necessaire.</li>
                        <li><strong>Fichier :</strong> le fichier Excel de presence (.xlsx, .xls ou .csv).</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon bg-doc-purple">
                        <i class="ph-duotone ph-eye"></i>
                    </div>
                    <h5 class="doc-card__title">Page de detail</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Cartes resume :</strong> nombre d'etudiants par categorie avec montant unitaire.</li>
                        <li><strong>Calcul du paiement :</strong> tableau cliquable — cliquer sur une ligne pour voir la liste des etudiants de cette categorie.</li>
                        <li><strong>Grille de presence :</strong> vue jour par jour de chaque etudiant (P = present, A = absent).</li>
                        <li><strong>Categorie ajustable :</strong> menu deroulant par etudiant pour modifier la categorie manuellement.</li>
                        <li><strong>Bouton Approuver :</strong> valide le paiement final du professeur.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- FAQ                                                                --}}
    {{-- ================================================================== --}}

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <div class="doc-card">
                    <div class="doc-card__header">
                        <div class="doc-card__icon bg-doc-yellow">
                            <i class="ph-duotone ph-question"></i>
                        </div>
                        <h5 class="doc-card__title">Questions frequentes</h5>
                    </div>
                    <div class="doc-card__body">
                        <ul class="doc-list">
                            <li><strong>Comment modifier la categorie d'un etudiant ?</strong> Dans la page de detail de l'import, utiliser le menu deroulant dans la colonne « Categorie ». Le total sera recalcule automatiquement.</li>
                            <li><strong>Un etudiant est classe « Zero » alors qu'il a paye. Que faire ?</strong> Verifier sa presence dans la grille. S'il est present mais mal classe, changer manuellement sa categorie via le menu deroulant.</li>
                            <li><strong>Puis-je re-importer un fichier corrige ?</strong> Oui, chaque import cree une nouvelle version. L'ancienne version reste accessible dans l'historique.</li>
                            <li><strong>Comment fonctionne l'arrondi ?</strong> Le systeme utilise floor() (arrondi vers le bas). 550 x 0.75 = 412.5 est arrondi a 412 DH. C'est le meme calcul que fait le responsable a la main.</li>
                            <li><strong>Ou configurer le taux par etudiant ?</strong> Soit dans le profil de l'enseignant (Gestion ecole &rarr; Enseignants &rarr; Modifier), soit directement lors de l'import dans le champ « Taux par etudiant ».</li>
                            <li><strong>Que se passe-t-il si un etudiant arrive en milieu de mois ?</strong> Il n'aura de presence que pour les semaines ou il etait inscrit. Le systeme le classera en fonction des quartiers ou il a ete present.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- COMMUNICATION — Campagnes WhatsApp                                --}}
    {{-- ================================================================== --}}

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <section class="doc-hero" style="background: radial-gradient(circle at top right, rgba(37,211,102,0.18), transparent 28%), linear-gradient(135deg, #ffffff 0%, #f2fbf5 55%, #f4f8fd 100%);">
                    <div class="doc-hero__body">
                        <div class="doc-eyebrow" style="background:#e6f9ee;color:#1a8f4c;">
                            <i class="ph-duotone ph-whatsapp-logo"></i>
                            Module Communication
                        </div>
                        <h2 id="doc-whatsapp" class="doc-hero__title">Campagnes WhatsApp — Envois massifs</h2>
                        <p class="doc-hero__text">
                            Ce module permet d'envoyer un meme message WhatsApp (texte + piece jointe optionnelle) a une liste
                            de numeros. Les envois se font depuis un poste Windows sur lequel WhatsApp Desktop est ouvert et
                            connecte, avec des delais aleatoires entre chaque message pour eviter la suspension du compte.
                        </p>
                        <div class="doc-metrics">
                            <div class="doc-metric">
                                <div class="doc-metric__value">Par centre</div>
                                <div class="doc-metric__label">campagnes filtrables</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">Doublons</div>
                                <div class="doc-metric__label">detection automatique</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">Live</div>
                                <div class="doc-metric__label">suivi en temps reel</div>
                            </div>
                            <div class="doc-metric">
                                <div class="doc-metric__value">Pause</div>
                                <div class="doc-metric__label">controle d'envoi</div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="row g-4">
            <div class="col-12">
                <div class="doc-card">
                    <div class="doc-card__header">
                        <div class="doc-card__icon" style="background:#e6f9ee;color:#1a8f4c;">
                            <i class="ph-duotone ph-arrows-left-right"></i>
                        </div>
                        <h5 class="doc-card__title">Flux d'une campagne</h5>
                    </div>
                    <div class="doc-card__body">
                        <div class="doc-workflow" style="margin-top:4px;">
                            <div class="doc-step">
                                <div class="doc-step__num">1</div>
                                <h6 class="doc-step__title">Creer la campagne</h6>
                                <p class="doc-step__text"><strong>Communication &rarr; Campagnes WhatsApp &rarr; Nouvelle campagne</strong>. Choisir le nom, le centre, la liste de numeros et le message.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">2</div>
                                <h6 class="doc-step__title">Verifier les doublons</h6>
                                <p class="doc-step__text">Le systeme compare avec tous les envois deja « reussis » dans les anciennes campagnes et permet de retirer les numeros deja contactes.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">3</div>
                                <h6 class="doc-step__title">Demarrer l'envoi</h6>
                                <p class="doc-step__text">Sur la page de detail, cliquer « Demarrer ». Le worker Windows ouvre WhatsApp Desktop et envoie un message a la fois avec un delai aleatoire.</p>
                            </div>
                            <div class="doc-step">
                                <div class="doc-step__num">4</div>
                                <h6 class="doc-step__title">Suivre en direct</h6>
                                <p class="doc-step__text">La progression (envoyes / echecs / en attente) est mise a jour en temps reel. On peut <strong>Mettre en pause</strong>, <strong>Reprendre</strong> ou <strong>Arreter</strong> a tout moment.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon" style="background:#e6f9ee;color:#1a8f4c;">
                        <i class="ph-duotone ph-gear"></i>
                    </div>
                    <h5 class="doc-card__title">Formulaire Nouvelle campagne</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Nom :</strong> libelle interne pour retrouver la campagne (ex: « Liste annules mars », « Interets B2 »).</li>
                        <li><strong>Centre :</strong> centre GLS associe. Il est <strong>pre-selectionne automatiquement</strong> a partir du centre de votre compte utilisateur. Permet ensuite de filtrer l'historique par centre.</li>
                        <li><strong>Delai min / max (s) :</strong> intervalle aleatoire entre deux envois. Par defaut 45 s / 90 s. Plus c'est long, moins WhatsApp risque de bloquer le numero.</li>
                        <li><strong>Attente chargement (s) :</strong> temps d'attente apres ouverture de la conversation avant d'envoyer. Par defaut 7 s.</li>
                        <li><strong>Piece jointe (optionnelle) :</strong> PDF, JPG, PNG, WEBP ou MP4 jusqu'a 20 Mo. Si fournie, le message devient la legende.</li>
                        <li><strong>Liste des numeros :</strong> un numero par ligne au format <code>numero[,nom]</code>. Accepte les formats marocains (06..., +2126..., 2126...).</li>
                        <li><strong>Message :</strong> texte a envoyer. Variables disponibles : <code>{business}</code>, <code>{name}</code>, <code>{phone}</code>.</li>
                    </ul>
                    <div class="doc-note mt-3">
                        <strong>Doublons :</strong> le champ « Liste des numeros » s'auto-analyse. Si certains numeros ont deja recu un message « reussi » dans une campagne precedente, un bandeau jaune s'affiche avec les options <strong>Afficher</strong> et <strong>Retirer automatiquement</strong>.
                    </div>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon" style="background:#e6f9ee;color:#1a8f4c;">
                        <i class="ph-duotone ph-funnel"></i>
                    </div>
                    <h5 class="doc-card__title">Historique et filtre Centre</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Filtre automatique :</strong> a l'ouverture de la page <strong>Campagnes WhatsApp</strong>, la liste est filtree par defaut sur votre centre (celui defini dans votre compte utilisateur).</li>
                        <li><strong>Tous les centres :</strong> choisir « — Tous les centres — » pour voir toutes les campagnes.</li>
                        <li><strong>Non assigne :</strong> choisir « — Non assigne — » pour retrouver les campagnes creees sans centre.</li>
                        <li><strong>Colonnes :</strong> Nom, Centre, Creee par, Statut, Total, Envoyes, Echecs, Progression (%), Creee le, Actions.</li>
                        <li><strong>Statuts :</strong> <span class="badge bg-secondary">QUEUED</span> en file d'attente, <span class="badge bg-info">RUNNING</span> en cours, <span class="badge bg-warning">PAUSED</span> en pause, <span class="badge bg-success">COMPLETED</span> termine, <span class="badge bg-dark">STOPPED</span> arrete.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-section">
        <div class="doc-grid-2">
            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon" style="background:#e6f9ee;color:#1a8f4c;">
                        <i class="ph-duotone ph-chart-line-up"></i>
                    </div>
                    <h5 class="doc-card__title">Tableau de bord Communication</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Totaux :</strong> nombre total de campagnes, de destinataires, de messages envoyes, echoues, en attente.</li>
                        <li><strong>Taux de reussite :</strong> pourcentage global envoyes / total.</li>
                        <li><strong>Statuts :</strong> repartition entre queued, running, paused, completed, stopped.</li>
                        <li><strong>Serie 14 jours :</strong> evolution des envois et echecs par jour.</li>
                        <li><strong>Classement par centre :</strong> nombre de campagnes et de messages envoyes par centre.</li>
                        <li><strong>Top utilisateurs :</strong> les 10 utilisateurs ayant lance le plus de campagnes.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-card">
                <div class="doc-card__header">
                    <div class="doc-card__icon" style="background:#fff2e8;color:#d66b1f;">
                        <i class="ph-duotone ph-warning-circle"></i>
                    </div>
                    <h5 class="doc-card__title">Precautions et bonnes pratiques</h5>
                </div>
                <div class="doc-card__body">
                    <ul class="doc-list">
                        <li><strong>Numero secondaire :</strong> utilisez un numero WhatsApp dedie. L'envoi massif peut entrainer la suspension du compte.</li>
                        <li><strong>WhatsApp Desktop ouvert :</strong> la machine Windows qui execute le serveur doit avoir WhatsApp Desktop ouvert et connecte.</li>
                        <li><strong>Ne pas toucher la souris :</strong> pendant qu'une campagne tourne, ne pas interagir avec WhatsApp ; cela peut faire echouer un envoi.</li>
                        <li><strong>Delais raisonnables :</strong> ne pas descendre sous 30 / 40 secondes. Les valeurs par defaut (45 / 90) sont un bon compromis.</li>
                        <li><strong>Une campagne a la fois :</strong> impossible de lancer deux campagnes en parallele. Attendre la fin ou arreter la campagne en cours.</li>
                        <li><strong>Pause vs Arret :</strong> Pause permet de reprendre plus tard (etat paused). Arret termine la campagne definitivement (etat stopped) ; seuls les numeros restes « pending » sont perdus.</li>
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
                            <li><strong>5. Campagnes WhatsApp :</strong> si envoi prevu, filtrer par centre, verifier les doublons avant lancement.</li>
                            <li><strong>6. Suivi Paiement / Paiement Professeurs :</strong> importer / verifier les fichiers mensuels selon le jour du mois.</li>
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
@endsection
