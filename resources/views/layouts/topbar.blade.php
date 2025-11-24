<!-- [ Header Topbar ] start -->
<header class="pc-header">
    <div class="header-wrapper">

        <div class="ms-auto">
            <ul class="list-unstyled">

                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0"
                       data-bs-toggle="dropdown" href="#" role="button"
                       aria-haspopup="false" data-bs-auto-close="outside"
                       aria-expanded="false">
                        <img src="{{ asset('assets/images/user/avatar-2.jpg') }}"
                             alt="user-image" class="user-avtar">
                    </a>

                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">

                        <div class="dropdown-header d-flex align-items-center justify-content-between">
                            <h5 class="m-0">Profile</h5>
                        </div>

                        <div class="dropdown-body">
                            <ul class="list-group list-group-flush w-100">

                                <!-- User Info -->
                                <li class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('assets/images/user/avatar-2.jpg') }}"
                                             alt="user-image" class="wid-50 rounded-circle">

                                        <div class="ms-3">
                                            <h5 class="mb-0">{{ Auth::user()->name ?? 'Admin User' }}</h5>
                                            <a class="link-primary" href="mailto:{{ Auth::user()->email }}">
                                                {{ Auth::user()->email }}
                                            </a>
                                        </div>
                                    </div>
                                </li>

                                <!-- Change Password -->
                                <li class="list-group-item">
                                    <a href="#" class="dropdown-item">
                                        <span class="d-flex align-items-center">
                                            <i class="ph-duotone ph-key"></i>
                                            <span class="ms-2">Change Password</span>
                                        </span>
                                    </a>
                                </li>

                                <!-- Edit Profile -->
                                <li class="list-group-item">
                                    <a href="#" class="dropdown-item">
                                        <span class="d-flex align-items-center">
                                            <i class="ph-duotone ph-user-circle"></i>
                                            <span class="ms-2">Edit Profile</span>
                                        </span>
                                    </a>
                                </li>

                                <!-- Logout -->
                                <li class="list-group-item">
                                    <a href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                       class="dropdown-item">
                                        <span class="d-flex align-items-center">
                                            <i class="ph-duotone ph-power"></i>
                                            <span class="ms-2">Logout</span>
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
