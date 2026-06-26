@extends('auth.app')

@section('content')
    <div class="secure-login-wrapper" style="--secure-login-image: url('{{ asset('assets/images/customer.jpg') }}');">
        <div class="secure-login-bg"></div>
        <div class="secure-login-overlay"></div>
        <div class="secure-login-glow secure-login-glow-1"></div>
        <div class="secure-login-glow secure-login-glow-2"></div>

        <div class="container-fluid secure-login-container">
            <div class="row min-vh-100 align-items-center">
                <div class="col-lg-7 d-none d-lg-block">
                    <div class="secure-left-content">
                        <div class="secure-brand-pill">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span>Admin Secure Access</span>
                        </div>

                        <h1>
                            Real Estate <br>
                            Management Software
                        </h1>

                        <p>
                            Securely manage bookings, payments, staff permissions, reports and plot inventory from one
                            protected admin panel.
                        </p>

                        <div class="secure-feature-row">
                            <div class="secure-feature-card">
                                <i class="bi bi-house-check-fill"></i>
                                <strong>Plot Booking</strong>
                                <small>Manage bookings safely</small>
                            </div>
                            <div class="secure-feature-card">
                                <i class="bi bi-cash-stack"></i>
                                <strong>Payments</strong>
                                <small>Track EMI and dues</small>
                            </div>
                            <div class="secure-feature-card">
                                <i class="bi bi-person-lock"></i>
                                <strong>Role Security</strong>
                                <small>Control staff access</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="secure-login-box">
                        <div class="text-center mb-4 position-relative">
                            <img src="{{ asset('assets/images/admin.png') }}" alt="Logo" class="secure-login-logo">

                            <h2 class="fw-bold text-white mt-3 mb-1">Admin Login</h2>
                            <p class="text-white-50 small mb-0">Sign in with your authorized admin account</p>
                        </div>

                        <form method="POST" action="{{ route('login.submit') }}" class="position-relative login-secure-form"
                            autocomplete="on" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label class="form-label text-white-50 small">Email Address</label>
                                <div class="secure-input-group">
                                    <i class="bi bi-envelope-fill"></i>
                                    <input type="email" name="email" value="{{ old('email') }}"
                                        class="form-control @error('email') is-invalid @enderror"
                                        placeholder="Enter email address" autocomplete="username" inputmode="email"
                                        maxlength="255" required autofocus>
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-2">
                                <label class="form-label text-white-50 small">Password</label>
                                <div class="secure-input-group">
                                    <i class="bi bi-lock-fill"></i>
                                    <input type="password" name="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Enter password" autocomplete="current-password" minlength="6" required>
                                    <button type="button" class="secure-password-toggle" data-toggle-password="password">
                                        <i class="bi bi-eye" id="password-icon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center my-4">
                                <small class="text-white-50">
                                    Protected login with session regeneration.
                                </small>
                                <a href="{{ route('password.request') }}"
                                    class="small text-success text-decoration-none fw-semibold">
                                    Forgot password?
                                </a>
                            </div>

                            <button type="submit" class="btn secure-login-btn w-100 login-submit-btn">
                                <span class="btn-label">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Secure Login
                                </span>
                                <span class="btn-loader d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Verifying...
                                </span>
                            </button>
                        </form>

                        <div class="text-center mt-4 position-relative">
                            <small class="text-white-50">
                                &copy; {{ date('Y') }} Signature IT Software Designers
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(button => {
            button.addEventListener('click', function() {
                const input = document.getElementById(this.dataset.togglePassword);
                const icon = document.getElementById(this.dataset.togglePassword + '-icon');

                input.type = input.type === 'password' ? 'text' : 'password';
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
        });

        document.querySelector('.login-secure-form')?.addEventListener('submit', function() {
            const button = this.querySelector('.login-submit-btn');
            button.disabled = true;
            button.querySelector('.btn-label')?.classList.add('d-none');
            button.querySelector('.btn-loader')?.classList.remove('d-none');
        });
    </script>
@endpush
