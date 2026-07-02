@if ($step == 5)
    <form method="POST" action="{{ route('customer-booking.update', $customer->id) }}">
        @csrf
        @method('PUT')

        <input type="hidden" name="step" value="5">

        @include('customer-booking.partials.payment-form')

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('customer-booking.edit', [$customer->id, 'step' => 4]) }}"
                class="btn btn-outline-secondary px-4">
                Previous
            </a>

            <button type="submit" class="btn btn-success ms-2 px-4">
                Save Payment & Complete
            </button>
        </div>
    </form>
@endif

@push('scripts')
    <script>
        $(document).ready(function() {

            function resetFields() {
                $('.common-field').addClass('d-none');
                $('.full-field').addClass('d-none');
                $('.emi-field').addClass('d-none');
                $('.bank-field').addClass('d-none');
                $('.instrument-field').addClass('d-none');
                $('.bank-detail-field').addClass('d-none');
                $('.cheque-number-field').addClass('d-none');
                $('.dd-number-field').addClass('d-none');
                $('.neft-number-field').addClass('d-none');

                $('#paymentSummary').addClass('d-none');
                $('#paymentSummaryBody').empty();
            }

            function resetModeFields() {
                $('.bank-field').addClass('d-none');
                $('.instrument-field').addClass('d-none');
                $('.bank-detail-field').addClass('d-none');
                $('.cheque-number-field').addClass('d-none');
                $('.dd-number-field').addClass('d-none');
                $('.neft-number-field').addClass('d-none');

                $('#instrumentDateLabel').html('Cheque Date <span class="text-danger">*</span>');
                $('#transactionNumberLabel').html('NEFT / RTGS No. <span class="text-danger">*</span>');
                $('#transactionNumber').attr('placeholder', 'Enter NEFT / RTGS number');
            }

            // function updatePaymentSummary() {
            //     let plan = $('#planType').val();
            //     let mode = $('#paymentMode').val();
            //     let totalPlot = parseFloat($('#totalPlotCost').val()) || 0;
            //     let booking = parseFloat($('#bookingAmount').val()) || 0;
            //     let due = parseFloat($('#dueAmount').val()) || 0;
            //     let emiMonths = parseInt($('#emiMonths').val()) || 0;
            //     let summary = '';

            //     if (!plan) {
            //         $('#paymentSummary').addClass('d-none');
            //         return;
            //     }

            //     summary += '<h6 class="fw-semibold mb-3">Payment Summary</h6>';
            //     summary += '<ul class="list-group list-group-flush">';

            //     if (plan == 'full_payment') {
            //         let statusText = 'Booked';

            //         if (mode == 'cheque' || mode == 'dd') {
            //             statusText = 'Hold';
            //         }

            //         summary += `<li class="list-group-item px-0 py-2"><span class="text-muted">Payment Status</span><div class="fw-semibold">${statusText}</div></li>`;

            //         if (mode == 'card') {
            //             summary += `<li class="list-group-item px-0 py-2"><span class="text-muted">Receipt Type</span><div class="fw-semibold">Email</div></li>`;
            //         }

            //         summary += `<li class="list-group-item px-0 py-2"><span class="text-muted">Total Payable</span><div class="fw-semibold">₹${totalPlot.toFixed(2)}</div></li>`;
            //         summary += `<li class="list-group-item px-0 py-2"><span class="text-muted">Booking Amount</span><div class="fw-semibold">₹${booking.toFixed(2)}</div></li>`;
            //         summary += `<li class="list-group-item px-0 py-2"><span class="text-muted">Remaining Due</span><div class="fw-semibold">₹${due.toFixed(2)}</div></li>`;
            //     } else if (plan == 'emi_plan') {
            //         summary += `<li class="list-group-item px-0 py-2"><span class="text-muted">Payment Status</span><div class="fw-semibold">EMI Plan</div></li>`;
            //         summary += `<li class="list-group-item px-0 py-2"><span class="text-muted">Total Payable</span><div class="fw-semibold">₹${totalPlot.toFixed(2)}</div></li>`;
            //         summary += `<li class="list-group-item px-0 py-2"><span class="text-muted">Booking Amount</span><div class="fw-semibold">₹${booking.toFixed(2)}</div></li>`;
            //         summary += `<li class="list-group-item px-0 py-2"><span class="text-muted">Remaining Due</span><div class="fw-semibold">₹${due.toFixed(2)}</div></li>`;

            //         if (emiMonths > 0) {
            //             let emiAmount = due / emiMonths;

            //             summary += `<li class="list-group-item px-0 py-2"><span class="text-muted">EMI Months</span><div class="fw-semibold">${emiMonths}</div></li>`;
            //             summary += `<li class="list-group-item px-0 py-2"><span class="text-muted">Monthly EMI</span><div class="fw-semibold">₹${emiAmount.toFixed(2)}</div></li>`;
            //         } else {
            //             summary += `<li class="list-group-item px-0 py-2"><span class="text-muted">EMI Months</span><div class="fw-semibold text-warning">Please enter months</div></li>`;
            //         }
            //     }

            //     summary += '</ul>';
            //     summary += '<div class="mt-3 text-muted small">Receipt number will be generated when payment is saved.</div>';

            //     $('#paymentSummary').removeClass('d-none');
            //     $('#paymentSummaryBody').html(summary);
            // }
            function updatePaymentSummary() {
                let plan = $('#planType').val();
                let mode = $('#paymentMode').val();
                let totalPlot = parseFloat($('#totalPlotCost').val()) || 0;
                let booking = parseFloat($('#bookingAmount').val()) || 0;
                let due = parseFloat($('#dueAmount').val()) || 0;
                let emiMonths = parseInt($('#emiMonths').val()) || 0;
                let summary = '';

                if (!plan) {
                    $('#paymentSummary').addClass('d-none');
                    return;
                }

                let statusText = 'Booked';
                let statusClass = 'success';
                let statusIcon = 'bi-check-circle';

                if (plan == 'emi_plan') {
                    statusText = 'EMI Plan';
                    statusClass = 'primary';
                    statusIcon = 'bi-calendar-check';
                }

                if (plan == 'full_payment' && (mode == 'cheque' || mode == 'dd')) {
                    statusText = 'Hold';
                    statusClass = 'warning';
                    statusIcon = 'bi-hourglass-split';
                }

                let monthlyEmi = 0;

                if (plan == 'emi_plan' && emiMonths > 0) {
                    monthlyEmi = due / emiMonths;
                }

                summary += `
        <div class="payment-summary-box">
            <div class="payment-summary-header">
                <div>
                    <h6 class="mb-1 fw-bold">Payment Summary</h6>
                    <p class="mb-0 text-muted small">Review calculated booking payment details.</p>
                </div>

                <span class="badge bg-${statusClass}-subtle text-${statusClass} border border-${statusClass}-subtle px-3 py-2 rounded-pill">
                    <i class="bi ${statusIcon} me-1"></i>
                    ${statusText}
                </span>
            </div>

            <div class="payment-summary-grid mt-3">

                <div class="payment-summary-item">
                    <span>Total Payable</span>
                    <strong>₹${totalPlot.toFixed(2)}</strong>
                </div>

                <div class="payment-summary-item">
                    <span>Booking Amount</span>
                    <strong class="text-success">₹${booking.toFixed(2)}</strong>
                </div>

                <div class="payment-summary-item">
                    <span>Remaining Due</span>
                    <strong class="${due > 0 ? 'text-danger' : 'text-success'}">₹${due.toFixed(2)}</strong>
                </div>
    `;

                if (plan == 'emi_plan') {
                    summary += `
                <div class="payment-summary-item">
                    <span>EMI Months</span>
                    <strong>${emiMonths > 0 ? emiMonths : '<span class="text-warning">Required</span>'}</strong>
                </div>

                <div class="payment-summary-item">
                    <span>Monthly EMI</span>
                    <strong class="text-primary">
                        ${emiMonths > 0 ? '₹' + monthlyEmi.toFixed(2) : '<span class="text-warning">0.00</span>'}
                    </strong>
                </div>
        `;
                }

                if (mode == 'card') {
                    summary += `
                <div class="payment-summary-item">
                    <span>Receipt Type</span>
                    <strong>Email</strong>
                </div>
        `;
                }

                summary += `
            </div>

            <div class="payment-summary-note mt-3">
                <i class="bi bi-info-circle me-1"></i>
                Receipt number will be generated when payment is saved.
            </div>
        </div>
    `;

                $('#paymentSummary').removeClass('d-none border-info');
                $('#paymentSummaryBody').html(summary);
            }

            function calculateAmounts() {
                let totalPlot = parseFloat($('#totalPlotCost').val()) || 0;
                let booking = parseFloat($('#bookingAmount').val()) || 0;
                let plan = $('#planType').val();

                $('#bookingAmount').prop('readonly', false);

                if (booking > totalPlot) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Amount',
                        text: 'Amount cannot exceed total plot cost'
                    });

                    booking = totalPlot;
                    $('#bookingAmount').val(totalPlot.toFixed(2));
                }

                let due = totalPlot - booking;

                if (due < 0) {
                    due = 0;
                }

                $('#dueAmount').val(due.toFixed(2));

                if (plan == 'full_payment') {
                    $('#netPayable').val(due.toFixed(2));
                }

                if (plan == 'emi_plan') {
                    let months = parseInt($('#emiMonths').val()) || 0;

                    if (months > 0) {
                        let emiAmount = due / months;
                        $('#afterBookingAmount').val(emiAmount.toFixed(2));
                    } else {
                        $('#afterBookingAmount').val('0.00');
                    }
                }

                updatePaymentSummary();
            }

            function loadPaymentModeFields() {
                resetModeFields();

                let mode = $('#paymentMode').val();

                if (mode == 'cheque') {
                    $('.bank-field').removeClass('d-none');
                    $('.bank-detail-field').removeClass('d-none');
                    $('.instrument-field').removeClass('d-none');
                    $('.cheque-number-field').removeClass('d-none');

                    $('#instrumentDateLabel').html('Cheque Date <span class="text-danger">*</span>');
                }

                if (mode == 'dd') {
                    $('.bank-field').removeClass('d-none');
                    $('.bank-detail-field').removeClass('d-none');
                    $('.instrument-field').removeClass('d-none');
                    $('.dd-number-field').removeClass('d-none');

                    $('#instrumentDateLabel').html('DD Date <span class="text-danger">*</span>');
                }

                if (mode == 'neft_rtgs') {
                    $('.bank-detail-field').removeClass('d-none');
                    $('.instrument-field').removeClass('d-none');
                    $('.neft-number-field').removeClass('d-none');

                    $('#instrumentDateLabel').html('NEFT / RTGS Date <span class="text-danger">*</span>');
                    $('#transactionNumberLabel').html('NEFT / RTGS No. <span class="text-danger">*</span>');
                    $('#transactionNumber').attr('placeholder', 'Enter NEFT / RTGS number');
                }

                if (mode == 'card') {
                    $('.neft-number-field').removeClass('d-none');

                    $('#transactionNumberLabel').html('Card Transaction No. <span class="text-danger">*</span>');
                    $('#transactionNumber').attr('placeholder', 'Enter card transaction number');
                }

                let hasBankField =
                    mode == 'cheque' ||
                    mode == 'dd' ||
                    mode == 'neft_rtgs' ||
                    mode == 'card';

                if (hasBankField) {
                    $('.payment-bank-empty').addClass('d-none');
                } else {
                    $('.payment-bank-empty').removeClass('d-none');
                }

                calculateAmounts();
            }

            function loadPaymentFields() {
                resetFields();

                let plan = $('#planType').val();

                if (plan) {
                    $('.common-field').removeClass('d-none');
                }

                if (plan == 'full_payment') {
                    $('.full-field').removeClass('d-none');
                }

                if (plan == 'emi_plan') {
                    $('.emi-field').removeClass('d-none');
                }

                loadPaymentModeFields();
                calculateAmounts();
            }

            $('#planType').on('change', loadPaymentFields);
            $('#paymentMode').on('change', loadPaymentModeFields);
            $('#bookingAmount, #emiMonths').on('keyup change', calculateAmounts);

            loadPaymentFields();
        });
    </script>
@endpush
