@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 transaction-page staff-management-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-pencil-square"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Staff Account</span>
                        <h3 class="fw-bold mb-1 text-dark">Edit User: {{ $user->name }}</h3>
                        <p class="text-muted mb-0 small">Update staff profile, role and account status.</p>
                    </div>
                </div>

                <a href="{{ route('users.index') }}" class="btn btn-outline-success">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Users
                </a>
            </div>
        </div>

        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data"
            id="staffForm">
            @csrf
            @method('PUT')

            <div class="transaction-card mb-4">
                <div class="transaction-card-body">
                    <div class="transaction-section-title">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon">
                                <i class="bi bi-person-vcard"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold mb-1">Account Details</h5>
                                <small class="text-muted">Update staff account and access assignment.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assign Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="">Select Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}"
                                        {{ old('role', $user->roles->pluck('name')->first()) == $role->name ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="form-control @error('name') is-invalid @enderror" placeholder="Enter full name"
                                required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                class="form-control @error('email') is-invalid @enderror" placeholder="name@company.com"
                                required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="transaction-card mb-4">
                <div class="transaction-card-body">
                    <div class="transaction-section-title">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon">
                                <i class="bi bi-image"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold mb-1">Profile Image</h5>
                                <small class="text-muted">Change profile image if required.</small>
                            </div>
                        </div>
                    </div>

                    <div class="staff-photo-box">
                        <div class="position-relative">
                            <img id="previewImage"
                                src="{{ $user->profile_image ? getFileUrl($user->profile_image) : asset('assets/images/avatar.png') }}"
                                class="staff-photo-preview" alt="{{ $user->name }}"
                                onerror="this.src='{{ asset('assets/images/avatar.png') }}'">
                            <span class="staff-photo-camera">
                                <i class="bi bi-camera-fill"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark">Change Profile Photo</div>
                            <div class="text-muted small mb-2">JPG, PNG or JPEG. Maximum size 2 MB.</div>
                            <label for="imageInput" class="btn btn-outline-success btn-sm">
                                Choose File
                            </label>
                            <input type="file" name="profile_image" id="imageInput" class="d-none"
                                accept="image/*">
                        </div>
                    </div>
                    @error('profile_image')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="transaction-action-bar mb-4">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-success" id="staffSubmitBtn">
                    <span class="btn-label">
                        <i class="bi bi-check-circle me-1"></i>
                        Update User
                    </span>
                    <span class="btn-loader d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Updating...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('imageInput')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => document.getElementById('previewImage').src = e.target.result;
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('staffForm')?.addEventListener('submit', function() {
            const button = document.getElementById('staffSubmitBtn');
            button.disabled = true;
            button.querySelector('.btn-label')?.classList.add('d-none');
            button.querySelector('.btn-loader')?.classList.remove('d-none');
        });
    </script>
@endpush
