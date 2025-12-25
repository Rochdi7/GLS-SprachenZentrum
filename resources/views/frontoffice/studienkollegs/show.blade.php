@extends('frontoffice.layouts.app')

@section('title', $studienkolleg->meta_title ?? $studienkolleg->name . ' | Studienkolleg in Germany')
@section('description', $studienkolleg->meta_description ?? 'Detailed information about ' . $studienkolleg->name)

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/studienkollegs/studienkolleg-show.css') }}">

@section('content')

    {{-- =========================
HERO
========================= --}}
    @php
        $hero = $studienkolleg->getFirstMediaUrl('studienkolleg_hero');
    @endphp

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

                <button class="btn-outline favorite-btn" data-id="{{ $studienkolleg->id }}">
                    <i class="ph ph-heart"></i>
                    Add to Favorites
                </button>
                <a href="https://www.uni-assist.de/apply/" target="_blank" class="btn-primary">
                    Apply now
                </a>
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
                            <strong>{{ $studienkolleg->language_of_instruction }}</strong>
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
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($studienkolleg->deadlines ?? [] as $semester => $d)
                                    <tr>
                                        <td>{{ $semester }}</td>
                                        <td>{{ $d['start'] ?? '—' }}</td>
                                        <td>{{ $d['end'] ?? '—' }}</td>
                                        <td>{{ $d['note'] ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No deadlines available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- REQUIREMENTS --}}
                    @if (!empty($studienkolleg->requirements))
                        <div class="info-card reveal delay-3">
                            <h3><i class="ph ph-list-checks"></i> Admission Requirements</h3>

                            @foreach ($studienkolleg->requirements as $req)
                                <div class="accordion-item">
                                    <button class="accordion-header">
                                        <span>{{ $req['title'] }}</span>
                                        <i class="ph ph-caret-down"></i>
                                    </button>
                                    <div class="accordion-content">
                                        {!! nl2br(e($req['content'])) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- DOCUMENTS --}}
                    @if (!empty($studienkolleg->documents))
                        <div class="info-card reveal delay-4">
                            <h3><i class="ph ph-file-text"></i> Application Documents</h3>
                            <ul class="document-list">
                                @foreach ($studienkolleg->documents as $doc)
                                    <li>{{ $doc }}</li>
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
                                {!! $studienkolleg->map_embed !!}
                            </div>
                        @endif
                    </div>

                </div>

                {{-- RIGHT SIDEBAR --}}
                <aside class="content-sidebar">

                    @if (!empty($studienkolleg->courses))
                        <div class="sidebar-card reveal delay-2">
                            <h4><i class="ph ph-books"></i> Course Types</h4>
                            <div class="course-grid">
                                @foreach ($studienkolleg->courses as $course)
                                    <div class="course-item">{{ $course }}</div>
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

    {{-- Accordion --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.accordion-item').forEach(item => {
                item.querySelector('.accordion-header').addEventListener('click', () => {
                    document.querySelectorAll('.accordion-item').forEach(i => i !== item && i
                        .classList.remove('active'));
                    item.classList.toggle('active');
                });
            });
        });
    </script>

@endsection
