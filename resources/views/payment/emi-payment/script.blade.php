@push('scripts')
    <script>
        $(document).ready(function() {

            /*
            |--------------------------------------------------------------------------
            | Payment Mode Show / Hide Fields
            |--------------------------------------------------------------------------
            */
            $('#payment_mode').change(function() {

                let mode = $(this).val();

                $('.bank-field').addClass('d-none');
                $('.cheque-field').addClass('d-none');
                $('.dd-field').addClass('d-none');
                $('.transaction-field').addClass('d-none');

                // Bank fields
                if (
                    mode === 'cheque' ||
                    mode === 'dd' ||
                    mode === 'neft_rtgs' ||
                    mode === 'card'
                ) {

                    $('.bank-field')
                        .removeClass('d-none');

                }

                // Cheque fields
                if (mode === 'cheque') {

                    $('.cheque-field')
                        .removeClass('d-none');

                }

                // DD fields
                if (mode === 'dd') {

                    $('.dd-field')
                        .removeClass('d-none');

                }

                // Transaction fields
                if (
                    mode === 'neft_rtgs' ||
                    mode === 'card'
                ) {

                    $('.transaction-field')
                        .removeClass('d-none');

                }

            });

            /*
            |--------------------------------------------------------------------------
            | Trigger On Page Load
            |--------------------------------------------------------------------------
            */
            $('#payment_mode').trigger('change');


            /*
            |--------------------------------------------------------------------------
            | Project -> Blocks
            |--------------------------------------------------------------------------
            */
            $('#project_id').change(function() {

                let projectId = $(this).val();

                $('#block_id').html(
                    '<option value="">Select Block</option>'
                );

                $('#plot_id').html(
                    '<option value="">Select Plot</option>'
                );

                $.get(
                    '/admin/emi-payment/blocks/' + projectId,
                    function(res) {

                        $.each(
                            res.data,
                            function(key, block) {

                                $('#block_id').append(`
                            <option value="${block.id}">
                                ${block.block}
                            </option>
                        `);

                            }
                        );

                    }
                );

            });


            /*
            |--------------------------------------------------------------------------
            | Block -> EMI Plots
            |--------------------------------------------------------------------------
            */
            $('#block_id').change(function() {

                let blockId = $(this).val();

                $('#plot_id').html(
                    '<option value="">Select Plot</option>'
                );

                $.get(
                    '/admin/emi-payment/plots/' + blockId,
                    function(res) {

                        $.each(
                            res,
                            function(key, plot) {

                                $('#plot_id').append(`
                            <option value="${plot.id}">
                                ${plot.plot_number}
                            </option>
                        `);

                            }
                        );

                    }
                );

            });


            /*
            |--------------------------------------------------------------------------
            | Plot -> Booking Details
            |--------------------------------------------------------------------------
            */
            $('#plot_id').change(function() {

                let plotId = $(this).val();

                $.get(
                    '/admin/emi-payment/details/' + plotId,
                    function(res) {

                        if (!res.status) {

                            alert(
                                'EMI Booking Not Found'
                            );

                            return;

                        }

                        $('#customer_booking_id')
                            .val(
                                res.booking_db_id
                            );

                        $('#plot_sale_detail_id')
                            .val(
                                res.plot_sale_id
                            );

                        $('#booking_id')
                            .val(
                                res.booking_code
                            );

                        $('#customer_id')
                            .val(
                                res.customer_code
                            );

                        $('#customer_name')
                            .val(
                                res.customer_name
                            );

                        $('#total_cost')
                            .html(
                                '₹' + res.total_cost
                            );

                        $('#booking_amount')
                            .html(
                                '₹' + res.booking_amount
                            );

                        $('#total_paid')
                            .html(
                                '₹' + res.total_paid
                            );

                        $('#due_amount')
                            .html(
                                '₹' + res.due_amount
                            );

                        $('#emi_start_date')
                            .html(
                                res.emi_start_date
                            );

                        $('#emi_months')
                            .html(
                                res.months_passed +
                                ' / ' +
                                res.emi_months +
                                ' Months'
                            );

                        $('#monthly_emi')
                            .html(
                                '₹' + res.monthly_emi
                            );

                        let html = '';

                        $.each(
                            res.payment_history,
                            function(
                                key,
                                payment
                            ) {

                                html += `
                        <tr>
                            <td>${payment.receipt_no}</td>
                            <td>${payment.date}</td>
                            <td>₹${payment.amount}</td>
                            <td>${payment.mode}</td>
                        </tr>
                    `;

                            }
                        );

                        $('#payment_history')
                            .html(
                                html
                            );

                    }
                );

            });

        });
    </script>
@endpush
