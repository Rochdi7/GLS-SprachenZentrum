@extends('frontoffice.layouts.app')

@section('title', $studienkolleg->meta_title ?? $studienkolleg->name . ' | Studienkolleg in Germany')
@section('description', $studienkolleg->meta_description ?? 'Detailed information about ' . $studienkolleg->name)

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/studienkollegs/studienkolleg-show.css') }}">

@section('content')

@php
    $hero = $studienkolleg->getFirstMediaUrl('studienkolleg_hero');

    $normalizeToArray = function ($value) {
        if (is_array($value)) return $value;
        if (is_string($value)) {
            $trim = trim($value);
            if ($trim !== '' && (str_starts_with($trim, '[') || str_starts_with($trim, '{'))) {
                $decoded = json_decode($trim, true);
                return is_array($decoded) ? $decoded : [];
            }
            return [];
        }
        return [];
    };

    $deadlines    = $normalizeToArray($studienkolleg->deadlines ?? []);
    $requirements = $normalizeToArray($studienkolleg->requirements ?? []);
    $documents    = $normalizeToArray($studienkolleg->documents ?? []);
    $courses      = $normalizeToArray($studienkolleg->courses ?? []);

    if (empty($documents) && is_string($studienkolleg->documents ?? null)) {
        $lines = preg_split("/\r\n|\n|\r/", trim($studienkolleg->documents));
        $documents = array_values(array_filter(array_map('trim', $lines)));
    }
    if (empty($courses) && is_string($studienkolleg->courses ?? null)) {
        $lines = preg_split("/\r\n|\n|\r/", trim($studienkolleg->courses));
        $courses = array_values(array_filter(array_map('trim', $lines)));
    }
    if (empty($requirements) && is_string($studienkolleg->requirements ?? null) && trim($studienkolleg->requirements) !== '') {
        $requirements = [['title' => 'Requirements', 'content' => $studienkolleg->requirements]];
    }

    // Find the next upcoming deadline (best-effort parse)
    $nextDeadline = null;
    $nextDeadlineDate = null;
    foreach ($deadlines as $d) {
        if (is_string($d)) { $dec = json_decode($d, true); $d = is_array($dec) ? $dec : []; }
        if (!is_array($d) || empty($d['range'])) continue;

        // pull last date in range as the actual deadline
        if (preg_match_all('/(\d{1,2})[\s.\-\/]+([A-Za-zéûûô]+|\d{1,2})[\s.\-\/]+(\d{4})/u', $d['range'], $matches, PREG_SET_ORDER)) {
            $last = end($matches);
            try {
                $dt = \Carbon\Carbon::parse($last[0]);
                if ($dt->isFuture() && (!$nextDeadlineDate || $dt->lt($nextDeadlineDate))) {
                    $nextDeadline = $d;
                    $nextDeadlineDate = $dt;
                }
            } catch (\Throwable $e) { /* ignore */ }
        }
    }
    $daysLeft = $nextDeadlineDate ? max(0, (int) now()->diffInDays($nextDeadlineDate, false)) : null;
@endphp

{{-- ── HERO ─────────────────────────────── --}}
<section class="studienkolleg-hero">
    @if($hero)
        <img src="{{ $hero }}" alt="{{ $studienkolleg->name }}">
    @endif

    <div class="hero-overlay">
        <span class="hero-eyebrow">
            <i class="bi bi-bookmark-star"></i>
            Studienkolleg · {{ $studienkolleg->city }}
        </span>

        <h1>{{ $studienkolleg->name }}</h1>

        <div class="hero-specs">
            <span class="hero-spec"><i class="bi bi-geo-alt"></i> {{ $studienkolleg->city }}, {{ $studienkolleg->country }}</span>
            @if($studienkolleg->public !== null)
                <span class="hero-spec {{ $studienkolleg->public ? 'is-success' : '' }}">
                    <i class="bi {{ $studienkolleg->public ? 'bi-bank' : 'bi-building' }}"></i>
                    {{ $studienkolleg->public ? 'Public' : 'Private' }}
                </span>
            @endif
            @if($studienkolleg->language_of_instruction)
                <span class="hero-spec"><i class="bi bi-translate"></i> {{ $studienkolleg->language_of_instruction }}</span>
            @endif
            @if($studienkolleg->duration_semesters)
                <span class="hero-spec"><i class="bi bi-clock"></i> {{ $studienkolleg->duration_semesters }} {{ Str::plural('semester', $studienkolleg->duration_semesters) }}</span>
            @endif
            @if($studienkolleg->entrance_exam)
                <span class="hero-spec is-warn"><i class="bi bi-clipboard-check"></i> Entrance exam</span>
            @endif
            @if($studienkolleg->uni_assist)
                <span class="hero-spec is-success"><i class="bi bi-shield-check"></i> Uni-Assist</span>
            @endif
        </div>

        <div class="hero-actions">
            @if ($studienkolleg->application_url)
                <a href="{{ $studienkolleg->application_url }}" target="_blank" rel="noopener" class="btn-primary">
                    <i class="bi bi-arrow-up-right"></i> Apply now
                </a>
            @endif
            <button type="button" class="btn-outline favorite-btn" data-id="{{ $studienkolleg->id }}" aria-pressed="false">
                <i class="ph-duotone ph-heart"></i>
                <span data-fav-label data-text="Add to Favorites" data-active-text="Saved">Add to Favorites</span>
            </button>
        </div>
    </div>
</section>

{{-- ── STICKY STRIP : breadcrumb + anchor nav ── --}}
<div class="sk-strip">
    <div class="container">
        <nav class="studienkolleg-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('front.home') }}">Home</a>
            <span>›</span>
            <a href="{{ route('front.studienkollegs') }}">Studienkollegs</a>
            <span>›</span>
            <strong>{{ $studienkolleg->name }}</strong>
        </nav>
        <nav class="sk-anchor-nav" id="skAnchorNav" aria-label="On this page">
            <a href="#sk-overview">Overview</a>
            <a href="#sk-deadlines">Deadlines</a>
            <a href="#sk-requirements">Requirements</a>
            <a href="#sk-documents">Documents</a>
            <a href="#sk-contact">Contact</a>
        </nav>
    </div>
</div>

{{-- ── CONTENT ─────────────────────────────── --}}
<section class="studienkolleg-content">
    <div class="container">
        <div class="content-grid">

            {{-- LEFT --}}
            <div class="content-main">

                {{-- OVERVIEW --}}
                <article class="info-card" id="sk-overview">
                    <h3><i class="bi bi-mortarboard-fill"></i> Application Process &amp; Selection</h3>

                    @if ($studienkolleg->application_method)
                        <div class="info-row">
                            <span>Application method</span>
                            <strong>{{ $studienkolleg->application_method }}</strong>
                        </div>
                    @endif

                    <div class="info-row">
                        <span>Language of instruction</span>
                        <strong>
                            @if (!empty($studienkolleg->languages) && is_array($studienkolleg->languages))
                                {{ implode(', ', $studienkolleg->languages) }}
                            @else
                                {{ $studienkolleg->language_of_instruction ?? '—' }}
                            @endif
                        </strong>
                    </div>

                    <div class="info-row">
                        <span>Entrance exam</span>
                        <strong>
                            {{ $studienkolleg->entrance_exam ? 'Required' : 'Not required' }}
                            @if ($studienkolleg->exam_subjects) ({{ $studienkolleg->exam_subjects }}) @endif
                        </strong>
                    </div>

                    @if($studienkolleg->tuition !== null)
                        <div class="info-row">
                            <span>Tuition</span>
                            <strong>{{ $studienkolleg->tuition ?: 'Free' }}</strong>
                        </div>
                    @endif

                    @if ($studienkolleg->uni_assist)
                        <div class="info-highlight">
                            <i class="bi bi-shield-check"></i>
                            Uni-Assist application required
                        </div>
                    @endif
                </article>

                {{-- DEADLINES --}}
                <article class="info-card" id="sk-deadlines">
                    <h3><i class="bi bi-calendar-check"></i> Application Deadlines</h3>

                    @if(empty($deadlines))
                        <p class="text-muted mb-0">No deadlines available.</p>
                    @else
                        <div class="sk-deadlines">
                            @foreach($deadlines as $d)
                                @php
                                    if (is_string($d)) { $dec = json_decode($d, true); $d = is_array($dec) ? $dec : []; }
                                    if (!is_array($d)) $d = [];

                                    $sem  = $d['semester'] ?? '—';
                                    $rng  = $d['range']    ?? '—';
                                    $note = $d['note']     ?? '';

                                    $isNext = $nextDeadline && $nextDeadline === $d;
                                @endphp
                                <div class="sk-deadline">
                                    <div class="sk-deadline-semester">
                                        <span class="sem">{{ $sem }}</span>
                                        <span class="sem-sub">Semester</span>
                                    </div>
                                    <div class="sk-deadline-detail">
                                        <span class="range">{{ $rng }}</span>
                                        @if($note)<span class="note">{{ $note }}</span>@endif
                                    </div>
                                    @if($isNext && $daysLeft !== null)
                                        <span class="sk-deadline-badge {{ $daysLeft <= 14 ? 'is-urgent' : ($daysLeft <= 60 ? 'is-soon' : '') }}">
                                            <span class="num">{{ $daysLeft }}</span>
                                            days left
                                        </span>
                                    @else
                                        <span class="sk-deadline-badge is-passed">Upcoming</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>

                {{-- REQUIREMENTS --}}
                <article class="info-card" id="sk-requirements">
                    <h3><i class="bi bi-list-check"></i> Admission Requirements</h3>
                    @if(empty($requirements))
                        <p class="text-muted mb-0">No requirements listed.</p>
                    @else
                        <ul class="sk-checklist">
                            @foreach ($requirements as $req)
                                @php
                                    if (is_string($req)) {
                                        $dec = json_decode($req, true);
                                        $req = is_array($dec) ? $dec : ['title' => '', 'content' => $req];
                                    }
                                    if (!is_array($req)) $req = [];
                                    $t = $req['title'] ?? ''; $c = $req['content'] ?? '';
                                    $output = (!empty($t) && !empty($c)) ? $t . ': ' . $c : ($t ?: $c);
                                @endphp
                                <li>{{ $output }}</li>
                            @endforeach
                        </ul>
                    @endif
                </article>

                {{-- DOCUMENTS --}}
                <article class="info-card" id="sk-documents">
                    <h3><i class="bi bi-file-earmark-text"></i> Application Documents</h3>
                    @if(empty($documents))
                        <p class="text-muted mb-0">No documents listed.</p>
                    @else
                        <ul class="sk-checklist">
                            @foreach ($documents as $doc)
                                <li>{{ is_string($doc) ? $doc : '' }}</li>
                            @endforeach
                        </ul>
                    @endif
                </article>

                {{-- CERT & TRANSLATION --}}
                <article class="info-card">
                    <h3><i class="bi bi-patch-check"></i> Certification &amp; Translation</h3>
                    <div class="info-row">
                        <span>Certification</span>
                        <strong>{{ $studienkolleg->certification_required ? 'Required' : 'Not required' }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Translation</span>
                        <strong>{{ $studienkolleg->translation_required ? 'Required' : 'Not required' }}</strong>
                    </div>
                    @if ($studienkolleg->translation_note)
                        <div class="info-highlight" style="background:rgba(245,180,10,.07);border-color:rgba(245,180,10,.25);color:#8a6d00;">
                            <i class="bi bi-info-circle-fill" style="color:var(--sk-gold-bright);"></i>
                            {{ $studienkolleg->translation_note }}
                        </div>
                    @endif
                </article>

                {{-- CONTACT --}}
                <article class="info-card" id="sk-contact">
                    <h3><i class="bi bi-geo-alt-fill"></i> Contact &amp; Location</h3>
                    <div class="sk-contact">
                        <div class="sk-contact-list">
                            @if ($studienkolleg->contact_email)
                                <div class="sk-contact-row">
                                    <span class="ico"><i class="bi bi-envelope"></i></span>
                                    <div>
                                        <span class="lab">Email</span>
                                        <span class="val"><a href="mailto:{{ $studienkolleg->contact_email }}">{{ $studienkolleg->contact_email }}</a></span>
                                    </div>
                                </div>
                            @endif
                            @if ($studienkolleg->address)
                                <div class="sk-contact-row">
                                    <span class="ico"><i class="bi bi-geo-alt"></i></span>
                                    <div>
                                        <span class="lab">Address</span>
                                        <span class="val">{{ $studienkolleg->address }}</span>
                                    </div>
                                </div>
                            @endif
                            @if ($studienkolleg->official_website)
                                <div class="sk-contact-row">
                                    <span class="ico"><i class="bi bi-globe"></i></span>
                                    <div class="sk-website" style="min-width:0;flex:1;">
                                        <span class="lab">Official website</span>
                                        <span class="val sk-website-hidden" data-url="{{ $studienkolleg->official_website }}">
                                            <button type="button" class="sk-website-btn">
                                                <i class="bi bi-eye"></i> Reveal link
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if ($studienkolleg->map_embed)
                            <div class="map-box">
                                {!! \App\Support\HtmlSanitizer::mapEmbed($studienkolleg->map_embed) !!}
                            </div>
                        @endif
                    </div>
                </article>

            </div>

            {{-- SIDEBAR --}}
            <aside class="sk-sidebar">

                {{-- APPLY CARD --}}
                <div class="sk-apply-card">
                    <h4>Apply at {{ $studienkolleg->name }}</h4>
                    <p>{{ $studienkolleg->application_portal_note ?: 'Submit your application directly via the official portal of the Studienkolleg.' }}</p>

                    @if($nextDeadline && $daysLeft !== null)
                        <div class="next-deadline">
                            <div>
                                <span class="lab">Next deadline</span>
                                <span class="val">{{ $nextDeadline['semester'] ?? '—' }} · {{ $nextDeadline['range'] ?? '' }}</span>
                            </div>
                            <div class="countdown">
                                <div class="num">{{ $daysLeft }}</div>
                                <div class="lab">days left</div>
                            </div>
                        </div>
                    @endif

                    @if ($studienkolleg->application_url)
                        <a href="{{ $studienkolleg->application_url }}" target="_blank" rel="noopener" class="btn-apply">
                            Start application <i class="bi bi-arrow-up-right"></i>
                        </a>
                    @endif

                    <div class="small-actions">
                        @if($studienkolleg->official_website)
                            <a href="{{ $studienkolleg->official_website }}" target="_blank" rel="noopener">Website</a>
                        @endif
                        @if($studienkolleg->contact_email)
                            <a href="mailto:{{ $studienkolleg->contact_email }}">Email</a>
                        @endif
                    </div>
                </div>

                {{-- COURSES --}}
                @if (!empty($courses))
                    <div class="sidebar-card">
                        <h4><i class="bi bi-book"></i> Course Types</h4>
                        <div class="sk-courses">
                            @foreach ($courses as $course)
                                <span class="sk-course-chip">{{ is_string($course) ? $course : '' }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ENTRANCE EXAM --}}
                @if ($studienkolleg->entrance_exam)
                    <div class="sidebar-card">
                        <h4><i class="bi bi-clipboard-check"></i> Entrance Exam</h4>
                        <div class="sk-exam">
                            <i class="bi bi-journal-text"></i>
                            <div class="title">{{ $studienkolleg->exam_subjects ?: 'Required' }}</div>
                            @if ($studienkolleg->exam_link)
                                <a href="{{ $studienkolleg->exam_link }}" target="_blank" rel="noopener">
                                    More details <i class="bi bi-arrow-up-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- HELP CROSS-LINK --}}
                <div class="sk-help-card">
                    <div class="help-eyebrow">
                        <i class="bi bi-life-preserver"></i>
                        Need help with your file?
                    </div>
                    <h5>GLS handles your translations &amp; attestations</h5>
                    <p>Many Studienkollegs require certified Morocco–Germany translations. We take care of it.</p>
                    <div class="sk-help-links">
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.translations.track')) }}">
                            <span><i class="bi bi-translate"></i> Track my translations</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.attestation-request.create')) }}">
                            <span><i class="bi bi-file-earmark-medical"></i> Request an attestation</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.certificate.check')) }}">
                            <span><i class="bi bi-patch-check"></i> Verify a GLS certificate</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

            </aside>

        </div>
    </div>
</section>

{{-- MOBILE FIXED APPLY BAR --}}
@if ($studienkolleg->application_url)
    <div class="sk-mobile-bar">
        <div class="label">
            <strong>{{ $studienkolleg->name }}</strong>
            <small>
                @if($daysLeft !== null) {{ $daysLeft }} days until next deadline
                @else Apply directly to the official portal @endif
            </small>
        </div>
        <a href="{{ $studienkolleg->application_url }}" target="_blank" rel="noopener" class="btn-apply">Apply</a>
    </div>
@endif

<script>
(function () {
    // Reveal-on-click for the official website
    document.querySelectorAll('.sk-website-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const wrap = this.closest('.sk-website-hidden');
            if (!wrap) return;
            const url = wrap.dataset.url;
            wrap.innerHTML = '<a href="' + url + '" target="_blank" rel="noopener">' + url + '</a>';
        });
    });
})();
(function () {
    // Anchor nav highlight on scroll
    const links = document.querySelectorAll('#skAnchorNav a');
    const sections = Array.from(links).map(a => document.querySelector(a.getAttribute('href'))).filter(Boolean);
    if (!('IntersectionObserver' in window) || !sections.length) return;
    const map = new Map();
    sections.forEach((s, i) => map.set(s, links[i]));
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            links.forEach(l => l.classList.remove('is-active'));
            const a = map.get(e.target);
            if (a) a.classList.add('is-active');
        });
    }, { rootMargin: '-40% 0px -50% 0px', threshold: 0 });
    sections.forEach(s => io.observe(s));
})();
</script>

<script src="https://unpkg.com/@phosphor-icons/web"></script>
<script src="{{ asset('assets/js/favorites.js') }}"></script>

@endsection
