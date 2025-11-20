@extends('frontoffice.layouts.app')

@section('title', 'Student Stories – GLS Sprachenzentrum')
@section('meta_description', 'Discover how students from Morocco made it to Germany with GLS support – Ausbildung, Studienkolleg, work contracts, and more.')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/ressource/student-stories.css') }}">

@section('content')
<main class="blog-page">

    {{-- ===========================
         STUDENT STORIES HERO
    ============================ --}}
    <section class="hero-section section blog-hero-section student-hero">
        <div class="container">
            <div class="blog-hero-inner">
                <div class="blog-hero-badge">Student Stories</div>
                <h1 class="blog-hero-title">From Morocco to Germany</h1>
                <p class="blog-hero-subtitle">
                    Real success stories of students who started their German journey with GLS — Ausbildung, studies, and career paths made possible.
                </p>
                <div class="blog-hero-meta">
                    <span>Ausbildung · Studienkolleg · Student Visa · Job Placement</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ===========================
         STUDENT STORY GRID
    ============================ --}}
    <section class="section blog-list-section">
        <div class="container">
            <div class="blog-list-header text-center mb-5">
                <h2 class="h-section-subtitle blog-list-title">Meet Our Students</h2>
                <p class="blog-list-subtitle">
                    Each started at GLS. Today, they're building a life in Germany.
                </p>
            </div>

            <div class="row g-4">
                @php
                    $students = [
                        ['image' => 'student1.webp', 'name' => 'Sara El Amrani', 'path' => 'Ausbildung in Nursing – Stuttgart'],
                        ['image' => 'student2.webp', 'name' => 'Omar Bennis', 'path' => 'Contract Work in IT – Berlin'],
                        ['image' => 'student3.webp', 'name' => 'Fatima Zahra', 'path' => 'Studienkolleg Prep – Düsseldorf'],
                        ['image' => 'student4.webp', 'name' => 'Yassine Lahlou', 'path' => 'Ausbildung in Hospitality – Hamburg'],
                        ['image' => 'student5.webp', 'name' => 'Imane Abkari', 'path' => 'Student Visa – Universität Köln'],
                        ['image' => 'student6.webp', 'name' => 'Hicham Idrissi', 'path' => 'Contract Job – Mechanical Sector, Munich'],
                    ];
                @endphp

                @foreach($students as $student)
                    <div class="col-md-6 col-lg-4">
                        <div class="story-card">
                            <div class="story-image-wrapper">
                                <img src="{{ asset('assets/images/student-stories/' . $student['image']) }}"
                                     alt="{{ $student['name'] }}"
                                     class="story-image">
                            </div>
                            <div class="story-body">
                                <div>
                                    <h3 class="story-name">{{ $student['name'] }}</h3>
                                    <p class="story-path">{{ $student['path'] }}</p>
                                </div>
                                <div class="story-badge">GLS Journey</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</main>
@endsection
