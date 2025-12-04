@extends('frontoffice.layouts.app')

@section('title', __('student-stories.meta.title'))
@section('meta_description', __('student-stories.meta.description'))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/ressource/student-stories.css') }}">

@section('content')
<main class="blog-page">

    {{-- ===========================
         STUDENT STORIES HERO
    ============================ --}}
    <section class="hero-section section blog-hero-section student-hero reveal delay-1">
        <div class="container reveal delay-2">
            <div class="blog-hero-inner reveal delay-3">

                <div class="blog-hero-badge fade-blur-title reveal delay-1">
                    {{ __('student-stories.hero.badge') }}
                </div>

                <h1 class="blog-hero-title fade-blur-title reveal delay-2">
                    {{ __('student-stories.hero.title') }}
                </h1>

                <p class="blog-hero-subtitle reveal delay-3">
                    {{ __('student-stories.hero.subtitle') }}
                </p>

                <div class="blog-hero-meta reveal delay-1">
                    <span>{{ __('student-stories.hero.meta') }}</span>
                </div>

            </div>
        </div>
    </section>



    {{-- ===========================
         STUDENT STORY GRID
    ============================ --}}
    <section class="section blog-list-section reveal delay-1">
        <div class="container reveal delay-2">

            <div class="blog-list-header text-center mb-5 reveal delay-3">
                <h2 class="h-section-subtitle blog-list-title fade-blur-title reveal delay-1">
                    {{ __('student-stories.list.title') }}
                </h2>

                <p class="blog-list-subtitle reveal delay-2">
                    {{ __('student-stories.list.subtitle') }}
                </p>
            </div>

            <div class="row g-4">

                @php
                    $students = [
                        ['image' => 'student1.webp', 'name' => 'Amine EL amrani', 'path' => 'Ausbildung in Nursing – Stuttgart'],
                        ['image' => 'student2.webp', 'name' => 'Omar Bennis', 'path' => 'Contract Work in IT – Berlin'],
                        ['image' => 'student3.webp', 'name' => 'Fatima Zahra', 'path' => 'Studienkolleg Prep – Düsseldorf'],
                        ['image' => 'student4.webp', 'name' => 'malak Lahlou', 'path' => 'Ausbildung in Hospitality – Hamburg'],
                        ['image' => 'student5.webp', 'name' => 'hatim Abkari', 'path' => 'Student Visa – Universität Köln'],
                        ['image' => 'student6.webp', 'name' => 'ikram Idrissi', 'path' => 'Contract Job – Mechanical Sector, Munich'],
                    ];
                @endphp

                @foreach($students as $index => $student)
                    <div class="col-md-6 col-lg-4 reveal delay-{{ ($index % 3) + 1 }}">
                        <div class="story-card reveal delay-{{ ($index % 3) + 1 }}">

                            <div class="story-image-wrapper reveal delay-1">
                                <img src="{{ asset('assets/images/student-stories/' . $student['image']) }}"
                                     alt="{{ $student['name'] }}"
                                     class="story-image">
                            </div>

                            <div class="story-body reveal delay-2">
                                <div>
                                    <h3 class="story-name fade-blur-title reveal delay-2">{{ $student['name'] }}</h3>
                                    <p class="story-path reveal delay-3">{{ $student['path'] }}</p>
                                </div>

                                <div class="story-badge reveal delay-1">
                                    {{ __('student-stories.badge') }}
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach

            </div>

        </div>
    </section>

</main>
@endsection
