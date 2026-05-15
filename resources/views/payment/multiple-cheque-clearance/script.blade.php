@push('scripts')
    <script>
        $(document).ready(function() {

            let selectedPayments = [];


            function updateActionButton() {

                selectedPayments = [];

                $('.payment_checkbox:checked').each(function() {

                    selectedPayments.push(
                        $(this).val()
                    );

                });

                $('#payment_ids').val(
                    selectedPayments.join(',')
                );

                if (
                    selectedPayments.length > 0
                ) {

                    $('#bulk_action_btn')
                        .removeClass(
                            'd-none'
                        );

                } else {

                    $('#bulk_action_btn')
                        .addClass(
                            'd-none'
                        );

                }

            }


            $('#select_all').on(
                'change',
                function() {

                    $('.payment_checkbox')
                        .prop(
                            'checked',
                            $(this).is(':checked')
                        );

                    updateActionButton();

                }
            );


            $(document).on(
                'change',
                '.payment_checkbox',
                function() {

                    updateActionButton();

                    let totalCheckbox =
                        $('.payment_checkbox')
                        .length;

                    let checkedCheckbox =
                        $('.payment_checkbox:checked')
                        .length;

                    $('#select_all').prop(
                        'checked',
                        totalCheckbox === checkedCheckbox
                    );

                }
            );


            $('#cheque_status').on(
                'change',
                function() {

                    let status =
                        $(this).val();

                    if (
                        status === 'cancelled' ||
                        status === 'bounced' ||
                        status === 'pending'
                    ) {

                        $('#reason_box')
                            .removeClass(
                                'd-none'
                            );

                    } else {

                        $('#reason_box')
                            .addClass(
                                'd-none'
                            );

                    }

                }
            );


            $('#cheque_status')
                .trigger(
                    'change'
                );
           $('#cheque_status').select2({
    width: '100%',
    dropdownParent: $('#statusModal')
});
        });
    </script>
@endpush
