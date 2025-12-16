@extends('frontoffice.layouts.app')

@section('title', 'À propos | GLS Sprachenzentrum')

<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/about/about.css') }}">

@section('content')

{{-- =========================
   🔵 HERO SECTION
========================= --}}
<section class="hero-section section about-hero reveal delay-1">
  <div class="container is-hero reveal">
    <div class="hero_subtitle reveal delay-2 fade-blur-title">Nous prenons l’allemand au sérieux !</div>

    <h1 class="hero_title reveal delay-3 fade-blur-title">
      GLS Sprachenzentrum – L’école de langue allemande au Maroc
    </h1>

    <div class="hero-image reveal delay-2">
      <img 
        src="{{ asset('assets/images/about/Centre-GLS-de-langue-Allemande.jpg') }}" 
        alt="Étudiants apprenant l’allemand au GLS Sprachenzentrum" 
        class="full-image" 
        loading="lazy"
      >
    </div>
  </div>
</section>

{{-- =========================
   🟢 INTRO SECTION
========================= --}}
<section class="rich-text-section section reveal">
  <div class="container reveal delay-1">
    <div class="rich-text w-richtext reveal delay-2">

      <h2 class="fade-blur-title">Apprenez l’allemand avec les experts au Maroc</h2>
      <p>
        Bienvenue au <strong>GLS Sprachenzentrum</strong> — le centre leader de l’apprentissage de l’allemand au Maroc.  
        Avec des centres à <strong>Marrakech, Rabat, Kénitra, Salé, Casablanca</strong> et <strong>Agadir</strong>,  
        nous offrons un environnement moderne, dynamique et immersif pour garantir une progression rapide.
      </p>
      <p>
        Notre mission est de rendre l’allemand accessible à tous grâce à un enseignement de haute qualité,  
        axé sur la communication et la pratique réelle.  
        Que ce soit pour vos études, votre carrière ou votre projet en Allemagne — GLS vous accompagne step by step.
      </p>

      <h2 class="fade-blur-title">Cours Intensifs & Cours en Ligne</h2>
      <h3 class="fade-blur-title">Apprenez où que vous soyez au Maroc</h3>
      <p>
        Les <strong>cours intensifs</strong> de GLS offrent un apprentissage structuré avec une forte immersion.  
        Grâce à la pratique quotidienne, vous progressez rapidement en expression et compréhension.
      </p>
      <p>
        Pour ceux qui ne peuvent pas se déplacer, nos <strong>cours en ligne</strong> reproduisent l’ambiance de classe GLS :  
        interactifs, flexibles et personnalisés.
      </p>

    </div>
  </div>
</section>

{{-- =========================
   🟡 2-COL GRID SECTION
========================= --}}
<section class="section is-off-white reveal">

  {{-- ===== FIRST ROW ===== --}}
  <div class="container is-2-col-grid reveal delay-1">
    <div class="get-started-contents reveal delay-2">
      <div class="box-rich-text w-richtext">
        <h2 class="fade-blur-title">Des prix justes et accessibles</h2>
        <h3 class="fade-blur-title">Une éducation allemande de qualité pour tous</h3>
        <p>
          Au <strong>GLS Sprachenzentrum</strong>, nous croyons que l’allemand doit être accessible à tous.  
          Nos tarifs sont transparents, nos horaires flexibles et notre qualité d’enseignement reste une priorité.
        </p>
        <p>
          Nous proposons également des <strong>séances d’essai</strong> et des tests de niveau afin de vous orienter vers la bonne classe.  
          Découvrez la différence GLS — où excellence et progression se rencontrent.
        </p>
      </div>
    </div>

    <div class="image-block reveal delay-3">
      <img
        src="{{ asset('assets/images/about/grid-1.png') }}"
        alt="Étudiants du GLS Sprachenzentrum en classe"
        class="full-image"
        loading="lazy">
    </div>
  </div>

  {{-- ===== SECOND ROW ===== --}}
  <div class="container is-2-col-grid reveal delay-1">
    <div class="image-block is-1-1 reveal delay-2">
      <img
        src="{{ asset('assets/images/about/grid1.jpeg') }}"
        alt="Étudiants au GLS Café se relaxant entre les cours"
        class="full-image is-ratio"
        loading="lazy">
    </div>

    <div class="get-started-contents reveal delay-3">
      <div class="box-rich-text w-richtext">
        <h2 class="fade-blur-title">Plus qu’une école — une communauté</h2>
        <p>
          Chaque centre GLS est un espace dédié à <strong>l’apprentissage, la communication et la culture</strong>.  
          Entre les cours, les étudiants profitent d’un cadre convivial pour pratiquer l’allemand naturellement.
        </p>
        <p>
          Nos salles sont modernes, équipées et connectées, et nos enseignants privilégient une approche interactive.  
          Chez GLS, vous n’apprenez pas seulement l’allemand — vous le vivez.
        </p>
      </div>
    </div>
  </div>

</section>

{{-- =========================
   🟣 EXAMS SECTION
========================= --}}
<section class="rich-text-section section reveal">
  <div class="container reveal delay-1">
    <div class="rich-text w-richtext reveal delay-2">
      <h2 class="fade-blur-title">Examens & Certifications</h2>
      <h3 class="fade-blur-title">Une nouvelle étape pour l’allemand au Maroc</h3>
      <p>
        <strong>GLS Sprachenzentrum</strong> est fier d’introduire au Maroc les examens allemands officiels.  
        En plus de nos cours, nous proposerons bientôt les examens <strong>GLS</strong> et <strong>ÖSD</strong> —  
        permettant aux étudiants de certifier leur niveau locallement.
      </p>
      <p>
        Nos programmes couvrent les niveaux <strong>A1 à B2</strong>, offrant une base solide pour réussir vos projets  
        académiques, professionnels ou personnels.
      </p>
      <p>
        Nos professeurs sont certifiés, expérimentés et accompagnent chaque étudiant pas à pas jusqu’à la réussite.
      </p>
      <p>
        Rejoignez GLS et soyez parmi les premiers au Maroc à passer les <strong>examens officiels allemands</strong> — bientôt disponibles !
      </p>
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
      <img 
        src="{{ asset('assets/images/about/subscribe.jpeg') }}" 
        alt="Étudiants souriant au GLS Sprachenzentrum" 
        class="full-image rounded-4"
        loading="lazy">
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
          Notre équipe est là pour vous guider étape par étape — vers vos objectifs linguistiques et professionnels.
        </p>
      </div>

      <a href="{{ route('front.intensive-courses') }}" class="button w-button">En savoir plus</a>
    </div>
  </div>
</section>

{{-- =========================
   🔵 CONTACT SECTION
========================= --}}
<section class="contact-section section reveal">
  <div class="container is-2-col-grid reveal delay-1">

    {{-- LEFT --}}
    <div class="div-block-5-copy reveal delay-2">
      <h2 class="h-section-subtitle fade-blur-title">Des questions ?<br>Contactez-nous !</h2>

      <div class="div-block-21">
        <a href="tel:+212669515019" class="link-block">
          <div class="text-block-3">
            <span class="text-span">APPELEZ-NOUS<br></span>+212 6 69 51 50 19
          </div>
        </a>
        <a href="mailto:info@glssprachenzentrum.ma" class="link-block-2">
          <div class="text-block-3">
            <span class="text-span">ENVOYEZ-NOUS UN EMAIL<br></span>info@glssprachenzentrum.ma
          </div>
        </a>
      </div>

      <div class="text-block-3 visit-block">
        <span class="text-span">VISITEZ-NOUS<br></span>
        14 Bd de Paris, 1er étage N°8, Casablanca 20000<br>
        Avenue Yacoub El Mansour, 3ème étage Bureau 28, Marrakech<br>
        Avenue Fal Ould Oumeir, 1er étage N°1, Agdal, Rabat<br>
        Avenue Mohammed V, Bureaux Rania, 7ème étage, Kénitra<br>
        Avenue Mohamed V Rue Halima N°12 Diyar, Salé<br>
        Av. Massoude Al Wafkaoui, Place des taxis, Hay Essalam, Agadir
      </div>

      <div class="footer-socials-block">
        <div class="text-block-3"><span class="text-span">SUIVEZ-NOUS</span></div>
        <div class="div-block-20">
          <a href="#" class="footer-social-link ig"><i class="bi bi-instagram"></i></a>
          <a href="#" class="footer-social-link fb"><i class="bi bi-facebook"></i></a>
          <a href="#" class="footer-social-link yt"><i class="bi bi-youtube"></i></a>
          <a href="#" class="footer-social-link wa"><i class="bi bi-whatsapp"></i></a>
        </div>
      </div>
    </div>

    {{-- RIGHT MAP --}}
    <a href="https://maps.app.goo.gl/g4PjrPB7wHQAqrSZA" target="_blank" class="div-block-7 reveal delay-3">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3331.744621379457!2d-6.836039!3d33.978558!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda76b6d63b66b1d%3A0x3c6ee0a64f273aa2!2sAgdal%2C%20Rabat!5e0!3m2!1sfr!2sma!4v1700000000000"
        allowfullscreen
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </a>

  </div>
</section>

@endsection
