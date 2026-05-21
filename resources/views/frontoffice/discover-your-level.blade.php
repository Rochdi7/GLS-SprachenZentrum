@extends('frontoffice.layouts.app')

@section('title', __('quiz/level_test.meta.title'))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/quiz/discover.css') }}">

@section('content')

    <section class="hero-section section is-no-image reveal delay-1">
        <div class="container is-hero reveal delay-2">
            <h1 class="hero_title fade-blur-title reveal delay-1">{{ __('quiz/level_test.hero.title') }}</h1>
            <div class="hero_subtitle reveal delay-2">
                {!! __('quiz/level_test.hero.subtitle') !!}
            </div>
        </div>
    </section>

    @php
        $level = $quizLevel ?? strtoupper(request('quiz', '')); // quiz param
        $allowed = ['A1', 'A2', 'B1', 'B2'];
        $level = in_array($level, $allowed, true) ? $level : '';
    @endphp

    <section class="home-courses-section section reveal delay-1">
        <div class="container is-h-courses reveal delay-2">

            <h2 class="h-section-subtitle-courses reveal fade-blur-title delay-1">
                {{ __('quiz/level_test.select.title') }}
            </h2>

            <div class="subtitle reveal delay-2">
                {{ __('quiz/level_test.select.subtitle') }}
            </div>

            <p class="paragraph-2 reveal delay-3">
                {{ __('quiz/level_test.select.text') }}
            </p>

            <div class="courses-cards reveal delay-1">

                {{-- A1 --}}
                <div class="course-card reveal delay-2">
                    <div class="couse-card_level reveal delay-3">
                        <div class="course-card_level-circle reveal delay-1">A</div>
                        <div class="course-card_level-circle reveal delay-2">1</div>
                    </div>
                    <h3 class="course-card_title reveal fade-blur-title delay-1">
                        {{ __('quiz/level_test.quizzes.a1.title') }}
                    </h3>
                    <div class="course-card_text reveal delay-2">
                        {{ __('quiz/level_test.quizzes.a1.text') }}
                    </div>
                    <a href="{{ LaravelLocalization::localizeUrl(route('front.discover-your-level.quiz', ['quiz' => 'A1'])) }}"
                        class="button is-course-card w-button reveal delay-3">
                        {{ __('quiz/level_test.quizzes.cta') }}
                    </a>
                </div>

                {{-- A2 --}}
                <div class="course-card is-green reveal delay-3">
                    <div class="couse-card_level reveal delay-1">
                        <div class="course-card_level-circle reveal delay-2">A</div>
                        <div class="course-card_level-circle reveal delay-3">2</div>
                    </div>
                    <h3 class="course-card_title reveal fade-blur-title delay-1">
                        {{ __('quiz/level_test.quizzes.a2.title') }}
                    </h3>
                    <div class="course-card_text reveal delay-2">
                        {{ __('quiz/level_test.quizzes.a2.text') }}
                    </div>
                    <a href="{{ LaravelLocalization::localizeUrl(route('front.discover-your-level.quiz', ['quiz' => 'A2'])) }}"
                        class="button is-course-card w-button reveal delay-3">
                        {{ __('quiz/level_test.quizzes.cta') }}
                    </a>
                </div>

                {{-- B1 --}}
                <div class="course-card is-purple reveal delay-1">
                    <div class="couse-card_level reveal delay-2">
                        <div class="course-card_level-circle reveal delay-3">B</div>
                        <div class="course-card_level-circle reveal delay-1">1</div>
                    </div>
                    <h3 class="course-card_title reveal fade-blur-title delay-2">
                        {{ __('quiz/level_test.quizzes.b1.title') }}
                    </h3>
                    <div class="course-card_text reveal delay-3">
                        {{ __('quiz/level_test.quizzes.b1.text') }}
                    </div>
                    <a href="{{ LaravelLocalization::localizeUrl(route('front.discover-your-level.quiz', ['quiz' => 'B1'])) }}"
                        class="button is-course-card w-button reveal delay-1">
                        {{ __('quiz/level_test.quizzes.cta') }}
                    </a>
                </div>

                {{-- B2 --}}
                <div class="course-card is-yellow reveal delay-2">
                    <div class="couse-card_level reveal delay-3">
                        <div class="course-card_level-circle reveal delay-1">B</div>
                        <div class="course-card_level-circle reveal delay-2">2</div>
                    </div>
                    <h3 class="course-card_title reveal fade-blur-title delay-1">
                        {{ __('quiz/level_test.quizzes.b2.title') }}
                    </h3>
                    <div class="course-card_text reveal delay-2">
                        {{ __('quiz/level_test.quizzes.b2.text') }}
                    </div>
                    <a href="{{ LaravelLocalization::localizeUrl(route('front.discover-your-level.quiz', ['quiz' => 'B2'])) }}"
                        class="button is-course-card w-button reveal delay-3">
                        {{ __('quiz/level_test.quizzes.cta') }}
                    </a>
                </div>

            </div>

        </div>
    </section>


    <section class="gls-level-section section reveal delay-1">
        <div class="container is-gls-level reveal delay-2">

            <h2 class="gls-level-title reveal fade-blur-title delay-1">
                {{ __('quiz/level_test.gls.title') }}
            </h2>

            <div class="gls-level-subtitle reveal delay-2">
                {!! __('quiz/level_test.gls.subtitle') !!}
            </div>

            @if ($level)
                <p class="gls-level-badge reveal delay-3">
                    {{ __('quiz/level_test.gls.your_level') }} :
                    <span class="gls-level-badge_value">{{ $level }}</span>
                </p>
            @else
                <p class="gls-level-badge reveal delay-3">
                    {{ __('quiz/level_test.gls.pick_hint') }}
                </p>
            @endif

            <div class="gls-level-cards courses-cards reveal delay-1">

                <div class="course-card reveal delay-2 {{ $level === 'A1' ? 'is-selected' : '' }}">
                    <div class="couse-card_level reveal delay-3">
                        <div class="course-card_level-circle reveal delay-1">A</div>
                        <div class="course-card_level-circle reveal delay-2">1</div>
                    </div>
                    <h3 class="course-card_title reveal fade-blur-title delay-1">
                        {!! __('intensive.courses.a1.title') !!}
                    </h3>
                    <div class="course-card_text reveal delay-2">
                        {!! __('quiz/level_test.gls.a1') !!}
                    </div>
                    <a href="{{ route('front.niveaux.a1') }}" class="button is-course-card w-button reveal delay-3">
                        {{ __('quiz/level_test.gls.cta') }}
                    </a>
                </div>

                <div class="course-card is-green reveal delay-3 {{ $level === 'A2' ? 'is-selected' : '' }}">
                    <div class="couse-card_level reveal delay-1">
                        <div class="course-card_level-circle reveal delay-2">A</div>
                        <div class="course-card_level-circle reveal delay-3">2</div>
                    </div>
                    <h3 class="course-card_title reveal fade-blur-title delay-1">
                        {!! __('intensive.courses.a2.title') !!}
                    </h3>
                    <div class="course-card_text reveal delay-2">
                        {!! __('quiz/level_test.gls.a2') !!}
                    </div>
                    <a href="{{ route('front.niveaux.a2') }}" class="button is-course-card w-button reveal delay-3">
                        {{ __('quiz/level_test.gls.cta') }}
                    </a>
                </div>

                <div class="course-card is-purple reveal delay-1 {{ $level === 'B1' ? 'is-selected' : '' }}">
                    <div class="couse-card_level reveal delay-2">
                        <div class="course-card_level-circle reveal delay-3">B</div>
                        <div class="course-card_level-circle reveal delay-1">1</div>
                    </div>
                    <h3 class="course-card_title reveal fade-blur-title delay-2">
                        {!! __('intensive.courses.b1.title') !!}
                    </h3>
                    <div class="course-card_text reveal delay-3">
                        {!! __('quiz/level_test.gls.b1') !!}
                    </div>
                    <a href="{{ route('front.niveaux.b1') }}" class="button is-course-card w-button reveal delay-1">
                        {{ __('quiz/level_test.gls.cta') }}
                    </a>
                </div>

                <div class="course-card is-yellow reveal delay-2 {{ $level === 'B2' ? 'is-selected' : '' }}">
                    <div class="couse-card_level reveal delay-3">
                        <div class="course-card_level-circle reveal delay-1">B</div>
                        <div class="course-card_level-circle reveal delay-2">2</div>
                    </div>
                    <h3 class="course-card_title reveal fade-blur-title delay-1">
                        {!! __('intensive.courses.b2.title') !!}
                    </h3>
                    <div class="course-card_text reveal delay-2">
                        {!! __('quiz/level_test.gls.b2') !!}
                    </div>
                    <a href="{{ route('front.niveaux.b2') }}" class="button is-course-card w-button reveal delay-3">
                        {{ __('quiz/level_test.gls.cta') }}
                    </a>
                </div>

            </div>

        </div>
    </section>


    {{-- =========================
   🟦 SPECIAL GERMAN COURSES (like screenshot)
========================= --}}
    <section class="special-courses-section section reveal delay-1">
        <div class="container is-special-courses reveal delay-2">

            <h2 class="special-courses-title fade-blur-title reveal delay-1">
                {{ __('quiz/level_test.special.title') }}
            </h2>

            <div class="special-courses-subtitle reveal delay-2">
                {{ __('quiz/level_test.special.subtitle') }}
            </div>

            <div class="special-courses-grid reveal delay-3">

                {{-- Card 1: Online --}}
                <div class="special-course-card is-orange reveal delay-1">
                    <h3 class="special-course-card-title fade-blur-title reveal delay-1">
                        {!! __('quiz/level_test.special.cards.online.title') !!}
                    </h3>
                    <p class="special-course-card-text reveal delay-2">
                        {{ __('quiz/level_test.special.cards.online.text') }}
                    </p>
                    <a href="{{ route('front.online-courses') }}"
                        class="button is-special-course-btn w-button reveal delay-3">
                        {{ __('quiz/level_test.special.cards.cta') }}
                    </a>
                </div>

                {{-- Card 2: Intensive Courses --}}
                <div class="special-course-card is-blue reveal delay-2">
                    <h3 class="special-course-card-title fade-blur-title reveal delay-1">
                        {!! __('quiz/level_test.special.cards.intensive.title') !!}
                    </h3>
                    <p class="special-course-card-text reveal delay-2">
                        {{ __('quiz/level_test.special.cards.intensive.text') }}
                    </p>
                    <a href="{{ route('front.intensive-courses') }}"
                        class="button is-special-course-btn w-button reveal delay-3">
                        {{ __('quiz/level_test.special.cards.cta') }}
                    </a>
                </div>

                {{-- Card 3: GLS Exam --}}
                <div class="special-course-card is-yellow reveal delay-3">
                    <h3 class="special-course-card-title fade-blur-title reveal delay-1">
                        {!! __('quiz/level_test.special.cards.gls_exam.title') !!}
                    </h3>
                    <p class="special-course-card-text reveal delay-2">
                        {{ __('quiz/level_test.special.cards.gls_exam.text') }}
                    </p>
                    <a href="{{ route('front.exams.gls') }}"
                        class="button is-special-course-btn w-button reveal delay-3">
                        {{ __('quiz/level_test.special.cards.cta') }}
                    </a>
                </div>

                {{-- Card 4: ÖSD Exam --}}
                <div class="special-course-card is-green reveal delay-1">
                    <h3 class="special-course-card-title fade-blur-title reveal delay-1">
                        {!! __('quiz/level_test.special.cards.osd_exam.title') !!}
                    </h3>
                    <p class="special-course-card-text reveal delay-2">
                        {{ __('quiz/level_test.special.cards.osd_exam.text') }}
                    </p>
                    <a href="{{ route('front.exams.osd') }}"
                        class="button is-special-course-btn w-button reveal delay-3">
                        {{ __('quiz/level_test.special.cards.cta') }}
                    </a>
                </div>

                {{-- Card 5: Goethe Exam --}}
                <div class="special-course-card is-gray reveal delay-2">
                    <h3 class="special-course-card-title fade-blur-title reveal delay-1">
                        {!! __('quiz/level_test.special.cards.goethe_exam.title') !!}
                    </h3>
                    <p class="special-course-card-text reveal delay-2">
                        {{ __('quiz/level_test.special.cards.goethe_exam.text') }}
                    </p>
                    <a href="{{ route('front.exams.goethe') }}"
                        class="button is-special-course-btn w-button reveal delay-3">
                        {{ __('quiz/level_test.special.cards.cta') }}
                    </a>
                </div>

            </div>
        </div>
    </section>


    {{-- =========================
   🟢 CTA SECTION
========================= --}}
    <section class="get-started-section section reveal">
        <div class="container is-2-col-grid reveal delay-1">

            {{-- Image --}}
            <div class="get-started-image reveal delay-2">
                <img src="{{ asset('assets/images/about/ctagls.jpg') }}" alt="Étudiants souriant au GLS Sprachenzentrum"
                    class="full-image rounded-4" loading="lazy">
            </div>

            {{-- Content --}}
            <div class="get-started-card reveal delay-3">
                <div class="box-rich-text w-richtext">
                    <h2 class="fade-blur-title">Commencez dès aujourd’hui !</h2>
                    <h3 class="fade-blur-title">Rejoignez l’aventure allemande avec GLS Maroc</h3>
                    <p>
                        Lancez votre apprentissage de l’allemand au <strong>GLS Sprachenzentrum</strong>.
                        Nos cours intensifs et en ligne sont ouverts à tous les niveaux — du A1 au B2.
                    </p>
                    <p>
                        Visitez l’un de nos centres à
                        <strong>Marrakech, Rabat, Kénitra, Salé, Casablanca</strong> ou <strong>Agadir</strong>,
                        et découvrez la méthode la plus efficace pour apprendre l’allemand au Maroc.
                    </p>
                    <p>
                        Notre équipe est là pour vous guider étape par étape — vers vos objectifs linguistiques et
                        professionnels.
                    </p>
                </div>

                <a href="{{ route('front.intensive-courses') }}" class="button w-button">En savoir plus</a>
            </div>
        </div>
    </section>

    <!-- ============================
                                                 CONTACT SECTION
                                            ============================= -->
    <section class="contact-section section reveal delay-1">
        <div class="container is-2-col-grid reveal delay-2">

            <div class="div-block-5-copy reveal delay-1">

                <h2 class="h-section-subtitle-contact fade-blur-title reveal delay-1">
                    {!! __('quiz/level_test.contact.title') !!}
                </h2>

                <div class="div-block-21">
                    <a href="tel:+212669515019" class="link-block reveal delay-1">
                        <div class="text-block-3">
                            <span class="text-span">{{ __('quiz/level_test.contact.call') }}<br></span>
                            +212 6 69 51 50 19
                        </div>
                    </a>

                    <a href="mailto:info@gls-sprachzentrum.ma" class="link-block-2 reveal delay-2">
                        <div class="text-block-3">
                            <span class="text-span">{{ __('quiz/level_test.contact.email') }}<br></span>
                            info@gls-sprachzentrum.ma
                        </div>
                    </a>
                </div>

                <div class="text-block-3 visit-block reveal delay-3">
                    <span class="text-span">{{ __('quiz/level_test.contact.visit') }}<br></span>
                    {!! __('quiz/level_test.contact.addresses') !!}
                </div>

                <div class="footer-socials-block reveal delay-1">
                    <div class="text-block-3">
                        <span class="text-span">{{ __('quiz/level_test.contact.follow') }}</span>
                    </div>
                    <div class="div-block-20">
                        <a href="https://www.instagram.com/gls.sprachenzentrum/" class="footer-social-link ig"
                            target="_blank" rel="noopener noreferrer" aria-label="GLS Sprachenzentrum sur Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>

                        <a href="https://www.facebook.com/gls.sale/" class="footer-social-link fb" target="_blank"
                            rel="noopener noreferrer" aria-label="GLS Sprachenzentrum sur Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>

                        <a href="https://www.youtube.com/@9onsolsTalks" class="footer-social-link yt" target="_blank"
                            rel="noopener noreferrer" aria-label="GLS Sprachenzentrum sur YouTube">
                            <i class="bi bi-youtube"></i>
                        </a>

                        <a href="https://api.whatsapp.com/send/?phone=0669515019&text&type=phone_number&app_absent=0"
                            class="footer-social-link wa" target="_blank" rel="noopener noreferrer"
                            aria-label="Contacter GLS Sprachenzentrum sur WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>

                        <a href="https://www.tiktok.com/@gls.sprachenzentrum?is_from_webapp=1&sender_device=pc"
                            class="footer-social-link tt" target="_blank" rel="noopener noreferrer"
                            aria-label="GLS Sprachenzentrum sur TikTok">
                            <i class="bi bi-tiktok"></i>
                        </a>

                    </div>
                </div>
            </div>

            <a href="https://maps.app.goo.gl/g4PjrPB7wHQAqrSZA" target="_blank" class="div-block-7 reveal delay-2">

                <iframe src="{{ __('quiz/level_test.contact.map_url') }}" loading="lazy" allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>

            </a>

        </div>
    </section>

@endsection
