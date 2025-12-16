@extends('frontoffice.layouts.app')

@section('title', __('contact.meta.title'))

{{-- Bootstrap CSS --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/contact/contact.css') }}">

@section('content')

{{-- ================================
     CONTACT HERO SECTION
================================ --}}
<section class="hero-section section is-no-image reveal delay-1">
    <div class="container is-hero reveal delay-2">
        <h1 class="hero_title fade-blur-title reveal delay-1">{{ __('contact.hero.title') }}</h1>
        <div class="hero_subtitle reveal delay-2">
            {!! __('contact.hero.subtitle') !!}
        </div>
    </div>
</section>

<section class="locations-carousel-section py-5 reveal delay-1">
    <div class="container reveal delay-2">
        <div class="row align-items-stretch g-5">

            {{-- LEFT STATIC MAP --}}
            <div class="col-lg-5 d-flex align-items-center justify-content-center reveal delay-1">
                <div class="map-container position-relative w-100 h-100 reveal delay-2">
                    <img src="{{ asset('assets/images/contact/reception.jpg') }}"
                         alt="{{ __('contact.locations.map_alt') }}"
                         class="img-fluid rounded-4 shadow location-image w-100 h-100 object-fit-cover reveal delay-3"
                         loading="lazy">
                </div>
            </div>

            {{-- RIGHT AUTO SWITCHING CARDS --}}
            <div class="col-lg-7 d-flex flex-column justify-content-center reveal delay-2">

                <h2 class="fw-bold mb-4 location-title fade-blur-title reveal delay-1">
                    {{ __('contact.locations.title') }}
                </h2>

                <div id="locationsContainer" class="position-relative">

                    <div class="location-slide active">
                        @foreach (__('contact.locations.slide1') as $center)
                            <div class="location-card reveal delay-1">
                                <h5 class="location-name fade-blur-title reveal delay-1">{{ $center['name'] }}</h5>

                                <p class="location-address reveal delay-2">
                                    <a href="{{ $center['map'] }}" target="_blank">
                                        <i class="bi bi-geo-alt-fill map-icon"></i>
                                        {{ $center['address'] }}
                                    </a>
                                </p>

                                <a href="{{ $center['phone_link'] }}" class="phone reveal delay-3">
                                    <i class="bi bi-telephone-fill me-2"></i>{{ $center['phone'] }}
                                </a>
                            </div>
                        @endforeach
                    </div>

                    {{-- SLIDE 2 --}}
                    <div class="location-slide">
                        @foreach (__('contact.locations.slide2') as $center)
                            <div class="location-card reveal delay-1">
                                <h5 class="location-name fade-blur-title reveal delay-1">{{ $center['name'] }}</h5>

                                <p class="location-address reveal delay-2">
                                    <a href="{{ $center['map'] }}" target="_blank">
                                        <i class="bi bi-geo-alt-fill map-icon"></i>
                                        {{ $center['address'] }}
                                    </a>
                                </p>

                                <a href="{{ $center['phone_link'] }}" class="phone reveal delay-3">
                                    <i class="bi bi-telephone-fill me-2"></i>{{ $center['phone'] }}
                                </a>
                            </div>
                        @endforeach
                    </div>

                </div>

                <div class="dots-wrapper text-start mt-4">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- ================================
     CONTACT FORM SECTION
================================ --}}
<section id="contact-form" class="contact-form-section section py-5 reveal delay-1">
    <div class="container py-4 reveal delay-2">

        <div class="text-center mb-5 reveal delay-1">
            <h2 class="fw-bold fade-blur-title reveal delay-1">{{ __('contact.form.title') }}</h2>
            <p class="text-muted reveal delay-2">{{ __('contact.form.subtitle') }}</p>
        </div>

        {{-- ALERTES --}}
        @if(session('success'))
            <div class="alert alert-success text-center fw-semibold rounded-3 mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger text-center fw-semibold rounded-3 mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger rounded-3 mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li class="fw-semibold">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ LaravelLocalization::localizeUrl(route('front.contact.post')) }}" 
              method="POST" 
              class="mx-auto reveal delay-3" 
              style="max-width: 700px;">

            @csrf
            <div class="row g-4">

                <div class="col-md-6 reveal delay-1">
                    <label class="form-label fw-semibold">{{ __('contact.form.name') }}</label>
                    <input type="text" name="name" class="form-control rounded-3 p-3" required>
                </div>

                <div class="col-md-6 reveal delay-2">
                    <label class="form-label fw-semibold">{{ __('contact.form.email') }}</label>
                    <input type="email" name="email" class="form-control rounded-3 p-3" required>
                </div>

                <div class="col-12 reveal delay-1">
                    <label class="form-label fw-semibold">{{ __('contact.form.subject') }}</label>
                    <input type="text" name="subject" class="form-control rounded-3 p-3" required>
                </div>

                <div class="col-12 reveal delay-2">
                    <label class="form-label fw-semibold">{{ __('contact.form.message') }}</label>
                    <textarea name="message" rows="5" class="form-control rounded-3 p-3" required></textarea>
                </div>

                <div class="col-12 text-center reveal delay-3">
                    <button type="submit" class="btn btn-success px-5 py-3 rounded-pill fw-semibold">
                        {{ __('contact.form.button') }}
                    </button>
                </div>

            </div>
        </form>
    </div>
</section>


{{-- ================================
     CONSULTATION CTA
================================ --}}
<section class="consultation-section text-center position-relative reveal delay-1">
    <div class="consultation-gradient"></div>

    <div class="container position-relative z-2 py-5 reveal delay-2">
        <h2 class="fw-bold mb-4 text-white fade-blur-title reveal delay-1">
            {{ __('contact.consultation.title') }}
        </h2>

        <p class="text-white-50 mb-5 mx-auto reveal delay-2" style="max-width: 720px;">
            {!! __('contact.consultation.text') !!}
        </p>

        <a href="{{ route('front.contact') }}" class="btn btn-light px-4 py-3 rounded-pill fw-semibold reveal delay-3">
            {{ __('contact.consultation.button') }}
        </a>
    </div>
</section>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Auto Switch --}}
<script>
    const slides = document.querySelectorAll('.location-slide');
    const dots = document.querySelectorAll('.dot');
    let currentIndex = 0;

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
            slide.classList.toggle('active', i === index);
            slide.style.display = i === index ? 'block' : 'none';

            dots[i].classList.toggle('active', i === index);
        });

        animateCards(slides[index]);
    }

    animateCards(slides[0]);

    setInterval(() => {
        currentIndex = (currentIndex + 1) % slides.length;
        showSlide(currentIndex);
    }, 3000);
</script>

@endsection
