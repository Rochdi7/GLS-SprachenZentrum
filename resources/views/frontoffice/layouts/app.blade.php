<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>GLS Sprachenzentrum – Learning Center Morocco</title>
    <link rel="stylesheet" href="{{ asset('assets/css/frontoffice/style.css') }}">

    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Outfit:wght@800;900&display=swap"
        rel="stylesheet">

</head>

<body>

    {{-- Include the header partial --}}
    @include('frontoffice.partials.header')

    <main>
        {{-- Main content will be injected here by child views --}}
        @yield('content')
    </main>

    <script>
        // mobile drawer interactions
        const burger = document.getElementById('burger');
        const drawer = document.getElementById('mobile-drawer');
        const backdrop = document.getElementById('backdrop');

        function openDrawer(open) {
            drawer.classList.toggle('open', open);
            backdrop.classList.toggle('show', open);
            burger.setAttribute('aria-expanded', String(open));
            document.body.style.overflow = open ? 'hidden' : '';
        }

        burger.addEventListener('click', () => {
            openDrawer(!drawer.classList.contains('open'));
        });
        backdrop.addEventListener('click', () => openDrawer(false));
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') openDrawer(false);
        });
    </script>

</body>

</html>
