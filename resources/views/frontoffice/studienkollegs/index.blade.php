@extends('frontoffice.layouts.app')

@section('title', 'Studienkollegs in Germany')
@section('description', 'Explore public Studienkollegs in Germany and prepare your university admission.')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/studienkollegs/studienkollegs.css') }}">

<style>
    .favorite-btn {
        cursor: pointer;
        transition: transform .2s ease, opacity .2s ease;
    }

    .favorite-btn.active {
        color: #ef4444;
        opacity: 1;
    }

    .favorite-btn.active:hover {
        transform: scale(1.05);
    }
</style>

@section('content')

    @php
        $youtubeId = null;

        if (isset($featured) && !empty($featured->video_url)) {
            parse_str(parse_url($featured->video_url, PHP_URL_QUERY) ?? '', $qs);
            $youtubeId = $qs['v'] ?? null;

            if (!$youtubeId && str_contains($featured->video_url, 'youtu.be/')) {
                $youtubeId = last(explode('/', $featured->video_url));
            }
        }
    @endphp

    <div class="studienkollegs-page">

        <div class="studienkollegs-filters reveal delay-1">
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

        @if ($featured)
            <ul class="studienkollegs-featured-list">
                <li class="featured-card reveal delay-2">
                    <div class="featured-card-inner">

                        <div class="featured-image">
                            <img src="{{ $featured->getFirstMediaUrl('studienkolleg_hero') }}" alt="{{ $featured->name }}">
                        </div>

                        <div class="featured-content">

                            <div class="featured-header">
                                <div class="featured-logo">
                                    <img src="{{ $featured->getFirstMediaUrl('university_logo') }}"
                                        alt="{{ $featured->university }}">
                                </div>

                                <div class="featured-university">
                                    <div class="featured-university-name">
                                        {{ $featured->university }}
                                    </div>
                                    <div class="featured-university-location">
                                        {{ $featured->city }}, Germany
                                    </div>
                                </div>

                                <i class="ph-duotone ph-heart favorite-btn" data-id="{{ $featured->id }}"></i>
                            </div>

                            <hr class="featured-separator">

                            <h2 class="featured-title fade-blur-title delay-3">
                                <a href="{{ route('front.studienkollegs.show', $featured->slug) }}"
                                    class="featured-title-link">
                                    {{ $featured->name }}
                                </a>
                            </h2>


                            <div class="featured-tag">
                                <img src="{{ asset('assets/images/studienkollegs/germany.webp') }}" alt="Germany">
                                <span>Studienkolleg · {{ $featured->public ? 'Public' : 'Private' }}</span>
                            </div>

                            <hr class="featured-separator">

                            <div class="featured-meta">
                                <div class="featured-meta-item">
                                    <i class="ph-duotone ph-clock"></i>
                                    <div>
                                        <div class="featured-meta-value">
                                            {{ $featured->duration_semesters }} Semesters
                                        </div>
                                        <div class="featured-meta-label">Duration</div>
                                    </div>
                                </div>

                                <div class="featured-meta-item">
                                    <i class="ph-duotone ph-currency-eur"></i>
                                    <div>
                                        <div class="featured-meta-value">
                                            {{ $featured->tuition ?? 'Free' }}
                                        </div>
                                        <div class="featured-meta-label">Tuitions</div>
                                    </div>
                                </div>
                            </div>

                            @if ($featured->featured)
                                <div class="featured-badge">
                                    <span>
                                        <i class="ph-duotone ph-star"></i>
                                        Recommended by GLS
                                    </span>
                                </div>
                            @endif

                        </div>

                        @if ($youtubeId)
                            <div class="featured-video">
                                <img src="https://img.youtube.com/vi/{{ $youtubeId }}/hqdefault.jpg"
                                    alt="{{ $featured->name }} video">

                                <button class="video-play-btn"
                                    onclick="this.parentElement.innerHTML = `
                                    <iframe
                                        width='100%'
                                        height='100%'
                                        src='https://www.youtube.com/embed/{{ $youtubeId }}?autoplay=1&rel=0'
                                        frameborder='0'
                                        allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share'
                                        allowfullscreen>
                                    </iframe>
                                `;">
                                    <i class="ph-fill ph-play"></i>
                                </button>
                            </div>
                        @endif

                    </div>
                </li>
            </ul>
        @endif

        <ul class="studienkollegs-grid">
            @foreach ($studienkollegs as $item)
                <li class="studienkolleg-card reveal delay-2">

                    <a href="{{ route('front.studienkollegs.show', $item->slug) }}" class="card-link-overlay"
                        aria-label="View {{ $item->name }}"></a>

                    <div class="card-header">
                        <img src="{{ $item->getFirstMediaUrl('university_logo') ?: asset('assets/images/studienkollegs/default-logo.svg') }}"
                            alt="{{ $item->university }}">

                        <div class="card-university">
                            <div class="card-university-name">
                                {{ $item->university ?: $item->name }}
                            </div>
                            <div class="card-university-location">
                                {{ $item->city }}, {{ $item->country ?? 'Germany' }}
                            </div>
                        </div>

                        <i class="ph-duotone ph-heart card-favorite favorite-btn" data-id="{{ $item->id }}"></i>
                    </div>

                    <hr class="card-separator">

                    <h3 class="card-title fade-blur-title delay-3">
                        {{ $item->name }}
                    </h3>

                    <div class="card-tag reveal delay-3">
                        <img src="{{ asset('assets/images/studienkollegs/germany.webp') }}" alt="Germany">
                        <span>{{ $item->public ? 'Public Studienkolleg' : 'Private Studienkolleg' }}</span>
                    </div>

                    <div class="card-meta reveal delay-4">
                        <div class="card-meta-item">
                            <i class="ph-duotone ph-clock"></i>
                            <div>
                                <div class="card-meta-value">
                                    {{ $item->duration_semesters }} Semesters
                                </div>
                                <div class="card-meta-label">Duration</div>
                            </div>
                        </div>

                        <div class="card-meta-item">
                            <i class="ph-duotone ph-currency-eur"></i>
                            <div>
                                <div class="card-meta-value">
                                    {{ $item->tuition ?? 'Free' }}
                                </div>
                                <div class="card-meta-label">Tuitions</div>
                            </div>
                        </div>
                    </div>

                </li>
            @endforeach
        </ul>


        <div class="studienkollegs-pagination reveal delay-2">
            {{ $studienkollegs->links() }}
        </div>

    </div>

    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="{{ asset('assets/js/favorites.js') }}"></script>

@endsection
