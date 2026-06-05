@php
    $isAssociate = auth()->guard('associate')->check();

    $currentUser = $isAssociate
        ? auth()->guard('associate')->user()
        : auth()->user();

    $profilePhoto = $isAssociate
        ? ($currentUser->photo ? getFileUrl($currentUser->photo) : asset('assets/images/user2-160x160.jpg'))
        : ($currentUser->profile_image ? getFileUrl($currentUser->profile_image) : asset('assets/images/user2-160x160.jpg'));

    $userName = $isAssociate
        ? ($currentUser->associate_name ?? 'Associate')
        : ($currentUser->name ?? 'Admin');

    $userRole = $isAssociate ? 'Associate' : 'Administrator';
@endphp

<nav class="app-header navbar navbar-expand header-navbar">
    <div class="container-fluid px-4">

        {{-- Left --}}
        <div class="d-flex align-items-center gap-3">

            <a class="header-icon-btn" data-lte-toggle="sidebar" href="#" role="button">
                <i class="bi bi-list"></i>
            </a>

            <div class="d-none d-md-block">
                <h6 class="fw-bold mb-0 text-dark">
                    Real Estate Management Software
                </h6>
                <small class="text-muted">
                    Welcome Back
                </small>
            </div>

        </div>

        {{-- Right --}}
        <ul class="navbar-nav ms-auto align-items-center gap-3">

            {{-- Date --}}
            <li class="nav-item d-none d-lg-block">
                <div class="header-date-box">
                    <div class="fw-bold text-dark">
                        {{ now()->format('d M Y') }}
                    </div>
                    <small class="text-muted">
                        {{ now()->format('l') }}
                    </small>
                </div>
            </li>

            {{-- Notification --}}
            {{-- <li class="nav-item">
                <a href="#" class="header-icon-btn position-relative">
                    <i class="bi bi-bell"></i>

                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger header-badge">
                        3
                    </span>
                </a>
            </li> --}}

            {{-- User Dropdown --}}
            <li class="nav-item dropdown user-menu">

                <a href="#" class="nav-link header-user-box d-flex align-items-center"
                    data-bs-toggle="dropdown">

                    <img src="{{ $profilePhoto }}"
                        class="header-user-img"
                        alt="User Image">

                    <div class="ms-2 d-none d-md-block text-start">
                        <div class="fw-bold text-dark lh-sm">
                            {{ $userName }}
                        </div>

                        <small class="text-muted">
                            {{ $userRole }}
                        </small>
                    </div>

                    <i class="bi bi-chevron-down ms-2 text-muted small"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end header-dropdown shadow-lg border-0 p-0">

                    <li class="header-dropdown-top text-center">
                        <img src="{{ $profilePhoto }}"
                            class="rounded-circle border border-3 border-white shadow mb-2"
                            width="76"
                            height="76"
                            alt="User Image">

                        <h6 class="fw-bold mb-1 text-white">
                            {{ $userName }}
                        </h6>

                        <small class="text-white-50">
                            {{ $isAssociate ? 'ID: ' . ($currentUser->associate_id ?? '') : ($currentUser->email ?? '') }}
                        </small>
                    </li>

                    <li>
                        <a href="{{ $isAssociate ? route('associate-panel.view-profile') : route('profile') }}"
                            class="dropdown-item header-dropdown-item">
                            <i class="bi bi-person"></i>
                            Manage Profile
                        </a>
                    </li>

                    <li>
                        <a href="{{ $isAssociate ? route('associate-panel.change-password') : route('change-password') }}"
                            class="dropdown-item header-dropdown-item">
                            <i class="bi bi-shield-lock"></i>
                            Change Password
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider m-0">
                    </li>

                    <li>
                        <form action="{{ $isAssociate ? route('associate-panel.logout') : route('logout') }}"
                            method="POST">
                            @csrf

                            <button type="submit" class="dropdown-item header-dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right"></i>
                                Sign out
                            </button>
                        </form>
                    </li>

                </ul>
            </li>

        </ul>

    </div>
</nav>