@if ($step == 2)
    @php
        $primary = $customer?->primaryDetail;
        $secondaryToggle = old('fill_secondary_detail', $primary?->fill_secondary_detail ?? 'no');
    @endphp

    <form method="POST" action="{{ route('customer-booking.update', $customer->id) }}">
        @csrf
        @method('PUT')

        <input type="hidden" name="step" value="2">

        @include('customer-booking.partials.primary-form')

        @include('customer-booking.partials.correspondence-form')

        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
            <div class="card-header bg-success text-white py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center"
                        style="width:48px;height:48px;">
                        <i class="bi bi-person-plus fs-4"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold mb-0">Secondary Applicant</h5>
                        <small class="text-white-50">
                            Add second applicant details if required.
                        </small>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <label class="form-label fw-semibold d-block mb-3">
                    Add Secondary Applicant? <span class="text-danger">*</span>
                </label>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="card border rounded-4 p-3 h-100">
                            <div class="d-flex align-items-center gap-3">
                                <input type="radio" class="form-check-input secondaryToggle"
                                    name="fill_secondary_detail" value="yes"
                                    {{ $secondaryToggle == 'yes' ? 'checked' : '' }}>

                                <div>
                                    <div class="fw-bold">
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                        Yes, Add Secondary Applicant
                                    </div>
                                    <small class="text-muted">
                                        Secondary applicant details will be required.
                                    </small>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="col-md-6">
                        <label class="card border rounded-4 p-3 h-100">
                            <div class="d-flex align-items-center gap-3">
                                <input type="radio" class="form-check-input secondaryToggle"
                                    name="fill_secondary_detail" value="no"
                                    {{ $secondaryToggle == 'no' ? 'checked' : '' }}>

                                <div>
                                    <div class="fw-bold">
                                        <i class="bi bi-x-circle text-secondary me-1"></i>
                                        No Secondary Applicant
                                    </div>
                                    <small class="text-muted">
                                        Continue with primary applicant only.
                                    </small>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                @error('fill_secondary_detail')
                    <div class="text-danger small fw-semibold mt-2">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div id="secondarySection" style="{{ $secondaryToggle == 'yes' ? '' : 'display:none;' }}">
            @include('customer-booking.partials.secondary-form')
        </div>

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('customer-booking.edit', [$customer->id, 'step' => 1]) }}"
                class="btn btn-outline-secondary px-4">
                Previous
            </a>

            <button type="submit" class="btn btn-success ms-2 px-4">
                Save & Next
            </button>
        </div>
    </form>
@endif

@push('scripts')
    <script>
        $(document).ready(function() {

            $('.addressToggle').on('change', function() {
                if ($(this).val() == 'yes') {
                    $('#corrAddress').val($('#primaryAddress').val());
                    $('#corrPin').val($('#primaryPin').val());
                    $('#corrCity').val($('#primaryCity').val());
                    $('#corrState').val($('#primaryState').val());
                } else {
                    $('#corrAddress').val('');
                    $('#corrPin').val('');
                    $('#corrCity').val('');
                    $('#corrState').val('');
                }
            });

            $('.secondaryToggle').on('change', function() {
                if ($(this).val() == 'yes') {
                    $('#secondarySection').stop(true, true).slideDown(300);
                } else {
                    $('#secondarySection').stop(true, true).slideUp(300);
                }
            });

            $(document).on('change', '.secondary_AddressToggle', function() {
                if ($(this).val() == 'yes') {
                    $('#secondary_CorrAddress').val($('#secondaryAddress').val());
                    $('#secondary_CorrPin').val($('#secondaryPin').val());
                    $('#secondary_CorrCity').val($('#secondaryCity').val());
                    $('#secondary_CorrState').val($('#secondaryState').val());
                } else {
                    $('#secondary_CorrAddress').val('');
                    $('#secondary_CorrPin').val('');
                    $('#secondary_CorrCity').val('');
                    $('#secondary_CorrState').val('');
                }
            });

        });
    </script>
@endpush