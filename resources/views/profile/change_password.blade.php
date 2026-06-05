@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        <div class="row justify-content-center">
            <div class="col-xl-7 col-lg-8">

                {{-- Page Header --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">

                        <div class="d-flex align-items-center">
                            <div class="rounded-4 bg-light d-flex align-items-center justify-content-center me-3"
                                style="width:60px;height:60px;">
                                <i class="bi bi-shield-lock fs-2 text-secondary"></i>
                            </div>

                            <div>
                                <h3 class="fw-bold mb-1 text-dark">
                                    Change Password
                                </h3>

                                <p class="text-muted mb-0">
                                    Update your account password securely.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Password Form --}}
                <div class="card border-0 shadow-sm rounded-4">

                    <div class="card-body p-4 p-lg-5">

                        <form action="{{ route('change-password.update') }}" method="POST">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    Current Password
                                </label>

                                <div class="input-group">
                                    <input type="password"
                                        name="current_password"
                                        id="current_password"
                                        class="form-control @error('current_password') is-invalid @enderror"
                                        placeholder="Enter your current password">

                                    <button type="button"
                                        class="btn btn-light border"
                                        onclick="togglePassword('current_password', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    @error('current_password')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    New Password
                                </label>

                                <div class="input-group">
                                    <input type="password"
                                        name="new_password"
                                        id="new_password"
                                        class="form-control @error('new_password') is-invalid @enderror"
                                        placeholder="Enter new password">

                                    <button type="button"
                                        class="btn btn-light border"
                                        onclick="togglePassword('new_password', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    @error('new_password')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    Confirm Password
                                </label>

                                <div class="input-group">
                                    <input type="password"
                                        name="new_password_confirmation"
                                        id="confirm_password"
                                        class="form-control"
                                        placeholder="Confirm new password">

                                    <button type="button"
                                        class="btn btn-light border"
                                        onclick="togglePassword('confirm_password', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="alert alert-light border rounded-4 mb-4">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi bi-info-circle text-secondary mt-1"></i>
                                    <small class="text-muted">
                                        Use a strong password with letters, numbers, and special characters for better security.
                                    </small>
                                </div>
                            </div>

                            <div class="border-top pt-4">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">

                                    <a href="{{ url()->previous() }}"
                                        class="btn btn-light border px-4">
                                        Back
                                    </a>

                                    <button type="submit"
                                        class="btn btn-success px-4">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Update Password
                                    </button>

                                </div>
                            </div>

                        </form>

                    </div>

                </div>

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');

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
    </script>
@endpush