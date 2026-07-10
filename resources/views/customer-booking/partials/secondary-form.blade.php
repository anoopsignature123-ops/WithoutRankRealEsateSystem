@php
    $secondary = $customer?->secondaryDetail;
@endphp

<div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">

    <div class="card-header bg-success text-white py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center"
                style="width:48px;height:48px;">
                <i class="bi bi-person-badge fs-4"></i>
            </div>

            <div>
                <h5 class="fw-bold mb-0">Secondary Applicant Details</h5>
                <small class="text-white-50">
                    Fill second applicant personal and permanent address details.
                </small>
            </div>
        </div>
    </div>

    <div class="card-body p-4">

        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" name="secondary_name"
                    class="form-control @error('secondary_name') is-invalid @enderror"
                    placeholder="Enter secondary applicant name" value="{{ old('secondary_name', $secondary?->name) }}">

                @error('secondary_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Relation Type</label>
                <select name="secondary_title" class="form-select @error('secondary_title') is-invalid @enderror">
                    <option value="">Select relation</option>
                    <option value="s/o" {{ old('secondary_title', $secondary?->title) == 's/o' ? 'selected' : '' }}>
                        S/O</option>
                    <option value="w/o" {{ old('secondary_title', $secondary?->title) == 'w/o' ? 'selected' : '' }}>
                        W/O</option>
                    <option value="d/o" {{ old('secondary_title', $secondary?->title) == 'd/o' ? 'selected' : '' }}>
                        D/O</option>
                    <option value="c/o" {{ old('secondary_title', $secondary?->title) == 'c/o' ? 'selected' : '' }}>
                        C/O</option>
                </select>

                @error('secondary_title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Relation Name</label>
                <input type="text" name="secondary_relation_name"
                    class="form-control @error('secondary_relation_name') is-invalid @enderror"
                    placeholder="Enter relation name"
                    value="{{ old('secondary_relation_name', $secondary?->relation_name) }}">

                @error('secondary_relation_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Date Of Birth</label>
                <input type="date" name="secondary_dob"
                    class="form-control @error('secondary_dob') is-invalid @enderror"
                    value="{{ old('secondary_dob', $secondary?->dob ? \Carbon\Carbon::parse($secondary->dob)->format('Y-m-d') : '') }}">

                @error('secondary_dob')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Gender</label>
                <select name="secondary_gender" class="form-select @error('secondary_gender') is-invalid @enderror">
                    <option value="">Select gender</option>
                    <option value="male"
                        {{ old('secondary_gender', $secondary?->gender) == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female"
                        {{ old('secondary_gender', $secondary?->gender) == 'female' ? 'selected' : '' }}>Female
                    </option>
                </select>

                @error('secondary_gender')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Pin Code</label>
                <input type="text" id="secondaryPin" name="secondary_pin_code"
                    class="form-control @error('secondary_pin_code') is-invalid @enderror" placeholder="Enter pin code"
                    value="{{ old('secondary_pin_code', $secondary?->pin_code) }}">

                @error('secondary_pin_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @include('state-city', [
                'states' => $states,
                'stateId' => 'secondaryState',
                'cityId' => 'secondaryCity',
                'stateName' => 'secondary_state',
                'cityName' => 'secondary_city',
                'selectedState' => old('secondary_state', $secondary->state ?? ''),
                'selectedCity' => old('secondary_city', $secondary->city ?? ''),
            ])

            <div class="col-md-12">
                <label class="form-label fw-semibold">Permanent Address</label>
                <textarea name="secondary_permanent_address" id="secondaryAddress" rows="3"
                    class="form-control @error('secondary_permanent_address') is-invalid @enderror"
                    placeholder="Enter complete permanent address">{{ old('secondary_permanent_address', $secondary?->permanent_address) }}</textarea>

                @error('secondary_permanent_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

@include('customer-booking.partials.correspondence-form', [
    'prefix' => 'secondary_',
    'title' => 'Secondary Correspondence Details',
])
