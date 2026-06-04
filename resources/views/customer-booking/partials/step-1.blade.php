<div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">

    <div class="card-header bg-success text-white py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center"
                style="width:48px;height:48px;">
                <i class="bi bi-person-check fs-4"></i>
            </div>

            <div>
                <h5 class="fw-bold mb-0">Customer Type</h5>
                <small class="text-white-50">
                    Select booking customer category before proceeding.
                </small>
            </div>
        </div>
    </div>

    <div class="card-body p-4">

        <div class="row g-3">

            <div class="col-md-4">
                <label class="card h-100 border rounded-4 p-3 cursor-pointer">
                    <div class="d-flex align-items-start gap-3">
                        <input class="form-check-input customerType mt-1" type="radio" name="customer_type"
                            value="returning_customer" id="returningCustomer"
                            {{ old('customer_type', $customer->customer_type ?? '') == 'returning_customer' ? 'checked' : '' }}>

                        <div>
                            <div class="fw-bold mb-1">
                                <i class="bi bi-arrow-repeat text-success me-1"></i>
                                Returning Customer
                            </div>
                            <small class="text-muted">
                                Existing customer booking.
                            </small>
                        </div>
                    </div>
                </label>
            </div>

            <div class="col-md-4">
                <label class="card h-100 border rounded-4 p-3 cursor-pointer">
                    <div class="d-flex align-items-start gap-3">
                        <input class="form-check-input customerType mt-1" type="radio" name="customer_type"
                            value="sale_customer" id="saleCustomer"
                            {{ old('customer_type', $customer->customer_type ?? '') == 'sale_customer' ? 'checked' : '' }}>

                        <div>
                            <div class="fw-bold mb-1">
                                <i class="bi bi-person-plus text-success me-1"></i>
                                Sale Customer
                            </div>
                            <small class="text-muted">
                                New customer direct sale.
                            </small>
                        </div>
                    </div>
                </label>
            </div>

            <div class="col-md-4">
                <label class="card h-100 border rounded-4 p-3 cursor-pointer">
                    <div class="d-flex align-items-start gap-3">
                        <input class="form-check-input customerType mt-1" type="radio" name="customer_type"
                            value="sale_to_associate" id="saleToAssociate"
                            {{ old('customer_type', $customer->customer_type ?? '') == 'sale_to_associate' ? 'checked' : '' }}>

                        <div>
                            <div class="fw-bold mb-1">
                                <i class="bi bi-people text-success me-1"></i>
                                Sale To Associate
                            </div>
                            <small class="text-muted">
                                Booking through associate sale.
                            </small>
                        </div>
                    </div>
                </label>
            </div>

            @error('customer_type')
                <div class="col-md-12">
                    <small class="text-danger fw-semibold">{{ $message }}</small>
                </div>
            @enderror

        </div>

    </div>
</div>

<div id="returningCustomerSection" class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden"
    style="display:none;">

    <div class="card-header bg-light py-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-vcard text-success fs-5"></i>
            <div>
                <h6 class="fw-bold mb-0">Existing Customer Details</h6>
                <small class="text-muted">
                    Search and select existing customer details.
                </small>
            </div>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Select Customer <span class="text-danger">*</span>
                </label>

                <select name="existing_customer_id" id="existingCustomer"
                    class="form-select @error('existing_customer_id') is-invalid @enderror">
                    <option value="">Select customer</option>

                    @foreach ($customers as $existingCustomer)
                        <option value="{{ $existingCustomer->id }}"
                            data-code="{{ $existingCustomer->customer_code }}"
                            data-name="{{ $existingCustomer->customer_name }}"
                            {{ old('existing_customer_id', $customer->customer_id ?? '') == $existingCustomer->id ? 'selected' : '' }}>
                            {{ $existingCustomer->customer_code }} / {{ $existingCustomer->customer_name }}
                        </option>
                    @endforeach
                </select>

                @error('existing_customer_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Customer Code</label>

                <input type="text" name="customer_id" id="customerCode"
                    class="form-control bg-light @error('customer_id') is-invalid @enderror"
                    readonly placeholder="Auto filled"
                    value="{{ old('customer_id', $customer->customer_code ?? '') }}">

                @error('customer_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">

    <div class="card-header bg-light py-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-diagram-3 text-success fs-5"></i>
            <div>
                <h6 class="fw-bold mb-0">Associate Details</h6>
                <small class="text-muted">
                    Select associate to attach booking reference.
                </small>
            </div>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-3">

            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    Select Associate
                </label>

                <select name="associate_id" id="associateSelect"
                    class="form-select @error('associate_id') is-invalid @enderror">
                    <option value="">Select associate</option>

                    @foreach ($associates as $associate)
                        <option value="{{ $associate->id }}"
                            data-code="{{ $associate->associate_id }}"
                            data-name="{{ $associate->associate_name }}"
                            {{ old('associate_id', $customer->associate_id ?? '') == $associate->id ? 'selected' : '' }}>
                            {{ $associate->associate_id }} / {{ $associate->associate_name }}
                        </option>
                    @endforeach
                </select>

                @error('associate_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Associate Code</label>

                <input type="text" name="associate_code" id="associateCode"
                    class="form-control bg-light @error('associate_code') is-invalid @enderror"
                    readonly placeholder="Auto filled"
                    value="{{ old('associate_code', $customer->associate_code ?? '') }}">

                @error('associate_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Associate Name</label>

                <input type="text" name="associate_name" id="associateName"
                    class="form-control bg-light @error('associate_name') is-invalid @enderror"
                    readonly placeholder="Auto filled"
                    value="{{ old('associate_name', $customer->associate_name ?? '') }}">

                @error('associate_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        </div>
    </div>
</div>

<div class="text-end">
    <button type="submit" class="btn btn-success px-4">
        Save & Next
    </button>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {

            function toggleCustomerSection() {
                let selectedType = $('input[name="customer_type"]:checked').val();

                if (selectedType == 'returning_customer') {
                    $('#returningCustomerSection').slideDown();
                } else {
                    $('#returningCustomerSection').slideUp();
                    $('#existingCustomer').val('');
                    $('#customerCode').val('');
                }
            }

            toggleCustomerSection();

            $('.customerType').change(function() {
                toggleCustomerSection();
            });

            $('#associateSelect').change(function() {
                let selected = $(this).find(':selected');

                $('#associateCode').val(selected.data('code') ?? '');
                $('#associateName').val(selected.data('name') ?? '');
            });

            $('#existingCustomer').change(function() {
                let selected = $(this).find(':selected');

                $('#customerCode').val(selected.data('code') ?? '');
            });

            $('#associateSelect').trigger('change');
            $('#existingCustomer').trigger('change');
        });
    </script>
@endpush