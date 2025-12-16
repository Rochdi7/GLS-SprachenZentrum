@extends('frontoffice.layouts.app')

@section('title', 'Studienkollegs in Germany')
@section('description', 'Explore public Studienkollegs in Germany and prepare your university admission.')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/studienkollegs/studienkollegs.css') }}">

@section('content')

<div class="studienkollegs-page">

    {{-- =========================
    FILTER BAR
    ========================= --}}
    <div class="studienkollegs-filters">
        <div class="filters-actions">

            <button class="filter-btn filter-btn-primary">
                <i class="ph-duotone ph-funnel"></i>
                <span>All Filters</span>
            </button>

            <button class="filter-btn">
                <i class="ph-duotone ph-laptop"></i>
                <span>Online</span>
            </button>

            <button class="filter-btn">
                <i class="ph-duotone ph-graduation-cap"></i>
                <span>Uni Assist</span>
            </button>

            <button class="filter-btn">
                <i class="ph-duotone ph-calendar-check"></i>
                <span>Application Open Now</span>
            </button>

        </div>
    </div>

    {{-- =========================
    FEATURED STUDIENKOLLEG
    ========================= --}}
    <ul class="studienkollegs-featured-list">

        <li class="featured-card">
            <div class="featured-card-inner">

                {{-- IMAGE --}}
                <div class="featured-image">
                    <img src="{{ asset('assets/images/studienkollegs/1.webp') }}"
                         alt="Studienkolleg Leipzig">
                </div>

                {{-- CONTENT --}}
                <div class="featured-content">

                    {{-- HEADER --}}
                    <div class="featured-header">
                        <div class="featured-logo">
                            <img src="https://assets.edwerk.com/universities/logos/uni_leipzig.svg"
                                 alt="University of Leipzig">
                        </div>

                        <div class="featured-university">
                            <div class="featured-university-name">University of Leipzig</div>
                            <div class="featured-university-location">Leipzig, Germany</div>
                        </div>

                        <i class="ph-duotone ph-heart featured-favorite"></i>
                    </div>

                    <hr class="featured-separator">

                    <h2 class="featured-title">Studienkolleg Leipzig</h2>

                    <div class="featured-tag">
                        <img src="{{ asset('assets/images/studienkollegs/germany.webp') }}" alt="Germany">
                        <span>Studienkolleg · Public</span>
                    </div>

                    <hr class="featured-separator">

                    {{-- META --}}
                    <div class="featured-meta">

                        <div class="featured-meta-item">
                            <i class="ph-duotone ph-clock"></i>
                            <div>
                                <div class="featured-meta-value">2 Semesters</div>
                                <div class="featured-meta-label">Duration</div>
                            </div>
                        </div>

                        <div class="featured-meta-item">
                            <i class="ph-duotone ph-currency-eur"></i>
                            <div>
                                <div class="featured-meta-value">Free</div>
                                <div class="featured-meta-label">Tuitions</div>
                            </div>
                        </div>

                    </div>

                    {{-- BADGE --}}
                    <div class="featured-badge">
                        <span>
                            <i class="ph-duotone ph-star"></i>
                            Premium
                        </span>
                    </div>

                </div>

                {{-- VIDEO --}}
                <div class="featured-video">

    <!-- Poster image YouTube -->
    <img
        src="https://img.youtube.com/vi/3b3WdGQqO-g/hqdefault.jpg"
        alt="Studienkolleg Leipzig Campus Video">

    <!-- Play button -->
    <button class="video-play-btn"
            aria-label="Play video"
            onclick="this.parentElement.innerHTML = `
                <iframe
                    src='https://www.youtube.com/embed/3b3WdGQqO-g?autoplay=1&rel=0&modestbranding=1'
                    title='Studienkolleg Leipzig – Campus Video'
                    frameborder='0'
                    allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture'
                    referrerpolicy='strict-origin-when-cross-origin'
                    allowfullscreen>
                </iframe>
            `;">
        <i class="ph-fill ph-play"></i>
    </button>

</div>


            </div>
        </li>

    </ul>

    {{-- =========================
    GRID STUDIENKOLLEGS
    ========================= --}}
    <ul class="studienkollegs-grid">

        <li class="studienkolleg-card">

            <div class="card-header">
                <img src="https://assets.edwerk.com/universities/logos/tu_darmstadt.svg"
                     alt="Technical University of Darmstadt">

                <div class="card-university">
                    <div class="card-university-name">Technical University of Darmstadt</div>
                    <div class="card-university-location">Darmstadt, Germany</div>
                </div>

                <i class="ph-duotone ph-heart card-favorite"></i>
            </div>

            <hr class="card-separator">

            <h3 class="card-title">Studienkolleg Darmstadt</h3>

            <div class="card-tag">
                <img src="{{ asset('assets/images/studienkollegs/germany.webp') }}" alt="Germany">
                <span>Public Studienkolleg</span>
            </div>

            <div class="card-meta">

                <div class="card-meta-item">
                    <i class="ph-duotone ph-clock"></i>
                    <div>
                        <div class="card-meta-value">2 Semesters</div>
                        <div class="card-meta-label">Duration</div>
                    </div>
                </div>

                <div class="card-meta-item">
                    <i class="ph-duotone ph-currency-eur"></i>
                    <div>
                        <div class="card-meta-value">Free</div>
                        <div class="card-meta-label">Tuitions</div>
                    </div>
                </div>

            </div>

        </li>

    </ul>

</div>
<script src="https://unpkg.com/@phosphor-icons/web"></script>

@endsection
