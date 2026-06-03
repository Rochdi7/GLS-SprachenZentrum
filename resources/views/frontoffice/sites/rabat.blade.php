@extends('frontoffice.layouts.app')
@section('title', 'GLS Rabat | Centre de langue allemande')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/sites/marrakech.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/9onsol.css') }}">

@section('content')

    <!-- ===========================
                                     HERO – RABAT
                                =========================== -->
    <section class="hero-section section about-hero reveal delay-1">
        <div class="container is-hero reveal delay-2">

            <div class="hero_subtitle reveal delay-1">
                {{ __('sites/rabat.hero.subtitle') }}
            </div>

            <h1 class="hero_title fade-blur-title reveal delay-2">
                {{ __('sites/rabat.hero.title') }}
            </h1>

            <div class="hero-image reveal delay-3">
                <img src="{{ asset('assets/images/sites/rabat/centre-rabat.avif') }}" alt="GLS Rabat"
                    class="full-image reveal delay-1" loading="lazy">
            </div>

        </div>
    </section>



    <!-- ===========================
                                     ABOUT RABAT
                                =========================== -->
    <section class="gls-section gls-richtext-wrapper reveal delay-1">
        <div class="gls-container reveal delay-2">
            <div class="gls-richtext reveal delay-3">

                <h2 class="fade-blur-title reveal delay-1">{{ __('sites/rabat.about.title1') }}</h2>
                <h3 class="fade-blur-title reveal delay-2">{{ __('sites/rabat.about.subtitle1') }}</h3>

                <p class="reveal delay-1">{!! __('sites/rabat.about.p1') !!}</p>
                <p class="reveal delay-2">{!! __('sites/rabat.about.p2') !!}</p>

                <h2 class="fade-blur-title reveal delay-1">{{ __('sites/rabat.about.title2') }}</h2>
                <h3 class="fade-blur-title reveal delay-2">{{ __('sites/rabat.about.subtitle2') }}</h3>

                <p class="reveal delay-1">{{ __('sites/rabat.about.text_list') }}</p>

                <ul>
                    <li class="reveal delay-1"><strong>{{ __('sites/rabat.about.offers.1') }}</strong></li>
                    <li class="reveal delay-2"><strong>{{ __('sites/rabat.about.offers.2') }}</strong></li>
                    <li class="reveal delay-3"><strong>{{ __('sites/rabat.about.offers.3') }}</strong></li>
                    <li class="reveal delay-1"><strong>{{ __('sites/rabat.about.offers.4') }}</strong></li>
                    <li class="reveal delay-2"><strong>{{ __('sites/rabat.about.offers.5') }}</strong></li>
                </ul>

                <p class="reveal delay-3">{!! __('sites/rabat.about.p3') !!}</p>

            </div>
        </div>
    </section>



    <!-- ===========================
                                     PHOTO STRIP – RABAT
                                =========================== -->
    <section class="gls-photo-strip section reveal delay-1">
        <div class="gls-container gls-photo-grid reveal delay-2">

            <img src="{{ asset('assets/images/sites/rabat/centre-rabat1.avif') }}" class="reveal delay-1"
                alt="GLS Rabat Students">
            <img src="{{ asset('assets/images/sites/rabat/centre-rabat2.avif') }}" class="reveal delay-2"
                alt="GLS Rabat Classroom">
            <img src="{{ asset('assets/images/sites/rabat/centre-rabat3.avif') }}" class="reveal delay-3"
                alt="GLS Rabat Activities">

        </div>
    </section>



    <!-- ===========================
                                     INFO CARDS
                                =========================== -->
    <section class="gls-info-section gls-section reveal delay-1">

        <div class="gls-container reveal delay-2">

            <h2 class="gls-info-title fade-blur-title reveal delay-3">
                {{ __('sites/rabat.info.title') }}
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
                    <h3 class="gls-info-card-title fade-blur-title reveal delay-3">
                        {{ __('sites/rabat.info.certification') }}
                    </h3>
                    <div class="gls-info-text reveal delay-1" id="graduation-text"></div>
                </div>

                <div class="gls-info-card reveal delay-2">
                    <div class="gls-info-icon reveal delay-3">@include('frontoffice.svg.sites-duration')</div>
                    <h3 class="gls-info-card-title fade-blur-title reveal delay-1">
                        {{ __('sites/rabat.info.duration') }}
                    </h3>
                    <div class="gls-info-text reveal delay-2" id="duration-text"></div>
                </div>

                <div class="gls-info-card reveal delay-3">
                    <div class="gls-info-icon reveal delay-1">@include('frontoffice.svg.sites-times')</div>
                    <h3 class="gls-info-card-title fade-blur-title reveal delay-2">
                        {{ __('sites/rabat.info.times') }}
                    </h3>
                    <div class="gls-info-text reveal delay-3" id="times-text"></div>
                </div>

                <div class="gls-info-card reveal delay-1">
                    <div class="gls-info-icon reveal delay-2">@include('frontoffice.svg.sites-price')</div>
                    <h3 class="gls-info-card-title fade-blur-title reveal delay-3">
                        {{ __('sites/rabat.info.price') }}
                    </h3>
                    <div class="gls-info-text reveal delay-1" id="price-text"></div>
                </div>

            </div>

        </div>

    </section>



    <!-- ===========================
                                     GROUP SCHEDULE – RABAT
                                =========================== -->
    <section class="gls-schedule-section reveal delay-1">
        <div class="gls-schedule-container reveal delay-2">

            <h2 class="gls-schedule-main-title fade-blur-title reveal delay-3">
                {{ __('sites/rabat.groups.title') }}
            </h2>

            @php
                $periods = [
                    'morning' => __('sites/rabat.groups.morning'),
                    'midday' => __('sites/rabat.groups.midday'),
                    'afternoon' => __('sites/rabat.groups.afternoon'),
                    'evening' => __('sites/rabat.groups.evening'),
                ];

                // Champ du nom du groupe à afficher
                $groupNameField = 'name_fr';
            @endphp

            @foreach ($periods as $key => $label)
                @php $collection = $groups[$key] ?? collect(); @endphp

                <div class="schedule-dropdown reveal delay-1">

                    <div class="schedule-dropdown_trigger reveal delay-2">
                        <h2 class="fade-blur-title reveal delay-3">{{ $label }}</h2>

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
                                    <p><strong>{{ __('sites/rabat.groups.active') }}</strong></p>

                                    @forelse ($collection->where('status', 'active') as $group)
                                        <p class="reveal delay-1 gls-group-row">
                                            <span class="gls-group-text">
                                                {{ data_get($group, $groupNameField) ?? $group->name }}
                                                - {{ strtoupper($group->level) }}
                                                - {{ $group->time_range }}
                                            </span>

                                            <a href="#" class="gls-apply-btn" data-bs-toggle="modal"
                                                data-bs-target="#glsApplyGroupModal" data-group-id="{{ $group->id }}"
                                                data-group-label="{{ $group->name_fr ?? ($group->name ?? 'Groupe #' . $group->id) }}"
                                                data-group-level="{{ $group->level ?? ($group->niveau ?? '') }}"
                                                data-group-schedule="{{ $group->period_label ?? ($group->period ?? '') }}">
                                                Apply
                                            </a>
                                        </p>
                                    @empty
                                        <p class="reveal delay-1">{{ __('home.groups.empty_active') }}</p>
                                    @endforelse
                                </div>

                                <!-- UPCOMING -->
                                <div class="table-rich-text reveal delay-3">
                                    <p><strong>{{ __('sites/rabat.groups.upcoming') }}</strong></p>

                                    @forelse ($collection->where('status', 'upcoming') as $group)
                                        <p class="reveal delay-1 gls-group-row">
                                            <span class="gls-group-text">
                                                {{ data_get($group, $groupNameField) ?? $group->name }}
                                                - {{ strtoupper($group->level) }}
                                                - {{ $group->time_range }}
                                            </span>

                                            <a href="#" class="gls-apply-btn" data-bs-toggle="modal"
                                                data-bs-target="#glsApplyGroupModal" data-group-id="{{ $group->id }}"
                                                data-group-label="{{ $group->name_fr ?? ($group->name ?? 'Groupe #' . $group->id) }}"
                                                data-group-level="{{ $group->level ?? ($group->niveau ?? '') }}"
                                                data-group-schedule="{{ $group->period_label ?? ($group->period ?? '') }}">
                                                Apply
                                            </a>
                                        </p>
                                    @empty
                                        <p class="reveal delay-1">{{ __('home.groups.empty_upcoming') }}</p>
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
                                     9ONSOL — RABAT EPISODE
                                =========================== -->
    <section class="home-about-section section reveal delay-1">
        <div class="container about-grid reveal delay-2">

            <div class="about-card text-light reveal delay-1">
                <h2 class="h-section-subtitle mb-4 fade-blur-title reveal delay-2">
                    {!! __('sites/rabat.9onsol.title') !!}
                </h2>

                <p class="lead mb-4 reveal delay-3">
                    {!! __('sites/rabat.9onsol.text') !!}
                </p>

                <a href="https://www.youtube.com/@9onsolsTalks" target="_blank"
                    class="btn btn-light rounded-pill fw-semibold px-4 py-2 mt-auto reveal delay-1">
                    {{ __('sites/rabat.9onsol.button') }}
                </a>
            </div>

            <div class="about-video reveal delay-3">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/HdZcNCPoJm8?si=5udMi0NFC4MbzvV1"
                    frameborder="0" allowfullscreen loading="lazy">
                </iframe>
            </div>

        </div>
    </section>

    <!-- ===========================
                                     CTA – RABAT
                                =========================== -->

    <section class="inline-cta-section section reveal delay-1">
        <div class="inline-cta-block reveal delay-2">

            <h2 class="heading-cta fade-blur-title reveal delay-3">
                {!! __('sites/rabat.cta.title') !!}
            </h2>

            <p class="cta-box-subtext reveal delay-1">
                {!! __('sites/rabat.cta.text') !!}
            </p>

            <button type="button" class="cta-btn reveal delay-2" data-bs-toggle="modal"
                data-bs-target="#consultationModal">
                {{ __('sites/rabat.cta.button') }}
            </button>

        </div>
    </section>

    {{-- ===============================
 CONTACT SECTION
================================ --}}
    <section class="contact-section section {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-1">
        <div class="container is-2-col-grid reveal delay-2">

            {{-- LEFT SIDE --}}
            <div class="div-block-5-copy reveal delay-3">

                <h2 class="contact-section-subtitle reveal fade-blur-title delay-1">
                    {!! __('sites/rabat.contact.title') !!}
                </h2>


                <div class="div-block-21 reveal delay-2">

                    <div class="link-block reveal delay-1">
                        <div class="text-block-3 reveal delay-2">
                            <span class="text-span reveal delay-3">{!! __('sites/rabat.contact.call_label') !!}<br></span>
                            <a href="tel:{{ __('sites/rabat.contact.phone_link') }}">{{ __('sites/rabat.contact.phone_display') }}</a><br>
                            <a href="tel:+212537372003">+212 5 37 37 20 03</a>
                        </div>
                    </div>

                    <a href="mailto:{{ __('sites/rabat.contact.email') }}" class="link-block-2 reveal delay-3">
                        <div class="text-block-3 reveal delay-1">
                            <span class="text-span reveal delay-2">{!! __('sites/rabat.contact.email_label') !!}<br></span>
                            {{ __('sites/rabat.contact.email') }}
                        </div>
                    </a>

                </div>

                <div class="text-block-3 visit-block reveal delay-3">
                    <span class="text-span reveal delay-1">{!! __('sites/rabat.contact.visit_label') !!}</span><br>
                    @include('frontoffice.partials.gls-centers-links')
                </div>

                <div class="footer-socials-block reveal delay-1">

                    <div class="text-block-3 reveal delay-2">
                        <span class="text-span reveal delay-3">{!! __('sites/rabat.contact.follow_label') !!}</span>
                    </div>

                    <div class="div-block-20 reveal delay-1">
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

                        <a href="{{ __('sites/rabat.contact.whatsapp_url') }}" class="footer-social-link wa"
                            target="_blank" rel="noopener noreferrer"
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

            {{-- RIGHT SIDE: MAP --}}
            <a href="{{ __('sites/rabat.contact.map_url') }}" target="_blank" class="div-block-7 reveal delay-3">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3307.8001465016737!2d-6.8485901!3d33.9976668!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda76dcf7a656da5%3A0xcaf46ae5e6e81d87!2sGLS%20Sprachenzentrum%20-%20Centre%20GLS%20de%20langue%20Allemande%20Rabat!5e0!3m2!1sen!2sma!4v1769193870895!5m2!1sen!2sma"
                    allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                    class="reveal delay-1"></iframe>
            </a>

        </div>
    </section>

    <!-- ===========================
                                     JS
                                =========================== -->
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
    </script>

    <script>
        const data = @json(__('sites/rabat.info_cards.levels'));

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

        updateCards("A1");
    </script>

    @include('frontoffice.sites.partials._book-on-whatsapp', [
        'centerName' => 'GLS Rabat',
        'centerPhoneE164' => '212600000000',
    ])

@endsection
