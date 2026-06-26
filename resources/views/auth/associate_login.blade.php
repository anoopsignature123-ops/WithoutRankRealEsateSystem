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
                            <i class="bi bi-person-badge-fill"></i>
                            <span>Associate Secure Access</span>
                        </div>

                        <h1>
                            Track Business <br>
                            And Team Growth
                        </h1>

                        <p>
                            Access your dashboard, customer ledger, booking details, commissions and team performance
                            securely from one associate panel.
                        </p>

                        <div class="secure-feature-row">
                            <div class="secure-feature-card">
                                <i class="bi bi-diagram-3-fill"></i>
                                <strong>Team Network</strong>
                                <small>View direct and downline</small>
                            </div>
                            <div class="secure-feature-card">
                                <i class="bi bi-journal-check"></i>
                                <strong>Bookings</strong>
                                <small>Track customer plots</small>
                            </div>
                            <div class="secure-feature-card">
                                <i class="bi bi-wallet2"></i>
                                <strong>Payouts</strong>
                                <small>Monitor commissions</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="secure-login-box">
                        <div class="text-center mb-4 position-relative">
                            <img src="{{ asset('assets/images/admin.png') }}" alt="Logo" class="secure-login-logo">

                            <h2 class="fw-bold text-white mt-3 mb-1">Associate Login</h2>
                            <p class="text-white-50 small mb-0">Sign in using your associate ID</p>
                        </div>

                        <form method="POST" action="{{ route('associate-panel.login.submit') }}"
                            class="position-relative login-secure-form" autocomplete="on" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label class="form-label text-white-50 small">Associate ID</label>
                                <div class="secure-input-group">
                                    <i class="bi bi-person-badge-fill"></i>
                                    <input type="text" name="associate_id" value="{{ old('associate_id') }}"
                                        class="form-control @error('associate_id') is-invalid @enderror"
                                        placeholder="Enter associate ID" autocomplete="username" maxlength="80" required
                                        autofocus>
                                </div>
                                @error('associate_id')
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
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label small text-white-50" for="remember">
                                        Remember me
                                    </label>
                                </div>

                                <small class="text-white-50">Secure session</small>
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
