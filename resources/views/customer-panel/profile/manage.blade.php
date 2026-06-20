@extends('layouts.app')

@section('content')
    @php
        $primary = $customer->primaryDetail;
        $document = $primary?->customerDocument;
        $profilePath = $document?->profile_picture;
        $profileImage = $profilePath ? getFileUrl($profilePath) : asset('assets/images/user2-160x160.jpg');
        $displayName = $primary?->name ?? $customer->customer_name ?? 'Customer';
        $initials = collect(explode(' ', trim($displayName)))
            ->filter()
            ->take(2)
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
            ->implode('');
        $initials = $initials ?: 'C';
    @endphp

    <div class="container-fluid customer-panel-page customer-profile-page customer-manage-profile-page">
        <div class="customer-profile-hero mb-4">
            <div class="customer-profile-main">
                <div class="customer-manage-profile-avatar-wrap">
                    <img src="{{ $profileImage }}" alt="Customer profile" class="customer-manage-profile-avatar"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <span class="customer-manage-profile-initials" style="display: none;">{{ $initials }}</span>
                </div>

                <div>
                    <span class="customer-dashboard-kicker">Manage Profile</span>
                    <h3 class="mb-1">{{ $displayName }}</h3>
                    <p class="mb-0">
                        Customer Code: <strong>{{ $customer->customer_code ?? 'N/A' }}</strong>
                    </p>
                </div>
            </div>

            <div class="customer-profile-meta">
                <span class="badge bg-white text-success border rounded-pill px-3 py-2">
                    Account Settings
                </span>
                <a href="{{ route('customer-panel.profile') }}" class="btn btn-success btn-sm fw-semibold">
                    <i class="bi bi-eye me-1"></i> View Profile
                </a>
            </div>
        </div>
        <div class="row g-4 align-items-stretch">
            <div class="col-xl-8 col-lg-7">
                <div class="customer-manage-profile-card h-100">
                    <div class="customer-manage-profile-card-head">
                        <div class="customer-manage-profile-icon">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Profile Details</h5>
                            <div class="text-muted small">Update your name and profile image here.</div>
                        </div>
                    </div>

                    <form action="{{ route('customer-panel.manage-profile.update') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="customer-manage-profile-photo-box mb-4">
                            <div class="customer-manage-profile-preview-wrap">
                                <img src="{{ $profileImage }}" alt="Customer profile preview"
                                    class="customer-manage-profile-preview"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <span class="customer-manage-profile-preview-initials"
                                    style="display: none;">{{ $initials }}</span>
                            </div>

                            <div class="flex-grow-1">
                                <label class="form-label fw-semibold">Profile Image</label>
                                <label class="customer-file-upload-control @error('profile_picture') is-invalid @enderror"
                                    for="customer_profile_picture">
                                    <span class="customer-file-upload-btn">
                                        <i class="bi bi-upload me-1"></i> Choose Photo
                                    </span>
                                    <span class="customer-file-upload-name" id="customer_profile_picture_name">
                                        No file selected
                                    </span>
                                </label>
                                <input type="file" name="profile_picture" id="customer_profile_picture"
                                    class="d-none"
                                    accept="image/png,image/jpeg,image/jpg,image/webp">
                                @error('profile_picture')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Allowed formats: JPG, PNG, WEBP. Maximum size: 2 MB.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Name</label>
                            <div class="input-group customer-manage-input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" name="name" value="{{ old('name', $displayName) }}"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Enter your name">
                            </div>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="customer-manage-profile-note mb-4">
                            <i class="bi bi-info-circle"></i>
                            <span>This name will appear in the customer panel header and profile page.</span>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-check2-circle me-1"></i> Save Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5">
                <div class="customer-manage-profile-card customer-password-card h-100">
                    <div class="customer-manage-profile-card-head">
                        <div class="customer-manage-profile-icon customer-manage-profile-icon-lock">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Change Password</h5>
                            <div class="text-muted small">Your current password is required to update it.</div>
                        </div>
                    </div>

                    <form action="{{ route('customer-panel.manage-profile.password.update') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password</label>
                            <div class="input-group customer-manage-input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" name="current_password" id="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    placeholder="Enter current password">
                                <button class="btn customer-password-toggle" type="button"
                                    onclick="toggleCustomerPassword('current_password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password</label>
                            <div class="input-group customer-manage-input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Enter new password">
                                <button class="btn customer-password-toggle" type="button"
                                    onclick="toggleCustomerPassword('password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirm New Password</label>
                            <div class="input-group customer-manage-input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-key-fill"></i>
                                </span>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control" placeholder="Re-enter new password">
                                <button class="btn customer-password-toggle" type="button"
                                    onclick="toggleCustomerPassword('password_confirmation', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="customer-password-tip mb-4">
                            <i class="bi bi-shield-check"></i>
                            <span>Use a strong password with at least 8 characters.</span>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-key me-1"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleCustomerPassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');

            if (!input || !icon) {
                return;
            }

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        const customerProfileInput = document.getElementById('customer_profile_picture');
        if (customerProfileInput) {
            customerProfileInput.addEventListener('change', function () {
                const file = this.files && this.files[0];
                const fileName = document.getElementById('customer_profile_picture_name');
                if (!file) {
                    if (fileName) {
                        fileName.textContent = 'No file selected';
                    }
                    return;
                }

                if (fileName) {
                    fileName.textContent = file.name;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    document.querySelectorAll('.customer-manage-profile-avatar, .customer-manage-profile-preview')
                        .forEach(function (image) {
                            image.src = event.target.result;
                            image.style.display = 'flex';
                        });

                    document.querySelectorAll('.customer-manage-profile-initials, .customer-manage-profile-preview-initials')
                        .forEach(function (initials) {
                            initials.style.display = 'none';
                        });
                };
                reader.readAsDataURL(file);
            });
        }
    </script>
@endpush
