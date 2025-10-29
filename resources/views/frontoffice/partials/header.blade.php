{{-- The Header and Mobile Drawer --}}

<header class="site-header">
  <div class="nav-wrap">
    <a class="brand" href="#">GLS SprachenZentrum</a>

    <nav class="nav" aria-label="Primary Navigation">
      <button>About GLS <span class="caret"></span></button>
      <button>German Courses <span class="caret"></span></button>
      <button>Exams <span class="caret"></span></button>
      <button>Resources <span class="caret"></span></button>
      <a href="#">Locations in Morocco</a>
    </nav>

    <div class="right">
      <span class="chip chip--dark">EN</span>
      <span class="chip">DE</span>
      <a class="btn" href="#">Enroll Now</a>
    </div>

    <div class="menu-toggle" id="burger" aria-label="Open mobile menu" aria-expanded="false" aria-controls="mobile-drawer">
      <span></span><span></span><span></span>
    </div>
  </div>
</header>

<div class="drawer" id="mobile-drawer" role="dialog" aria-modal="true" aria-label="Mobile Menu">
  <div class="drawer-inner">
    <div class="row">
      <span class="label">About GLS</span>
      <span class="tiny-caret"></span>
    </div>
    <div class="row">
      <span class="label">German Courses</span>
      <span class="tiny-caret"></span>
    </div>
    <div class="row">
      <span class="label">Exams</span>
      <span class="tiny-caret"></span>
    </div>
    <div class="row">
      <span class="label">Resources</span>
      <span class="tiny-caret"></span>
    </div>
    <div class="row">
      <span class="label">Locations in Morocco</span>
      <span class="note"></span>
    </div>

    <div class="langs">
      <span class="chip chip--dark">EN</span>
      <span class="chip">DE</span>
    </div>
    <div class="cta">
      <a class="btn" href="#">Enroll Now</a>
    </div>
  </div>
</div>

<div class="backdrop" id="backdrop"></div>
