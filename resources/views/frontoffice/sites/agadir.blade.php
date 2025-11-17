@extends('frontoffice.layouts.app')
@section('title', 'GLS Agadir | German Language Center')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/sites/marrakech.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/9onsol.css') }}">

@section('content')

<!-- ===========================
     HERO SECTION – AGADIR
=========================== -->
<section class="hero-section section about-hero">
  <div class="container is-hero">

    <div class="hero_subtitle">Learn German in the Heart of Agadir</div>
    <h1 class="hero_title">GLS Sprachenzentrum – Agadir Center</h1>

    <div class="hero-image">
      <img 
        src="{{ asset('assets/images/sites/agadir/centre-agadir.webp') }}" 
        alt="GLS Sprachenzentrum Agadir" 
        class="full-image" 
        loading="lazy"
      >
    </div>
  </div>
</section>

<!-- ===========================
     ABOUT AGADIR CENTER
=========================== -->
<section class="gls-section gls-richtext-wrapper">
    <div class="gls-container">
        <div class="gls-richtext">

            <h2>Welcome to GLS Agadir</h2>
            <h3>Your German Language Gateway in Hay Essalam</h3>

            <p>
                The <strong>GLS Sprachenzentrum Agadir</strong> is one of the most dynamic German language 
                centers in southern Morocco. Strategically located at  
                <strong>Av. Massoude Al Wafaoui – Place des Taxis, Hay Essalam</strong>,  
                our center offers an engaging environment where learners of all ages 
                study German with confidence and motivation.
            </p>

            <p>
                Whether your goal is studying in Germany, preparing for Ausbildung, or enhancing your
                career opportunities, GLS Agadir provides <strong>structured, immersive, and result-oriented</strong> programs.
            </p>

            <h2>What We Offer</h2>
            <h3>German Courses for Every Objective</h3>

            <p>At GLS Agadir, you benefit from:</p>

            <ul>
                <li><strong>Intensive German Courses (A1–B2)</strong></li>
                <li><strong>Online German Courses</strong> with live teachers</li>
                <li><strong>Speaking & conversation workshops</strong></li>
                <li><strong>Professional visa & study guidance</strong> for Germany</li>
                <li><strong>Soon: Official ÖSD & GLS Certified Exams</strong></li>
            </ul>

            <p>
                Our methodology is based on real communication, interaction, and progressive learning —
                helping every learner advance with confidence.
            </p>

        </div>
    </div>
</section>


<!-- ===========================
     PHOTO STRIP – AGADIR
=========================== -->
<section class="gls-photo-strip section">
    <div class="gls-container gls-photo-grid">

        <img src="{{ asset('assets/images/sites/agadir/centre-agadir1.webp') }}" alt="GLS Agadir Students">
        <img src="{{ asset('assets/images/sites/agadir/centre-agadir2.webp') }}" alt="GLS Agadir Classroom">
        <img src="{{ asset('assets/images/sites/agadir/centre-agadir3.webp') }}" alt="GLS Agadir Activities">

    </div>
</section>



<section class="gls-info-section gls-section">

    <div class="gls-container">

        <h2 class="gls-info-title">Information about our German Courses</h2>

        <!-- LEVEL SWITCHER -->
        <div class="gls-niveau-tabs">
            <button class="gls-niveau-btn active" data-level="A1">A1</button>
            <button class="gls-niveau-btn" data-level="A2">A2</button>
            <button class="gls-niveau-btn" data-level="B1">B1</button>
            <button class="gls-niveau-btn" data-level="B2">B2</button>
        </div>

        <!-- INFO CARDS GRID -->
        <div class="gls-info-grid">

            <!-- GRADUATION -->
            <div class="gls-info-card">
                <div class="gls-info-icon">
                    @include('frontoffice.svg.sites-info')
                </div>
                <h3 class="gls-info-card-title">Graduation</h3>
                <div class="gls-info-text" id="graduation-text"></div>
            </div>

            <!-- DURATION -->
            <div class="gls-info-card">
                <div class="gls-info-icon">
                    @include('frontoffice.svg.sites-duration')
                </div>
                <h3 class="gls-info-card-title">Duration</h3>
                <div class="gls-info-text" id="duration-text"></div>
            </div>

            <!-- COURSE TIMES -->
            <div class="gls-info-card">
                <div class="gls-info-icon">
                    @include('frontoffice.svg.sites-times')
                </div>
                <h3 class="gls-info-card-title">Course Times</h3>
                <div class="gls-info-text" id="times-text"></div>
            </div>

            <!-- PRICE -->
            <div class="gls-info-card">
                <div class="gls-info-icon">
                    @include('frontoffice.svg.sites-price')
                </div>
                <h3 class="gls-info-card-title">Price</h3>
                <div class="gls-info-text" id="price-text"></div>
            </div>

        </div>

    </div>

</section>

<section class="gls-schedule-section">
    <div class="gls-schedule-container">

        <!-- SECTION TITLE -->
        <h2 class="gls-schedule-main-title">Our Groups – GLS Agadir</h2>

        <!-- ==============================
             GROUP 1 — 10:00 - 12:30
        =============================== -->
        <div class="schedule-dropdown">
            <div class="schedule-dropdown_trigger">
                <h2 class="heading-5">10:00 – 12:30 • Morning Groups</h2>

                <div class="dropdown-icon">
                    <div class="dropdown-line"></div>
                    <div class="dropdown-line is-rotated"></div>
                </div>
            </div>

            <div class="schedule-dropdown_content">
                <div class="schedule-dropdown_height">

                    <div class="price-table-rich-text">

                        <div class="table-rich-text">
                            <p><strong>Active Groups</strong></p>
                            <p>Prof. Soufiane</p>
                            <p>Prof. Amina</p>
                            <p>Prof. Yassine</p>
                        </div>

                        <div class="table-rich-text">
                            <p><strong>Starting November</strong></p>
                            <p>Prof. Zakaria</p>
                            <p>Prof. Hajar</p>
                            <p>New Group (13:00)</p>
                            <p>New Group (16:00)</p>
                            <p>New Group (19:00)</p>
                        </div>

                    </div>

                </div>
            </div>
        </div>


        <!-- ==============================
             GROUP 2 — 16:00 - 18:30
        =============================== -->
        <div class="schedule-dropdown">
            <div class="schedule-dropdown_trigger">
                <h2 class="heading-5">16:00 – 18:30 • Afternoon Groups</h2>

                <div class="dropdown-icon">
                    <div class="dropdown-line"></div>
                    <div class="dropdown-line is-rotated"></div>
                </div>
            </div>

            <div class="schedule-dropdown_content">
                <div class="schedule-dropdown_height">

                    <div class="price-table-rich-text">
                        <div class="table-rich-text">
                            <p><strong>Active</strong></p>
                            <p>Prof. Amina</p>
                            <p>Prof. Manal</p>
                            <p>Prof. Hajar</p>
                        </div>

                        <div class="table-rich-text">
                            <p><strong>Upcoming</strong></p>
                            <p>No new groups planned</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <!-- ==============================
             GROUP 3 — 19:00 - 21:30
        =============================== -->
        <div class="schedule-dropdown">
            <div class="schedule-dropdown_trigger">
                <h2 class="heading-5">19:00 – 21:30 • Evening Groups</h2>

                <div class="dropdown-icon">
                    <div class="dropdown-line"></div>
                    <div class="dropdown-line is-rotated"></div>
                </div>
            </div>

            <div class="schedule-dropdown_content">
                <div class="schedule-dropdown_height">

                    <div class="price-table-rich-text">

                        <div class="table-rich-text">
                            <p><strong>Active</strong></p>
                            <p>Prof. Zakaria</p>
                            <p>Prof. Manal</p>
                            <p>Prof. Yassine</p>
                            <p>Prof. Imane</p>
                        </div>

                        <div class="table-rich-text">
                            <p><strong>Upcoming</strong></p>
                            <p>More groups will open soon</p>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>
</section>

{{-- ===========================
     ABOUT GLS MOROCCO SECTION – 9onsol’s Talks
=========================== --}}
<section class="home-about-section section">
    <div class="container about-grid">

        {{-- Left Gradient Card --}}
        <div class="about-card text-light">
            <h2 class="h-section-subtitle mb-4">
                Deutshow – Episode 1<br>Auditions from Agadir
            </h2>

            <p class="lead mb-4">
    Welcome to <strong>Deutshow</strong>, Morocco’s first German-language talent competition! 🇩🇪🇲🇦 <br><br>
    In this Agadir episode, learners share their German journey — from exams to challenges  
    and real experiences that reflect their determination. <br><br>
    Produced by <strong>9onsol’s Talks</strong>, it showcases the drive of our GLS Agadir community.
</p>


            <a href="https://www.youtube.com/@9onsolsTalks" target="_blank"
                class="btn btn-light rounded-pill fw-semibold px-4 py-2 mt-auto">
                More Episodes
            </a>
        </div>

        {{-- Right Video Embed --}}
        <div class="about-video">
            <iframe width="560" height="315"
                src="https://www.youtube.com/embed/RsV4EUUTdTY?si=cH-_pOzzZg2WNRyG"
                title="Deutshow – Episode 1: Auditions from Agadir"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen loading="lazy">
            </iframe>
        </div>

    </div>
</section>


<!-- CTA -->
<section class="inline-cta-section section">
    <div class="inline-cta-block">
        
        <h2 class="heading-cta">
            Ready to Start Your German Journey<br>in Agadir?
        </h2>

        <p class="cta-box-subtext">
            Book your free consultation at GLS Agadir and get  
            step-by-step guidance about studying or working in Germany.  
            Our administrative team will explain the programs, the visa steps,  
            and help you choose the perfect German course for your goals.
        </p>

        <a href="/contact" class="cta-btn">Book Your Consultation</a>

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
</script>


<script>
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

// TAB CLICK
document.querySelectorAll(".gls-niveau-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        document.querySelectorAll(".gls-niveau-btn").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        updateCards(btn.dataset.level);
    });
});

// default A1
updateCards("A1");
</script>

@endsection
