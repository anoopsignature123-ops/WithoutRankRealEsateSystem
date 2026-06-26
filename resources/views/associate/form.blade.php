<div class="row associate-form-grid">
    <div class="col-12 mb-4">
        <div class="border rounded p-4 bg-light">
            <h4 class="fw-bold mb-4">Basic Information</h4>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="mb-2">Sponsor Id</label>
                    <select name="sponsor_id" id="sponsor_id" class="form-control">
                        <option value="">Select Sponsor</option>
                        @foreach ($associates as $item)
                            <option value="{{ $item->associate_id }}"
                                {{ old('sponsor_id', $associate->sponsor_id ?? '') == $item->associate_id ? 'selected' : '' }}>
                                {{ $item->associate_name }}
                                ({{ $item->associate_id }})
                            </option>
                        @endforeach
                    </select>
                    @error('sponsor_id')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="mb-2">Rank</label>
                    <select name="rank_id" id="rank_id" class="form-control">
                        <option value="">Select Rank</option>
                        @if (isset($ranks))
                            @foreach ($ranks as $rank)
                                <option value="{{ $rank->id }}"
                                    {{ old('rank_id', $associate->rank_id ?? '') == $rank->id ? 'selected' : '' }}>
                                    {{ $rank->designation }} / {{ $rank->commission }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('rank_id')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="mb-2">Under Place Id</label>
                    <input type="text" name="under_place_id" id="under_place_id"
                        value="{{ old('under_place_id', $associate->under_place_id ?? '') }}"
                        placeholder="Enter under place id" class="form-control">
                    @error('under_place_id')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>
                {{-- Associate Name --}}
                <div class="col-md-6 mb-3">
                    <label class="mb-2">Associate Name</label>
                    <input type="text" name="associate_name"
                        value="{{ old('associate_name', $associate->associate_name ?? '') }}"
                        placeholder="Enter associate name" class="form-control">
                    @error('associate_name')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>
                {{-- Gender --}}
                <div class="col-md-3 mb-3">
                    <label class="mb-2">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="male"
                            {{ old('gender', $associate->gender ?? '') == 'male' ? 'selected' : '' }}>
                            Male
                        </option>
                        <option value="female"
                            {{ old('gender', $associate->gender ?? '') == 'female' ? 'selected' : '' }}>
                            Female
                        </option>
                    </select>
                    @error('gender')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>
                {{-- Title --}}
                <div class="col-md-3
                            mb-3">
                    <label class="mb-2">Title</label>
                    <select name="title" class="form-control">
                        <option value="">Select Title</option>
                        <option value="s/o" {{ old('title', $associate->title ?? '') == 's/o' ? 'selected' : '' }}>
                            S/O
                        </option>

                        <option value="w/o" {{ old('title', $associate->title ?? '') == 'w/o' ? 'selected' : '' }}>
                            W/O
                        </option>
                        <option value="b/o" {{ old('title', $associate->title ?? '') == 'b/o' ? 'selected' : '' }}>
                            B/O
                        </option>

                        <option value="d/o" {{ old('title', $associate->title ?? '') == 'd/o' ? 'selected' : '' }}>
                            D/O
                        </option>
                        <option value="f/o" {{ old('title', $associate->title ?? '') == 'f/o' ? 'selected' : '' }}>
                            F/O
                        </option>
                    </select>
                    @error('title')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>
                {{-- Father Name --}}
                <div class="col-md-6 mb-3">
                    <label class="mb-2">Father / Husband Name</label>
                    <input type="text" name="father_name"
                        value="{{ old('father_name', $associate->father_name ?? '') }}" placeholder="Enter name"
                        class="form-control">
                    @error('father_name')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>
                {{-- DOB --}}
                <div class="col-md-6 mb-3">
                    <label class="mb-2">Date Of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob', $associate->dob ?? '') }}"
                        class="form-control">
                    @error('dob')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>
                {{-- Address --}}
                <div class="col-md-12 mb-3">
                    <label class="mb-2">Address</label>
                    <textarea name="address" rows="2" class="form-control" placeholder="Enter full address">{{ old('address', $associate->address ?? '') }}</textarea>
                    @error('address')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>
                {{-- City --}}
                {{-- <div class="col-md-3 mb-3">
                    <label class="mb-2">City</label>
                    <input type="text" name="city" value="{{ old('city', $associate->city ?? '') }}"
                        class="form-control" placeholder="Enter City">
                    @error('city')
                        <small class="text-danger d-block ">{{ $message }}</small>
                    @enderror
                </div> --}}
                {{-- State --}}
                {{-- <div class="col-md-3 mb-3">
                    <label class="mb-2">State</label>
                    <input type="text" name="state" value="{{ old('state', $associate->state ?? '') }}"
                        class="form-control" placeholder="Enter State">
                    @error('state')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div> --}}
               
                @include('state-city', [
                    'states' => $states,
                    'selectedState' => old('state', $associate->state ?? ''),
                    'selectedCity' => old('city', $associate->city ?? ''),
                ])
                {{-- Mobile --}}
                <div class="col-md-3 mb-3 mt-3">
                    <label class="mb-2">Mobile Number</label>
                    <input type="text" name="mobile_number"
                        value="{{ old('mobile_number', $associate->mobile_number ?? '') }}" class="form-control"
                        placeholder="Enter Mobile Number">
                    @error('mobile_number')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>
                {{-- Email --}}
                <div class="col-md-3 mb-3 mt-3">
                    <label class="mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email', $associate->email ?? '') }}"
                        class="form-control" placeholder="Enter Email">
                    @error('email')
                        <small class="text-danger d-block ">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6 mb-3 mt-3">
                    <label class="mb-2">PAN Card Number</label>
                    <input type="text" name="pancard_number"
                        value="{{ old('pancard_number', $associate->pancard_number ?? '') }}" class="form-control"
                        placeholder="Enter PAN Card Number">
                    @error('pancard_number')
                        <small class="text-danger d-block ">{{ $message }}</small>
                    @enderror

                </div>
                {{-- Aadhaar --}}
                <div class="col-md-6 mb-3">
                    <label class="mb-2">Aadhaar Number</label>
                    <input type="text" name="aadhar_number"
                        value="{{ old('aadhar_number', $associate->aadhar_number ?? '') }}" class="form-control"
                        placeholder="Enter Aadhaar Number">
                    @error('aadhar_number')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>

            </div>

        </div>

    </div>
    <!-- Bank Details -->
    <div class="col-12 mb-4">

        <div class="border rounded p-4 bg-light">

            <h4 class="fw-bold mb-4">
                Bank Details
            </h4>

            <div class="row">

                {{-- Bank Name --}}
                <div class="col-md-3 mb-3">

                    <label class="mb-2">
                        Bank Name
                    </label>

                    <input type="text" name="bank_name"
                        value="{{ old('bank_name', $associate->bankDetail->bank_name ?? '') }}"
                        placeholder="Enter bank name" class="form-control @error('bank_name') is-invalid @enderror">

                    @error('bank_name')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>


                {{-- Account Number --}}
                <div class="col-md-3 mb-3">

                    <label class="mb-2">
                        Account Number
                    </label>

                    <input type="text" name="account_number"
                        value="{{ old('account_number', $associate->bankDetail->account_number ?? '') }}"
                        placeholder="Enter account number"
                        class="form-control @error('account_number') is-invalid @enderror">

                    @error('account_number')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>


                {{-- IFSC Code --}}
                <div class="col-md-3 mb-3">

                    <label class="mb-2">
                        IFSC Code
                    </label>

                    <input type="text" name="ifsc_code"
                        value="{{ old('ifsc_code', $associate->bankDetail->ifsc_code ?? '') }}"
                        placeholder="Enter IFSC code" class="form-control @error('ifsc_code') is-invalid @enderror">

                    @error('ifsc_code')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>


                {{-- Account Holder Name --}}
                <div class="col-md-3 mb-3">

                    <label class="mb-2">
                        Account Holder Name
                    </label>

                    <input type="text" name="account_holder_name"
                        value="{{ old('account_holder_name', $associate->bankDetail->account_holder_name ?? '') }}"
                        placeholder="Enter account holder name"
                        class="form-control @error('account_holder_name') is-invalid @enderror">

                    @error('account_holder_name')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

            </div>

        </div>

    </div>
    <!-- Nominee Details -->
    <div class="col-12 mb-4">

        <div class="border rounded p-4 bg-light">

            <h4 class="fw-bold mb-4">
                Nominee Details
            </h4>

            <div class="row">

                {{-- Nominee Name --}}
                <div class="col-md-3 mb-3">

                    <label class="mb-2">
                        Nominee Name
                    </label>

                    <input type="text" name="nominee_name"
                        value="{{ old('nominee_name', $associate->nominee_name ?? '') }}"
                        placeholder="Enter nominee name"
                        class="form-control @error('nominee_name') is-invalid @enderror">

                    @error('nominee_name')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>


                {{-- Nominee Relation --}}
                <div class="col-md-3 mb-3">

                    <label class="mb-2">
                        Nominee Relation
                    </label>

                    <input type="text" name="nominee_relation"
                        value="{{ old('nominee_relation', $associate->nominee_relation ?? '') }}"
                        placeholder="Enter relation"
                        class="form-control @error('nominee_relation') is-invalid @enderror">

                    @error('nominee_relation')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>


                {{-- Nominee Age --}}
                <div class="col-md-3 mb-3">

                    <label class="mb-2">
                        Nominee Age
                    </label>

                    <input type="number" name="nominee_age"
                        value="{{ old('nominee_age', $associate->nominee_age ?? '') }}" placeholder="Enter age"
                        class="form-control @error('nominee_age') is-invalid @enderror">

                    @error('nominee_age')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>


                {{-- Joining Date --}}
                <div class="col-md-3 mb-3">
                    <label class="mb-2">
                        Joining Date
                    </label>
                    <input type="date" name="joining_date"
                        value="{{ old('joining_date', $associate->joining_date ?? '') }}"
                        class="form-control @error('joining_date') is-invalid @enderror">
                    @error('joining_date')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- ocument Details -->
    <div class="col-12 mb-4">
        <div class="border rounded p-4 bg-light">

            <h4 class="fw-bold mb-4">
                Document Details
            </h4>

            <div class="row">

                {{-- Photo --}}
                <div class="col-md-3 mb-3">

                    <label class="mb-2">
                        Upload Photo
                    </label>

                    <input type="file" name="photo"
                        class="form-control preview-file @error('photo') is-invalid @enderror">

                    <img class="img-preview mt-2 rounded border"
                        style="width:100px; height:100px; object-fit:cover; display:none;">

                    @if (!empty($associate->photo))
                        <div class="mt-2">
                            <a href="{{ getFileUrl($associate->photo) }}" target="_blank">
                                View Current Photo
                            </a>
                        </div>
                    @endif

                    @error('photo')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>



                {{-- ID Proof --}}
                <div class="col-md-3 mb-3">

                    <label class="mb-2">
                        Upload ID Proof
                    </label>

                    <input type="file" name="id_proof_photo"
                        class="form-control preview-file @error('id_proof_photo') is-invalid @enderror">

                    <img class="img-preview mt-2 rounded border"
                        style="width:100px; height:100px; object-fit:cover; display:none;">

                    @if (!empty($associate->id_proof_photo))
                        <div class="mt-2">
                            <a href="{{ getFileUrl($associate->id_proof_photo) }}" target="_blank">
                                View Current ID Proof
                            </a>
                        </div>
                    @endif

                    @error('id_proof_photo')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>



                {{-- PAN Card --}}
                <div class="col-md-3 mb-3">

                    <label class="mb-2">
                        Upload PAN Card
                    </label>

                    <input type="file" name="pancard_photo"
                        class="form-control preview-file @error('pancard_photo') is-invalid @enderror">

                    <img class="img-preview mt-2 rounded border"
                        style="width:100px; height:100px; object-fit:cover; display:none;">

                    @if (!empty($associate->pancard_photo))
                        <div class="mt-2">
                            <a href="{{ getFileUrl($associate->pancard_photo) }}" target="_blank">
                                View Current PAN Card
                            </a>
                        </div>
                    @endif

                    @error('pancard_photo')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>
                {{-- Bank Passbook --}}
                <div class="col-md-3 mb-3">

                    <label class="mb-2">
                        Upload Bank Passbook
                    </label>

                    <input type="file" name="bank_passbook"
                        class="form-control preview-file @error('bank_passbook') is-invalid @enderror">

                    <img class="img-preview mt-2 rounded border"
                        style="width:100px; height:100px; object-fit:cover; display:none;">

                    @if (!empty($associate->bankDetail->bank_passbook))
                        <div class="mt-2">
                            <a href="{{ getFileUrl($associate->bankDetail->bank_passbook) }}" target="_blank">
                                View Current Passbook
                            </a>
                        </div>
                    @endif

                    @error('bank_passbook')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

            </div>

        </div>
    </div>
    <div class="col-12">
        <div class="associate-form-actions">
            <a href="{{ route('associate.index') }}" class="btn btn-light">
                <i class="bi bi-x-circle"></i>
                Cancel
            </a>
            <button class="btn btn-success px-5" type="submit">
                <i class="bi bi-check-circle"></i>
                {{ isset($associate) ? 'Update Associate' : 'Save Associate' }}
            </button>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        $(function() {
            const $sponsor = $('#sponsor_id');
            const $rank = $('#rank_id');
            const $underPlace = $('#under_place_id');

            $sponsor.on('change', function() {
                const associateId = $(this).val();

                if (associateId) {
                    $underPlace.val(associateId);
                }

                $rank.html('<option value="">Loading...</option>');

                if (!associateId) {
                    $rank.html('<option value="">Select Rank</option>');
                    return;
                }

                $.get('/get-sponsor-ranks/' + associateId, function(response) {
                    let options = '<option value="">Select Rank</option>';

                    response.forEach(function(rank) {
                        options += `
                            <option value="${rank.id}">
                                ${rank.designation} / ${rank.rank_number ?? rank.commission ?? ''}
                            </option>
                        `;
                    });

                    $rank.html(options);
                }).fail(function() {
                    $rank.html('<option value="">Unable to load ranks</option>');
                });
            });

            $('.preview-file').on('change', function() {
                const input = this;
                const preview = $(this).closest('[class*="col-md-"]').find('.img-preview');

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
