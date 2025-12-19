@extends('frontoffice.layouts.app')

@section('title', 'Studienkolleg der FU Berlin')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/studienkollegs/studienkolleg-show.css') }}">

@section('content')

{{-- =========================
   HERO
========================= --}}
<section class="studienkolleg-hero">
    <img src="{{ asset('assets/images/studienkollegs/12.webp') }}" alt="Studienkolleg der FU Berlin">

    <div class="hero-overlay">
        <h1>Studienkolleg der FU Berlin</h1>

        <div class="hero-actions">
            <a href="https://www.uni-assist.de/apply/" target="_blank" class="btn-primary">
                Apply now
            </a>

            <button class="btn-outline">
                <i class="ph ph-heart"></i>
                Add to Favorites
            </button>
        </div>
    </div>
</section>

{{-- =========================
   HEADER
========================= --}}
<section class="studienkolleg-header">
    <div class="container">

        <nav class="studienkolleg-breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span>›</span>
            <a href="{{ route('front.studienkollegs') }}">Studienkollegs</a>
            <span>›</span>
            <strong>Studienkolleg der FU Berlin</strong>
        </nav>

        <h2 class="studienkolleg-title">
            Studienkolleg der FU Berlin
        </h2>

        <div class="studienkolleg-location">
            <i class="ph ph-map-pin"></i>
            Berlin, Germany
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
                        <span>Application method</span>
                        <strong>Via Uni-Assist (mandatory)</strong>
                    </div>

                    <div class="info-row">
                        <span>Language of instruction</span>
                        <strong>German</strong>
                    </div>

                    <div class="info-row">
                        <span>Entrance Exam</span>
                        <strong>Required (German & Mathematics)</strong>
                    </div>

                    <div class="info-highlight">
                        <i class="ph ph-check-circle"></i>
                        Uni-Assist application required
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
                            <th>Semester</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Notes</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Winter Semester (WS)</td>
                            <td>01.06</td>
                            <td>15.07</td>
                            <td>Only intake period</td>
                        </tr>
                        <tr>
                            <td>Summer Semester (SS)</td>
                            <td colspan="2">—</td>
                            <td>Not available</td>
                        </tr>
                        </tbody>
                    </table>

                    <div class="info-highlight warning">
                        <i class="ph ph-info"></i>
                        Admission possible only for Winter Semester (WS)
                    </div>
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
                                <span>Minimum B2 (recognized certificate required)</span>
                            </div>
                        </div>
                    </div>

                    {{-- Program --}}
                    <div class="accordion-item">
                        <button class="accordion-header" type="button">
                            <span>Program-specific requirements</span>
                            <i class="ph ph-caret-down"></i>
                        </button>

                        <div class="accordion-content">
                            <p>
                                Placement into T, W, M or G course depends on your
                                previous academic background and entrance exam results.
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

                    <ul class="document-list">
                        <li>School leaving certificate (certified copy)</li>
                        <li>Transcript of records</li>
                        <li>German language certificate (B2)</li>
                        <li>Passport copy</li>
                        <li>CV (recommended)</li>
                    </ul>
                </div>

                {{-- Certification --}}
                <div class="info-card">
                    <h3>
                        <i class="ph ph-certificate"></i>
                        Certification & Translation
                    </h3>

                    <p>
                        <strong>Certification:</strong> Required (official copies)
                    </p>
                    <p>
                        <strong>Translation:</strong>
                        Required if documents are not in German or English
                    </p>
                </div>

                {{-- Contact --}}
                <div class="info-card">
                    <h3>
                        <i class="ph ph-map-pin"></i>
                        Contact & Location
                    </h3>

                    <p>
                        <strong>Email:</strong>
                        studienkolleg@fu-berlin.de
                    </p>

                    <p>
                        <strong>Address:</strong>
                        Malteserstraße 74–100, 12249 Berlin, Germany
                    </p>

                    <p>
                        <strong>Official website:</strong><br>
                        <a href="https://www.fu-berlin.de/en/studium/international/studienkolleg" target="_blank">
                            www.fu-berlin.de/studienkolleg
                        </a>
                    </p>

                    <div class="map-box">
                        <iframe
                            src="https://www.openstreetmap.org/export/embed.html?bbox=13.354%2C52.427%2C13.374%2C52.437&layer=mapnik"
                            loading="lazy">
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

                {{-- Entry Exam --}}
                <div class="sidebar-card sidebar-card-icon">
                    <div class="sidebar-icon">
                        <i class="ph ph-clipboard-text"></i>
                    </div>

                    <h4>Entrance Exam</h4>

                    <div class="exam-card">
                        <i class="ph ph-book-open"></i>
                        <div class="exam-title">
                            German & Mathematics
                        </div>

                        <a href="https://www.fu-berlin.de/en/studium/international/studienkolleg/aufnahmepruefung.html"
                           target="_blank"
                           class="exam-link">
                            Details <i class="ph ph-arrow-up-right"></i>
                        </a>
                    </div>
                </div>

                {{-- Uni-Assist --}}
                <div class="sidebar-card sidebar-card-icon">
                    <div class="sidebar-icon">
                        <i class="ph ph-envelope"></i>
                    </div>

                    <h4>Application Portal</h4>

                    <p class="sidebar-text">
                        Applications are submitted via Uni-Assist:
                    </p>

                    <a href="https://www.uni-assist.de/apply/" target="_blank" class="btn-gerassist">
                        Start application now
                    </a>
                </div>

            </aside>

        </div>
    </div>
</section>

{{-- Icons --}}
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const items = document.querySelectorAll('.accordion-item');

    items.forEach(item => {
        const header = item.querySelector('.accordion-header');
        header.addEventListener('click', () => {
            items.forEach(i => i !== item && i.classList.remove('active'));
            item.classList.toggle('active');
        });
    });
});
</script>

@endsection
