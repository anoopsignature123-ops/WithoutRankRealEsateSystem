@php
    $isAssociate = auth()->guard('associate')->check();
    $isCustomer = auth()->guard('customer')->check();

    if ($isAssociate) {
        $currentUser = auth()->guard('associate')->user();
    } elseif ($isCustomer) {
        $currentUser = auth()->guard('customer')->user();
    } else {
        $currentUser = auth()->user();
    }
    if ($isAssociate) {
        $profilePhoto = $currentUser->photo
            ? getFileUrl($currentUser->photo)
            : asset('assets/images/user2-160x160.jpg');
    } elseif ($isCustomer) {
        $profilePhoto = $currentUser->primaryDocument?->profile_picture
            ? getFileUrl($currentUser->primaryDocument->profile_picture)
            : asset('assets/images/user2-160x160.jpg');
    } else {
        $profilePhoto = $currentUser?->profile_image
            ? getFileUrl($currentUser->profile_image)
            : asset('assets/images/user2-160x160.jpg');
    }

    if ($isAssociate) {
        $userName = $currentUser->associate_name ?? 'Associate';
        $userRole = 'Associate';
        $profileRoute = route('associate-panel.view-profile');
        $passwordRoute = route('associate-panel.change-password');
        $logoutRoute = route('associate-panel.logout');
        $headerTitle = 'Real Estate Management Software';
        $headerSubtitle = 'Associate / Agent Panel';
        $dropdownInfo = 'ID: ' . ($currentUser->associate_id ?? '');
    } elseif ($isCustomer) {
        $userName = $currentUser->primaryDetail?->name
            ?? $currentUser->customer_name
            ?? 'Customer';

        $userRole = 'Customer';
        $profileRoute = route('customer-panel.manage-profile');
        $passwordRoute = '#';
        $logoutRoute = route('customer-panel.logout');
        $headerTitle = 'Real Estate Management Software';
        $headerSubtitle = 'Customer Panel';
        $dropdownInfo = 'ID: ' . ($currentUser->customer_code ?? '');
    } else {
        $userName = $currentUser->name ?? 'Admin';
        $userRole = 'Administrator';
        $profileRoute = route('profile');
        $passwordRoute = route('change-password');
        $logoutRoute = route('logout');
        $headerTitle = 'Real Estate Management Software';
        $headerSubtitle = 'Admin Panel';
        $dropdownInfo = $currentUser->email ?? '';
    }
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
                    {{ $headerTitle }}
                </h6>
                <small class="text-muted">
                    {{ $headerSubtitle }}
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
            {{-- User Dropdown --}}
            <li class="nav-item dropdown user-menu">

                <a href="#" class="nav-link header-user-box d-flex align-items-center"
                    data-bs-toggle="dropdown">

                    <img src="{{ $profilePhoto }}"
                        class="header-user-img"
                        alt="User Image"
                        onerror="this.onerror=null;this.src='{{ asset('assets/images/user2-160x160.jpg') }}';">

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
                            alt="User Image"
                            onerror="this.onerror=null;this.src='{{ asset('assets/images/user2-160x160.jpg') }}';">

                        <h6 class="fw-bold mb-1 text-white">
                            {{ $userName }}
                        </h6>

                        <small class="text-white-50">
                            {{ $dropdownInfo }}
                        </small>
                    </li>

                    <li>
                        <a href="{{ $profileRoute }}"
                            class="dropdown-item header-dropdown-item">
                            <i class="bi bi-person"></i>
                            Manage Profile
                        </a>
                    </li>

                    @if (!$isCustomer)
                        <li>
                            <a href="{{ $passwordRoute }}"
                                class="dropdown-item header-dropdown-item">
                                <i class="bi bi-shield-lock"></i>
                                Change Password
                            </a>
                        </li>
                    @endif

                    <li>
                        <hr class="dropdown-divider m-0">
                    </li>

                    <li>
                        <form action="{{ $logoutRoute }}" method="POST">
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
