<section class="gls-mv-section reveal delay-1" aria-label="{{ __('home.marketing_videos.title') }}">
    <div class="gls-mv-wrap reveal delay-2">
        <header class="gls-mv-header reveal delay-3">
            <h2 class="gls-mv-title reveal fade-blur-title delay-1">
                {{ __('home.marketing_videos.title') }}
            </h2>
            <p class="gls-mv-subtitle reveal delay-2">
                {{ __('home.marketing_videos.subtitle') }}
            </p>
        </header>

        <div class="gls-mv-carousel reveal delay-1">
            <button class="gls-mv-carousel-btn gls-mv-carousel-btn--prev" type="button" aria-label="Previous">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>

            <div class="gls-mv-viewport">
            <div class="gls-mv-grid">

                {{-- Video 1 — Master BWL --}}
                <div class="gls-mv-card gls-mv-card--blue reveal delay-1">
                    <div class="gls-mv-video reveal delay-2">
                        @include('frontoffice.partials.video-facade', ['id' => '1173822209', 'title' => 'Master BWL in Deutschland'])
                    </div>
                    <div class="gls-mv-label reveal delay-3">
                        <span class="gls-mv-dot"></span>
                        Master BWL
                    </div>
                </div>

                {{-- Video 2 — Deutsche Sprache --}}
                <div class="gls-mv-card gls-mv-card--orange reveal delay-2">
                    <div class="gls-mv-video reveal delay-3">
                        @include('frontoffice.partials.video-facade', ['id' => '1172166445', 'title' => 'Deutsche Sprache'])
                    </div>
                    <div class="gls-mv-label reveal delay-1">
                        <span class="gls-mv-dot"></span>
                        Deutsche Sprache
                    </div>
                </div>

                {{-- Video 3 — Arbeit --}}
                <div class="gls-mv-card gls-mv-card--green reveal delay-3">
                    <div class="gls-mv-video reveal delay-1">
                        @include('frontoffice.partials.video-facade', ['id' => '1172167791', 'title' => 'Arbeit'])
                    </div>
                    <div class="gls-mv-label reveal delay-2">
                        <span class="gls-mv-dot"></span>
                        Arbeit
                    </div>
                </div>

                {{-- Video 4 — GLS Témoignage PLAN C --}}
                <div class="gls-mv-card gls-mv-card--purple reveal delay-1">
                    <div class="gls-mv-video reveal delay-2">
                        @include('frontoffice.partials.video-facade', ['id' => '1173823269', 'title' => 'GLS Témoignage PLAN C'])
                    </div>
                    <div class="gls-mv-label reveal delay-3">
                        <span class="gls-mv-dot"></span>
                        GLS Témoignage PLAN C
                    </div>
                </div>

                {{-- Video 5 — Ausbildung --}}
                <div class="gls-mv-card gls-mv-card--yellow reveal delay-2">
                    <div class="gls-mv-video reveal delay-3">
                        @include('frontoffice.partials.video-facade', ['id' => '1173821770', 'title' => 'Ausbildung'])
                    </div>
                    <div class="gls-mv-label reveal delay-1">
                        <span class="gls-mv-dot"></span>
                        Ausbildung
                    </div>
                </div>

                {{-- Video 6 — Final HQ --}}
                <div class="gls-mv-card gls-mv-card--blue reveal delay-3">
                    <div class="gls-mv-video reveal delay-1">
                        @include('frontoffice.partials.video-facade', ['id' => '1172171254', 'title' => 'Final HQ'])
                    </div>
                    <div class="gls-mv-label reveal delay-2">
                        <span class="gls-mv-dot"></span>
                        Final HQ
                    </div>
                </div>

                {{-- Video 7 — Koch Ausbildung Vertrag --}}
                <div class="gls-mv-card gls-mv-card--orange reveal delay-1">
                    <div class="gls-mv-video reveal delay-2">
                        @include('frontoffice.partials.video-facade', ['id' => '1172166709', 'title' => 'Koch Ausbildung Vertrag'])
                    </div>
                    <div class="gls-mv-label reveal delay-3">
                        <span class="gls-mv-dot"></span>
                        Koch Ausbildung Vertrag
                    </div>
                </div>

                {{-- Video 8 — FC Marokko Herne --}}
                <div class="gls-mv-card gls-mv-card--green reveal delay-2">
                    <div class="gls-mv-video reveal delay-3">
                        @include('frontoffice.partials.video-facade', ['id' => '1172167181', 'title' => 'FC Marokko Herne in Deutschland'])
                    </div>
                    <div class="gls-mv-label reveal delay-1">
                        <span class="gls-mv-dot"></span>
                        FC Marokko Herne
                    </div>
                </div>

            </div>
            </div>

            <button class="gls-mv-carousel-btn gls-mv-carousel-btn--next" type="button" aria-label="Next">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>

        <div class="gls-mv-carousel-dots reveal delay-3"></div>

    </div>
</section>

<link rel="stylesheet" href="{{ asset('assets/css/marketing-videos.css') }}?v={{ filemtime(public_path('assets/css/marketing-videos.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/video-facade.css') }}?v={{ @filemtime(public_path('assets/css/video-facade.css')) ?: '1' }}">
<script defer src="{{ asset('assets/js/marketing-videos.js') }}?v={{ filemtime(public_path('assets/js/marketing-videos.js')) }}"></script>
<script defer src="{{ asset('assets/js/video-facade.js') }}?v={{ @filemtime(public_path('assets/js/video-facade.js')) ?: '1' }}"></script>
