<!-- [ breadcrumb ] start -->
@php
    $breadcrumbItem = trim($__env->yieldContent('breadcrumb-item'));
    $breadcrumbItemActive = trim($__env->yieldContent('breadcrumb-item-active'));
    $homeAliases = ['accueil', 'home', 'dashboard', 'tableau de bord'];
    $isDuplicateHome = $breadcrumbItem !== '' && in_array(mb_strtolower($breadcrumbItem), $homeAliases, true);
@endphp
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                    @if ($breadcrumbItem !== '' && ! $isDuplicateHome)
                        <li class="breadcrumb-item"><a href="@yield('breadcrumb-item-link', 'javascript: void(0)')">{{ $breadcrumbItem }}</a></li>
                    @endif
                    <li class="breadcrumb-item" aria-current="page">{{ $breadcrumbItemActive }}</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">{{ $breadcrumbItemActive }}</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ breadcrumb ] end -->
