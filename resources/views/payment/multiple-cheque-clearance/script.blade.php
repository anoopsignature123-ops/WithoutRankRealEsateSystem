@push('scripts')
    <script>
        $(document).ready(function() {
            let selectedPayments = [];

            function updateBulkButton() {
                selectedPayments = [];

                $('.payment_checkbox:checked').each(function() {
                    selectedPayments.push($(this).val());
                });

                $('#payment_ids').val(selectedPayments.join(','));

                if (selectedPayments.length > 0) {
                    $('#bulk_action_btn').removeClass('d-none');
                } else {
                    $('#bulk_action_btn').addClass('d-none');
                }
            }

            $('#select_all').on('change', function() {
                $('.payment_checkbox').prop('checked', $(this).is(':checked'));
                updateBulkButton();
            });

            $(document).on('change', '.payment_checkbox', function() {
                updateBulkButton();

                let total = $('.payment_checkbox').length;
                let checked = $('.payment_checkbox:checked').length;
                $('#select_all').prop('checked', total === checked);
            });

            $('#cheque_status').on('change', function() {
                let status = $(this).val();

                if (['cancelled', 'bounced', 'pending'].includes(status)) {
                    $('#reason_box').removeClass('d-none');
                } else {
                    $('#reason_box').addClass('d-none');
                }
            });

            $('#cheque_status').trigger('change').select2({
                width: '100%',
                dropdownParent: $('#statusModal')
            });
        });
    </script>
@endpush
