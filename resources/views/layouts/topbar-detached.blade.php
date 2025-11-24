<!-- [ Header Topbar ] start -->
<header class="pc-header">
    <div class="m-header">
        <a href="{{ route('dashboard') }}" class="b-brand text-primary">
            <!-- ========   Change your logo from here   ============ -->
            <img src="{{ URL::asset('build/images/assets/images/logo/gls.png') }}" alt="logo image" class="logo-lg" />
            <span class="badge bg-brand-color-2 rounded-pill ms-1 theme-version">v1.0.0</span>
        </a>
    </div>
    <div class="header-wrapper"> @include('layouts.topbar-d')</div>
</header>
<!-- [ Header ] end -->
