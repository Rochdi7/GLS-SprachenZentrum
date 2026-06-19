<!DOCTYPE html>
<html lang="en">

<head>
    <title>@yield('title') | Code Sommet Laravel 11 Admin & Dashboard Template</title>
    <!-- [Meta] -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description"
        content="Code Sommet is trending dashboard template made using Laravel 11 &  Bootstrap 5 design framework. Code Sommet is available in Bootstrap, React, CodeIgniter, Angular,  and .net Technologies.">
    <meta name="keywords"
        content="Laravel 11 Bootstrap admin template, Dashboard UI Kit, Dashboard Template, Backend Panel, react dashboard, angular dashboard">
    <meta name="author" content="Gls Team">

    <!-- [Favicon] icon -->
    <link rel="icon" href="{{ URL::asset('assets/images/favicon/favicon.svg') }}" type="image/x-icon">
    @yield('css')

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

    @include('layouts.head-css')
</head>

<body data-pc-preset="preset-5" data-pc-sidebar-theme="light" data-pc-sidebar-caption="true" data-pc-direction="ltr"
    data-pc-theme="light">

    @include('layouts.loader')

    @if (View::hasSection('auth-v2'))
        <div class="auth-main v2">
            <div class="bg-overlay bg-dark"></div>
            <div class="auth-wrapper">
                <div class="auth-sidecontent">
                    @include('layouts.authFooter')
                </div>
            @else
                <div class="auth-main v1">
                    <div class="auth-wrapper">
    @endif
    @yield('content')
    @if (!View::hasSection('auth-v2'))
        @include('layouts.authFooter')
    @endif
    </div>
    </div>
    @if (View::hasSection('auth-v2'))
        </div>
    @endif
    @include('layouts.customizer')

    @include('layouts.footerjs')

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
                    button.setAttribute('aria-label', isHidden ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
                });
            });
        });
    </script>

    @yield('scripts')
</body>

</html>
