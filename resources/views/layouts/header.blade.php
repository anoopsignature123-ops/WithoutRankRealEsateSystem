@php
    $isAssociate = auth()->guard('associate')->check();
    $currentUser = $isAssociate ? auth()->guard('associate')->user() : auth()->user();
    $profilePhoto = $isAssociate
        ? ($currentUser->photo
            ? getFileUrl($currentUser->photo)
            : asset('assets/images/user2-160x160.jpg'))
        : ($currentUser->profile_image
            ? getFileUrl($currentUser->profile_image)
            : asset('assets/images/user2-160x160.jpg'));

@endphp
<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
            {{-- <li class="nav-item d-none d-md-block">
                <a href="#" class="nav-link">Home</a>
            </li>
            <li class="nav-item d-none d-md-block">
                <a href="#" class="nav-link">Contact</a>
            </li> --}}
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                    <img src="{{ $profilePhoto }}" class="rounded-circle shadow-sm me-2" width="34" height="34"
                        alt="User Image">
                    <div class="d-none d-md-block text-start">
                        <div class="fw-semibold text-dark">
                            {{ $isAssociate ? $currentUser->associate_name ?? 'Associate' : $currentUser->name ?? 'Admin' }}
                        </div>
                        <small class="text-muted" style="font-size: 11px; display: block; line-height: 1;">
                            {{ $isAssociate ? 'Associate' : 'Administrator' }}
                        </small>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-0"
                    style="min-width: 260px; border-radius: 12px; overflow: hidden;">

                    <li class="bg-light text-center p-3 border-bottom">
                        <img src="{{ $profilePhoto }}" class="rounded-circle mb-2 shadow-sm" width="60"
                            height="60" alt="User Image">
                        <div class="fw-bold">
                            {{ $isAssociate ? $currentUser->associate_name ?? 'Associate' : $currentUser->name ?? 'Admin' }}
                        </div>
                        <small class="text-muted">
                            {{ $isAssociate ? 'ID: ' . ($currentUser->associate_id ?? '') : $currentUser->email ?? '' }}
                        </small>
                    </li>
                    <li>
                        <a href="{{ $isAssociate ? route('associate-panel.view-profile') : route('profile') }}"
                            class="dropdown-item py-2 d-flex align-items-center">
                            <i class="bi bi-person me-2"></i> Manage Profile
                        </a>
                    </li>
                    <li>
                        <a href="{{ $isAssociate ? route('associate-panel.change-password') : route('change-password') }}" class="dropdown-item py-2 d-flex align-items-center">
                            <i class="bi bi-shield-lock me-2"></i>
                            Change Password
                        </a>
                    </li>
                    <li>
                        <a href="#" class="dropdown-item py-2 d-flex align-items-center">
                            <i class="bi bi-gear me-2"></i> Settings
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider m-0">
                    </li>
                    <li>
                        <form action="{{ $isAssociate ? route('associate-panel.logout') : route('logout') }}"
                            method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger py-2 d-flex align-items-center"
                                style="width: 100%; border: none; background: none;">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Sign out
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
