@extends('frontoffice.layouts.app')

@section('title', '')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/pricing/pricing.css') }}">

@section('content')

<!-- ============================
     HERO SECTION
============================ -->
<section class="hero-section section about-hero">
  <div class="container is-hero">
    <div class="hero_subtitle">Invest in Your Future in Germany</div>
    <h1 class="hero_title">Simple and Affordable Pricing for All Students</h1>

    <div class="hero-image">
      <img 
        src="{{ asset('assets/images/about/Centre-GLS-de-langue-Allemande.jpg') }}" 
        alt="Students learning German at GLS Sprachenzentrum" 
        class="full-image" 
        loading="lazy"
      >
    </div>
  </div>
</section>


<!-- ============================
     PRICING TABS — ONE BOX
============================ -->
<div class="container py-5">

    <!-- TAB MENU -->
    <div class="gls-tabs-menu d-flex flex-wrap justify-content-center gap-2 mb-4">

        <button data-tab="Online" class="gls-tab-link active btn">Online Courses</button>

        <button data-tab="Casablanca" class="gls-tab-link btn">Casablanca</button>
        <button data-tab="Marrakech" class="gls-tab-link btn">Marrakech</button>
        <button data-tab="Rabat" class="gls-tab-link btn">Rabat</button>
        <button data-tab="Kenitra" class="gls-tab-link btn">Kénitra</button>
        <button data-tab="Sale" class="gls-tab-link btn">Salé</button>
        <button data-tab="Agadir" class="gls-tab-link btn">Agadir</button>

        <button data-tab="Exams" class="gls-tab-link btn">Examinations</button>

    </div>


    <!-- ONE HEADER BOX -->
    <div class="div-block-17 text-center">
        <h2 id="pricing-title" class="pricelist_header">Online Courses</h2>
        <div id="pricing-subtitle" class="text-block-6">Flexible online live sessions.</div>
    </div>

    <!-- ONE TABLE BOX -->
    <div class="table-wrapper">
        <div id="pricing-table" class="price-table-rich-text no-gap">
            <!-- JS will load content -->
        </div>
    </div>

</div>


<!-- ============================
     JS TAB SWITCHING
============================ -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    /* TAB BUTTONS */
    const buttons = document.querySelectorAll(".gls-tab-link");
    const table = document.getElementById("pricing-table");
    const title = document.getElementById("pricing-title");
    const subtitle = document.getElementById("pricing-subtitle");

    /* ALL PRICING DATA */
    const pricing = {

    Online: {
        title: "Online Courses",
        subtitle: "Flexible online live sessions.",
        col1: ["&zwj;", "A1 Online","A2 Online","B1 Online","B2 Online"],
        col2: ["Inscription","300 DH","300 DH","300 DH","300 DH"],
        col3: ["Mensuel","1000 DH","1000 DH","1000 DH","1000 DH"]
    },

    Casablanca: {
        title: "Casablanca Pricing",
        subtitle: "Séance from Monday to Friday",
        col1: ["&zwj;","A1","A2","B1","B2"],
        col2: ["Inscription","300 DH","300 DH","300 DH","300 DH"],
        col3: ["Mensuel","1400 DH","1400 DH","1400 DH","1400 DH"]
    },

    Marrakech: {
        title: "Marrakech Pricing",
        subtitle: "Séance from Monday to Friday",
        col1: ["&zwj;","A1","A2","B1","B2"],
        col2: ["Inscription","300 DH","300 DH","300 DH","300 DH"],
        col3: ["Mensuel","1300 DH","1300 DH","1300 DH","1300 DH"]
    },

    Rabat: {
        title: "Rabat Pricing",
        subtitle: "Séance from Monday to Friday",
        col1: ["&zwj;","A1","A2","B1","B2"],
        col2: ["Inscription","300 DH","300 DH","300 DH","300 DH"],
        col3: ["Mensuel","1400 DH","1400 DH","1400 DH","1400 DH"]
    },

    Kenitra: {
        title: "Kénitra Pricing",
        subtitle: "Séance from Monday to Friday",
        col1: ["&zwj;","A1","A2","B1","B2"],
        col2: ["Inscription","300 DH","300 DH","300 DH","300 DH"],
        col3: ["Mensuel","1300 DH","1300 DH","1300 DH","1300 DH"]
    },

    Sale: {
        title: "Salé Pricing",
        subtitle: "Séance from Monday to Friday",
        col1: ["&zwj;","A1","A2","B1","B2"],
        col2: ["Inscription","300 DH","300 DH","300 DH","300 DH"],
        col3: ["Mensuel","1300 DH","1300 DH","1300 DH","1300 DH"]
    },

    Agadir: {
        title: "Agadir Pricing",
        subtitle: "Séance from Monday to Friday",
        col1: ["&zwj;","A1","A2","B1","B2"],
        col2: ["Inscription","300 DH","300 DH","300 DH","300 DH"],
        col3: ["Mensuel","1200 DH","1200 DH","1200 DH","1200 DH"]
    },

    Exams: {
        title: "Examinations – ÖSD",
        subtitle: "",
        col1: ["&zwj;","ÖSD A1","ÖSD B1","ÖSD B2"],
        col2: ["Price","2000 DH","2300 DH","2500 DH"],
        col3: ["Level","A1","B1","B2"]
    }

};



    /* LOAD DATA INTO TABLE */
    function loadTable(tab) {
        const p = pricing[tab];

        title.textContent = p.title;
        subtitle.textContent = p.subtitle;

        table.innerHTML = `
            <div class="table-rich-text-pricing2 w-richtext">
                ${p.col1.map(v => `<p>${v}</p>`).join("")}
            </div>

            <div class="table-rich-text-pricing w-richtext text-center">
                ${p.col2.map(v => `<p>${v}</p>`).join("")}
            </div>

            <div class="table-rich-text-pricing w-richtext text-center">
                ${p.col3.map(v => `<p>${v}</p>`).join("")}
            </div>
        `;
    }

    /* DEFAULT LOADING */
    loadTable("Online");

    /* TAB SWITCHING */
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {

            buttons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            loadTable(btn.dataset.tab);
        });
    });
});
</script>

<section class="get-started-section section">
  <div class="container is-2-col-grid">
    
    {{-- ===== Left: Image Block ===== --}}
    <div class="get-started-image">
      <img 
        src="{{ asset('assets/images/about/subscribe.jpeg') }}" 
        alt="Join GLS Sprachenzentrum and start learning German" 
        class="full-image rounded-4"
        loading="lazy">
    </div>

    {{-- ===== Right: Content Block ===== --}}
    <div class="get-started-card">
      <div class="box-rich-text w-richtext">
        <h2>What Are You Waiting For?</h2>
        <h3>Start Your German Journey with GLS</h3>

        <p>
          Take the first step toward studying, working, or living in Germany.  
          <strong>GLS Sprachenzentrum</strong> helps you learn German the right way — from A1 to B2.
        </p>

        <p><strong>Your future starts now.</strong></p>
      </div>

      <a href="#" class="button w-button">Join Us Now</a>
    </div>
  </div>
</section>


@endsection
