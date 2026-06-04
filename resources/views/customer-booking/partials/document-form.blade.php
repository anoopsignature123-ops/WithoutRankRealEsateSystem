@php
    $prefix = $prefix ?? '';
    $title = $title ?? 'Documents';
    $document = $document ?? null;
@endphp

<div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">

    <div class="card-header bg-success text-white py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center"
                style="width:48px;height:48px;">
                <i class="bi bi-folder-check fs-4"></i>
            </div>

            <div>
                <h5 class="fw-bold mb-0">{{ $title }}</h5>
                <small class="text-white-50">
                    Upload applicant KYC documents and profile proof.
                </small>
            </div>
        </div>
    </div>

    <div class="card-body p-4">

        <div class="row g-4">

            {{-- Driving License --}}
            <div class="col-md-6">
                <div class="card h-100 border border-light rounded-4 document-card">
                    <div class="card-body p-4">

                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center"
                                    style="width:44px;height:44px;">
                                    <i class="bi bi-card-heading fs-5"></i>
                                </div>

                                <div>
                                    <h6 class="fw-bold mb-1">Driving License</h6>
                                    <small class="text-muted">Upload driving license copy.</small>
                                </div>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input type="checkbox"
                                    class="form-check-input doc-check"
                                    data-target="{{ $prefix }}dlBox"
                                    name="{{ $prefix }}dl"
                                    value="1"
                                    {{ old($prefix . 'dl', $document?->dl) ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div id="{{ $prefix }}dlBox"
                            style="{{ old($prefix . 'dl', $document?->dl) ? '' : 'display:none' }}">

                            <input type="file"
                                name="{{ $prefix }}dl_file"
                                class="form-control @error($prefix . 'dl_file') is-invalid @enderror">

                            @error($prefix . 'dl_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            @if ($document?->dl_file)
                                <div class="mt-3">
                                    <a href="{{ getFileUrl($document->dl_file) }}"
                                        target="_blank"
                                        class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i>
                                        View File
                                    </a>
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
            </div>

            {{-- Aadhar --}}
            <div class="col-md-6">
                <div class="card h-100 border border-light rounded-4 document-card">
                    <div class="card-body p-4">

                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center"
                                    style="width:44px;height:44px;">
                                    <i class="bi bi-person-vcard fs-5"></i>
                                </div>

                                <div>
                                    <h6 class="fw-bold mb-1">Aadhar Card</h6>
                                    <small class="text-muted">Upload applicant Aadhar card.</small>
                                </div>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input type="checkbox"
                                    class="form-check-input doc-check"
                                    data-target="{{ $prefix }}aadharBox"
                                    name="{{ $prefix }}aadhar"
                                    value="1"
                                    {{ old($prefix . 'aadhar', $document?->aadhar) ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div id="{{ $prefix }}aadharBox"
                            style="{{ old($prefix . 'aadhar', $document?->aadhar) ? '' : 'display:none' }}">

                            <input type="file"
                                name="{{ $prefix }}aadhar_file"
                                class="form-control @error($prefix . 'aadhar_file') is-invalid @enderror">

                            @error($prefix . 'aadhar_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            @if ($document?->aadhar_file)
                                <div class="mt-3">
                                    <a href="{{ getFileUrl($document->aadhar_file) }}"
                                        target="_blank"
                                        class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i>
                                        View File
                                    </a>
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
            </div>

            {{-- Voter ID --}}
            <div class="col-md-6">
                <div class="card h-100 border border-light rounded-4 document-card">
                    <div class="card-body p-4">

                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center"
                                    style="width:44px;height:44px;">
                                    <i class="bi bi-credit-card-2-front fs-5"></i>
                                </div>

                                <div>
                                    <h6 class="fw-bold mb-1">Voter ID</h6>
                                    <small class="text-muted">Upload voter identity proof.</small>
                                </div>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input type="checkbox"
                                    class="form-check-input doc-check"
                                    data-target="{{ $prefix }}voterBox"
                                    name="{{ $prefix }}voter_id"
                                    value="1"
                                    {{ old($prefix . 'voter_id', $document?->voter_id) ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div id="{{ $prefix }}voterBox"
                            style="{{ old($prefix . 'voter_id', $document?->voter_id) ? '' : 'display:none' }}">

                            <input type="file"
                                name="{{ $prefix }}voter_id_file"
                                class="form-control @error($prefix . 'voter_id_file') is-invalid @enderror">

                            @error($prefix . 'voter_id_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            @if ($document?->voter_id_file)
                                <div class="mt-3">
                                    <a href="{{ getFileUrl($document->voter_id_file) }}"
                                        target="_blank"
                                        class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i>
                                        View File
                                    </a>
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
            </div>

            {{-- Other --}}
            <div class="col-md-6">
                <div class="card h-100 border border-light rounded-4 document-card">
                    <div class="card-body p-4">

                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center"
                                    style="width:44px;height:44px;">
                                    <i class="bi bi-file-earmark-text fs-5"></i>
                                </div>

                                <div>
                                    <h6 class="fw-bold mb-1">Other Document</h6>
                                    <small class="text-muted">Upload any additional document.</small>
                                </div>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input type="checkbox"
                                    class="form-check-input doc-check"
                                    data-target="{{ $prefix }}otherBox"
                                    name="{{ $prefix }}other"
                                    value="1"
                                    {{ old($prefix . 'other', $document?->other) ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div id="{{ $prefix }}otherBox"
                            style="{{ old($prefix . 'other', $document?->other) ? '' : 'display:none' }}">

                            <input type="file"
                                name="{{ $prefix }}other_file"
                                class="form-control @error($prefix . 'other_file') is-invalid @enderror">

                            @error($prefix . 'other_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            @if ($document?->other_file)
                                <div class="mt-3">
                                    <a href="{{ getFileUrl($document->other_file) }}"
                                        target="_blank"
                                        class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i>
                                        View File
                                    </a>
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
            </div>

            {{-- Profile Picture --}}
            <div class="col-md-12">
                <div class="card h-100 border border-light rounded-4 document-card">
                    <div class="card-body p-4">

                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center"
                                    style="width:44px;height:44px;">
                                    <i class="bi bi-person-square fs-5"></i>
                                </div>

                                <div>
                                    <h6 class="fw-bold mb-1">Profile Picture</h6>
                                    <small class="text-muted">Upload applicant profile photo.</small>
                                </div>
                            </div>

                            <div class="form-check form-switch m-0">
                                <input type="checkbox"
                                    class="form-check-input doc-check"
                                    data-target="{{ $prefix }}profileBox"
                                    name="{{ $prefix }}profile_enabled"
                                    value="1"
                                    {{ old($prefix . 'profile_enabled', $document?->profile_enabled) ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div id="{{ $prefix }}profileBox"
                            style="{{ old($prefix . 'profile_enabled', $document?->profile_enabled) ? '' : 'display:none' }}">

                            <input type="file"
                                name="{{ $prefix }}profile_picture"
                                class="form-control @error($prefix . 'profile_picture') is-invalid @enderror">

                            @error($prefix . 'profile_picture')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            @if ($document?->profile_picture)
                                <div class="d-flex align-items-center gap-3 mt-3">
                                    <img src="{{ getFileUrl($document->profile_picture) }}"
                                        width="90"
                                        height="90"
                                        class="rounded-3 border object-fit-cover"
                                        alt="Profile Picture">

                                    <a href="{{ getFileUrl($document->profile_picture) }}"
                                        target="_blank"
                                        class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i>
                                        View Photo
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>