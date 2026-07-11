@extends('layouts.app')

@push('title')
    Associate Panel |  Edit Profile
@endpush
@section('content')
    @php
        $bank = $associate->bankDetail;
        $documents = [
            ['name' => 'photo', 'label' => 'Profile Photo', 'file' => $associate->photo, 'icon' => 'bi-person-square'],
            ['name' => 'id_proof_photo', 'label' => 'ID Proof', 'file' => $associate->id_proof_photo, 'icon' => 'bi-card-checklist'],
            ['name' => 'pancard_photo', 'label' => 'PAN Card', 'file' => $associate->pancard_photo, 'icon' => 'bi-credit-card-2-front'],
            ['name' => 'bank_passbook', 'label' => 'Bank Passbook', 'file' => $bank?->bank_passbook, 'icon' => 'bi-bank'],
        ];
    @endphp

    <div class="container-fluid mt-4 transaction-page">
        <form method="POST" action="{{ route('associate-panel.update-profile') }}" enctype="multipart/form-data">
            @csrf

            <div class="transaction-hero mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-icon"><i class="bi bi-person-gear"></i></span>
                        <div>
                            <span class="text-success fw-bold text-uppercase small">Associate Profile</span>
                            <h3 class="fw-bold mb-1 text-dark">Modify Profile Information</h3>
                            <p class="text-muted mb-0 small">Update your personal, bank, nominee and document details carefully.</p>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('associate-panel.view-profile') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>

            {{-- @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif --}}

            <div class="row g-4">
                <div class="col-xl-6">
                    <div class="transaction-card h-100">
                        <div class="transaction-card-body">
                            <div class="transaction-section-title">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="transaction-section-title-icon"><i class="bi bi-person-vcard"></i></span>
                                    <div>
                                        <h5 class="fw-bold mb-1">Personal Details</h5>
                                        <small class="text-muted">Identity and basic profile information.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Sponsor Name</label>
                                    <input type="text" class="form-control" value="{{ $associate->sponsor?->associate_name ?? '-' }}" disabled>
                                    <small class="text-muted">Sponsor details cannot be modified.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Associate Name <span class="text-danger">*</span></label>
                                    <input type="text" name="associate_name"
                                        class="form-control @error('associate_name') is-invalid @enderror"
                                        placeholder="Enter associate name"
                                        value="{{ old('associate_name', $associate->associate_name) }}">
                                    @error('associate_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                        @foreach (['Male', 'Female', 'Other'] as $gender)
                                            <option value="{{ $gender }}" {{ old('gender', $associate->gender) == $gender ? 'selected' : '' }}>
                                                {{ $gender }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Father / Husband Name <span class="text-danger">*</span></label>
                                    <input type="text" name="father_name"
                                        class="form-control @error('father_name') is-invalid @enderror"
                                        placeholder="Enter father or husband name"
                                        value="{{ old('father_name', $associate->father_name) }}">
                                    @error('father_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Date Of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror"
                                        placeholder="Select date of birth"
                                        value="{{ old('dob', $associate->dob ? \Carbon\Carbon::parse($associate->dob)->format('Y-m-d') : '') }}">
                                    @error('dob')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="transaction-card h-100">
                        <div class="transaction-card-body">
                            <div class="transaction-section-title">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="transaction-section-title-icon"><i class="bi bi-person-heart"></i></span>
                                    <div>
                                        <h5 class="fw-bold mb-1">Nominee Details</h5>
                                        <small class="text-muted">Nominee information for associate profile.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Nominee Name <span class="text-danger">*</span></label>
                                    <input type="text" name="nominee_name"
                                        class="form-control @error('nominee_name') is-invalid @enderror"
                                        placeholder="Enter nominee name"
                                        value="{{ old('nominee_name', $bank?->nominee_name ?? '') }}">
                                    @error('nominee_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nominee Relation <span class="text-danger">*</span></label>
                                    <input type="text" name="nominee_relation"
                                        class="form-control @error('nominee_relation') is-invalid @enderror"
                                        placeholder="Enter nominee relation"
                                        value="{{ old('nominee_relation', $bank?->nominee_relation ?? '') }}">
                                    @error('nominee_relation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nominee Age <span class="text-danger">*</span></label>
                                    <input type="number" name="nominee_age"
                                        class="form-control @error('nominee_age') is-invalid @enderror"
                                        placeholder="Enter nominee age"
                                        value="{{ old('nominee_age', $bank?->nominee_age ?? '') }}">
                                    @error('nominee_age')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="transaction-card h-100">
                        <div class="transaction-card-body">
                            <div class="transaction-section-title">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="transaction-section-title-icon"><i class="bi bi-geo-alt"></i></span>
                                    <div>
                                        <h5 class="fw-bold mb-1">Address & KYC</h5>
                                        <small class="text-muted">Contact address and identity numbers.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                                    <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror"
                                        placeholder="Enter complete address">{{ old('address', $associate->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @include('state-city', [
                                    'states' => $states,
                                    'stateId' => 'associateProfileState',
                                    'cityId' => 'associateProfileCity',
                                    'stateName' => 'state',
                                    'cityName' => 'city',
                                    'selectedState' => old('state', $associate->state),
                                    'selectedCity' => old('city', $associate->city),
                                ])
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mobile <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror"
                                        placeholder="Enter 10 digit mobile number"
                                        value="{{ old('mobile_number', $associate->mobile_number) }}">
                                    @error('mobile_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                        placeholder="Enter email address"
                                        value="{{ old('email', $associate->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">PAN Card No <span class="text-danger">*</span></label>
                                    <input type="text" name="pancard_number" class="form-control @error('pancard_number') is-invalid @enderror"
                                        placeholder="Enter PAN card number"
                                        value="{{ old('pancard_number', $associate->pancard_number) }}">
                                    @error('pancard_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Aadhaar No <span class="text-danger">*</span></label>
                                    <input type="text" name="aadhar_number" class="form-control @error('aadhar_number') is-invalid @enderror"
                                        placeholder="Enter 12 digit Aadhaar number"
                                        value="{{ old('aadhar_number', $associate->aadhar_number) }}">
                                    @error('aadhar_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="transaction-card h-100">
                        <div class="transaction-card-body">
                            <div class="transaction-section-title">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="transaction-section-title-icon"><i class="bi bi-bank"></i></span>
                                    <div>
                                        <h5 class="fw-bold mb-1">Bank Details</h5>
                                        <small class="text-muted">Payout bank account details.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                                        placeholder="Enter bank name"
                                        value="{{ old('bank_name', $bank?->bank_name ?? '') }}">
                                    @error('bank_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Account No <span class="text-danger">*</span></label>
                                    <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror"
                                        placeholder="Enter account number"
                                        value="{{ old('account_number', $bank?->account_number ?? '') }}">
                                    @error('account_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">IFSC Code <span class="text-danger">*</span></label>
                                    <input type="text" name="ifsc_code" class="form-control @error('ifsc_code') is-invalid @enderror"
                                        placeholder="Enter IFSC code"
                                        value="{{ old('ifsc_code', $bank?->ifsc_code ?? '') }}">
                                    @error('ifsc_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Account Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_holder_name"
                                        class="form-control @error('account_holder_name') is-invalid @enderror"
                                        placeholder="Enter account holder name"
                                        value="{{ old('account_holder_name', $bank?->account_holder_name ?? '') }}">
                                    @error('account_holder_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="transaction-card">
                        <div class="transaction-card-body">
                            <div class="transaction-section-title">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="transaction-section-title-icon"><i class="bi bi-folder2-open"></i></span>
                                    <div>
                                        <h5 class="fw-bold mb-1">Document Upload</h5>
                                        <small class="text-muted">Upload JPG, JPEG or PNG files up to 2 MB.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                @foreach ($documents as $document)
                                    <div class="col-xl-3 col-md-6 document-upload">
                                        <div class="border rounded p-3 h-100 bg-light">
                                            <div class="d-flex align-items-start gap-3">
                                                <span class="transaction-section-title-icon"><i class="bi {{ $document['icon'] }}"></i></span>
                                                <div class="flex-grow-1">
                                                    <label class="form-label fw-semibold mb-1">{{ $document['label'] }}</label>
                                                    <input type="file" name="{{ $document['name'] }}" accept="image/*"
                                                        class="form-control preview-file @error($document['name']) is-invalid @enderror">
                                                    <img class="img-preview mt-2 rounded border"
                                                        style="width:92px;height:92px;object-fit:cover;display:none;" alt="Preview">
                                                    @if ($document['file'])
                                                        <a href="{{ getFileUrl($document['file']) }}" target="_blank" class="btn btn-outline-success btn-sm mt-2">
                                                            <i class="bi bi-eye me-1"></i> Current File
                                                        </a>
                                                    @else
                                                        <small class="text-muted d-block mt-2">No file uploaded</small>
                                                    @endif
                                                    @error($document['name'])
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="transaction-card">
                        <div class="transaction-card-body">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div>
                                    <h5 class="fw-bold mb-1">Save Profile Changes</h5>
                                    <small class="text-muted">Please review all profile information before saving.</small>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('associate-panel.view-profile') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                                    <button type="submit" class="btn btn-success px-5">
                                        <i class="bi bi-check-circle me-1"></i> Save Profile Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

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
