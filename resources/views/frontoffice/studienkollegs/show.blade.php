@extends('frontoffice.layouts.app')

@section('title', $studienkolleg->meta_title ?? $studienkolleg->name . ' | Studienkolleg in Germany')
@section('description', $studienkolleg->meta_description ?? 'Detailed information about ' . $studienkolleg->name)

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/studienkollegs/studienkolleg-show.css') }}">

@section('content')

    <style>
        .favorite-btn.active i {
            color: #ef4444;
        }
    </style>
    @php
        $hero = $studienkolleg->getFirstMediaUrl('studienkolleg_hero');

        // ---------- SAFE NORMALIZERS ----------
        $normalizeToArray = function ($value) {
            if (is_array($value)) {
                return $value;
            }

            if (is_string($value)) {
                $trim = trim($value);

                // If JSON string -> decode
                if ($trim !== '' && (str_starts_with($trim, '[') || str_starts_with($trim, '{'))) {
                    $decoded = json_decode($trim, true);
                    return is_array($decoded) ? $decoded : [];
                }

                // If plain string -> treat as empty (or single item for list fields)
                return [];
            }

            // null / other types
            return [];
        };

        // Deadlines: expected associative array: { "Winter": {"start":..., ...}, ... }
        $deadlines = $normalizeToArray($studienkolleg->deadlines ?? []);

        // Requirements: expected array of items. Each item can be array {title, content}
        $requirements = $normalizeToArray($studienkolleg->requirements ?? []);

        // Documents: expected array of strings
        $documents = $normalizeToArray($studienkolleg->documents ?? []);

        // Courses: expected array of strings
        $courses = $normalizeToArray($studienkolleg->courses ?? []);

        // Convert single string cases if DB contains newline-separated lists (optional safe)
        if (empty($documents) && is_string($studienkolleg->documents ?? null)) {
            $lines = preg_split("/\r\n|\n|\r/", trim($studienkolleg->documents));
            $documents = array_values(array_filter(array_map('trim', $lines)));
        }

        if (empty($courses) && is_string($studienkolleg->courses ?? null)) {
            $lines = preg_split("/\r\n|\n|\r/", trim($studienkolleg->courses));
            $courses = array_values(array_filter(array_map('trim', $lines)));
        }

        // Requirements: if DB contains newline text, create 1 item
        if (
            empty($requirements) &&
            is_string($studienkolleg->requirements ?? null) &&
            trim($studienkolleg->requirements) !== ''
        ) {
            $requirements = [['title' => 'Requirements', 'content' => $studienkolleg->requirements]];
        }
    @endphp

    {{-- =========================
HERO
========================= --}}
    <section class="studienkolleg-hero reveal">
        <img src="{{ $hero }}" alt="{{ $studienkolleg->name }}">

        <div class="hero-overlay">
            <h1 class="fade-blur-title delay-2">
                {{ $studienkolleg->name }}
            </h1>

            <div class="hero-actions reveal delay-3">
                @if ($studienkolleg->application_url)
                    <a href="{{ $studienkolleg->application_url }}" target="_blank" class="btn-primary">
                        Apply now
                    </a>
                @endif

                <button type="button" class="btn-outline favorite-btn" data-id="{{ $studienkolleg->id }}"
                    aria-pressed="false">
                    <i class="ph-duotone ph-heart"></i>
                    <span data-fav-label data-text="Add to Favorites" data-active-text="Added">
                        Add to Favorites
                    </span>
                </button>


            </div>
        </div>
    </section>

    {{-- =========================
HEADER
========================= --}}
    <section class="studienkolleg-header">
        <div class="container">

            <nav class="studienkolleg-breadcrumb reveal delay-1">
                <a href="{{ route('front.home') }}">Home</a>
                <span>›</span>
                <a href="{{ route('front.studienkollegs') }}">Studienkollegs</a>
                <span>›</span>
                <strong>{{ $studienkolleg->name }}</strong>
            </nav>

            <h2 class="studienkolleg-title fade-blur-title delay-2">
                {{ $studienkolleg->name }}
            </h2>

            <div class="studienkolleg-location reveal delay-3">
                <i class="ph ph-map-pin"></i>
                {{ $studienkolleg->city }}, {{ $studienkolleg->country }}
            </div>

        </div>
    </section>

    {{-- =========================
CONTENT
========================= --}}
    <section class="studienkolleg-content">
        <div class="container">
            <div class="content-grid">

                {{-- LEFT COLUMN --}}
                <div class="content-main">

                    {{-- APPLICATION PROCESS --}}
                    <div class="info-card reveal delay-1">
                        <h3><i class="ph ph-graduation-cap"></i> Application Process & Selection</h3>

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
                                    {{ $studienkolleg->language_of_instruction }}
                                @endif
                            </strong>
                        </div>


                        <div class="info-row">
                            <span>Entrance Exam</span>
                            <strong>
                                {{ $studienkolleg->entrance_exam ? 'Required' : 'Not required' }}
                                @if ($studienkolleg->exam_subjects)
                                    ({{ $studienkolleg->exam_subjects }})
                                @endif
                            </strong>
                        </div>

                        @if ($studienkolleg->uni_assist)
                            <div class="info-highlight">
                                <i class="ph ph-check-circle"></i>
                                Uni-Assist application required
                            </div>
                        @endif
                    </div>

                    {{-- DEADLINES --}}
                    <div class="info-card reveal delay-2">
                        <h3><i class="ph ph-calendar-check"></i> Application Deadlines</h3>

                        <table class="info-table">
                            <thead>
                                <tr>
                                    <th>Semester</th>
                                    <th>Deadline Range</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deadlines as $d)
                                    @php
                                        // if $d accidentally comes as string, normalize
                                        if (is_string($d)) {
                                            $decoded = json_decode($d, true);
                                            $d = is_array($decoded) ? $decoded : [];
                                        }
                                        if (!is_array($d)) {
                                            $d = [];
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $d['semester'] ?? '—' }}</td>
                                        <td>{{ !empty($d['range']) ? $d['range'] : '—' }}</td>
                                        <td>{{ !empty($d['note']) ? $d['note'] : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No deadlines available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- REQUIREMENTS --}}
                    @if (!empty($requirements))
                        <div class="info-card reveal delay-3">
                            <h3><i class="ph ph-list-checks"></i> Admission Requirements</h3>
                            <ul class="document-list">
                                @foreach ($requirements as $req)
                                    @php
                                        // normalize each requirement item
                                        if (is_string($req)) {
                                            $decoded = json_decode($req, true);
                                            if (is_array($decoded)) {
                                                $req = $decoded;
                                            } else {
                                                $req = ['title' => '', 'content' => $req];
                                            }
                                        }
                                        if (!is_array($req)) {
                                            $req = [];
                                        }

                                        // Format output: "Title: Content" or just content if title missing
                                        $reqTitle = $req['title'] ?? '';
                                        $reqContent = $req['content'] ?? '';
                                        $output =
                                            !empty($reqTitle) && !empty($reqContent)
                                                ? $reqTitle . ': ' . $reqContent
                                                : ($reqTitle ?:
                                                $reqContent);
                                    @endphp
                                    <li>{{ $output }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="info-card reveal delay-3">
                            <h3><i class="ph ph-list-checks"></i> Admission Requirements</h3>
                            <p>No requirements available</p>
                        </div>
                    @endif

                    {{-- DOCUMENTS --}}
                    @if (!empty($documents))
                        <div class="info-card reveal delay-4">
                            <h3><i class="ph ph-file-text"></i> Application Documents</h3>
                            <ul class="document-list">
                                @foreach ($documents as $doc)
                                    <li>{{ is_string($doc) ? $doc : '' }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- CERTIFICATION --}}
                    <div class="info-card reveal delay-4">
                        <h3><i class="ph ph-certificate"></i> Certification & Translation</h3>
                        <p><strong>Certification:</strong>
                            {{ $studienkolleg->certification_required ? 'Required' : 'Not required' }}
                        </p>
                        <p><strong>Translation:</strong>
                            {{ $studienkolleg->translation_required ? 'Required' : 'Not required' }}
                        </p>
                        @if ($studienkolleg->translation_note)
                            <p>{{ $studienkolleg->translation_note }}</p>
                        @endif
                    </div>

                    {{-- CONTACT --}}
                    <div class="info-card reveal delay-5">
                        <h3><i class="ph ph-map-pin"></i> Contact & Location</h3>

                        @if ($studienkolleg->contact_email)
                            <p><strong>Email:</strong> {{ $studienkolleg->contact_email }}</p>
                        @endif

                        @if ($studienkolleg->address)
                            <p><strong>Address:</strong> {{ $studienkolleg->address }}</p>
                        @endif

                        @if ($studienkolleg->official_website)
                            <a href="{{ $studienkolleg->official_website }}" target="_blank">
                                {{ $studienkolleg->official_website }}
                            </a>
                        @endif

                        @if ($studienkolleg->map_embed)
                            <div class="map-box">
                                {!! \App\Support\HtmlSanitizer::mapEmbed($studienkolleg->map_embed) !!}
                            </div>
                        @endif
                    </div>

                </div>

                {{-- RIGHT SIDEBAR --}}
                <aside class="content-sidebar">

                    @if (!empty($courses))
                        <div class="sidebar-card reveal delay-2">
                            <h4><i class="ph ph-books"></i> Course Types</h4>
                            <div class="course-grid">
                                @foreach ($courses as $course)
                                    <div class="course-item">{{ is_string($course) ? $course : '' }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($studienkolleg->entrance_exam)
                        <div class="sidebar-card sidebar-card-icon reveal delay-3">
                            <div class="sidebar-icon"><i class="ph ph-clipboard-text"></i></div>
                            <h4>Entrance Exam</h4>
                            <div class="exam-card">
                                <i class="ph ph-book-open"></i>
                                <div class="exam-title">{{ $studienkolleg->exam_subjects }}</div>

                                @if ($studienkolleg->exam_link)
                                    <a href="{{ $studienkolleg->exam_link }}" target="_blank" class="exam-link">
                                        Details <i class="ph ph-arrow-up-right"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($studienkolleg->application_url)
                        <div class="sidebar-card sidebar-card-icon reveal delay-4">
                            <div class="sidebar-icon"><i class="ph ph-envelope"></i></div>
                            <h4>Application Portal</h4>
                            <p class="sidebar-text">{{ $studienkolleg->application_portal_note }}</p>
                            <a href="{{ $studienkolleg->application_url }}" target="_blank" class="btn-gerassist">
                                Start application now
                            </a>
                        </div>
                    @endif

                </aside>

            </div>
        </div>
    </section>

    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="{{ asset('assets/js/favorites.js') }}"></script>

@endsection
