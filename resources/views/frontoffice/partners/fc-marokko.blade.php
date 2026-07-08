@extends('frontoffice.layouts.app')

@section('title', __('partners/fc_marokko.meta.title'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/frontoffice/partners/fc-marokko.css') }}">
@endpush

@section('content')

    {{-- ================== HERO ================== --}}
    <section class="fcm-hero">
        <div class="fcm-hero__bg"
            style="background-image:url('{{ asset('assets/images/fc-marokko/475873194_1137954098118088_822180009608469424_n.avif') }}');">
        </div>
        <div class="fcm-hero__overlay"></div>

        <div class="fcm-hero__stripes" aria-hidden="true">
            <span></span><span></span><span></span><span></span>
        </div>

        {{-- Rolling football intro animation --}}
        <div class="fcm-rolling-ball" aria-hidden="true">
            <svg width="110" height="110" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet" style="display:block;width:100%;height:100%;max-width:110px;max-height:110px;">
                <defs>
                    <radialGradient id="fcmBallBody" cx="38%" cy="32%" r="72%">
                        <stop offset="0%"   stop-color="#fff6c7"/>
                        <stop offset="18%"  stop-color="#ffe27a"/>
                        <stop offset="55%"  stop-color="#f5b100"/>
                        <stop offset="85%"  stop-color="#a66b00"/>
                        <stop offset="100%" stop-color="#5a3900"/>
                    </radialGradient>
                    <radialGradient id="fcmBallRim" cx="50%" cy="50%" r="50%">
                        <stop offset="70%"  stop-color="rgba(0,0,0,0)"/>
                        <stop offset="90%"  stop-color="rgba(0,0,0,.35)"/>
                        <stop offset="100%" stop-color="rgba(0,0,0,.75)"/>
                    </radialGradient>
                    <radialGradient id="fcmBallGloss" cx="34%" cy="26%" r="32%">
                        <stop offset="0%"  stop-color="rgba(255,255,255,.95)"/>
                        <stop offset="45%" stop-color="rgba(255,255,255,.25)"/>
                        <stop offset="100%" stop-color="rgba(255,255,255,0)"/>
                    </radialGradient>
                    <radialGradient id="fcmPanelDark" cx="50%" cy="40%" r="60%">
                        <stop offset="0%"  stop-color="#2a2220"/>
                        <stop offset="100%" stop-color="#0b0908"/>
                    </radialGradient>
                    <clipPath id="fcmBallClip">
                        <circle cx="100" cy="100" r="94"/>
                    </clipPath>
                </defs>
                <circle cx="100" cy="100" r="94" fill="url(#fcmBallBody)"/>
                <g clip-path="url(#fcmBallClip)" stroke="#0b0908" stroke-width="1.4" stroke-linejoin="round">
                    <polygon fill="url(#fcmPanelDark)" points="100,68 122,84 114,110 86,110 78,84"/>
                    <polygon fill="none" points="100,68 122,84 148,74 152,50 130,36 108,44"/>
                    <polygon fill="none" points="122,84 114,110 134,130 160,120 162,92 148,74"/>
                    <polygon fill="none" points="114,110 86,110 72,132 88,154 114,154 134,130"/>
                    <polygon fill="none" points="86,110 78,84 52,74 38,92 40,120 72,132"/>
                    <polygon fill="none" points="78,84 100,68 108,44 92,24 66,32 52,74"/>
                    <polygon fill="url(#fcmPanelDark)" points="92,24 108,44 130,36 126,14 104,8"/>
                    <polygon fill="url(#fcmPanelDark)" points="130,36 152,50 174,42 166,20 148,14"/>
                    <polygon fill="url(#fcmPanelDark)" points="162,92 148,74 168,62 184,78 180,98"/>
                    <polygon fill="url(#fcmPanelDark)" points="160,120 134,130 140,156 166,158 176,136"/>
                    <polygon fill="url(#fcmPanelDark)" points="114,154 88,154 82,180 108,188 124,172"/>
                    <polygon fill="url(#fcmPanelDark)" points="72,132 40,120 24,138 38,160 66,158"/>
                    <polygon fill="url(#fcmPanelDark)" points="40,120 38,92 16,78 6,104 22,122"/>
                    <polygon fill="url(#fcmPanelDark)" points="52,74 66,32 42,24 24,42 28,68"/>
                </g>
                <circle cx="100" cy="100" r="94" fill="url(#fcmBallRim)"/>
                <ellipse cx="72" cy="58" rx="40" ry="26" fill="url(#fcmBallGloss)"/>
                <circle cx="100" cy="100" r="94" fill="none" stroke="#0b0908" stroke-width="1.5"/>
            </svg>
        </div>

        {{-- ============ GOAL (3D perspective: back net → ball → front net) ============ --}}
        <div class="fcm-goal" aria-hidden="true" style="max-width:300px;max-height:380px;overflow:hidden;">

            {{-- BACK of goal: far posts + back net --}}
            <div class="fcm-goal__back" style="overflow:hidden;">
                <svg width="300" height="380" viewBox="0 0 240 340" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMax meet" style="display:block;width:100%;height:100%;max-width:300px;max-height:380px;">
                    <defs>
                        <linearGradient id="fcmBackPost" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0%"  stop-color="#d0d0d0"/>
                            <stop offset="50%" stop-color="#b0b0b0"/>
                            <stop offset="100%" stop-color="#7a7a7a"/>
                        </linearGradient>
                        <radialGradient id="fcmGoalFloor" cx="50%" cy="100%" r="70%">
                            <stop offset="0%"  stop-color="rgba(0,0,0,.55)"/>
                            <stop offset="100%" stop-color="rgba(0,0,0,0)"/>
                        </radialGradient>
                    </defs>
                    <ellipse cx="120" cy="320" rx="100" ry="18" fill="url(#fcmGoalFloor)"/>
                    <g class="fcm-goal__net fcm-goal__net--back" stroke="rgba(220,220,220,.35)" stroke-width=".9" fill="none">
                        <line x1="40"  y1="60" x2="40"  y2="310"/>
                        <line x1="60"  y1="60" x2="60"  y2="310"/>
                        <line x1="80"  y1="60" x2="80"  y2="310"/>
                        <line x1="100" y1="60" x2="100" y2="310"/>
                        <line x1="120" y1="60" x2="120" y2="310"/>
                        <line x1="140" y1="60" x2="140" y2="310"/>
                        <line x1="160" y1="60" x2="160" y2="310"/>
                        <line x1="180" y1="60" x2="180" y2="310"/>
                        <line x1="200" y1="60" x2="200" y2="310"/>
                        <line x1="30" y1="80"  x2="210" y2="80"/>
                        <line x1="30" y1="110" x2="210" y2="110"/>
                        <line x1="30" y1="140" x2="210" y2="140"/>
                        <line x1="30" y1="170" x2="210" y2="170"/>
                        <line x1="30" y1="200" x2="210" y2="200"/>
                        <line x1="30" y1="230" x2="210" y2="230"/>
                        <line x1="30" y1="260" x2="210" y2="260"/>
                        <line x1="30" y1="290" x2="210" y2="290"/>
                    </g>
                    <g class="fcm-goal__frame fcm-goal__frame--back">
                        <rect x="28" y="52" width="184" height="12" rx="2" fill="url(#fcmBackPost)" stroke="#222" stroke-width=".8"/>
                        <rect x="28"  y="52" width="12" height="260" rx="2" fill="url(#fcmBackPost)" stroke="#222" stroke-width=".8"/>
                        <rect x="200" y="52" width="12" height="260" rx="2" fill="url(#fcmBackPost)" stroke="#222" stroke-width=".8"/>
                    </g>
                    <g class="fcm-goal__sides" stroke="rgba(220,220,220,.3)" stroke-width=".8" fill="none">
                        <line x1="4"   y1="18"  x2="28"  y2="52"/>
                        <line x1="4"   y1="318" x2="28"  y2="312"/>
                        <line x1="16"  y1="18"  x2="34"  y2="52"/>
                        <line x1="236" y1="18"  x2="212" y2="52"/>
                        <line x1="236" y1="318" x2="212" y2="312"/>
                        <line x1="224" y1="18"  x2="206" y2="52"/>
                    </g>
                </svg>
            </div>

            {{-- FRONT of goal: front posts + front net --}}
            <div class="fcm-goal__front" style="overflow:hidden;">
                <svg width="300" height="380" viewBox="0 0 240 340" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMax meet" style="display:block;width:100%;height:100%;max-width:300px;max-height:380px;">
                    <defs>
                        <linearGradient id="fcmPost" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0%"  stop-color="#ffffff"/>
                            <stop offset="50%" stop-color="#e8e8e8"/>
                            <stop offset="100%" stop-color="#a8a8a8"/>
                        </linearGradient>
                    </defs>
                    <g class="fcm-goal__net fcm-goal__net--front" stroke="rgba(255,255,255,.45)" stroke-width="1" fill="none">
                        <line x1="18"  y1="30" x2="18"  y2="322"/>
                        <line x1="40"  y1="30" x2="40"  y2="322"/>
                        <line x1="62"  y1="30" x2="62"  y2="322"/>
                        <line x1="84"  y1="30" x2="84"  y2="322"/>
                        <line x1="106" y1="30" x2="106" y2="322"/>
                        <line x1="128" y1="30" x2="128" y2="322"/>
                        <line x1="150" y1="30" x2="150" y2="322"/>
                        <line x1="172" y1="30" x2="172" y2="322"/>
                        <line x1="194" y1="30" x2="194" y2="322"/>
                        <line x1="216" y1="30" x2="216" y2="322"/>
                        <line x1="4" y1="60"  x2="236" y2="60"/>
                        <line x1="4" y1="90"  x2="236" y2="90"/>
                        <line x1="4" y1="120" x2="236" y2="120"/>
                        <line x1="4" y1="150" x2="236" y2="150"/>
                        <line x1="4" y1="180" x2="236" y2="180"/>
                        <line x1="4" y1="210" x2="236" y2="210"/>
                        <line x1="4" y1="240" x2="236" y2="240"/>
                        <line x1="4" y1="270" x2="236" y2="270"/>
                        <line x1="4" y1="300" x2="236" y2="300"/>
                    </g>
                    <g class="fcm-goal__frame fcm-goal__frame--front">
                        <rect x="2" y="18" width="236" height="16" rx="3" fill="url(#fcmPost)" stroke="#333" stroke-width="1"/>
                        <rect x="2"   y="18" width="16" height="300" rx="3" fill="url(#fcmPost)" stroke="#333" stroke-width="1"/>
                        <rect x="222" y="18" width="16" height="300" rx="3" fill="url(#fcmPost)" stroke="#333" stroke-width="1"/>
                        <rect x="2" y="316" width="236" height="6" rx="1.5" fill="url(#fcmPost)" stroke="#333" stroke-width=".8"/>
                    </g>
                </svg>
            </div>

            {{-- GOAL flash --}}
            <div class="fcm-goal__flash">
                <div class="fcm-goal__burst"></div>
                <div class="fcm-goal__text">GOAL!</div>
            </div>
        </div>

        <div class="container fcm-hero__container">

            <div class="fcm-hero__badge reveal delay-1">
                <span class="fcm-dot"></span>
                {{ __('partners/fc_marokko.hero.badge') }}
            </div>

            <div class="fcm-hero__lockup reveal delay-2">
                <div class="fcm-lockup__logo">
                    <img src="{{ asset('assets/images/logo/gls-round.avif') }}"
                         onerror="this.src='{{ asset('assets/images/logo/gls-noir.avif') }}'"
                         alt="GLS Sprachenzentrum">
                </div>
                <div class="fcm-lockup__x">x</div>
                <div class="fcm-lockup__logo fcm-lockup__logo--alt">
                    <img src="https://fc-marokko.de/storage/2022/10/fcm-logo.png"
                         alt="{{ __('partners/fc_marokko.hero.alt') }}">
                </div>
            </div>

            <h1 class="fcm-hero__title reveal delay-3">
                {!! __('partners/fc_marokko.hero.title') !!}
            </h1>

            <p class="fcm-hero__subtitle reveal delay-3">
                {{ __('partners/fc_marokko.hero.subtitle') }}
            </p>

            <div class="fcm-hero__meta reveal delay-1">
                {{ __('partners/fc_marokko.hero.meta') }}
            </div>

            <div class="fcm-hero__actions reveal delay-2">
                <a href="#fcm-story" class="fcm-btn fcm-btn--primary">
                    {{ __('partners/fc_marokko.hero.cta_primary') }}
                    <i class="bi bi-arrow-down-short"></i>
                </a>
                <a href="https://fc-marokko.de/" target="_blank" rel="noopener" class="fcm-btn fcm-btn--ghost">
                    {{ __('partners/fc_marokko.hero.cta_secondary') }}
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </div>

        </div>
    </section>

    {{-- ================== STATS STRIP ================== --}}
    <section class="fcm-stats">
        <div class="container">
            <div class="fcm-stats__grid">
                <div class="fcm-stat reveal fcm-anim fcm-anim--stat delay-1">
                    <div class="fcm-stat__value" data-fcm-count="{{ __('partners/fc_marokko.stats.s1_value') }}">{{ __('partners/fc_marokko.stats.s1_value') }}</div>
                    <div class="fcm-stat__label">{{ __('partners/fc_marokko.stats.s1_label') }}</div>
                </div>
                <div class="fcm-stat reveal fcm-anim fcm-anim--stat delay-2">
                    <div class="fcm-stat__value" data-fcm-count="{{ __('partners/fc_marokko.stats.s2_value') }}">{{ __('partners/fc_marokko.stats.s2_value') }}</div>
                    <div class="fcm-stat__label">{{ __('partners/fc_marokko.stats.s2_label') }}</div>
                </div>
                <div class="fcm-stat reveal fcm-anim fcm-anim--stat delay-3">
                    <div class="fcm-stat__value" data-fcm-count="{{ __('partners/fc_marokko.stats.s3_value') }}">{{ __('partners/fc_marokko.stats.s3_value') }}</div>
                    <div class="fcm-stat__label">{{ __('partners/fc_marokko.stats.s3_label') }}</div>
                </div>
                <div class="fcm-stat reveal fcm-anim fcm-anim--stat delay-1">
                    <div class="fcm-stat__value" data-fcm-count="{{ __('partners/fc_marokko.stats.s4_value') }}">{{ __('partners/fc_marokko.stats.s4_value') }}</div>
                    <div class="fcm-stat__label">{{ __('partners/fc_marokko.stats.s4_label') }}</div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            (function () {
                const targets = document.querySelectorAll('.fcm-stats [data-fcm-count]');
                if (!targets.length) return;

                const parse = (raw) => {
                    const match = String(raw).match(/^(\D*)(\d[\d\s.,]*)(\D*)$/);
                    if (!match) return null;
                    const prefix = match[1];
                    const numStr = match[2].replace(/[\s,]/g, '');
                    const suffix = match[3];
                    const end = parseFloat(numStr);
                    if (!isFinite(end)) return null;
                    return { prefix, suffix, end, isInt: !numStr.includes('.') };
                };

                const ease = (t) => 1 - Math.pow(1 - t, 3);

                const animate = (el) => {
                    const raw = el.getAttribute('data-fcm-count') || el.textContent;
                    const parsed = parse(raw);
                    if (!parsed) return;

                    const duration = 1600;
                    const start = performance.now();
                    el.textContent = parsed.prefix + '0' + parsed.suffix;

                    const step = (now) => {
                        const p = Math.min((now - start) / duration, 1);
                        const v = parsed.end * ease(p);
                        const shown = parsed.isInt ? Math.floor(v) : v.toFixed(1);
                        el.textContent = parsed.prefix + shown + parsed.suffix;
                        if (p < 1) requestAnimationFrame(step);
                        else el.textContent = raw;
                    };
                    requestAnimationFrame(step);
                };

                const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (reduce || !('IntersectionObserver' in window)) return;

                const io = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        animate(entry.target);
                        io.unobserve(entry.target);
                    });
                }, { threshold: 0.4 });

                targets.forEach((el) => io.observe(el));
            })();
        </script>
    @endpush

    {{-- ================== STORY ================== --}}
    <section id="fcm-story" class="fcm-story">
        <div class="container">
            <div class="row g-5 align-items-center">

                <div class="col-12 col-lg-6 reveal delay-1">
                    <div class="fcm-story__kicker">
                        <span class="fcm-bar"></span>
                        {{ __('partners/fc_marokko.content.kicker') }}
                    </div>
                    <h2 class="fcm-story__heading reveal fcm-anim fcm-anim--sweep delay-2">
                        {{ __('partners/fc_marokko.content.heading') }}
                    </h2>

                    <div class="fcm-story__text">
                        <p>{!! __('partners/fc_marokko.content.p1') !!}</p>
                        <p>{!! __('partners/fc_marokko.content.p2') !!}</p>
                        <p>{!! __('partners/fc_marokko.content.p3') !!}</p>
                    </div>

                    <a href="https://fc-marokko.de/" target="_blank" rel="noopener" class="fcm-btn fcm-btn--dark">
                        {{ __('partners/fc_marokko.section1.button') }}
                        <i class="bi bi-arrow-up-right"></i>
                    </a>
                </div>

                <div class="col-12 col-lg-6 reveal delay-2">
                    <div class="fcm-story__visual reveal fcm-anim fcm-anim--tilt delay-2">
                        <div class="fcm-story__card fcm-story__card--main">
                            <img src="{{ asset('assets/images/fc-marokko/475873194_1137954098118088_822180009608469424_n.avif') }}"
                                 alt="FC Marokko" loading="lazy">
                            <div class="fcm-story__card-tag">
                                <i class="bi bi-trophy-fill"></i>
                                FC Marokko
                            </div>
                        </div>
                        <div class="fcm-story__card fcm-story__card--badge">
                            <img src="https://fc-marokko.de/storage/2022/10/fcm-logo.png" alt="FC Marokko logo" loading="lazy">
                        </div>
                        <div class="fcm-story__ball" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.6" fill="#fff"/>
                                <path d="M12 4.5 14.8 7l-1 3.2h-3.6l-1-3.2L12 4.5Z" fill="currentColor"/>
                                <path d="M12 13.6 15.2 16l-1.2 3.3h-4l-1.2-3.3L12 13.6Z" fill="currentColor"/>
                                <path d="m4.4 8.8 2.9.9.8 3.2-2.6 2.2-2.3-1.7.2-4.6 1-.0Z" fill="currentColor"/>
                                <path d="m19.6 8.8 1 .0.2 4.6-2.3 1.7-2.6-2.2.8-3.2 2.9-.9Z" fill="currentColor"/>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ================== VIDEO ================== --}}
    <section class="fcm-video">
        <div class="fcm-video__stripes" aria-hidden="true">
            <span></span><span></span><span></span>
        </div>

        <div class="container">
            <div class="fcm-section-head text-center reveal delay-1">
                <div class="fcm-section-head__kicker" style="justify-content:center;">
                    <span class="fcm-bar"></span>
                    {{ __('partners/fc_marokko.video.kicker') }}
                    <span class="fcm-bar"></span>
                </div>
                <h2 class="fcm-section-head__title fcm-video__title reveal fcm-anim fcm-anim--sweep delay-2">
                    {{ __('partners/fc_marokko.video.title') }}
                </h2>
                <p class="fcm-video__subtitle">
                    {{ __('partners/fc_marokko.video.subtitle') }}
                </p>
            </div>

            <div class="fcm-video__wrap reveal fcm-anim fcm-anim--pop delay-2">
                <div class="fcm-video__frame">
                    <div class="fcm-video__corner fcm-video__corner--tl"></div>
                    <div class="fcm-video__corner fcm-video__corner--tr"></div>
                    <div class="fcm-video__corner fcm-video__corner--bl"></div>
                    <div class="fcm-video__corner fcm-video__corner--br"></div>

                    <div class="fcm-video__badge">
                        <span class="fcm-video__dot"></span>
                        FC Marokko Herne
                    </div>

                    <div class="fcm-video__ratio">
                        <iframe
                            src="https://player.vimeo.com/video/1172167181?title=0&byline=0&portrait=0&badge=0&autopause=0&player_id=0&app_id=58479"
                            allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen
                            loading="lazy"
                            title="FC Marokko Herne in Deutschland"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================== PILLARS ================== --}}
    <section class="fcm-pillars">
        <div class="container">

            <div class="fcm-section-head text-center reveal delay-1">
                <div class="fcm-section-head__kicker">
                    <span class="fcm-bar"></span>
                    {{ __('partners/fc_marokko.pillars.title') }}
                    <span class="fcm-bar"></span>
                </div>
                <h2 class="fcm-section-head__title reveal fcm-anim fcm-anim--sweep delay-2">{{ __('partners/fc_marokko.pillars.subtitle') }}</h2>
            </div>

            <div class="row g-4 fcm-pillars__grid">

                <div class="col-12 col-md-4 reveal fcm-anim fcm-anim--pillar delay-1">
                    <article class="fcm-pillar fcm-pillar--green">
                        <div class="fcm-pillar__index">{{ __('partners/fc_marokko.pillars.c1_kicker') }}</div>
                        <div class="fcm-pillar__icon"><i class="bi bi-shield-check"></i></div>
                        <h3 class="fcm-pillar__title">{{ __('partners/fc_marokko.pillars.c1_title') }}</h3>
                        <p class="fcm-pillar__text">{!! __('partners/fc_marokko.pillars.c1_text') !!}</p>
                    </article>
                </div>

                <div class="col-12 col-md-4 reveal fcm-anim fcm-anim--pillar delay-2">
                    <article class="fcm-pillar fcm-pillar--orange">
                        <div class="fcm-pillar__index">{{ __('partners/fc_marokko.pillars.c2_kicker') }}</div>
                        <div class="fcm-pillar__icon"><i class="bi bi-bullseye"></i></div>
                        <h3 class="fcm-pillar__title">{{ __('partners/fc_marokko.pillars.c2_title') }}</h3>
                        <p class="fcm-pillar__text">{!! __('partners/fc_marokko.pillars.c2_text') !!}</p>
                    </article>
                </div>

                <div class="col-12 col-md-4 reveal fcm-anim fcm-anim--pillar delay-3">
                    <article class="fcm-pillar fcm-pillar--red">
                        <div class="fcm-pillar__index">{{ __('partners/fc_marokko.pillars.c3_kicker') }}</div>
                        <div class="fcm-pillar__icon"><i class="bi bi-people-fill"></i></div>
                        <h3 class="fcm-pillar__title">{{ __('partners/fc_marokko.pillars.c3_title') }}</h3>
                        <p class="fcm-pillar__text">{!! __('partners/fc_marokko.pillars.c3_text') !!}</p>
                    </article>
                </div>

            </div>

        </div>
    </section>

    {{-- ================== GALLERY ================== --}}
    <section class="fcm-gallery">
        <div class="container">

            <div class="fcm-section-head reveal delay-1">
                <div class="fcm-section-head__kicker">
                    <span class="fcm-bar"></span>
                    {{ __('partners/fc_marokko.gallery.kicker') }}
                </div>
                <h2 class="fcm-section-head__title fcm-section-head__title--left reveal fcm-anim fcm-anim--sweep delay-2">
                    {{ __('partners/fc_marokko.gallery.title') }}
                </h2>
                <p class="fcm-section-head__sub">{!! __('partners/fc_marokko.gallery.subtitle') !!}</p>
            </div>

            @php
                $fcmGallery = [
                    asset('assets/images/fc-marokko/fc marokko gallery.avif'),
                    asset('assets/images/fc-marokko/475873194_1137954098118088_822180009608469424_n.avif'),
                    asset('assets/images/fc-marokko/539389842_17990714768828776_5722745523080845426_n.avif'),
                    asset('assets/images/fc-marokko/630_0900_4148810_sp_240804cr_FB_FC_Frohlinde_blau_Marokk.avif'),
                    asset('assets/images/fc-marokko/3WlontCvCGqZL5pQqbzsFz_t3.avif'),
                ];
            @endphp

            <div class="fcm-gallery__grid">
                @foreach ($fcmGallery as $i => $img)
                    <button type="button"
                            class="fcm-gallery__item reveal fcm-anim fcm-anim--tile delay-{{ ($i % 4) + 1 }}{{ $i === 0 ? ' fcm-gallery__item--big' : '' }}"
                            data-bs-toggle="modal"
                            data-bs-target="#fcmGalleryModal"
                            data-fcm-index="{{ $i }}">
                        <img src="{{ $img }}" alt="FC Marokko" loading="lazy">
                        <div class="fcm-gallery__overlay"><i class="bi bi-arrows-fullscreen"></i></div>
                    </button>
                @endforeach
            </div>

        </div>
    </section>

    {{-- ================== GALLERY MODAL (CAROUSEL) ================== --}}
    <div class="modal fade fcm-modal" id="fcmGalleryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content fcm-modal__content">
                <button type="button" class="fcm-modal__close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>

                <div id="fcmGalleryCarousel" class="carousel slide" data-bs-ride="false" data-bs-interval="false">
                    <div class="carousel-inner">
                        @foreach ($fcmGallery as $i => $img)
                            <div class="carousel-item @if ($i === 0) active @endif">
                                <img src="{{ $img }}" class="d-block w-100 fcm-modal__img" alt="FC Marokko">
                            </div>
                        @endforeach
                    </div>

                    <button class="carousel-control-prev fcm-modal__nav" type="button"
                            data-bs-target="#fcmGalleryCarousel" data-bs-slide="prev">
                        <span class="fcm-modal__nav-icon"><i class="bi bi-chevron-left"></i></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next fcm-modal__nav" type="button"
                            data-bs-target="#fcmGalleryCarousel" data-bs-slide="next">
                        <span class="fcm-modal__nav-icon"><i class="bi bi-chevron-right"></i></span>
                        <span class="visually-hidden">Next</span>
                    </button>

                    <div class="fcm-modal__counter">
                        <span class="fcm-current">1</span> / <span class="fcm-total">{{ count($fcmGallery) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                const modalEl = document.getElementById('fcmGalleryModal');
                if (!modalEl) return;
                const carouselEl = document.getElementById('fcmGalleryCarousel');
                const counter = modalEl.querySelector('.fcm-current');

                modalEl.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) return;
                    const index = parseInt(trigger.getAttribute('data-fcm-index') || '0', 10);
                    const carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
                    carousel.to(index);
                    if (counter) counter.textContent = index + 1;
                });

                carouselEl.addEventListener('slid.bs.carousel', function (event) {
                    if (counter) counter.textContent = event.to + 1;
                });

                document.addEventListener('keydown', function (e) {
                    if (!modalEl.classList.contains('show')) return;
                    const carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
                    if (e.key === 'ArrowLeft')  carousel.prev();
                    if (e.key === 'ArrowRight') carousel.next();
                });
            })();
        </script>
    @endpush

    {{-- ================== CTA ================== --}}
    <section class="fcm-cta-wrap">
        <div class="container">
            <div class="fcm-cta reveal fcm-anim fcm-anim--pop delay-1">
                <div class="fcm-cta__stripes" aria-hidden="true">
                    <span></span><span></span><span></span>
                </div>
                <div class="fcm-cta__content">
                    <div class="fcm-cta__kicker">{{ __('partners/fc_marokko.cta.kicker') }}</div>
                    <h2 class="fcm-cta__title">{{ __('partners/fc_marokko.cta.title') }}</h2>
                    <p class="fcm-cta__text">{!! __('partners/fc_marokko.cta.text') !!}</p>
                    <a href="{{ LaravelLocalization::localizeUrl(route('front.contact')) }}" class="fcm-btn fcm-btn--primary fcm-btn--lg">
                        {{ __('partners/fc_marokko.cta.button') }}
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection
