@php
    $prefix = $prefix ?? '';
    $title = $title ?? 'Correspondence Details';

    $radioClass = $prefix ? $prefix . 'AddressToggle' : 'addressToggle';
    $addressId = $prefix ? $prefix . 'CorrAddress' : 'corrAddress';
    $pinId = $prefix ? $prefix . 'CorrPin' : 'corrPin';
    $cityId = $prefix ? $prefix . 'CorrCity' : 'corrCity';
    $stateId = $prefix ? $prefix . 'CorrState' : 'corrState';

    if ($prefix == 'secondary_') {
        $detail = $customer?->secondaryDetail?->correspondenceDetail;
    } else {
        $detail = $customer?->primaryDetail?->correspondenceDetail;
    }
@endphp

<div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">

    <div class="card-header bg-light py-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-envelope-paper text-success fs-5"></i>
            <div>
                <h6 class="fw-bold mb-0">{{ $title }}</h6>
                <small class="text-muted">
                    Enter communication address and contact information.
                </small>
            </div>
        </div>
    </div>

    <div class="card-body p-4">

        <div class="alert alert-success bg-success-subtle border-success-subtle text-success rounded-4 mb-4">
            <label class="form-label fw-semibold d-block mb-2">
                Same as permanent address?
            </label>

            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input {{ $radioClass }}"
                    name="{{ $prefix }}same_as_permanent_address" value="yes"
                    {{ old($prefix . 'same_as_permanent_address', $detail?->same_as_permanent_address) == 'yes' ? 'checked' : '' }}>

                <label class="form-check-label fw-semibold">Yes</label>
            </div>

            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input {{ $radioClass }}"
                    name="{{ $prefix }}same_as_permanent_address" value="no"
                    {{ old($prefix . 'same_as_permanent_address', $detail?->same_as_permanent_address) == 'no' ? 'checked' : '' }}>

                <label class="form-check-label fw-semibold">No</label>
            </div>

            @error($prefix . 'same_as_permanent_address')
                <div class="text-danger small fw-semibold mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label fw-semibold">Correspondence Address</label>
                <textarea rows="3" id="{{ $addressId }}" name="{{ $prefix }}correspondence_address"
                    class="form-control @error($prefix . 'correspondence_address') is-invalid @enderror"
                    placeholder="Enter correspondence address">{{ old($prefix . 'correspondence_address', $detail?->correspondence_address) }}</textarea>

                @error($prefix . 'correspondence_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Pin Code</label>
                <input type="text" id="{{ $pinId }}" name="{{ $prefix }}pin_code"
                    class="form-control @error($prefix . 'pin_code') is-invalid @enderror"
                    placeholder="Enter pin code"
                    value="{{ old($prefix . 'pin_code', $detail?->pin_code) }}">

                @error($prefix . 'pin_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">City</label>
                <input type="text" id="{{ $cityId }}" name="{{ $prefix }}city"
                    class="form-control @error($prefix . 'city') is-invalid @enderror"
                    placeholder="Enter city"
                    value="{{ old($prefix . 'city', $detail?->city) }}">

                @error($prefix . 'city')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">State</label>
                <input type="text" id="{{ $stateId }}" name="{{ $prefix }}state"
                    class="form-control @error($prefix . 'state') is-invalid @enderror"
                    placeholder="Enter state"
                    value="{{ old($prefix . 'state', $detail?->state) }}">

                @error($prefix . 'state')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Mobile Number</label>
                <input type="text" name="{{ $prefix }}telephone_no"
                    class="form-control @error($prefix . 'telephone_no') is-invalid @enderror"
                    placeholder="Enter mobile number"
                    value="{{ old($prefix . 'telephone_no', $detail?->telephone_no) }}">

                @error($prefix . 'telephone_no')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" name="{{ $prefix }}email"
                    class="form-control @error($prefix . 'email') is-invalid @enderror"
                    placeholder="Enter email address"
                    value="{{ old($prefix . 'email', $detail?->email) }}">

                @error($prefix . 'email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">ID Proof Type</label>
                <select name="{{ $prefix }}id_proof_type"
                    class="form-select @error($prefix . 'id_proof_type') is-invalid @enderror">
                    <option value="">Select ID Proof</option>
                    <option value="pancard"
                        {{ old($prefix . 'id_proof_type', $detail?->id_proof_type) == 'pancard' ? 'selected' : '' }}>
                        PAN Card
                    </option>
                    <option value="aadhar"
                        {{ old($prefix . 'id_proof_type', $detail?->id_proof_type) == 'aadhar' ? 'selected' : '' }}>
                        Aadhar Card
                    </option>
                </select>

                @error($prefix . 'id_proof_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">ID Proof Number</label>
                <input type="text" name="{{ $prefix }}id_proof_number"
                    class="form-control @error($prefix . 'id_proof_number') is-invalid @enderror"
                    placeholder="Enter ID proof number"
                    value="{{ old($prefix . 'id_proof_number', $detail?->id_proof_number) }}">

                @error($prefix . 'id_proof_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Occupation</label>
                <input type="text" name="{{ $prefix }}occupation"
                    class="form-control @error($prefix . 'occupation') is-invalid @enderror"
                    placeholder="Enter occupation"
                    value="{{ old($prefix . 'occupation', $detail?->occupation) }}">

                @error($prefix . 'occupation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Nationality</label>
                <input type="text" name="{{ $prefix }}nationality"
                    class="form-control @error($prefix . 'nationality') is-invalid @enderror"
                    placeholder="Enter nationality"
                    value="{{ old($prefix . 'nationality', $detail?->nationality ?? 'India') }}">

                @error($prefix . 'nationality')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        </div>

    </div>
</div>