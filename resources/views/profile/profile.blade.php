@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-9">

                {{-- Page Header --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

                            <div class="d-flex align-items-center">
                                <div class="rounded-4 bg-light d-flex align-items-center justify-content-center me-3"
                                    style="width:60px;height:60px;">
                                    <i class="bi bi-person-circle fs-2 text-secondary"></i>
                                </div>

                                <div>
                                    <h3 class="fw-bold mb-1 text-dark">
                                        Profile Settings
                                    </h3>

                                    <p class="text-muted mb-0">
                                        Manage your personal information and account settings.
                                    </p>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                {{-- Profile Form --}}
                <div class="card border-0 shadow-sm rounded-4">

                    <div class="card-body p-4 p-lg-5">

                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="text-center mb-5">

                                <div class="position-relative d-inline-block">

                                    <div class="rounded-circle overflow-hidden border shadow-sm bg-light"
                                        style="width:130px;height:130px;">

                                        @if ($user->profile_image)
                                            <img src="{{ getFileUrl($user->profile_image) }}"
                                                id="profilePreview"
                                                class="w-100 h-100 object-fit-cover"
                                                alt="Profile">
                                        @else
                                            <div id="profilePlaceholder"
                                                class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-dark fw-bold"
                                                style="font-size:40px;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif

                                    </div>

                                    <label class="btn btn-light border rounded-circle position-absolute bottom-0 end-0 shadow-sm"
                                        style="width:40px;height:40px;padding:0;display:flex;align-items:center;justify-content:center;cursor:pointer;">

                                        <i class="bi bi-camera"></i>

                                        <input type="file"
                                            name="image"
                                            class="d-none"
                                            accept="image/*"
                                            onchange="previewImage(event)">
                                    </label>

                                </div>

                                <h4 class="fw-bold mt-3 mb-1">
                                    {{ $user->name }}
                                </h4>

                                <span class="text-muted">
                                    Administrator
                                </span>

                                @error('image')
                                    <div class="text-danger small mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="row g-4">

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Full Name
                                    </label>

                                    <input type="text"
                                        name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $user->name) }}"
                                        placeholder="Enter full name">

                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Email Address
                                    </label>

                                    <input type="email"
                                        class="form-control bg-light"
                                        value="{{ $user->email }}"
                                        disabled>

                                    <small class="text-muted">
                                        Email address cannot be changed.
                                    </small>
                                </div>

                            </div>

                            <div class="border-top mt-5 pt-4">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">

                                    <a href="{{ url()->previous() }}"
                                        class="btn btn-light border px-4">
                                        Back
                                    </a>

                                    <button type="submit"
                                        class="btn btn-success px-4">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Save Changes
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
        function previewImage(event) {
            const input = event.target;

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    let img = document.getElementById('profilePreview');

                    if (!img) {
                        const placeholder = document.getElementById('profilePlaceholder');

                        placeholder.outerHTML =
                            `<img src="" id="profilePreview" class="w-100 h-100 object-fit-cover" alt="Profile">`;

                        img = document.getElementById('profilePreview');
                    }

                    img.src = e.target.result;
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush