<style>
    .notification-alert {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 215px;
        max-width: 245px;
        padding: 8px 12px;
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.10);
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
        transition: all 0.25s ease;
    }

    .notification-alert:hover {
        transform: translateY(-1px);
        text-decoration: none;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
    }

    .notify-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 17px;
    }

    .notify-content {
        min-width: 0;
        flex: 1;
    }

    .notify-title {
        font-size: 12.5px;
        font-weight: 800;
        color: #1f2937;
        line-height: 1.1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .notify-subtitle {
        font-size: 10.5px;
        color: #6b7280;
        line-height: 1.2;
        margin-top: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .notify-count {
        min-width: 24px;
        height: 24px;
        padding: 0 7px;
        border-radius: 50px;
        background: #dc3545;
        color: #ffffff;
        font-size: 12px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 2px;
    }

    .notification-alert.cheque-alert {
        border-color: rgba(245, 158, 11, 0.30);
        background: linear-gradient(135deg, #ffffff, #fff8ed);
    }

    .notification-alert.cheque-alert .notify-icon {
        background: #f59e0b;
    }

    .notification-alert.support-alert {
        border-color: rgba(13, 110, 253, 0.28);
        background: linear-gradient(135deg, #ffffff, #eef6ff);
    }

    .notification-alert.support-alert .notify-icon {
        background: #0d6efd;
    }

    @media (max-width: 1400px) {
        .notification-alert {
            min-width: 190px;
            max-width: 205px;
            padding: 7px 10px;
        }

        .notify-title {
            font-size: 12px;
        }

        .notify-subtitle {
            font-size: 10px;
        }
    }

    @media (max-width: 1200px) {
        .notification-alert {
            min-width: auto;
            width: 44px;
            height: 44px;
            padding: 0;
            justify-content: center;
            border-radius: 14px;
            position: relative;
        }

        .notify-content {
            display: none;
        }

        .notify-count {
            position: absolute;
            top: -7px;
            right: -7px;
            min-width: 20px;
            height: 20px;
            font-size: 10px;
            padding: 0 5px;
        }
    }

    .header-date-box {
        display: flex;
        align-items: center;
        gap: 12px;

        height: 54px;
        padding: 0 16px;

        background: #fff;
        border: 1px solid #e7edf5;
        border-radius: 16px;

        box-shadow: 0 4px 12px rgba(0, 0, 0, .05);

        transition: .25s;
    }

    .header-date-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, .08);
    }

    .date-icon {
        width: 40px;
        height: 40px;

        border-radius: 12px;

        background: #28a745;

        display: flex;
        align-items: center;
        justify-content: center;

        color: #fff;
        font-size: 17px;

        flex-shrink: 0;
    }

    .date-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        line-height: 1.1;
    }

    .date-value {
        font-size: 15px;
        font-weight: 700;
        color: #1f2937;
    }
    .date-day {
        margin-top: 3px;
        font-size: 12px;
        color: #6c757d;
        font-weight: 600;
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
                    <div class="date-icon">
                        <i class="bi bi-calendar2-week-fill"></i>
                    </div>

                    <div class="date-content">
                        <div class="date-value">{{ now()->format('d M Y') }}</div>
                        <div class="date-day">{{ now()->format('l') }}</div>
                    </div>
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

                        <a href="{{ $notificationRoute }}" class="notification-alert cheque-alert">

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

            @php
                $pendingSupportCount = \App\Models\Support::where('status', 'Pending')->count();

                $supportRoute = Route::has('support.index') ? route('support.index') : '#';
            @endphp

            @if ($pendingSupportCount > 0)
                <li class="nav-item me-2">

                    <a href="{{ $supportRoute }}" class="notification-alert support-alert">

                        <div class="notify-icon" style="background:#0d6efd;">
                            <i class="bi bi-headset"></i>
                        </div>

                        <div class="notify-content">
                            <div class="notify-title" style="color:#0d47a1;">
                                🎧 Customer Support
                            </div>

                            <div class="notify-subtitle">
                                {{ $pendingSupportCount }} New Support Request
                            </div>
                        </div>

                        <span class="notify-count">
                            {{ $pendingSupportCount }}
                        </span>

                    </a>

                </li>
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
