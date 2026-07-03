<style>
    .notification-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 315px;
        padding: 2px 18px;
        border-radius: 50px;
        background: linear-gradient(133deg, #FFF7EB, #FFF0CA);
        border: 2px solid #f79b24;
        text-decoration: none;
        overflow: hidden;
        position: relative;
        animation: borderFlash 1.6s infinite;
        transition: .3s;
    }

    .notification-alert:hover {
        transform: translateY(-3px) scale(1.02);
        text-decoration: none;
    }

    .notify-icon {
        width: 46px;
        height: 46px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: #ff9800;
        color: #fff;
        font-size: 22px;

        animation: iconShake .8s infinite;
    }

    .notify-title {
        font-size: 14px;
        font-weight: 700;
        color: #8a4d00;
    }

    .notify-subtitle {
        font-size: 12px;
        color: #555;
    }

    .notify-count {

        min-width: 34px;
        height: 34px;

        border-radius: 50%;

        display: flex;
        align-items: center;
        justify-content: center;

        background: #dc3545;
        color: #fff;

        font-weight: bold;

        animation: countPulse 1s infinite;
    }

    /* Glow */

    @keyframes borderFlash {

        0% {
            box-shadow: 0 0 0 rgba(255, 152, 0, .3);
        }

        50% {
            box-shadow: 0 0 18px rgba(255, 152, 0, .8);
        }

        100% {
            box-shadow: 0 0 0 rgba(255, 152, 0, .3);
        }

    }

    /* Shake Icon */

    @keyframes iconShake {

        0%,
        100% {
            transform: rotate(0deg);
        }

        20% {
            transform: rotate(-12deg);
        }

        40% {
            transform: rotate(12deg);
        }

        60% {
            transform: rotate(-8deg);
        }

        80% {
            transform: rotate(8deg);
        }

    }

    /* Badge Pulse */

    @keyframes countPulse {

        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.25);
        }

        100% {
            transform: scale(1);
        }

    }
</style>

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
        $userName = $currentUser->primaryDetail?->name ?? ($currentUser->customer_name ?? 'Customer');

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


            {{-- Admin Cheque / Hold Notification --}}
            @if (!$isAssociate && !$isCustomer)
                @php
                    $pendingChequeCount = \App\Models\CustomerPayment::whereIn('payment_mode', ['cheque', 'dd'])
                        ->whereIn('cheque_status', ['pending', 'hold'])
                        ->count();

                    $notificationRoute = Route::has('multiple-cheque-clearance.index')
                        ? route('multiple-cheque-clearance.index')
                        : '#';
                @endphp

                @if ($pendingChequeCount > 0)
                    <li class="nav-item me-2">

                        <a href="{{ $notificationRoute }}" class="notification-alert">

                            <div class="notify-icon">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>

                            <div class="notify-content">
                                <div class="notify-title">
                                    ⚠ Multiple Cheque Clearance
                                </div>

                                <div class="notify-subtitle">
                                    {{ $pendingChequeCount }} Pending Cheque / DD Clearance
                                </div>
                            </div>

                            <span class="notify-count">
                                {{ $pendingChequeCount }}
                            </span>

                        </a>

                    </li>
                @endif
            @endif


            {{-- User Dropdown --}}
            <li class="nav-item dropdown user-menu">

                <a href="#" class="nav-link header-user-box d-flex align-items-center" data-bs-toggle="dropdown">

                    <img src="{{ $profilePhoto }}" class="header-user-img" alt="User Image"
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
                        <img src="{{ $profilePhoto }}" class="rounded-circle border border-3 border-white shadow mb-2"
                            width="76" height="76" alt="User Image"
                            onerror="this.onerror=null;this.src='{{ asset('assets/images/user2-160x160.jpg') }}';">

                        <h6 class="fw-bold mb-1 text-white">
                            {{ $userName }}
                        </h6>

                        <small class="text-white-50">
                            {{ $dropdownInfo }}
                        </small>
                    </li>

                    <li>
                        <a href="{{ $profileRoute }}" class="dropdown-item header-dropdown-item">
                            <i class="bi bi-person"></i>
                            Manage Profile
                        </a>
                    </li>

                    @if (!$isCustomer)
                        <li>
                            <a href="{{ $passwordRoute }}" class="dropdown-item header-dropdown-item">
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
