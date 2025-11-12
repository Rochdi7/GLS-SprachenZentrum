{{-- =========================================================
     HEADER + MOBILE DRAWER
     Responsive + Accordion Dropdowns
   ========================================================= --}}

<header class="site-header">
    <div class="nav-wrap container-xxl d-flex justify-content-between align-items-center">
        {{-- ===== Brand ===== --}}
        <a href="#" class="brand">
            gls sprachENzentrum
        </a>

        {{-- ===== Desktop Navigation ===== --}}
        <nav class="nav d-none d-lg-flex" aria-label="Primary Navigation">
            {{-- About --}}
            <div class="dropdown">
                <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    About
                </button>
                <ul class="dropdown-menu">
    <li><a class="dropdown-item" href="{{ route('front.about') }}">About Us</a></li>
    <li><a class="dropdown-item" href="{{ route('front.sites') }}">Our Sites</a></li>
    <li><a class="dropdown-item" href="{{ route('front.faq') }}">FAQ</a></li>
    <li><a class="dropdown-item" href="{{ route('front.contact') }}">Contact</a></li>
</ul>

            </div>

            {{-- German Courses --}}
            <div class="dropdown">
                <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    German Courses
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Intensive Courses</a></li>
                    <li><a class="dropdown-item" href="#">Online Courses</a></li>
                    <li><a class="dropdown-item" href="#">Pricing</a></li>
                </ul>
            </div>

            {{-- Exams --}}
            <div class="dropdown">
                <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    Exams
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Goethe Exams</a></li>
                    <li><a class="dropdown-item" href="#">ÖSD Exams</a></li>
                </ul>
            </div>

            {{-- Resources --}}
            <div class="dropdown">
                <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    Resources
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Blog</a></li>
                    <li><a class="dropdown-item" href="#">Student Stories</a></li>
                </ul>
            </div>
        </nav>

        {{-- ===== Right Side (Desktop) ===== --}}
        <div class="right d-none d-lg-flex align-items-center gap-2">
            {{-- <span class="chip chip--dark">EN</span>
            <span class="chip">DE</span> --}}
            <a class="btn btn-primary" href="#">Enroll Now</a>
        </div>

        {{-- ===== Mobile Toggle ===== --}}
        <div class="menu-toggle d-flex d-lg-none flex-column justify-content-center" id="burger"
            aria-label="Open mobile menu" aria-expanded="false" aria-controls="mobile-drawer">
            <span></span><span></span><span></span>
        </div>
    </div>
</header>

{{-- =========================================================
     MOBILE DRAWER (Accordion Style)
   ========================================================= --}}
<div class="drawer" id="mobile-drawer">
    <div class="drawer-inner">

        <div class="menu-section">
            <div class="menu-item">
                <button class="menu-label" type="button">About</button>
                <div class="submenu">
    <a href="{{ route('front.about') }}">About Us</a>
    <a href="{{ route('front.sites') }}">Our Sites</a>
    <a href="{{ route('front.faq') }}">FAQ</a>
    <a href="{{ route('front.contact') }}">Contact</a>
</div>

            </div>

            <div class="menu-item">
                <button class="menu-label" type="button">German Courses</button>
                <div class="submenu">
                    <a href="#">Intensive Courses</a>
                    <a href="#">Online Courses</a>
                    <a href="#">Pricing</a>
                </div>
            </div>

            <div class="menu-item">
                <button class="menu-label" type="button">Exams</button>
                <div class="submenu">
                    <a href="#">Goethe Exams</a>
                    <a href="#">ÖSD Exams</a>
                </div>
            </div>

            <div class="menu-item">
                <button class="menu-label" type="button">Resources</button>
                <div class="submenu">
                    <a href="#">Blog</a>
                    <a href="#">Student Stories</a>
                </div>
            </div>
        </div>

        {{-- <div class="langs mt-4">
            <span class="chip chip--dark">EN</span>
            <span class="chip">DE</span>
        </div> --}}

        <div class="cta mt-3">
            <a class="btn btn-primary w-100" href="#">Enroll Now</a>
        </div>

    </div>
</div>

<div class="backdrop" id="backdrop"></div>
