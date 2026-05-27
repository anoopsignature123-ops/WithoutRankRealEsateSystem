@extends('auth.app')

@section('content')
    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            <div class="col-lg-7 d-none d-lg-block position-relative overflow-hidden">
                <div class="position-absolute top-0 start-0 w-100 h-100"
                    style="
                        background:
                        linear-gradient(rgba(11, 15, 25, 0.78), rgba(22, 101, 52, 0.78)),
                        url('{{ asset('assets/images/login_b.jpg') }}') center center/cover no-repeat;">
                </div>

                <div class="position-relative h-100 d-flex flex-column justify-content-between p-5 text-white">
                    <div>
                        <div
                            class="d-inline-flex align-items-center gap-2 px-4 py-2 rounded-pill bg-white bg-opacity-10 border border-white border-opacity-10 mb-5">
                            <i class="bi bi-buildings-fill"></i>
                            <span class="fw-semibold">Real Estate CRM</span>
                        </div>
                        <h1 class="fw-bold display-4 lh-sm mb-4" style="max-width: 650px;">
                            Real Estate <br>Management Software
                        </h1>
                        <p class="fs-5 text-white text-opacity-75 mb-5" style="max-width: 560px;">
                            One Click to Book Plot and Collect Payment
                        </p>
                        <div class="row g-4 mt-2">
                            <div class="col-4">
                                <div class="glass-card p-4 rounded-4 h-100">
                                    <i class="bi bi-house-check-fill fs-1 mb-3"></i>
                                    <h5 class="fw-bold">Smart Booking</h5>
                                    <small class="text-white text-opacity-75">Quick plot booking system</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="glass-card p-4 rounded-4 h-100">
                                    <i class="bi bi-cash-stack fs-1 mb-3"></i>
                                    <h5 class="fw-bold">EMI Collection</h5>
                                    <small class="text-white text-opacity-75">Easy payment management</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="glass-card p-4 rounded-4 h-100">
                                    <i class="bi bi-bar-chart-line-fill fs-1 mb-3"></i>
                                    <h5 class="fw-bold">Live Reports</h5>
                                    <small class="text-white text-opacity-75">Business analytics dashboard</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-white text-opacity-75">Secure Real Estate Admin Panel</small>
                        <small class="text-white text-opacity-75">
                            © {{ date('Y') }} Signature IT Software Designers.
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5 d-flex align-items-center justify-content-center bg-light position-relative">
                <div class="login-shape"></div>
                <div class="w-100 px-4 position-relative" style="max-width: 440px; z-index: 2;">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <img src="{{ asset('assets/images/admin.png') }}" alt="Logo" class="login-logo">
                        </div>
                        <h2 class="fw-bold text-dark mb-1">
                            Admin Login
                        </h2>
                        <p class="text-muted small mb-0">
                            Login to access your dashboard
                        </p>
                    </div>
                    <div class="card border-0 shadow-lg rounded-5 overflow-hidden login-card">
                        <div class="top-gradient"></div>
                        <div class="card-body p-4 p-lg-5">
                            <form method="POST" action="{{ route('login.submit') }}">
                                @csrf
                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-dark">Email Address</label>
                                    <div class="input-group custom-input-group">
                                        <span class="input-group-text border-0 bg-transparent text-success">
                                            <i class="bi bi-envelope-fill"></i>
                                        </span>
                                        <input type="email" name="email" value="{{ old('email') }}"
                                            class="form-control border-0 shadow-none @error('email') is-invalid @enderror"
                                            placeholder="Enter Email Address">
                                    </div>
                                    @error('email')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-dark">Password</label>
                                    <div class="input-group custom-input-group">
                                        <span class="input-group-text border-0 bg-transparent text-success">
                                            <i class="bi bi-lock-fill"></i>
                                        </span>
                                        <input type="password" name="password" id="password"
                                            class="form-control border-0 shadow-none @error('password') is-invalid @enderror"
                                            placeholder="Enter Password">
                                        <button type="button" class="btn border-0 bg-transparent text-muted px-3"
                                            onclick="togglePassword()">
                                            <i class="bi bi-eye-fill" id="toggleIcon"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="text-end mb-4">
                                    <a href="{{ route('password.request') }}"
                                        class="small text-success fw-semibold text-decoration-none">Forgot Password?
                                    </a>
                                </div>
                                <div class="d-grid">
                                    <button type="submit"
                                        class="btn btn-success py-3 rounded-4 fw-semibold shadow-sm login-btn">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>
                                        Secure Login
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function togglePassword() {
            let passwordInput = document.getElementById('password');
            let toggleIcon = document.getElementById('toggleIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye-fill');
                toggleIcon.classList.add('bi-eye-slash-fill');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash-fill');
                toggleIcon.classList.add('bi-eye-fill');
            }
        }
    </script>
@endpush
