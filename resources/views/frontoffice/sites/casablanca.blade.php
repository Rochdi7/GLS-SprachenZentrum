@extends('frontoffice.layouts.app')
@section('title', __('sites/casablanca.hero.title'))

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/sites/marrakech.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/9onsol.css') }}">

@section('content')

<!-- ===========================
     HERO SECTION – CASABLANCA
=========================== -->
<section class="hero-section section about-hero reveal delay-1">
  <div class="container is-hero reveal delay-2">

    <div class="hero_subtitle reveal delay-1">{{ __('sites/casablanca.hero.subtitle') }}</div>

    <h1 class="hero_title fade-blur-title reveal delay-2">
      {{ __('sites/casablanca.hero.title') }}
    </h1>

    <div class="hero-image reveal delay-3">
      <img 
        src="{{ asset('assets/images/sites/casablanca/centre-casablanca.webp') }}" 
        alt="GLS Sprachenzentrum Casablanca" 
        class="full-image reveal delay-1"
        loading="lazy"
      >
    </div>

  </div>
</section>



<!-- ===========================
     ABOUT CASABLANCA CENTER
=========================== -->
<section class="gls-section gls-richtext-wrapper reveal delay-1">
    <div class="gls-container reveal delay-2">
        <div class="gls-richtext reveal delay-3">

            <h2 class="fade-blur-title reveal delay-1">{{ __('sites/casablanca.about.title1') }}</h2>
            <h3 class="fade-blur-title reveal delay-2">{{ __('sites/casablanca.about.subtitle1') }}</h3>

            <p class="reveal delay-1">{!! __('sites/casablanca.about.p1') !!}</p>
            <p class="reveal delay-2">{!! __('sites/casablanca.about.p2') !!}</p>

            <h2 class="fade-blur-title reveal delay-1">{{ __('sites/casablanca.about.title2') }}</h2>
            <h3 class="fade-blur-title reveal delay-2">{{ __('sites/casablanca.about.subtitle2') }}</h3>

            <p class="reveal delay-1">{{ __('sites/casablanca.about.text_list') }}</p>

            <ul>
                <li class="reveal delay-1">{{ __('sites/casablanca.about.offers.1') }}</li>
                <li class="reveal delay-2">{{ __('sites/casablanca.about.offers.2') }}</li>
                <li class="reveal delay-3">{{ __('sites/casablanca.about.offers.3') }}</li>
                <li class="reveal delay-1">{{ __('sites/casablanca.about.offers.4') }}</li>
                <li class="reveal delay-2">{{ __('sites/casablanca.about.offers.5') }}</li>
            </ul>

            <p class="reveal delay-3">{!! __('sites/casablanca.about.p3') !!}</p>

        </div>
    </div>
</section>



<!-- ===========================
     PHOTO STRIP
=========================== -->
<section class="gls-photo-strip section reveal delay-1">
    <div class="gls-container gls-photo-grid reveal delay-2">

        <img src="{{ asset('assets/images/sites/casablanca/centre-casablanca1.webp') }}" alt="" class="reveal delay-1">
        <img src="{{ asset('assets/images/sites/casablanca/centre-casablanca2.webp') }}" alt="" class="reveal delay-2">
        <img src="{{ asset('assets/images/sites/casablanca/centre-casablanca3.webp') }}" alt="" class="reveal delay-3">

    </div>
</section>



<!-- ===========================
     INFO CARDS
=========================== -->
<section class="gls-info-section gls-section reveal delay-1">
    <div class="gls-container reveal delay-2">

        <h2 class="gls-info-title fade-blur-title reveal delay-3">
            {{ __('sites/casablanca.info.title') }}
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
                <h3 class="gls-info-card-title fade-blur-title reveal delay-3">{{ __('sites/casablanca.info.certification') }}</h3>
                <div class="gls-info-text reveal delay-1" id="graduation-text"></div>
            </div>

            <div class="gls-info-card reveal delay-2">
                <div class="gls-info-icon reveal delay-3">@include('frontoffice.svg.sites-duration')</div>
                <h3 class="gls-info-card-title fade-blur-title reveal delay-1">{{ __('sites/casablanca.info.duration') }}</h3>
                <div class="gls-info-text reveal delay-2" id="duration-text"></div>
            </div>

            <div class="gls-info-card reveal delay-3">
                <div class="gls-info-icon reveal delay-1">@include('frontoffice.svg.sites-times')</div>
                <h3 class="gls-info-card-title fade-blur-title reveal delay-2">{{ __('sites/casablanca.info.times') }}</h3>
                <div class="gls-info-text reveal delay-3" id="times-text"></div>
            </div>

            <div class="gls-info-card reveal delay-1">
                <div class="gls-info-icon reveal delay-2">@include('frontoffice.svg.sites-price')</div>
                <h3 class="gls-info-card-title fade-blur-title reveal delay-3">{{ __('sites/casablanca.info.price') }}</h3>
                <div class="gls-info-text reveal delay-1" id="price-text"></div>
            </div>

        </div>

    </div>
</section>



<!-- ===========================
     GROUPS
=========================== -->
<section class="gls-schedule-section reveal delay-1">
    <div class="gls-schedule-container reveal delay-2">

        <h2 class="gls-schedule-main-title fade-blur-title reveal delay-3">
            {{ __('sites/casablanca.groups.title') }}
        </h2>

        @php
            $periods = [
                'morning'   => __('sites/casablanca.groups.morning'),
                'midday'    => __('sites/casablanca.groups.midday'),
                'afternoon' => __('sites/casablanca.groups.afternoon'),
                'evening'   => __('sites/casablanca.groups.evening'),
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

                            <!-- ACTIVE -->
                            <div class="table-rich-text reveal delay-2">
                                <p><strong>{{ __('sites/casablanca.groups.active') }}</strong></p>

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

                            <!-- UPCOMING -->
                            <div class="table-rich-text reveal delay-3">
                                <p><strong>{{ __('sites/casablanca.groups.upcoming') }}</strong></p>

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
     9ONSOL TALKS – CASABLANCA
=========================== -->
<section class="home-about-section section reveal delay-1">
    <div class="container about-grid reveal delay-2">

        <div class="about-card text-light reveal delay-1">
            <h2 class="h-section-subtitle mb-4 fade-blur-title reveal delay-2">{!! __('sites/casablanca.9onsol.title') !!}</h2>

            <p class="lead mb-4 reveal delay-3">{!! __('sites/casablanca.9onsol.text') !!}</p>

            <a href="https://www.youtube.com/@9onsolsTalks" target="_blank"
                class="btn btn-light rounded-pill fw-semibold px-4 py-2 mt-auto reveal delay-1">
                {{ __('sites/casablanca.9onsol.button') }}
            </a>
        </div>

        <div class="about-video reveal delay-3">
            <iframe width="560" height="315"
                src="https://www.youtube.com/embed/QeLeByG2eok?si=WZ6EWcq1uOTjyCA4"
                title="Deutshow – Casablanca"
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
        
        <h2 class="heading-cta fade-blur-title reveal delay-3">{!! __('sites/casablanca.cta.title') !!}</h2>

        <p class="cta-box-subtext reveal delay-1">{!! __('sites/casablanca.cta.text') !!}</p>

        <a href="{{ route('front.contact') }}" class="cta-btn reveal delay-2">
            {{ __('sites/casablanca.cta.button') }}
        </a>

    </div>
</section>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const dropdowns = document.querySelectorAll(".schedule-dropdown");

    dropdowns.forEach(drop => {
        const trigger = drop.querySelector(".schedule-dropdown_trigger");
        const content = drop.querySelector(".schedule-dropdown_content");

        trigger.addEventListener("click", () => {
            const isOpen = drop.classList.contains("open");

            dropdowns.forEach(d => {
                d.classList.remove("open");
                const c = d.querySelector(".schedule-dropdown_content");
                c.style.height = 0;
                c.style.opacity = 0;
            });

            if (!isOpen) {
                drop.classList.add("open");
                content.style.height = content.scrollHeight + "px";
                content.style.opacity = 1;
            }
        });
    });
});

const data = {
  A1: {
    graduation: "Certification A1 (Allemand débutant)",
    duration: "5 semaines<br>18 leçons par semaine",
    times: "Lun–Ven<br>13h15–16h30",
    price: "998 DH"
  },
  A2: {
    graduation: "Certification A2 (Niveau élémentaire)",
    duration: "5 semaines<br>18 leçons par semaine",
    times: "Lun–Ven<br>13h15–16h30",
    price: "1100 DH"
  },
  B1: {
    graduation: "Certification B1 (Niveau intermédiaire)",
    duration: "6 semaines<br>18 leçons par semaine",
    times: "Lun–Ven<br>13h15–16h30",
    price: "1300 DH"
  },
  B2: {
    graduation: "Certification B2 (Niveau avancé)",
    duration: "6 semaines<br>20 leçons par semaine",
    times: "Lun–Ven<br>13h15–16h30",
    price: "1500 DH"
  }
};

function updateCards(level) {
    document.getElementById("graduation-text").innerHTML = data[level].graduation;
    document.getElementById("duration-text").innerHTML = data[level].duration;
    document.getElementById("times-text").innerHTML = data[level].times;
    document.getElementById("price-text").innerHTML = data[level].price;
}

document.querySelectorAll(".gls-niveau-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        document.querySelectorAll(".gls-niveau-btn").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        updateCards(btn.dataset.level);
    });
});

// Default
updateCards("A1");
</script>

@endsection
