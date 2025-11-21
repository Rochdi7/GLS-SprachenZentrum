<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>GLS Sprachenzentrum – Learning Center Morocco</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />

    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/frontoffice/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/frontoffice/footer.css') }}">
<link rel="preload" href="/assets/fonts/Now-Regular.otf" as="font" type="font/otf" crossorigin>
<link rel="preload" href="/assets/fonts/Now-Bold.otf" as="font" type="font/otf" crossorigin>

    <!-- Font (Now Sans) -->
    <link href="https://fonts.cdnfonts.com/css/now" rel="stylesheet">

</head>

<body>
    {{-- Header --}}
    @include('frontoffice.partials.header')

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('frontoffice.partials.footer')
<!-- GLS ENROLL MODAL -->
<div class="modal fade" id="glsEnrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden;">

            <!-- HEADER -->
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Inscription GLS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body p-4">
                @include('frontoffice.templates.gls-form')
            </div>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // TOP-LEVEL MENUS (About, German Courses, etc.)
    document.querySelectorAll(".menu-item > .menu-label:not(.submenu-trigger)").forEach(topLabel => {
        topLabel.addEventListener("click", function () {
            const parentItem = this.closest(".menu-item");

            // CLOSE all other top-level menu-items
            document.querySelectorAll(".menu-item").forEach(item => {
                if (item !== parentItem) {
                    item.classList.remove("open");
                }
            });

            // COLLAPSE all sub-submenus (like Our Sites)
            document.querySelectorAll(".sub-submenu").forEach(sub => {
                sub.style.maxHeight = "0px";
            });

            // Remove .open from all submenu-triggers
            document.querySelectorAll(".submenu-trigger").forEach(btn => {
                btn.classList.remove("open");
            });

            // Toggle open state for clicked item
            parentItem.classList.toggle("open");
        });
    });


    // INNER SUBMENU TRIGGERS (like "Our Sites")
    document.querySelectorAll(".submenu-trigger").forEach(trigger => {
        trigger.addEventListener("click", function (e) {
            e.stopPropagation(); // don’t trigger parent open

            const submenu = this.nextElementSibling;

            // Close all other sub-submenus
            document.querySelectorAll(".sub-submenu").forEach(s => {
                if (s !== submenu) {
                    s.style.maxHeight = "0px";
                }
            });

            // Remove .open from all submenu-triggers except this
            document.querySelectorAll(".submenu-trigger").forEach(btn => {
                if (btn !== this) {
                    btn.classList.remove("open");
                }
            });

            // Toggle this submenu
            if (submenu.style.maxHeight && submenu.style.maxHeight !== "0px") {
                submenu.style.maxHeight = "0px";
                this.classList.remove("open");
            } else {
                submenu.style.maxHeight = submenu.scrollHeight + "px";
                this.classList.add("open");
            }
        });
    });


    // BURGER & DRAWER TOGGLE
    const burger = document.getElementById('burger');
    const drawer = document.getElementById('mobile-drawer');
    const backdrop = document.getElementById('backdrop');

    if (burger && drawer && backdrop) {
        burger.addEventListener('click', () => {
            drawer.classList.toggle('open');
            backdrop.classList.toggle('active');
        });

        backdrop.addEventListener('click', () => {
            drawer.classList.remove('open');
            backdrop.classList.remove('active');
        });
    }

});
</script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const header = document.querySelector('.site-header');
    const stickyOffset = header.offsetTop;

    window.addEventListener('scroll', function () {
      if (window.pageYOffset > stickyOffset) {
        header.classList.add('is-fixed');
      } else {
        header.classList.remove('is-fixed');
      }
    });
  });
</script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RZsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</body>

</html>
