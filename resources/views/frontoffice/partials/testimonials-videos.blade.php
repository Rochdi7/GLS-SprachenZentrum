<section class="gls-tv-section reveal delay-1" aria-label="Témoignages vidéo">
    <div class="gls-tv-wrap reveal delay-2">
        <header class="gls-tv-header reveal delay-3">
            <h2 class="gls-tv-title reveal fade-blur-title delay-1">
                @if (app()->getLocale() == 'fr')
                    ILS PARLENT DE NOUS
                @elseif (app()->getLocale() == 'ar')
                    يتحدثون عنا
                @else
                    THEY TALK ABOUT US
                @endif
            </h2>
        </header>

        @php
            $videoTestimonials = [
                [
                    'role' => 'Étudiant GLS',
                    'group' => 'Niveau B1 – Intermédiaire',
                    'poster' => asset('assets/images/logo/gls-round.avif'),
                    'vimeo' => 'https://player.vimeo.com/video/1172183086',
                ],
                [
                    'role' => 'Étudiant GLS',
                    'group' => 'Niveau B1 – Intermédiaire',
                    'poster' => asset('assets/images/logo/gls-round.avif'),
                    'vimeo' => 'https://player.vimeo.com/video/1172183039',
                ],
                [
                    'role' => 'Étudiant GLS',
                    'group' => 'Niveau B1 – Intermédiaire',
                    'poster' => asset('assets/images/logo/gls-round.avif'),
                    'vimeo' => 'https://player.vimeo.com/video/1172182987',
                ],
                [
                    'role' => 'Étudiant GLS',
                    'group' => 'Niveau B1 – Intermédiaire',
                    'poster' => asset('assets/images/logo/gls-round.avif'),
                    'vimeo' => 'https://player.vimeo.com/video/1172182943',
                ],
                [
                    'role' => 'Étudiant GLS',
                    'group' => 'Niveau B1 – Intermédiaire',
                    'poster' => asset('assets/images/logo/gls-round.avif'),
                    'vimeo' => 'https://player.vimeo.com/video/1172182895',
                ],
            ];
        @endphp

        <div class="gls-tv-stage reveal delay-1" data-gls-tv data-items='@json($videoTestimonials, JSON_UNESCAPED_UNICODE)'>
            <div class="gls-tv-deck reveal delay-2">
                <div class="gls-tv-card gls-tv-card--side reveal delay-1" data-pos="farLeft"></div>
                <div class="gls-tv-card gls-tv-card--side reveal delay-2" data-pos="left"></div>
                <div class="gls-tv-card gls-tv-card--center reveal delay-3" data-pos="center"></div>
                <div class="gls-tv-card gls-tv-card--side reveal delay-2" data-pos="right"></div>
                <div class="gls-tv-card gls-tv-card--side reveal delay-1" data-pos="farRight"></div>
            </div>

            <div class="gls-tv-phone reveal delay-3" role="group" aria-label="Aperçu des témoignages">
                <div class="gls-tv-device">
                    <div class="gls-tv-device-screen">
                        {{-- iOS Status Bar --}}
                        <div class="gls-tv-statusbar" aria-hidden="true">
                            <div class="gls-tv-sb-left">
                                <span data-sb-time>9:41</span>
                            </div>
                            <div class="gls-tv-sb-right">
                                {{-- Signal bars --}}
                                <svg class="gls-tv-sb-icon" viewBox="0 0 18 12" fill="currentColor">
                                    <rect x="0" y="8" width="3" height="4" rx="0.5" />
                                    <rect x="5" y="5" width="3" height="7" rx="0.5" />
                                    <rect x="10" y="2" width="3" height="10" rx="0.5" />
                                    <rect x="15" y="0" width="3" height="12" rx="0.5" />
                                </svg>
                                {{-- Wi-Fi --}}
                                <svg class="gls-tv-sb-icon" viewBox="0 0 16 12" fill="currentColor">
                                    <path
                                        d="M8 9.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM8 6c2.2 0 4.2.9 5.7 2.3l-1.4 1.4C11.1 8.6 9.6 8 8 8s-3.1.6-4.3 1.7L2.3 8.3C3.8 6.9 5.8 6 8 6zm0-4c3.3 0 6.3 1.3 8.5 3.5l-1.4 1.4C13.2 5.1 10.7 4 8 4S2.8 5.1.9 6.9L-.5 5.5C1.7 3.3 4.7 2 8 2z" />
                                </svg>
                                {{-- Battery --}}
                                <svg class="gls-tv-sb-icon gls-tv-sb-icon--battery" viewBox="0 0 25 12"
                                    fill="currentColor">
                                    <rect x="0" y="0" width="22" height="12" rx="2.5" fill="none"
                                        stroke="currentColor" stroke-width="1" />
                                    <rect x="2" y="2" width="17" height="8" rx="1" />
                                    <rect x="23" y="3.5" width="2" height="5" rx="0.5" />
                                </svg>
                            </div>
                        </div>
                        <article class="gls-tv-phone-video" data-phone-slide></article>
                        <div class="gls-tv-controls">
                            <span class="gls-tv-student-label">Étudiant GLS</span>
                            <button class="gls-tv-nav gls-tv-nav--prev" type="button" data-prev
                                aria-label="Précédent">‹</button>
                            <button class="gls-tv-nav gls-tv-nav--next" type="button" data-next
                                aria-label="Suivant">›</button>
                        </div>
                    </div>
                    <img class="gls-tv-device-mockup"
                        src="{{ asset('assets/images/apple-iphone-air-2025-medium.avif') }}" alt=""
                        loading="lazy" draggable="false">
                </div>
            </div>

            <div class="gls-tv-modal" data-modal aria-hidden="true">
                <div class="gls-tv-modal-backdrop" data-close></div>
                <div class="gls-tv-modal-dialog" role="dialog" aria-modal="true" aria-label="Lecture vidéo">
                    <button class="gls-tv-modal-close" type="button" data-close aria-label="Fermer">✕</button>
                </div>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="{{ asset('assets/css/testimonials-video.css') }}">
<script defer src="{{ asset('assets/js/testimonials-video.js') }}"></script>
