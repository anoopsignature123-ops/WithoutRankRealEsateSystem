@php
    $isEdit = isset($associate);
    $sponsorAssociate = $loggedInAssociate ?? (auth('associate')->user() ?? auth()->user());
    $currentBank = $associate->bankDetail ?? null;
@endphp

<div class="transaction-card mb-4">
    <div class="transaction-card-body">
        <div class="transaction-section-title">
            <div class="d-flex align-items-center gap-3">
                <span class="transaction-section-title-icon"><i class="bi bi-person-lines-fill"></i></span>
                <div>
                    <h5 class="fw-bold mb-1">Basic Information</h5>
                    <small class="text-muted">Sponsor, placement and personal details.</small>
                </div>
            </div>
            <span class="badge bg-success-subtle text-success border border-success-subtle">
                {{ $isEdit ? 'Update Mode' : 'New Registration' }}
            </span>
        </div>

        <div class="row g-3">
            <div class="col-lg-4 col-md-6">
                <label class="form-label fw-semibold">Sponsor ID</label>
                <input type="text" class="form-control" value="{{ $sponsorAssociate?->associate_id }}" readonly>
                <input type="hidden" name="sponsor_id" value="{{ $sponsorAssociate?->associate_id }}">
            </div>

            <div class="col-lg-4 col-md-6">
                <label class="form-label fw-semibold">Direction</label>

                <input type="text" class="form-control"
                    value="{{ ucfirst($sponsorAssociate?->direction ?? 'root') }}" readonly>

                <input type="hidden" name="direction" value="{{ $sponsorAssociate?->direction }}">
            </div>

            <div class="col-lg-4 col-md-6">
                <label class="form-label fw-semibold">Under Place ID</label>
                <input type="text" name="under_place_id" id="under_place_id"
                    value="{{ old('under_place_id', $associate->under_place_id ?? $sponsorAssociate?->associate_id) }}"
                    class="form-control @error('under_place_id') is-invalid @enderror" readonly>
                @error('under_place_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6">
                <label class="form-label fw-semibold">Associate Name <span class="text-danger">*</span></label>
                <input type="text" name="associate_name"
                    value="{{ old('associate_name', $associate->associate_name ?? '') }}"
                    placeholder="Enter associate name"
                    class="form-control @error('associate_name') is-invalid @enderror">
                @error('associate_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold">Gender <span class="text-danger">*</span> </label>
                <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                    <option value="">Select Gender</option>
                    <option value="male" {{ old('gender', $associate->gender ?? '') == 'male' ? 'selected' : '' }}>
                        Male</option>
                    <option value="female" {{ old('gender', $associate->gender ?? '') == 'female' ? 'selected' : '' }}>
                        Female</option>
                </select>
                @error('gender')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold">Title <span class="text-danger">*</span> </label>
                <select name="title" class="form-select @error('title') is-invalid @enderror">
                    <option value="">Select Title</option>
                    @foreach (['s/o' => 'S/O', 'w/o' => 'W/O', 'b/o' => 'B/O', 'd/o' => 'D/O', 'f/o' => 'F/O'] as $value => $label)
                        <option value="{{ $value }}"
                            {{ old('title', $associate->title ?? '') == $value ? 'selected' : '' }}>
                            {{ $label }}</option>
                    @endforeach
                </select>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6">
                <label class="form-label fw-semibold">Father / Husband Name <span class="text-danger">*</span> </label>
                <input type="text" name="father_name"
                    value="{{ old('father_name', $associate->father_name ?? '') }}"
                    placeholder="Enter father or husband name"
                    class="form-control @error('father_name') is-invalid @enderror">
                @error('father_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6 col-md-6">
                <label class="form-label fw-semibold">Date Of Birth <span class="text-danger">*</span> </label>
                <input type="date" name="dob" value="{{ old('dob', $associate->dob ?? '') }}"
                    class="form-control @error('dob') is-invalid @enderror">
                @error('dob')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6 col-md-6">
                <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span> </label>
                <input type="text" name="mobile_number"
                    value="{{ old('mobile_number', $associate->mobile_number ?? '') }}"
                    placeholder="Enter mobile number" class="form-control @error('mobile_number') is-invalid @enderror">
                @error('mobile_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6 col-md-6">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" value="{{ old('email', $associate->email ?? '') }}"
                    placeholder="Enter email" class="form-control @error('email') is-invalid @enderror">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @include('state-city', [
                'states' => $states,
                'stateId' => 'associateRegistrationState',
                'cityId' => 'associateRegistrationCity',
                'stateName' => 'state',
                'cityName' => 'city',
                'selectedState' => old('state', $associate->state ?? ''),
                'selectedCity' => old('city', $associate->city ?? ''),
            ])

            <div class="col-lg-6 col-md-6">
                <label class="form-label fw-semibold">PAN Card Number</label>
                <input type="text" name="pancard_number"
                    value="{{ old('pancard_number', $associate->pancard_number ?? '') }}"
                    placeholder="Enter PAN card number"
                    class="form-control @error('pancard_number') is-invalid @enderror">
                @error('pancard_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6 col-md-6">
                <label class="form-label fw-semibold">Aadhaar Number</label>
                <input type="text" name="aadhar_number"
                    value="{{ old('aadhar_number', $associate->aadhar_number ?? '') }}"
                    placeholder="Enter Aadhaar number"
                    class="form-control @error('aadhar_number') is-invalid @enderror">
                @error('aadhar_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Address <span class="text-danger">*</span> </label>
                <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror"
                    placeholder="Enter full address">{{ old('address', $associate->address ?? '') }}</textarea>
                @error('address')
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
                <span class="transaction-section-title-icon"><i class="bi bi-bank"></i></span>
                <div>
                    <h5 class="fw-bold mb-1">Bank Details</h5>
                    <small class="text-muted">Commission payout bank account information.</small>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold">Bank Name</label>
                <input type="text" name="bank_name" value="{{ old('bank_name', $currentBank->bank_name ?? '') }}"
                    placeholder="Enter bank name" class="form-control @error('bank_name') is-invalid @enderror">
                @error('bank_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold">Account Number</label>
                <input type="text" name="account_number"
                    value="{{ old('account_number', $currentBank->account_number ?? '') }}"
                    placeholder="Enter account number"
                    class="form-control @error('account_number') is-invalid @enderror">
                @error('account_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold">IFSC Code</label>
                <input type="text" name="ifsc_code" value="{{ old('ifsc_code', $currentBank->ifsc_code ?? '') }}"
                    placeholder="Enter IFSC code" class="form-control @error('ifsc_code') is-invalid @enderror">
                @error('ifsc_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold">Account Holder Name</label>
                <input type="text" name="account_holder_name"
                    value="{{ old('account_holder_name', $currentBank->account_holder_name ?? '') }}"
                    placeholder="Enter account holder name"
                    class="form-control @error('account_holder_name') is-invalid @enderror">
                @error('account_holder_name')
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
                <span class="transaction-section-title-icon"><i class="bi bi-person-heart"></i></span>
                <div>
                    <h5 class="fw-bold mb-1">Nominee Details</h5>
                    <small class="text-muted">Nominee and joining information.</small>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold">Nominee Name</label>
                <input type="text" name="nominee_name"
                    value="{{ old('nominee_name', $associate->nominee_name ?? '') }}"
                    placeholder="Enter nominee name"
                    class="form-control @error('nominee_name') is-invalid @enderror">
                @error('nominee_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold">Nominee Relation</label>
                <input type="text" name="nominee_relation"
                    value="{{ old('nominee_relation', $associate->nominee_relation ?? '') }}"
                    placeholder="Enter relation"
                    class="form-control @error('nominee_relation') is-invalid @enderror">
                @error('nominee_relation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold">Nominee Age</label>
                <input type="number" name="nominee_age"
                    value="{{ old('nominee_age', $associate->nominee_age ?? '') }}" placeholder="Enter age"
                    class="form-control @error('nominee_age') is-invalid @enderror">
                @error('nominee_age')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-semibold">Joining Date</label>
                <input type="date" name="joining_date"
                    value="{{ old('joining_date', $associate->joining_date ?? '') }}"
                    class="form-control @error('joining_date') is-invalid @enderror">
                @error('joining_date')
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
                <span class="transaction-section-title-icon"><i class="bi bi-folder2-open"></i></span>
                <div>
                    <h5 class="fw-bold mb-1">Document Details</h5>
                    <small class="text-muted">Upload associate photo and KYC documents.</small>
                </div>
            </div>
        </div>

        <div class="row g-3">
            @foreach ([['name' => 'photo', 'label' => 'Upload Photo', 'current' => $associate->photo ?? null], ['name' => 'id_proof_photo', 'label' => 'Upload ID Proof', 'current' => $associate->id_proof_photo ?? null], ['name' => 'pancard_photo', 'label' => 'Upload PAN Card', 'current' => $associate->pancard_photo ?? null], ['name' => 'bank_passbook', 'label' => 'Upload Bank Passbook', 'current' => $currentBank->bank_passbook ?? null]] as $document)
                <div class="col-lg-3 col-md-6 document-upload">
                    <label class="form-label fw-semibold">{{ $document['label'] }}</label>
                    <input type="file" name="{{ $document['name'] }}"
                        class="form-control preview-file @error($document['name']) is-invalid @enderror">
                    <img class="img-preview mt-2 rounded border"
                        style="width:100px;height:100px;object-fit:cover;display:none;" alt="Preview">
                    @if (!empty($document['current']))
                        <div class="mt-2">
                            <a href="{{ getFileUrl($document['current']) }}" target="_blank"
                                class="text-success fw-semibold">
                                View Current File
                            </a>
                        </div>
                    @endif
                    @error($document['name'])
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="transaction-card">
    <div class="transaction-card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold mb-1">{{ $isEdit ? 'Update Associate' : 'Save Associate' }}</h5>
                <small class="text-muted">Please review all details before submitting.</small>
            </div>
            <button type="submit" class="btn btn-success px-5">
                <i class="bi bi-check-circle me-1"></i> {{ $isEdit ? 'Update Associate' : 'Save Associate' }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.preview-file').on('change', function() {
                const input = this;
                const preview = $(this).closest('.document-upload').find('.img-preview');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.attr('src', e.target.result).show();
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            });
        });
    </script>
@endpush
