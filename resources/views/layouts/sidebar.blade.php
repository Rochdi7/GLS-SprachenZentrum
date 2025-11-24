<!-- [ Sidebar Menu ] start -->
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('dashboard') }}" class="b-brand text-primary">
                <img src="{{ URL::asset('assets/images/logo/gls.png') }}" alt="image du logo" class="logo-lg">
                <span class="badge bg-brand-color-2 rounded-pill ms-1 theme-version">v1.0.0</span>
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                @include('layouts.menu-list')
            </ul>
            <div class="card nav-action-card bg-brand-color-4">
                <div class="card-body" style="background-image: url('/build/images/layout/nav-card-bg.svg')">
                    <h5 class="text-dark">Centre d'aide</h5>
                    <p class="text-dark text-opacity-75">Veuillez nous contacter pour toute question.</p>
                    <a href="https://Gls Team.support-hub.io/" class="btn btn-primary" target="_blank">Accéder au Centre
                        d'aide</a>
                </div>
            </div>
        </div>
        <div class="card pc-user-card">
            <div class="card-body">
                @php
                    $authUser = Auth::user();
                    $media = $authUser->getFirstMedia('profile_photo');
                @endphp

                <div class="d-flex align-items-center">
                    @php
                        $media = Auth::user()->getFirstMedia('profile_photo');
                    @endphp

                    <img src="{{ $media
                        ? route('media.custom', ['id' => $media->id, 'filename' => $media->file_name])
                        : asset('assets/images/user/avatar-2.jpg') }}"
                        alt="image utilisateur" class="user-avtar wid-45 rounded-circle" />


                    <div class="flex-grow-1 ms-3">
                        <div class="dropdown">
                            <a href="#" class="arrow-none dropdown-toggle" data-bs-toggle="dropdown"
                                aria-expanded="false" data-bs-offset="0,20">

                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 me-2">

                                        {{-- Dynamic Name --}}
                                        <h6 class="mb-0">{{ $authUser->name }}</h6>

                                        {{-- Optional: Role (change later) --}}
                                        <small>Administrateur</small>

                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="btn btn-icon btn-link-secondary avtar">
                                            <i class="ph-duotone ph-windows-logo"></i>
                                        </div>
                                    </div>
                                </div>

                            </a>

                            <div class="dropdown-menu">
                                <ul>
                                    <li>
                                        <a class="pc-user-links" href="{{ route('profile.index') }}">
                                            <i class="ph-duotone ph-user"></i>
                                            <span>Mon Compte</span>
                                        </a>
                                    </li>

                                    <li>
                                        <a class="pc-user-links" href="{{ route('logout') }}"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="ph-duotone ph-power"></i>
                                            <span>Déconnexion</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</nav>
<!-- [ Sidebar Menu ] end -->
