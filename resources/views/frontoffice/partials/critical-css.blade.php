{{-- =========================================================
     CRITICAL ABOVE-THE-FOLD CSS (inlined in <head>)
     Mirrors the relevant rules from base.css / header.css / home/hero.css so the
     header + hero + LCP title render on the first HTML payload, WITHOUT waiting for
     the deferred main stylesheet. Visual values are copied verbatim — do not redesign.
     If you change hero/header/badge styling in the source CSS, mirror it here too.
========================================================= --}}
<style id="gls-critical-css">
/* Local "Now" font (used by hero title / headings — the LCP text). */
@font-face{font-family:"Now";src:url("{{ asset('assets/fonts/Now-Medium.otf') }}") format("opentype");font-weight:500;font-style:normal;font-display:swap}
@font-face{font-family:"Now";src:url("{{ asset('assets/fonts/Now-Bold.otf') }}") format("opentype");font-weight:700;font-style:normal;font-display:swap}
@font-face{font-family:"Now";src:url("{{ asset('assets/fonts/Now-Regular.otf') }}") format("opentype");font-weight:400;font-style:normal;font-display:swap}

:root{
--color-primary:#2563eb;--color-secondary:#007b3d;--color-tertiary:#fb923c;--color-accent:#7c3aed;
--color-bg:#ffffff;--color-surface:#fffee8;--color-text:#1c1c1a;--color-border:#e6e2c5;--color-dark:#111;
--light--off-white:#fffff9;--off-white:#fffee8;--dark--off-white:#bcbbaa;--off-black:#211e1d;--light--off-black:#3e3832;--dark--off-black:#181615;
--green:#009d5a;--dark--green:#007342;--light--green:#00d98a;
--blue:#1c45db;--light--blue:#3577f4;--dark--blue:#1533a1;
--orange:#ff7a08;--light--orange:#ffb90e;--dark--orange:#bc5a06;
--yellow:#fc0;--light--yellow:#fff400;--dark--yellow:#bc9600;
--purple:#9767f8;--light--purple:#d5a4fe;--dark--purple:#704cb6;
--font-sans:"Inter",system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
--fw-light:300;--fw-regular:400;--fw-medium:500;--fw-semibold:600;--fw-bold:700;--fw-black:900;
--container-max:1320px;
}
*,*::before,*::after{box-sizing:border-box}
html,body{font-family:Now,sans-serif;margin:0;padding:0;scroll-behavior:smooth;overflow-x:hidden;width:100%}
body{color:var(--color-text);background-color:var(--color-bg);line-height:1.5;font-size:16px;-webkit-font-smoothing:antialiased}
h1,h2,h3,h4{font-family:"Now",sans-serif;color:var(--color-text);margin:0 0 1rem;line-height:1.2}
a{color:inherit;text-decoration:none}
main{display:block}

/* Header (sticky, above-the-fold) */
.site-header{width:100%;background:var(--color-surface);position:sticky;top:0;z-index:999;box-shadow:0 1px 0 rgba(0,0,0,.05)}
.nav-wrap{max-width:var(--container-max);margin:0 auto;padding:16px 24px;display:flex;align-items:center;justify-content:space-around;gap:12px}
.brand{color:var(--color-text);text-decoration:none;white-space:nowrap;display:inline-flex;align-items:center}
.menu-toggle{cursor:pointer}

/* Hero (LCP block) */
.hero{position:relative;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;background:none;height:auto;min-height:1000px;margin-bottom:-64px;padding-top:4rem;padding-bottom:12rem;overflow:hidden;margin-top:-28px;z-index:1}
.hero__bg{position:absolute;inset:0;background-repeat:no-repeat;background-size:cover;background-position:50% 12%;z-index:0;filter:brightness(.94) contrast(1.04);transform:scale(1.02)}
.hero__bg::before{content:"";position:absolute;inset:0;z-index:1;background:linear-gradient(to bottom,rgba(20,20,22,.92) 0%,rgba(20,20,22,.85) 25%,rgba(20,20,22,.4) 45%,rgba(20,20,22,0) 100%);pointer-events:none}
.hero__inner{position:relative;z-index:2;max-width:1100px;padding:0 20px}
.hero-title{font-family:Now,sans-serif;color:var(--color-light,#f8fff8);font-weight:500;line-height:.9;letter-spacing:-.5px;text-shadow:0 2px 16px rgba(0,0,0,.30);font-size:clamp(56px,7vw,120px);margin:0 auto;filter:drop-shadow(0 2px 16px #0000004d)}

/* Hero badges */
.badge{position:absolute;border-radius:10000px;padding:.6rem 1.5rem;font-size:1.6rem;line-height:1;white-space:nowrap;box-shadow:0 10px 24px rgba(0,0,0,.18),inset 0 -1px 0 rgba(255,255,255,.15);z-index:3}
.b-blue{background-color:var(--blue);color:#fff;left:6%;top:33%;z-index:4}
.b-green{background-color:var(--green);color:#fff;left:8%;bottom:45%;z-index:3}
.b-orange{background-color:var(--dark--orange);color:#fff;right:6%;top:30%;z-index:4}
.b-violet{background-color:var(--purple);color:#fff;right:10%;bottom:47%;z-index:4}

@media (max-width:992px){
.hero{min-height:700px;padding-top:3rem;padding-bottom:8rem;margin-bottom:-32px;margin-top:0}
.hero__bg{background-position:50% 22%;transform:scale(1.03)}
.hero-title{font-size:clamp(2.4rem,6vw,5rem)}
.badge{font-size:1.2rem;padding:.45rem 1.2rem;transform:scale(.9)}
}
@media (max-width:600px){
.hero{min-height:100svh;padding-top:1.2rem;padding-bottom:6.5rem;margin:0}
.hero__bg{background-size:250%;background-position:50% 50%;transform:none}
}
@media (max-width:767px){
.hero{justify-content:flex-start}
.hero__inner{position:absolute;left:0;right:0;top:calc(env(safe-area-inset-top,0px) + 88px);padding:0 18px;z-index:5;max-width:1100px;margin:0 auto}
.hero .hero-title{margin:0;line-height:1.05;font-size:clamp(30px,7.2vw,44px);text-wrap:balance}
}

/* Burger visible before main CSS loads (Bootstrap utility classes deferred) */
.menu-toggle.d-flex{display:flex}
@media (min-width:992px){.menu-toggle.d-lg-none{display:none}.nav.d-lg-flex,.nav-language.d-lg-flex{display:flex}}
@media (max-width:991.98px){.nav.d-none,.nav-language.d-none{display:none}}
</style>
