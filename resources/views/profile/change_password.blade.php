@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="px-4 py-3 text-white" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
                        <h4 class="fw-bold mb-1">
                            <i class="bi bi-shield-lock me-2"></i> Change Password
                        </h4>
                        <small class="opacity-75">Change Your Current Password</small>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('change-password.update') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Current Password</label>
                                <div class="input-group">
                                    <input type="password" name="current_password" id="current_password"
                                        class="form-control rounded-start-3 @error('current_password') is-invalid @enderror"
                                        placeholder="Enter your current password">
                                    <button type="button" class="btn btn-outline-secondary rounded-end-3"
                                        onclick="togglePassword('current_password', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">New Password</label>
                                <div class="input-group">
                                    <input type="password" name="new_password" id="new_password"
                                        class="form-control rounded-start-3 @error('new_password') is-invalid @enderror"
                                        placeholder="Enter new password">
                                    <button type="button" class="btn btn-outline-secondary rounded-end-3"
                                        onclick="togglePassword('new_password', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('new_password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" name="new_password_confirmation" id="confirm_password"
                                        class="form-control rounded-start-3" placeholder="Confirm new password">
                                    <button type="button" class="btn btn-outline-secondary rounded-end-3"
                                        onclick="togglePassword('confirm_password', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100 rounded-3 py-2 fw-bold">
                                <i class="bi bi-check-circle me-1"></i> Update Password
                            </button>
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
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
@endpush
