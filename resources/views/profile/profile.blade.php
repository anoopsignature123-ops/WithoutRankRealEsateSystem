@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="px-4 py-3 text-white" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-person-circle me-2"></i> Profile Settings
                    </h4>
                    <small class="opacity-75">Update your profile information</small>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <div class="rounded-circle overflow-hidden shadow-sm border" style="width: 120px; height: 120px;">
                                    @if ($user->profile_image)
                                        <img src="{{ getFileUrl($user->profile_image) }}" 
                                             id="profilePreview" 
                                             class="w-100 h-100 object-fit-cover">
                                    @else
                                        <div id="profilePlaceholder" 
                                             class="w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold"
                                             style="font-size: 40px; background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <label class="btn btn-success btn-sm rounded-circle position-absolute bottom-0 end-0 d-flex align-items-center justify-content-center shadow"
                                       style="width: 38px; height: 38px; cursor:pointer;">
                                    <i class="bi bi-camera-fill text-white"></i>
                                    <input type="file" name="image" class="d-none" accept="image/*" onchange="previewImage(event)">
                                </label>
                            </div>

                            <h5 class="fw-bold mt-3 mb-0">{{ $user->name }}</h5>
                            @error('image')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="name" 
                                   class="form-control rounded-3 py-2 @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $user->name) }}" placeholder="Enter full name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control rounded-3 py-2 bg-light" value="{{ $user->email }}" disabled>
                            <small class="text-muted">Email address cannot be changed.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success rounded-3 px-4">
                                <i class="bi bi-check-circle me-1"></i> Save Changes
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-light border rounded-3 px-4">Back</a>
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
                
                // Agar pehle se placeholder hai, toh use replace karke img tag banayein
                if (!img) {
                    const placeholder = document.getElementById('profilePlaceholder');
                    placeholder.outerHTML = `<img src="" id="profilePreview" class="w-100 h-100 object-fit-cover">`;
                    img = document.getElementById('profilePreview');
                }
                img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush