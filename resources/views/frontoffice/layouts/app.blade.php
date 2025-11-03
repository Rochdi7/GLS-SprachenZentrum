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

    <!-- Scripts -->
    <script>
        document.querySelectorAll('.menu-label').forEach(btn => {
            btn.addEventListener('click', () => {
                const item = btn.closest('.menu-item');
                item.classList.toggle('open');
            });
        });

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
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RZsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</body>

</html>
