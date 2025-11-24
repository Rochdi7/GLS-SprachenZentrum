<!-- [ Header Topbar ] start -->
<header class="pc-header">
    <div class="header-wrapper">

        <div class="ms-auto">
            <ul class="list-unstyled">

                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false">
                        @php
                            $media = Auth::user()->getFirstMedia('profile_photo');
                        @endphp

                        <img src="{{ $media
                            ? route('media.custom', ['id' => $media->id, 'filename' => $media->file_name])
                            : asset('assets/images/user/avatar-2.jpg') }}"
                            alt="image-utilisateur" class="user-avtar rounded-circle">



                    </a>

                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">

                        <div class="dropdown-header d-flex align-items-center justify-content-between">
                            <h5 class="m-0">Profil</h5>
                        </div>

                        <div class="dropdown-body">
                            <ul class="list-group list-group-flush w-100">

                                <li class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        @php
                                            $user = Auth::user();
$media = $user?->getFirstMedia('profile_photo');
                                        @endphp

                                        <img class="rounded-circle img-fluid wid-90 img-thumbnail"
                                            src="{{ $media
                                                ? route('media.custom', ['id' => $media->id, 'filename' => $media->file_name])
                                                : URL::asset('build/images/user/avatar-1.jpg') }}"
                                            alt="Image utilisateur" />

                                        <div class="ms-3">
                                            <h5 class="mb-0">{{ Auth::user()->name ?? 'Administrateur' }}</h5>
                                            <a class="link-primary" href="mailto:{{ Auth::user()->email }}">
                                                {{ Auth::user()->email }}
                                            </a>
                                        </div>
                                    </div>
                                </li>

                                <li class="list-group-item">
                                    <a href="#" class="dropdown-item">
                                        <span class="d-flex align-items-center">
                                            <i class="ph-duotone ph-key"></i>
                                            <span class="ms-2">Changer le mot de passe</span>
                                        </span>
                                    </a>
                                </li>

                                <li class="list-group-item">
                                    <a href="{{ route('profile.index') }}" class="dropdown-item">
                                        <span class="d-flex align-items-center">
                                            <i class="ph-duotone ph-user-circle"></i>
                                            <span class="ms-2">Modifier le profil</span>
                                        </span>
                                    </a>
                                </li>

                                <li class="list-group-item">
                                    <a href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                        class="dropdown-item">
                                        <span class="d-flex align-items-center">
                                            <i class="ph-duotone ph-power"></i>
                                            <span class="ms-2">Déconnexion</span>
                                        </span>
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>

                            </ul>
                        </div>
                    </div>

                </li>
            </ul>
        </div>

    </div>
</header>
<!-- [ Header ] end -->
