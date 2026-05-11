@extends('frontoffice.layouts.app')

@section('title', 'Studienkollegs in Germany')
@section('description', 'Explore public Studienkollegs in Germany and prepare your university admission.')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/studienkollegs/studienkollegs.css') }}?v={{ @filemtime(public_path('assets/css/frontoffice/studienkollegs/studienkollegs.css')) ?: '1' }}">

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
        @php
            // Helpers: savoir si filtre actif
            $isActive = fn($key) => request()->boolean($key);

        @endphp
        @php
            $courseLabels = [
                'T'  => 'T — Engineering & Sciences',
                'M'  => 'M — Medicine & Biology',
                'W'  => 'W — Economics & Social',
                'G'  => 'G — Humanities & Arts',
                'S'  => 'S — Languages',
                'TI' => 'TI — Technical (FH)',
                'WW' => 'WW — Business (FH)',
                'SW' => 'SW — Social Work',
            ];
            $hasAnyFilter = request()->hasAny(['course', 'german_level', 'uni_assist']);
        @endphp

        @php
            // Build filter groups for the att-select pattern
            $filterGroups = [
                [
                    'name'        => 'course',
                    'placeholder' => 'Course (Kurs)',
                    'icon'        => 'ph-book-open',
                    'options'     => collect($allCourses)->mapWithKeys(fn($c) => [$c => $courseLabels[$c] ?? $c])->all(),
                ],
                [
                    'name'        => 'german_level',
                    'placeholder' => 'German Level',
                    'icon'        => 'ph-translate',
                    'options'     => ['B1' => 'B1', 'B2' => 'B2'],
                ],
                [
                    'name'        => 'uni_assist',
                    'placeholder' => 'Uni-Assist',
                    'icon'        => 'ph-graduation-cap',
                    'options'     => ['1' => 'Required', '0' => 'Not required'],
                ],
            ];
        @endphp

        <form method="GET" action="{{ url()->current() }}" class="studienkollegs-filters reveal delay-1" id="skFilters">
            <div class="sk-filters-row">

                {{-- ALL / RESET --}}
                <a href="{{ url()->current() }}"
                    class="sk-filter-reset {{ !$hasAnyFilter ? 'is-active' : '' }}">
                    <i class="ph-duotone ph-funnel"></i>
                    <span>All Filters</span>
                </a>

                @foreach ($filterGroups as $group)
                    @php
                        $current = request($group['name']);
                        $selectedLabel = $current !== null && $current !== '' && isset($group['options'][$current])
                            ? $group['options'][$current]
                            : null;
                        $labelId = 'sk-filter-' . $group['name'] . '-label';
                    @endphp

                    <div class="att-select sk-filter-select {{ $selectedLabel ? 'is-filled' : '' }}" data-att-select>
                        <span id="{{ $labelId }}" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;">{{ $group['placeholder'] }}</span>

                        <select name="{{ $group['name'] }}" class="att-select__native"
                                aria-labelledby="{{ $labelId }}"
                                onchange="document.getElementById('skFilters').submit()">
                            <option value="">{{ $group['placeholder'] }}</option>
                            @foreach ($group['options'] as $value => $label)
                                <option value="{{ $value }}" {{ (string) $current === (string) $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <button type="button" class="att-select__btn" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="{{ $labelId }}">
                            <i class="ph-duotone {{ $group['icon'] }} sk-filter-icon"></i>
                            <span class="att-select__value {{ $selectedLabel ? '' : 'att-select__value--placeholder' }}">
                                {{ $selectedLabel ?? $group['placeholder'] }}
                            </span>
                            <svg class="att-select__chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>

                        <ul class="att-select__menu" role="listbox" tabindex="-1" aria-labelledby="{{ $labelId }}" hidden>
                            <li class="att-select__opt" role="option" data-value="" tabindex="-1"
                                aria-selected="{{ $current === null || $current === '' ? 'true' : 'false' }}">
                                <span class="att-select__dot" aria-hidden="true"></span>
                                <span class="att-select__opt-label">{{ $group['placeholder'] }}</span>
                            </li>
                            @foreach ($group['options'] as $value => $label)
                                <li class="att-select__opt" role="option" data-value="{{ $value }}" tabindex="-1"
                                    aria-selected="{{ (string) $current === (string) $value ? 'true' : 'false' }}">
                                    <span class="att-select__dot" aria-hidden="true"></span>
                                    <span class="att-select__opt-label">{{ $label }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach

            </div>
        </form>

        @if ($featured && $featured->featured)
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
                                <span>Studienkolleg · Featured</span>
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
                                        <div class="featured-meta-label">Tuition</div>
                                    </div>
                                </div>
                            </div>

                            <div class="featured-badge">
                                <span>
                                    <i class="ph-duotone ph-star"></i>
                                    Recommended by GLS
                                </span>
                            </div>

                        </div>

                        @if ($youtubeId)
                            <div class="featured-video">
                                <img src="https://img.youtube.com/vi/{{ $youtubeId }}/hqdefault.jpg"
                                    alt="{{ $featured->name }} video">

                                <button class="video-play-btn"
                                    onclick="this.parentElement.innerHTML = `
                                <iframe
                                    src='https://www.youtube.com/embed/{{ $youtubeId }}?autoplay=1&rel=0'
                                    allow='autoplay; encrypted-media'
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
            <div class="pagination-meta">
                Showing {{ $studienkollegs->firstItem() ?? 0 }} to {{ $studienkollegs->lastItem() ?? 0 }} of
                {{ $studienkollegs->total() }} results
            </div>

            {{ $studienkollegs->onEachSide(1)->links('frontoffice.studienkollegs.partials.pagination') }}
        </div>


    </div>

    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="{{ asset('assets/js/favorites.js') }}"></script>

@endsection
