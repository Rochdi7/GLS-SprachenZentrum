@extends('frontoffice.layouts.app')

@section('title', 'Our Centers | GLS Sprachenzentrum Morocco')
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/oursites/sites.css') }}">
{{-- Bootstrap CSS (CDN) --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
{{-- Bootstrap Icons --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

@section('content')

    <!-- =========================================================
             HERO SECTION – Inspired by die deutSCHule “Format D” page
    ========================================================= -->
    <section class="hero-section section reveal delay-1">
        <div class="container is-hero reveal delay-2">

            <div class="hero_subtitle reveal delay-1">OUR LANGUAGE CENTERS IN MOROCCO</div>

            <h1 class="hero_title fade-blur-title reveal delay-2">
                Learn German with GLS – Across Morocco
            </h1>

            <div class="hero-image reveal delay-3">
                <img src="{{ asset('assets/images/oursites/hero.jpeg') }}" loading="lazy" alt="GLS Centers in Morocco"
                    class="full-image reveal delay-1">
            </div>

        </div>
    </section>

    {{-- ================================
 LOCATIONS – Static Map + Auto Switch
================================ --}}
    <section class="locations-carousel-section py-5 reveal delay-1">
        <div class="container reveal delay-2">
            <div class="row align-items-stretch g-5">

                {{-- LEFT STATIC MAP --}}
                <div class="col-lg-5 d-flex align-items-center justify-content-center reveal delay-1">
                    <div class="map-container position-relative w-100 h-100 reveal delay-2">
                        <img src="{{ asset('assets/images/contact/reception.jpg') }}" alt="GLS Morocco Centers Map"
                            class="img-fluid rounded-4 shadow location-image w-100 h-100 object-fit-cover reveal delay-3"
                            loading="lazy">
                    </div>
                </div>

                {{-- RIGHT AUTO SWITCHING CARDS --}}
                <div class="col-lg-7 d-flex flex-column justify-content-center reveal delay-2">

                    <h2 class="fw-bold mb-4 location-title fade-blur-title reveal delay-1">
                        Our Centers in Morocco
                    </h2>

                    <div id="locationsContainer" class="position-relative reveal delay-2">

                        {{-- SLIDE 1 --}}
                        <div class="location-slide active reveal delay-3">

                            <div class="location-card reveal delay-1">
                                <h5 class="fade-blur-title reveal delay-2">
                                    <a href="https://www.google.com/maps/place/GLS+Sprachzentrum+-+Centre+GLS+de+langue+Allemande+Casablanca/"
                                        target="_blank">GLS Casablanca</a>
                                </h5>
                                <p class="reveal delay-3">14 Bd de Paris, 1er étage N°8, Casablanca 20000</p>
                                <a href="https://wa.me/212808549717" class="phone reveal delay-1">
                                    <i class="bi bi-telephone-fill me-2"></i>+212 80-85 497 17
                                </a>
                            </div>

                            <div class="location-card reveal delay-2">
                                <h5 class="fade-blur-title reveal delay-2">
                                    <a href="https://www.google.com/maps/place/GLS+Sprachenzentrum+-+Centre+de+langue+Allemande+Marrakech/"
                                        target="_blank">GLS Marrakech</a>
                                </h5>
                                <p class="reveal delay-3">Avenue Yacoub El Mansour, Immeuble Espace Guéliz, 3ème étage
                                    Bureau 28</p>
                                <a href="https://wa.me/212808663983" class="phone reveal delay-1">
                                    <i class="bi bi-telephone-fill me-2"></i>+212 80-86 639 83
                                </a>
                            </div>

                            <div class="location-card reveal delay-3">
                                <h5 class="fade-blur-title reveal delay-2">
                                    <a href="https://www.google.com/maps/place/GLS+Sprachenzentrum+-+Centre+GLS+de+langue+Allemande+K%C3%A9nitra/"
                                        target="_blank">GLS Rabat</a>
                                </h5>
                                <p class="reveal delay-3">Avenue Fal Ould Oumeir, Immeuble 77, 1er étage N°1, Agdal, Rabat
                                </p>
                                <a href="https://wa.me/212808573509" class="phone reveal delay-1">
                                    <i class="bi bi-telephone-fill me-2"></i>+212 80-85 735 09
                                </a>
                            </div>

                        </div>

                        {{-- SLIDE 2 --}}
                        <div class="location-slide reveal delay-3">

                            <div class="location-card reveal delay-1">
                                <h5 class="fade-blur-title reveal delay-2">
                                    <a href="https://www.google.com/maps/place/GLS+Sprachenzentrum+-+Centre+GLS+de+langue+Allemand+K%C3%A9nitra/"
                                        target="_blank">GLS Kénitra</a>
                                </h5>
                                <p class="reveal delay-3">Avenue Mohammed V, Bureaux Rania, 7ème étage, Kénitra</p>
                                <a href="https://wa.me/212808651450" class="phone reveal delay-1">
                                    <i class="bi bi-telephone-fill me-2"></i>+212 80-86 514 50
                                </a>
                            </div>

                            <div class="location-card reveal delay-2">
                                <h5 class="fade-blur-title reveal delay-2">
                                    <a href="https://www.google.com/maps/place//data=!4m2!3m1!1s0xda76b254ea656d5:0xaf2f9258ee6fba89"
                                        target="_blank">GLS Salé</a>
                                </h5>
                                <p class="reveal delay-3">Avenue Mohamed V Rue Halima N°12 Diyar, Salé</p>
                                <a href="https://wa.me/212808540625" class="phone reveal delay-1">
                                    <i class="bi bi-telephone-fill me-2"></i>+212 80-85 40 625
                                </a>
                            </div>

                            <div class="location-card reveal delay-3">
                                <h5 class="fade-blur-title reveal delay-2">
                                    <a href="https://www.google.com/search?q=GLS+Sprachenzentrum+-+Centre+GLS+de+langue+Allemande+Agadir"
                                        target="_blank">GLS Agadir</a>
                                </h5>
                                <p class="reveal delay-3">Av. Massoude Al Wafkaoui, Place des taxis, Hay Essalam, Agadir</p>
                                <a href="https://wa.me/212606484051" class="phone reveal delay-1">
                                    <i class="bi bi-telephone-fill me-2"></i>+212 606-48 40 51
                                </a>
                            </div>

                        </div>

                    </div>

                    {{-- Dots indicator --}}
                    <div class="dots-wrapper text-start mt-4 reveal delay-1">
                        <span class="dot active reveal delay-2"></span>
                        <span class="dot reveal delay-3"></span>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- ===============================
         INLINE CTA SECTION
    ================================ -->
    <section class="inline-cta-section section my-5 reveal delay-1">
        <div class="container reveal delay-2">
            <div class="inline-cta-block mx-auto reveal delay-3">
                <h2 class="heading-4 overlay-text fade-blur-title reveal delay-1">
                    Get ready! Sign up now!
                </h2>
                <a href="{{ url('/booking-request') }}" class="button is-big is-white w-button reveal delay-2">
                    Enroll Online
                </a>
            </div>
        </div>
    </section>


    {{-- Bootstrap JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    {{-- Auto Switch Logic --}}
    <script>
        const slides = document.querySelectorAll('.location-slide');
        const dots = document.querySelectorAll('.dot');
        let currentIndex = 0;

        // Animate cards on load
        function animateCards(slide) {
            const cards = slide.querySelectorAll('.location-card');
            cards.forEach((card, i) => {
                card.style.opacity = 0;
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.opacity = 1;
                    card.style.transform = 'translateY(0)';
                }, i * 400);
            });
        }

        function showSlide(index) {
            slides.forEach((slide, i) => {
                if (i === index) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                    slide.style.opacity = 0;
                    slide.style.visibility = 'hidden';
                    slide.style.display = 'none';
                }
                dots[i].classList.toggle('active', i === index);
            });
            slides[index].style.display = 'block';
            slides[index].style.visibility = 'visible';
            slides[index].style.opacity = 1;
            animateCards(slides[index]);
        }


        // Initial animation on page load
        animateCards(slides[0]);

        // Auto switch every 3s
        setInterval(() => {
            currentIndex = (currentIndex + 1) % slides.length;
            showSlide(currentIndex);
        }, 3000);
    </script>

@endsection
