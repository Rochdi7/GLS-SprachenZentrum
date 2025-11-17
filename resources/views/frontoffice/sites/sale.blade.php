@extends('frontoffice.layouts.app')
@section('title', 'GLS Salé | German Language Center')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/sites/marrakech.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/9onsol.css') }}">

@section('content')

<!-- ===========================
     HERO SECTION – SALÉ
=========================== -->
<section class="hero-section section about-hero">
  <div class="container is-hero">

    <div class="hero_subtitle">Learn German in the Heart of Salé</div>
    <h1 class="hero_title">GLS Sprachenzentrum – Salé Center</h1>

    <div class="hero-image">
      <img 
        src="{{ asset('assets/images/sites/sale/centre-sale.webp') }}" 
        alt="GLS Sprachenzentrum Salé" 
        class="full-image" 
        loading="lazy"
      >
    </div>
  </div>
</section>

<!-- ===========================
     ABOUT SALÉ CENTER
=========================== -->
<section class="gls-section gls-richtext-wrapper">
    <div class="gls-container">
        <div class="gls-richtext">

            <h2>Welcome to GLS Salé</h2>
            <h3>Your German Language Gateway in Diyar, Salé</h3>

            <p>
                The <strong>GLS Sprachenzentrum Salé</strong> is a modern center dedicated to helping learners
                develop strong and practical German skills.  
                Located at <strong>Avenue Mohamed V, Rue Halima N°12, Diyar, Salé</strong>,  
                our center offers a warm and motivating learning environment for all levels.
            </p>

            <p>
                Whether your dream is to study in Germany, prepare for Ausbildung, 
                or enhance your career opportunities, GLS Salé provides 
                <strong>immersive, structured, and results-focused German courses</strong>.
            </p>

            <h2>What We Offer</h2>
            <h3>German Courses for Every Path</h3>

            <p>At GLS Salé, you benefit from:</p>

            <ul>
                <li><strong>Intensive German Courses (A1–B2)</strong></li>
                <li><strong>Online German Lessons</strong> with live instructors</li>
                <li><strong>Conversation & speaking workshops</strong></li>
                <li><strong>Visa, study & immigration guidance</strong> for Germany</li>
                <li><strong>Soon: Official ÖSD & GLS Certified Exams</strong></li>
            </ul>

            <p>
                Our training method emphasizes communication, interaction, and real-world usage — 
                ensuring solid progress for every learner.
            </p>

        </div>
    </div>
</section>


<!-- ===========================
     PHOTO STRIP – SALÉ
=========================== -->
<section class="gls-photo-strip section">
    <div class="gls-container gls-photo-grid">

        <img src="{{ asset('assets/images/sites/sale/centre-sale1.webp') }}" alt="GLS Salé Students">
        <img src="{{ asset('assets/images/sites/sale/centre-sale2.webp') }}" alt="GLS Salé Classroom">
        <img src="{{ asset('assets/images/sites/sale/centre-sale3.webp') }}" alt="GLS Salé Activities">

    </div>
</section>


<!-- ===========================
     INFO CARDS – NIVEAUX
=========================== -->
<section class="gls-info-section gls-section">

    <div class="gls-container">

        <h2 class="gls-info-title">Information about our German Courses</h2>

        <div class="gls-niveau-tabs">
            <button class="gls-niveau-btn active" data-level="A1">A1</button>
            <button class="gls-niveau-btn" data-level="A2">A2</button>
            <button class="gls-niveau-btn" data-level="B1">B1</button>
            <button class="gls-niveau-btn" data-level="B2">B2</button>
        </div>

        <div class="gls-info-grid">

            <div class="gls-info-card">
                <div class="gls-info-icon">@include('frontoffice.svg.sites-info')</div>
                <h3 class="gls-info-card-title">Graduation</h3>
                <div class="gls-info-text" id="graduation-text"></div>
            </div>

            <div class="gls-info-card">
                <div class="gls-info-icon">@include('frontoffice.svg.sites-duration')</div>
                <h3 class="gls-info-card-title">Duration</h3>
                <div class="gls-info-text" id="duration-text"></div>
            </div>

            <div class="gls-info-card">
                <div class="gls-info-icon">@include('frontoffice.svg.sites-times')</div>
                <h3 class="gls-info-card-title">Course Times</h3>
                <div class="gls-info-text" id="times-text"></div>
            </div>

            <div class="gls-info-card">
                <div class="gls-info-icon">@include('frontoffice.svg.sites-price')</div>
                <h3 class="gls-info-card-title">Price</h3>
                <div class="gls-info-text" id="price-text"></div>
            </div>

        </div>

    </div>

</section>

<!-- ===========================
     GROUP SCHEDULE – SALÉ
=========================== -->
<section class="gls-schedule-section">
    <div class="gls-schedule-container">

        <h2 class="gls-schedule-main-title">Our Groups – GLS Salé</h2>

        <!-- MORNING -->
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
                            <p>Prof. Yassine</p>
                            <p>Prof. Amina</p>
                        </div>

                        <div class="table-rich-text">
                            <p><strong>Starting November</strong></p>
                            <p>Prof. Laila</p>
                            <p>Prof. Manal</p>
                            <p>New Group (13:00)</p>
                            <p>New Group (16:00)</p>
                            <p>New Group (19:00)</p>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <!-- AFTERNOON -->
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
                            <p>Prof. Driss</p>
                            <p>Prof. Hanafi</p>
                            <p>Prof. Nizar</p>
                        </div>

                        <div class="table-rich-text">
                            <p><strong>Upcoming</strong></p>
                            <p>No new groups planned</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- EVENING -->
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
                            <p>Prof. Yassine</p>
                            <p>Prof. Manal</p>
                            <p>Prof. Hajar</p>
                            <p>Prof. Amina</p>
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
     9ONSOL – SALÉ EPISODE
=========================== --}}
<section class="home-about-section section">
    <div class="container about-grid">

        <div class="about-card text-light">
            <h2 class="h-section-subtitle mb-4">
                Deutshow – Episode 4<br>Auditions from Salé
            </h2>

            <p class="lead mb-4">
    Welcome to <strong>Deutshow</strong>, Morocco’s first German-language talent competition! 🇩🇪🇲🇦 <br><br>
    In this Salé episode, learners present expressive performances that reflect their  
    creativity, confidence, and growing German skills. <br><br>
    Produced by <strong>9onsol’s Talks</strong>, it highlights the dedication of GLS Salé students.
</p>


            <a href="https://www.youtube.com/@9onsolsTalks" target="_blank"
                class="btn btn-light rounded-pill fw-semibold px-4 py-2 mt-auto">
                More Episodes
            </a>
        </div>

        <div class="about-video">
            <iframe width="560" height="315"
                src="https://www.youtube.com/embed/NQa8WpPvd8w?si=RNbF6jNcIauKF7Dy"
                title="Deutshow – Episode 4: Auditions from Salé"
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
            Ready to Start Your German Journey<br>in Salé?
        </h2>

        <p class="cta-box-subtext">
            Book your free consultation at GLS Salé and get  
            full guidance about studying or working in Germany.  
            Our team will help you understand the programs,  
            visa process, schedules, and the best course for your goals.
        </p>

        <a href="/contact" class="cta-btn">Book Your Consultation</a>

    </div>
</section>


<!-- ===========================
     JAVASCRIPT
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

@endsection
