@push('scripts')
    <script>
        $(document).ready(function() {
            function resetSummary() {
                $('#payment_type').val('');
                $('#booking_id').val('');
                $('#customer_id').val('');
                $('#customer_name').val('');
                $('#customer_booking_id').val('');
                $('#plot_sale_detail_id').val('');
                $('#total_cost').text('0.00');
                $('#total_paid').text('0.00');
                $('#due_amount').text('0.00');
                $('#paid_amount').val('');

                $('#payment_history').html(`
                    <tr>
                        <td colspan="4" class="text-center text-muted">No payments found</td>
                    </tr>
                `);
            }

            function resetPaymentFields() {
                $('.bank-field').addClass('d-none');
                $('.cheque-field').addClass('d-none');
                $('.dd-field').addClass('d-none');
                $('.transaction-field').addClass('d-none');
            }

            // Payment mode change
            $('#payment_mode').change(function() {
                resetPaymentFields();
                let mode = $(this).val();

                if (['cheque', 'dd', 'neft_rtgs'].includes(mode)) {
                    $('.bank-field').removeClass('d-none');
                }
                if (mode === 'cheque') {
                    $('.cheque-field').removeClass('d-none');
                }
                if (mode === 'dd') {
                    $('.dd-field').removeClass('d-none');
                }
                if (['neft_rtgs', 'card'].includes(mode)) {
                    $('.transaction-field').removeClass('d-none');
                }
            });

            // Project → Blocks
            $('#project_id').change(function() {
                resetSummary();
                let projectId = $(this).val();
                $('#block_id').html('<option value="">Select Block</option>');
                $('#plot_id').html('<option value="">Select Plot</option>');

                if (!projectId) return;

                $.get("{{ route('admin.one-time-payment.blocks', ':id') }}".replace(':id', projectId), function(res) {
                    $.each(res, function(index, block) {
                        $('#block_id').append(`<option value="${block.id}">${block.block}</option>`);
                    });
                });
            });

            // Block → Plots
            $('#block_id').change(function() {
                resetSummary();
                let blockId = $(this).val();
                $('#plot_id').html('<option value="">Select Plot</option>');

                if (!blockId) return;

                $.get("{{ route('admin.one-time-payment.plots', ':id') }}".replace(':id', blockId), function(res) {
                    $.each(res, function(index, plot) {
                        $('#plot_id').append(`<option value="${plot.id}">${plot.plot_number}</option>`);
                    });
                });
            });

            // Plot → Booking Details
            $('#plot_id').change(function() {
                resetSummary();
                let plotId = $(this).val();

                if (!plotId) return;

                $.get("{{ route('admin.one-time-payment.details', ':id') }}".replace(':id', plotId), function(res) {
                    if (!res.status) {
                        Swal.fire({ icon: 'warning', title: 'No Booking Found' });
                        return;
                    }

                    // Autofill details
                    $('#payment_type').val(res.payment_type);
                    $('#customer_booking_id').val(res.booking_db_id);
                    $('#plot_sale_detail_id').val(res.plot_sale_id);
                    $('#booking_id').val(res.booking_code);
                    $('#customer_id').val(res.customer_code);
                    $('#customer_name').val(res.customer_name);

                    // Summary
                    $('#total_cost').text(res.total_cost);
                    $('#total_paid').text(res.total_paid);
                    $('#due_amount').text(res.due_amount);

                    // Payment history
                    let historyHtml = '';
                    if (res.payment_history && res.payment_history.length > 0) {
                        $.each(res.payment_history, function(index, payment) {
                            historyHtml += `<tr>
                                <td>${payment.receipt_no ?? '-'}</td>
                                <td>${payment.date ?? '-'}</td>
                                <td>₹${payment.paid_amount ?? '0'}</td>
                                <td>${payment.payment_mode ?? '-'}</td>
                            </tr>`;
                        });
                    } else {
                        historyHtml = `<tr>
                            <td colspan="4" class="text-center text-muted">No payments found</td>
                        </tr>`;
                    }
                    $('#payment_history').html(historyHtml);

                }).fail(function() {
                    Swal.fire({ icon: 'error', title: 'Something went wrong' });
                });
            });

            // Amount validation
            $('#paid_amount').keyup(function() {
                let enteredAmount = parseFloat($(this).val()) || 0;
                let dueAmount = parseFloat($('#due_amount').text()) || 0;

                if (enteredAmount > dueAmount) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Amount',
                        text: 'Amount cannot exceed due amount.'
                    });
                    $(this).val('');
                }
            });

            // Initial trigger
            $('#payment_mode').trigger('change');
        });
    </script>
@endpush