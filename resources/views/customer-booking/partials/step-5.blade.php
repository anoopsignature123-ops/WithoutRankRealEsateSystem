@if ($step == 5)
    <form method="POST" action="{{ route('admin.customer-booking.update', $customer->id) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="step" value="5">
        @include('customer-booking.partials.payment-form')
        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('admin.customer-booking.edit', [$customer->id, 'step' => 4]) }}"
                class="btn btn-outline-secondary px-4">Previous
            </a>
            <button type="submit" class="btn btn-success ms-2 px-4">Add Customer & Book Plot</button>
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
                $('#paymentSummary').addClass('d-none');
                $('#paymentSummaryBody').empty();
            }

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

                summary += '<h6 class="fw-semibold mb-3">Payment Summary</h6>';
                summary += '<ul class="list-group list-group-flush">';

                if (plan == 'full_payment') {
                    let statusText = 'Booked';
                    if (mode == 'cheque' || mode == 'dd') {
                        statusText = 'Hold';
                    }
                    summary +=
                        `<li class="list-group-item px-0 py-2"><span class="text-muted">Payment Status</span><div class="fw-semibold">${statusText}</div></li>`;
                    if (mode == 'card') {
                        summary +=
                            `<li class="list-group-item px-0 py-2"><span class="text-muted">Receipt Type</span><div class="fw-semibold">Email</div></li>`;
                    }
                    summary +=
                        `<li class="list-group-item px-0 py-2"><span class="text-muted">Total Payable</span><div class="fw-semibold">₹${totalPlot.toFixed(2)}</div></li>`;
                    summary +=
                        `<li class="list-group-item px-0 py-2"><span class="text-muted">Booking Amount</span><div class="fw-semibold">₹${booking.toFixed(2)}</div></li>`;
                    summary +=
                        `<li class="list-group-item px-0 py-2"><span class="text-muted">Remaining Due</span><div class="fw-semibold">₹${due.toFixed(2)}</div></li>`;
                } else if (plan == 'emi_plan') {
                    summary +=
                        `<li class="list-group-item px-0 py-2"><span class="text-muted">Payment Status</span><div class="fw-semibold">EMI Plan</div></li>`;
                    summary +=
                        `<li class="list-group-item px-0 py-2"><span class="text-muted">Total Payable</span><div class="fw-semibold">₹${totalPlot.toFixed(2)}</div></li>`;
                    summary +=
                        `<li class="list-group-item px-0 py-2"><span class="text-muted">Booking Amount</span><div class="fw-semibold">₹${booking.toFixed(2)}</div></li>`;
                    summary +=
                        `<li class="list-group-item px-0 py-2"><span class="text-muted">Remaining Due</span><div class="fw-semibold">₹${due.toFixed(2)}</div></li>`;
                    if (emiMonths > 0) {
                        let emiAmount = due / emiMonths;
                        summary +=
                            `<li class="list-group-item px-0 py-2"><span class="text-muted">EMI Months</span><div class="fw-semibold">${emiMonths}</div></li>`;
                        summary +=
                            `<li class="list-group-item px-0 py-2"><span class="text-muted">Monthly EMI</span><div class="fw-semibold">₹${emiAmount.toFixed(2)}</div></li>`;
                    } else {
                        summary +=
                            `<li class="list-group-item px-0 py-2"><span class="text-muted">EMI Months</span><div class="fw-semibold text-warning">Please enter months</div></li>`;
                    }
                }

                summary += '</ul>';
                summary +=
                    '<div class="mt-3 text-muted small">Receipt number will be generated when payment is saved.</div>';

                $('#paymentSummary').removeClass('d-none');
                $('#paymentSummaryBody').html(summary);
            }

            // function calculateAmounts() {
            //     let booking = parseFloat($('#bookingAmount').val()) || 0;
            //     let totalPlot = parseFloat($('#totalPlotCost').val()) || 0;
            //     let plan = $('#planType').val();

            //     if (plan == 'full_payment') {
            //         booking = totalPlot;
            //         $('#bookingAmount').val(totalPlot.toFixed(2));
            //         $('#bookingAmount').prop('readonly', true);
            //     } else {
            //         $('#bookingAmount').prop('readonly', false);
            //     }

            //     if (booking > totalPlot) {
            //         Swal.fire({
            //             icon: 'warning',
            //             title: 'Invalid amount',
            //             text: 'Booking amount cannot exceed total payable amount.',
            //         });
            //         booking = totalPlot;
            //         $('#bookingAmount').val(totalPlot.toFixed(2));
            //     }

            //     let due = totalPlot - booking;
            //     if (due < 0) {
            //         due = 0;
            //     }
            //     $('#dueAmount').val(due.toFixed(2));

            //     if (plan == 'full_payment') {
            //         $('#netPayable').val(due.toFixed(2));
            //     }

            //     if (plan == 'emi_plan') {
            //         let emiMonths = parseInt($('#emiMonths').val()) || 0;
            //         if (emiMonths > 0) {
            //             let emiAmount = due / emiMonths;
            //             $('#afterBookingAmount').val(emiAmount.toFixed(2));
            //         } else {
            //             $('#afterBookingAmount').val('0.00');
            //         }
            //     }

            //     updatePaymentSummary();
            // }

            function calculateAmounts() {

                let totalPlot =
                    parseFloat(
                        $('#totalPlotCost').val()
                    ) || 0;

                let booking =
                    parseFloat(
                        $('#bookingAmount').val()
                    ) || 0;

                let plan =
                    $('#planType').val();


                $('#bookingAmount')
                    .prop('readonly', false);


                if (booking > totalPlot) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Amount',
                        text: 'Amount cannot exceed total plot cost'
                    });

                    booking = totalPlot;

                    $('#bookingAmount')
                        .val(totalPlot.toFixed(2));
                }


                let due =
                    totalPlot - booking;


                if (due < 0) {
                    due = 0;
                }


                $('#dueAmount')
                    .val(due.toFixed(2));



                // Full payment
                if (plan == 'full_payment') {

                    $('#netPayable')
                        .val(due.toFixed(2));
                }



                // EMI plan
                if (plan == 'emi_plan') {

                    let months =
                        parseInt(
                            $('#emiMonths').val()
                        ) || 0;


                    if (months > 0) {

                        let emiAmount =
                            due / months;


                        $('#afterBookingAmount')
                            .val(
                                emiAmount.toFixed(2)
                            );

                    } else {

                        $('#afterBookingAmount')
                            .val('0.00');

                    }

                }


                updatePaymentSummary();

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
                $('#paymentMode').trigger('change');
                calculateAmounts();
            }

            $('#planType').change(loadPaymentFields);
            $('#paymentMode').change(function() {
                let mode = $(this).val();
                $('.bank-field').addClass('d-none');
                $('.instrument-field').addClass('d-none');
                $('.cheque-number-field').addClass('d-none');
                $('.dd-number-field').addClass('d-none');
                $('.bank-detail-field').addClass('d-none');
                if (mode == 'cheque' || mode == 'dd' || mode == 'neft_rtgs') {
                    $('.bank-field').removeClass('d-none');
                    $('.instrument-field').removeClass('d-none');
                    $('.bank-detail-field').removeClass('d-none');
                    if (mode == 'cheque') {
                        $('.cheque-number-field').removeClass('d-none');
                    } else if (mode == 'dd') {
                        $('.dd-number-field').removeClass('d-none');
                    }
                }
                if (mode == 'card') {
                    $('.bank-field').removeClass('d-none');
                }
            });
            $('#bookingAmount, #emiMonths').on('keyup change', calculateAmounts);
            loadPaymentFields();
        });
    </script>
@endpush
