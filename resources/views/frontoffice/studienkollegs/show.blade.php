@extends('frontoffice.layouts.app')

@section('title', 'Studienkolleg der FU Berlin')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/studienkollegs/studienkolleg-show.css') }}">

@section('content')

    {{-- =========================
   HERO
========================= --}}
    <section class="studienkolleg-hero">
        <img src="{{ asset('assets/images/studienkollegs/12.webp') }}" alt="Studienkolleg FU Berlin">

        <div class="hero-overlay">
            <h1>Studienkolleg der FU Berlin</h1>

            <div class="hero-actions">
                <a href="#" class="btn-primary">Apply now</a>

                <button class="btn-outline">
                    <i class="ph ph-heart"></i>
                    Add to Favorites
                </button>
            </div>
        </div>
    </section>

    {{-- =========================
   HEADER (UNDER HERO – SEPARATE SECTION)
========================= --}}
    <section class="studienkolleg-header">
        <div class="container">

            {{-- Breadcrumb --}}
            <nav class="studienkolleg-breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span>›</span>
                <a href="{{ route('front.studienkollegs') }}">Studienkollegs</a>
                <span>›</span>
                <strong>Studienkolleg der FU Berlin</strong>
            </nav>

            {{-- Title --}}
            <h2 class="studienkolleg-title">
                Studienkolleg der FU Berlin
            </h2>

            {{-- Location --}}
            <div class="studienkolleg-location">
                <i class="ph ph-map-pin"></i>
                Berlin, Berlin, Germany
            </div>

        </div>
    </section>


    {{-- =========================
   CONTENT
========================= --}}
    <section class="studienkolleg-content">
        <div class="container">
            <div class="content-grid">

                {{-- =========================
               LEFT COLUMN
            ========================= --}}
                <div class="content-main">

                    {{-- Application --}}
                    <div class="info-card">
                        <h3>
                            <i class="ph ph-graduation-cap"></i>
                            Application Process & Selection
                        </h3>

                        <div class="info-row">
                            <span>Application</span>
                            <strong>Via Uni-Assist</strong>
                        </div>

                        <div class="info-row">
                            <span>Language of instruction</span>
                            <strong>German</strong>
                        </div>

                        <div class="info-highlight">
                            <i class="ph ph-check-circle"></i>
                            GerAssist Application possible: <strong>Yes</strong>
                        </div>
                    </div>

                    {{-- Deadlines --}}
                    <div class="info-card">
                        <h3>
                            <i class="ph ph-calendar-check"></i>
                            Application Deadlines
                        </h3>

                        <table class="info-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Winter (WS)</th>
                                    <th>Summer (SS)</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Start</td>
                                    <td>01.06</td>
                                    <td>Only Winter Semester</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>End</td>
                                    <td>15.07</td>
                                    <td>Only Winter Semester</td>
                                    <td>-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Admission --}}
                    <div class="info-card">
                        <h3>
                            <i class="ph ph-list-checks"></i>
                            Admission Requirements
                        </h3>

                        {{-- Language --}}
                        <div class="accordion-item">

                            <button class="accordion-header" type="button">
                                <span>Language Requirements</span>
                                <i class="ph ph-caret-down"></i>
                            </button>

                            <div class="accordion-content">
                                <div class="accordion-row">
                                    <strong>German:</strong>
                                    <span>Minimum B2 – See Language Certificates</span>
                                </div>

                                <div class="accordion-row">
                                    <strong>English:</strong>
                                    <span>Minimum B1 – See Language Certificates</span>
                                </div>
                            </div>

                        </div>

                        {{-- Program --}}
                        <div class="accordion-item">

                            <button class="accordion-header" type="button">
                                <span>Program-specific admission requirements</span>
                                <i class="ph ph-caret-down"></i>
                            </button>

                            <div class="accordion-content">
                                <p>
                                    Additional requirements depend on the chosen course
                                    (T, M, W, or G course).
                                </p>
                            </div>

                        </div>
                    </div>

                    {{-- Documents --}}
                    <div class="info-card">
                        <h3>
                            <i class="ph ph-file-text"></i>
                            Application Documents
                        </h3>

                        <p class="text-muted">
                            No required documents provided yet.
                        </p>
                    </div>

                    {{-- Certification --}}
                    <div class="info-card">
                        <h3>
                            <i class="ph ph-certificate"></i>
                            Certification and Translation
                        </h3>

                        <p>
                            <strong>Certification:</strong> No
                        </p>
                        <p>
                            <strong>Translation:</strong>
                            Yes, if documents are not in German or English.
                        </p>
                    </div>

                    {{-- Contact --}}
                    <div class="info-card">
                        <h3>
                            <i class="ph ph-map-pin"></i>
                            Contact and Location
                        </h3>

                        <p>
                            <strong>Email:</strong>
                            studienkolleg@fu-berlin.de
                        </p>

                        <p>
                            <strong>Address:</strong>
                            Malteserstraße 74–100, Berlin
                        </p>

                        <div class="map-box">
                            <iframe src="https://www.openstreetmap.org/export/embed.html" loading="lazy">
                            </iframe>
                        </div>
                    </div>

                </div>

                {{-- =========================
               RIGHT SIDEBAR
            ========================= --}}
                <aside class="content-sidebar">

                    {{-- Course Types --}}
                    <div class="sidebar-card">
                        <h4>
                            <i class="ph ph-books"></i>
                            Course Types
                        </h4>

                        <div class="course-grid">
                            <div class="course-item">T Course</div>
                            <div class="course-item">W Course</div>
                            <div class="course-item">M Course</div>
                            <div class="course-item">G Course</div>
                        </div>
                    </div>

                    {{-- Entry Exam Examples --}}
                    <div class="sidebar-card sidebar-card-icon">

                        <div class="sidebar-icon">
                            <i class="ph ph-clipboard-text"></i>
                        </div>

                        <h4>Entry Exam Examples</h4>

                        <div class="exam-card">
                            <i class="ph ph-book-open"></i>

                            <div class="exam-title">
                                Sample entrance test
                            </div>

                            <a href="#" class="exam-link">
                                Open <i class="ph ph-arrow-up-right"></i>
                            </a>
                        </div>

                    </div>

                    {{-- Postal Address / GerAssist --}}
                    <div class="sidebar-card sidebar-card-icon">

                        <div class="sidebar-icon">
                            <i class="ph ph-envelope"></i>
                        </div>

                        <h4>Postal Address</h4>

                        <p class="sidebar-text">
                            Apply online via GerAssist:
                        </p>

                        <a href="#" class="btn-gerassist">
                            Start application now
                        </a>

                    </div>

                </aside>

            </div>
        </div>
    </section>

    {{-- Phosphor Icons (load once globally ideally) --}}
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const items = document.querySelectorAll('.accordion-item');

            items.forEach(item => {
                const header = item.querySelector('.accordion-header');

                header.addEventListener('click', () => {

                    // close others
                    items.forEach(i => {
                        if (i !== item) i.classList.remove('active');
                    });

                    // toggle current
                    item.classList.toggle('active');
                });
            });

        });
    </script>

@endsection
