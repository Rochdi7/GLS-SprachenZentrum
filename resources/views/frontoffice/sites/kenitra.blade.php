@extends('frontoffice.layouts.app')
@section('title', 'GLS Kénitra | Centre de langue allemande')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/sites/marrakech.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/9onsol.css') }}">

@section('content')

<!-- ===========================
     HERO SECTION – KÉNITRA
=========================== -->
<section class="hero-section section about-hero reveal delay-1">
    <div class="container is-hero reveal delay-2">

        <div class="hero_subtitle reveal delay-1">{{ __('sites/kenitra.hero.subtitle') }}</div>

        <h1 class="hero_title fade-blur-title reveal delay-2">
            {{ __('sites/kenitra.hero.title') }}
        </h1>

        <div class="hero-image reveal delay-3">
            <img 
                src="{{ asset('assets/images/sites/kenitra/centre-kenitra.webp') }}"
                alt="GLS Sprachenzentrum Kénitra"
                class="full-image reveal delay-1"
                loading="lazy">
        </div>
    </div>
</section>



<!-- ===========================
     ABOUT KENITRA CENTER
=========================== -->
<section class="gls-section gls-richtext-wrapper reveal delay-1">
    <div class="gls-container reveal delay-2">
        <div class="gls-richtext reveal delay-3">

            <h2 class="fade-blur-title reveal delay-1">{{ __('sites/kenitra.about.title1') }}</h2>
            <h3 class="fade-blur-title reveal delay-2">{{ __('sites/kenitra.about.subtitle1') }}</h3>

            <p class="reveal delay-1">{!! __('sites/kenitra.about.p1') !!}</p>
            <p class="reveal delay-2">{!! __('sites/kenitra.about.p2') !!}</p>

            <h2 class="fade-blur-title reveal delay-1">{{ __('sites/kenitra.about.title2') }}</h2>
            <h3 class="fade-blur-title reveal delay-2">{{ __('sites/kenitra.about.subtitle2') }}</h3>

            <p class="reveal delay-1">{{ __('sites/kenitra.about.text_list') }}</p>

            <ul>
                <li class="reveal delay-1"><strong>{{ __('sites/kenitra.about.offers.1') }}</strong></li>
                <li class="reveal delay-2"><strong>{{ __('sites/kenitra.about.offers.2') }}</strong></li>
                <li class="reveal delay-3"><strong>{{ __('sites/kenitra.about.offers.3') }}</strong></li>
                <li class="reveal delay-1"><strong>{{ __('sites/kenitra.about.offers.4') }}</strong></li>
                <li class="reveal delay-2"><strong>{{ __('sites/kenitra.about.offers.5') }}</strong></li>
            </ul>

            <p class="reveal delay-3">{!! __('sites/kenitra.about.p3') !!}</p>

        </div>
    </div>
</section>



<!-- ===========================
     PHOTO STRIP – KENITRA
=========================== -->
<section class="gls-photo-strip section reveal delay-1">
    <div class="gls-container gls-photo-grid reveal delay-2">

        <img src="{{ asset('assets/images/sites/kenitra/centre-kenitra1.webp') }}" alt="GLS Kénitra Students" class="reveal delay-1">
        <img src="{{ asset('assets/images/sites/kenitra/centre-kenitra2.webp') }}" alt="GLS Kénitra Classroom" class="reveal delay-2">
        <img src="{{ asset('assets/images/sites/kenitra/centre-kenitra3.webp') }}" alt="GLS Kénitra Activities" class="reveal delay-3">

    </div>
</section>



<!-- ===========================
     INFO CARDS
=========================== -->
<section class="gls-info-section gls-section reveal delay-1">

    <div class="gls-container reveal delay-2">

        <h2 class="gls-info-title fade-blur-title reveal delay-3">
            {{ __('sites/kenitra.info.title') }}
        </h2>

        <div class="gls-niveau-tabs reveal delay-1">
            <button class="gls-niveau-btn active reveal delay-1" data-level="A1">A1</button>
            <button class="gls-niveau-btn reveal delay-2" data-level="A2">A2</button>
            <button class="gls-niveau-btn reveal delay-3" data-level="B1">B1</button>
            <button class="gls-niveau-btn reveal delay-1" data-level="B2">B2</button>
        </div>

        <div class="gls-info-grid reveal delay-2">

            <div class="gls-info-card reveal delay-1">
                <div class="gls-info-icon reveal delay-2">@include('frontoffice.svg.sites-info')</div>
                <h3 class="gls-info-card-title fade-blur-title reveal delay-3">{{ __('sites/kenitra.info.certification') }}</h3>
                <div class="gls-info-text reveal delay-1" id="graduation-text"></div>
            </div>

            <div class="gls-info-card reveal delay-2">
                <div class="gls-info-icon reveal delay-3">@include('frontoffice.svg.sites-duration')</div>
                <h3 class="gls-info-card-title fade-blur-title reveal delay-1">{{ __('sites/kenitra.info.duration') }}</h3>
                <div class="gls-info-text reveal delay-2" id="duration-text"></div>
            </div>

            <div class="gls-info-card reveal delay-3">
                <div class="gls-info-icon reveal delay-1">@include('frontoffice.svg.sites-times')</div>
                <h3 class="gls-info-card-title fade-blur-title reveal delay-2">{{ __('sites/kenitra.info.times') }}</h3>
                <div class="gls-info-text reveal delay-3" id="times-text"></div>
            </div>

            <div class="gls-info-card reveal delay-1">
                <div class="gls-info-icon reveal delay-2">@include('frontoffice.svg.sites-price')</div>
                <h3 class="gls-info-card-title fade-blur-title reveal delay-3">{{ __('sites/kenitra.info.price') }}</h3>
                <div class="gls-info-text reveal delay-1" id="price-text"></div>
            </div>

        </div>

    </div>

</section>



<!-- ===========================
     GROUPS – KENITRA
=========================== -->
<section class="gls-schedule-section reveal delay-1">
    <div class="gls-schedule-container reveal delay-2">

        <h2 class="gls-schedule-main-title fade-blur-title reveal delay-3">
            {{ __('sites/kenitra.groups.title') }}
        </h2>

        @php
            $periods = [
                'morning'   => __('sites/kenitra.groups.morning'),
                'midday'    => __('sites/kenitra.groups.midday'),
                'afternoon' => __('sites/kenitra.groups.afternoon'),
                'evening'   => __('sites/kenitra.groups.evening'),
            ];
        @endphp

        @foreach ($periods as $key => $label)

            @php $collection = $groups[$key] ?? collect(); @endphp

            <div class="schedule-dropdown reveal delay-1">

                <div class="schedule-dropdown_trigger reveal delay-2">
                    <h2 class="heading-5 fade-blur-title reveal delay-3">{{ $label }}</h2>

                    <div class="dropdown-icon reveal delay-1">
                        <div class="dropdown-line"></div>
                        <div class="dropdown-line is-rotated"></div>
                    </div>
                </div>

                <div class="schedule-dropdown_content reveal delay-2">
                    <div class="schedule-dropdown_height reveal delay-3">

                        <div class="price-table-rich-text reveal delay-1">

                            <div class="table-rich-text reveal delay-2">
                                <p><strong>{{ __('sites/kenitra.groups.active') }}</strong></p>

                                @forelse ($collection->where('status', 'active') as $group)
                                    <p class="reveal delay-1">
                                        {{ $group->teacher->name }} -
                                        {{ strtoupper($group->level) }} -
                                        {{ $group->time_range }}
                                    </p>
                                @empty
                                    <p class="reveal delay-1">Aucun groupe actif</p>
                                @endforelse
                            </div>

                            <div class="table-rich-text reveal delay-3">
                                <p><strong>{{ __('sites/kenitra.groups.upcoming') }}</strong></p>

                                @forelse ($collection->where('status', 'upcoming') as $group)
                                    <p class="reveal delay-1">
                                        {{ $group->teacher->name }} -
                                        {{ strtoupper($group->level) }} -
                                        {{ $group->time_range }}
                                    </p>
                                @empty
                                    <p class="reveal delay-1">Pas de nouveaux groupes prévus</p>
                                @endforelse
                            </div>

                        </div>

                    </div>
                </div>

            </div>

        @endforeach

    </div>
</section>



<!-- ===========================
     9ONSOL – KENITRA EPISODE
=========================== -->
<section class="home-about-section section reveal delay-1">
    <div class="container about-grid reveal delay-2">

        <div class="about-card text-light reveal delay-1">
            <h2 class="h-section-subtitle mb-4 fade-blur-title reveal delay-2">{!! __('sites/kenitra.9onsol.title') !!}</h2>

            <p class="lead mb-4 reveal delay-3">{!! __('sites/kenitra.9onsol.text') !!}</p>

            <a href="https://www.youtube.com/@9onsolsTalks" 
               target="_blank"
               class="btn btn-light rounded-pill fw-semibold px-4 py-2 mt-auto reveal delay-1">
                {{ __('sites/kenitra.9onsol.button') }}
            </a>
        </div>

        <div class="about-video reveal delay-3">
            <iframe width="560" height="315"
                src="https://www.youtube.com/embed/msKtQYUXh9c?si=jI465zOXsg2kx9-T"
                frameborder="0"
                allowfullscreen loading="lazy">
            </iframe>
        </div>

    </div>
</section>



<!-- ===========================
     CTA
=========================== -->
<section class="inline-cta-section section reveal delay-1">
    <div class="inline-cta-block reveal delay-2">

        <h2 class="heading-cta fade-blur-title reveal delay-3">
            {!! __('sites/kenitra.cta.title') !!}
        </h2>

        <p class="cta-box-subtext reveal delay-1">
            {!! __('sites/kenitra.cta.text') !!}
        </p>

        <a href="{{ route('front.contact') }}" class="cta-btn reveal delay-2">
            {{ __('sites/kenitra.cta.button') }}
        </a>

    </div>
</section>

<!-- JAVASCRIPT -->
<<script>
document.addEventListener("DOMContentLoaded", () => {
    const dropdowns = document.querySelectorAll(".schedule-dropdown");

    dropdowns.forEach(drop => {
        const trigger = drop.querySelector(".schedule-dropdown_trigger");
        const content = drop.querySelector(".schedule-dropdown_content");

        trigger.addEventListener("click", () => {
            const isOpen = drop.classList.contains("open");

            // Close all dropdowns
            dropdowns.forEach(d => {
                d.classList.remove("open");
                const c = d.querySelector(".schedule-dropdown_content");
                c.style.height = 0;
                c.style.opacity = 0;
            });

            // Open clicked one
            if (!isOpen) {
                drop.classList.add("open");
                content.style.height = content.scrollHeight + "px";
                content.style.opacity = 1;
            }
        });
    });
});

// COURSE DATA
const data = {
    A1: { 
        graduation: "A1 Certification (Basic German)", 
        duration: "5 weeks<br>18 lessons per week", 
        times: "Mon–Fri<br>13:15–16:30", 
        price: "998 DH" 
    },
    A2: { 
        graduation: "A2 Certification (Elementary level)", 
        duration: "5 weeks<br>18 lessons per week", 
        times: "Mon–Fri<br>13:15–16:30", 
        price: "1100 DH" 
    },
    B1: { 
        graduation: "B1 Certification (Intermediate)", 
        duration: "6 weeks<br>18 lessons per week", 
        times: "Mon–Fri<br>13:15–16:30", 
        price: "1300 DH" 
    },
    B2: { 
        graduation: "B2 Certification (Upper-Intermediate)", 
        duration: "6 weeks<br>20 lessons per week", 
        times: "Mon–Fri<br>13:15–16:30", 
        price: "1500 DH" 
    }
};

// UPDATE CARDS
function updateCards(level) {
    document.getElementById("graduation-text").innerHTML = data[level].graduation;
    document.getElementById("duration-text").innerHTML = data[level].duration;
    document.getElementById("times-text").innerHTML = data[level].times;
    document.getElementById("price-text").innerHTML = data[level].price;
}

// LEVEL SWITCHER
document.querySelectorAll(".gls-niveau-btn").forEach(btn => {
    btn.addEventListener("click", () => {

        document.querySelectorAll(".gls-niveau-btn")
            .forEach(b => b.classList.remove("active"));

        btn.classList.add("active");

        updateCards(btn.dataset.level);
    });
});

// DEFAULT ON LOAD
updateCards("A1");
</script>


@endsection
