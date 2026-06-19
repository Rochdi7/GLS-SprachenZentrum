<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('build/fonts/tabler-icons.min.css') }}">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        .auth-password-field {
            position: relative;
        }

        .auth-password-field .form-control {
            padding-right: 3.25rem;
        }

        .auth-password-field .form-control.is-invalid,
        .auth-password-field .was-validated .form-control:invalid {
            padding-right: 5rem;
            background-position: right calc(2.5rem + 0.375em) center;
        }

        .auth-password-toggle {
            position: absolute;
            inset-inline-end: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 0;
            background: transparent;
            color: #6c757d;
            z-index: 4;
        }

        .auth-password-toggle:focus-visible {
            outline: 2px solid rgba(52, 124, 242, 0.35);
            outline-offset: 2px;
            border-radius: 999px;
        }

        @media (max-width: 575.98px) {
            .auth-password-field .form-control {
                min-height: 48px;
                padding-right: 3rem;
            }

            .auth-password-field .form-control.is-invalid,
            .auth-password-field .was-validated .form-control:invalid {
                padding-right: 4.5rem;
                background-position: right calc(2.2rem + 0.375em) center;
            }

            .auth-password-toggle {
                inset-inline-end: 0.625rem;
                width: 1.875rem;
                height: 1.875rem;
            }
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
                var input = document.getElementById(button.getAttribute('data-password-toggle'));
                var icon = button.querySelector('i');

                if (!input || !icon) {
                    return;
                }

                button.addEventListener('click', function () {
                    var isHidden = input.type === 'password';

                    input.type = isHidden ? 'text' : 'password';
                    icon.classList.toggle('ti-eye', isHidden);
                    icon.classList.toggle('ti-eye-off', !isHidden);
                    button.setAttribute('aria-pressed', String(isHidden));
                    button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                });
            });
        });
    </script>
</body>
</html>
