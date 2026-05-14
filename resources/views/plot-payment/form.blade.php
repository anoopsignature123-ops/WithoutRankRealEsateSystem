@php
    $payment = $selectedBooking->payment;
    $plotSale = $selectedBooking->plotSaleDetail;
@endphp

<div class="card shadow-sm border-0 mb-4">

    <div class="card-body p-4">

        <h5 class="fw-bold border-bottom pb-2 mb-4">
            Edit Payment Details
        </h5>

        <form method="POST" action="{{ route('admin.edit-payment-details.update', $selectedBooking->id) }}">

            @csrf
            @method('PUT')

            <input type="hidden" name="plot_sale_detail_id" value="{{ $plotSale?->id }}">

            <input type="hidden" id="totalPlotCost" value="{{ $plotSale?->total_plot_cost ?? 0 }}">


            <div class="row">

                {{-- Project --}}
                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Project Name
                    </label>

                    <input type="text" readonly class="form-control" value="{{ $plotSale?->project?->name }}">

                </div>


                {{-- Plan Type --}}
                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Plan Type
                    </label>

                    <select name="plan_type" id="planType" class="form-select">

                        <option value="full_payment"
                            {{ old('plan_type', $payment?->plan_type) == 'full_payment' ? 'selected' : '' }}>
                            Full Payment
                        </option>

                        <option value="emi_plan"
                            {{ old('plan_type', $payment?->plan_type) == 'emi_plan' ? 'selected' : '' }}>
                            EMI Plan
                        </option>

                    </select>

                </div>


                {{-- Payment Mode --}}
                <div class="col-md-4 mb-3 common-field d-none">

                    <label class="form-label">
                        Payment Mode
                    </label>

                    <select name="payment_mode" id="paymentMode" class="form-select">

                        <option value="cash"
                            {{ old('payment_mode', $payment?->payment_mode) == 'cash' ? 'selected' : '' }}>
                            Cash
                        </option>

                        <option value="cheque"
                            {{ old('payment_mode', $payment?->payment_mode) == 'cheque' ? 'selected' : '' }}>
                            Cheque
                        </option>

                        <option value="dd"
                            {{ old('payment_mode', $payment?->payment_mode) == 'dd' ? 'selected' : '' }}>
                            DD
                        </option>

                        <option value="neft_rtgs"
                            {{ old('payment_mode', $payment?->payment_mode) == 'neft_rtgs' ? 'selected' : '' }}>
                            NEFT / RTGS
                        </option>

                        <option value="card"
                            {{ old('payment_mode', $payment?->payment_mode) == 'card' ? 'selected' : '' }}>
                            Card
                        </option>

                    </select>

                </div>


                {{-- Booking Amount --}}
                <div class="col-md-4 mb-3 common-field d-none">

                    <label class="form-label">
                        Booking Amount
                    </label>

                    <input type="number" id="bookingAmount" name="booking_amount" class="form-control"
                        value="{{ old('booking_amount', $payment?->booking_amount) }}">

                </div>


                {{-- Due Amount --}}
                <div class="col-md-4 mb-3 common-field d-none">

                    <label class="form-label">
                        Due Amount
                    </label>

                    <input type="number" id="dueAmount" name="due_amount" readonly class="form-control"
                        value="{{ old('due_amount', $payment?->due_amount) }}">

                </div>


                {{-- EMI Months --}}
                <div class="col-md-4 mb-3 emi-field d-none">

                    <label class="form-label">
                        EMI Months
                    </label>

                    <input type="number" id="emiMonths" name="emi_months" class="form-control"
                        value="{{ old('emi_months', $payment?->emi_months) }}">

                </div>


                {{-- Monthly EMI --}}
                <div class="col-md-4 mb-3 emi-field d-none">

                    <label class="form-label">
                        Monthly EMI
                    </label>

                    <input type="number" id="afterBookingAmount" name="after_booking_payable_amount" readonly
                        class="form-control"
                        value="{{ old('after_booking_payable_amount', $payment?->after_booking_payable_amount) }}">

                </div>
                {{-- Receipt Number --}}
                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Receipt Number
                    </label>
                    <input type="text" name="receipt_number" class="form-control"
                        value="{{ old('receipt_number', $payment?->receipt_number) }}" readonly>
                </div>
                {{-- Account Number --}}
                <div class="col-md-4 mb-3 bank-field d-none">
                    <label class="form-label">
                        Account Number
                    </label>
                    <input type="text" name="account_number" class="form-control"
                        value="{{ old('account_number', $payment?->account_number) }}">
                </div>


                {{-- Bank Name --}}
                <div class="col-md-4 mb-3 bank-detail-field d-none">

                    <label class="form-label">
                        Bank Name
                    </label>

                    <input type="text" name="bank_name" class="form-control"
                        value="{{ old('bank_name', $payment?->bank_name) }}">

                </div>


                {{-- Branch Name --}}
                <div class="col-md-4 mb-3 bank-detail-field d-none">

                    <label class="form-label">
                        Branch Name
                    </label>

                    <input type="text" name="branch_name" class="form-control"
                        value="{{ old('branch_name', $payment?->branch_name) }}">

                </div>


                {{-- Cheque Number --}}
                <div class="col-md-4 mb-3 cheque-number-field d-none">

                    <label class="form-label">
                        Cheque Number
                    </label>

                    <input type="text" name="cheque_number" class="form-control"
                        value="{{ old('cheque_number', $payment?->cheque_number) }}">

                </div>


                {{-- DD Number --}}
                <div class="col-md-4 mb-3 dd-number-field d-none">

                    <label class="form-label">
                        DD Number
                    </label>

                    <input type="text" name="dd_number" class="form-control"
                        value="{{ old('dd_number', $payment?->dd_number) }}">

                </div>


                {{-- Submit --}}
                <div class="col-md-12 mt-3">

                    <button type="submit" class="btn btn-success px-4">

                        <i class="fa fa-save me-1"></i>

                        Update Payment

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>


@push('scripts')
    <script>
        $(document).ready(function() {

            function resetFields() {

                $('.common-field').addClass('d-none');
                $('.emi-field').addClass('d-none');
                $('.bank-field').addClass('d-none');
                $('.bank-detail-field').addClass('d-none');
                $('.cheque-number-field').addClass('d-none');
                $('.dd-number-field').addClass('d-none');

            }


            function calculateAmounts() {

                let total =
                    parseFloat($('#totalPlotCost').val()) || 0;

                let booking =
                    parseFloat($('#bookingAmount').val()) || 0;

                let plan =
                    $('#planType').val();


                if (plan == 'full_payment') {

                    booking = total;

                    $('#bookingAmount')
                        .val(total.toFixed(2))
                        .prop('readonly', true);

                } else {

                    $('#bookingAmount')
                        .prop('readonly', false);

                }


                let due = total - booking;

                if (due < 0) {
                    due = 0;
                }

                $('#dueAmount').val(
                    due.toFixed(2)
                );


                if (plan == 'emi_plan') {

                    let months =
                        parseInt($('#emiMonths').val()) || 0;

                    if (months > 0) {

                        $('#afterBookingAmount').val(
                            (due / months).toFixed(2)
                        );

                    } else {

                        $('#afterBookingAmount').val('');

                    }

                }

            }


            function loadFields() {

                resetFields();

                let plan =
                    $('#planType').val();


                $('.common-field')
                    .removeClass('d-none');


                if (plan == 'emi_plan') {

                    $('.emi-field')
                        .removeClass('d-none');

                }


                $('#paymentMode').trigger('change');

                calculateAmounts();

            }


            $('#planType').change(loadFields);


            $('#paymentMode').change(function() {

                resetFields();

                let plan =
                    $('#planType').val();

                let mode =
                    $(this).val();


                $('.common-field')
                    .removeClass('d-none');


                if (plan == 'emi_plan') {

                    $('.emi-field')
                        .removeClass('d-none');

                }


                if (
                    mode == 'cheque' ||
                    mode == 'dd' ||
                    mode == 'neft_rtgs'
                ) {

                    $('.bank-field')
                        .removeClass('d-none');

                    $('.bank-detail-field')
                        .removeClass('d-none');

                }


                if (mode == 'cheque') {

                    $('.cheque-number-field')
                        .removeClass('d-none');

                }


                if (mode == 'dd') {

                    $('.dd-number-field')
                        .removeClass('d-none');

                }

            });


            $('#bookingAmount, #emiMonths')
                .on('keyup change', calculateAmounts);


            loadFields();

        });
    </script>
@endpush
