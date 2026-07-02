<script>
$(document).ready(function () {
    function sanitizeAmount(value) {
        value = String(value || '').replace(/[^\d.]/g, '');
        const firstDot = value.indexOf('.');

        if (firstDot !== -1) {
            value = value.substring(0, firstDot + 1) + value.substring(firstDot + 1).replace(/\./g, '');
        }

        return value;
    }

    function resetFields() {
        $('.emi-field, .bank-field, .cheque-field, .dd-field, .transaction-field').addClass('d-none');
    }

    function toggleFields() {
        resetFields();

        const planType = $('#planType').val();
        const paymentMode = $('#paymentMode').val();

        if (planType === 'emi_plan') {
            $('.emi-field').removeClass('d-none');
        }

        if (['cheque', 'dd', 'neft_rtgs'].includes(paymentMode)) {
            $('.bank-field').removeClass('d-none');
        }

        if (paymentMode === 'cheque') {
            $('.cheque-field').removeClass('d-none');
        }

        if (paymentMode === 'dd') {
            $('.dd-field').removeClass('d-none');
        }

        if (['neft_rtgs', 'card'].includes(paymentMode)) {
            $('.transaction-field').removeClass('d-none');
        }
    }

    function calculateAmounts() {
        const total = parseFloat($('#totalPlotCost').val()) || 0;
        const paid = parseFloat(sanitizeAmount($('#paidAmount').val())) || 0;
        const months = parseInt($('#emiMonths').val()) || 0;
        const due = Math.max(0, total - paid);

        $('#dueAmount').val(due.toFixed(2));
        $('#netPayableAmount').val(due.toFixed(2));

        if ($('#planType').val() === 'emi_plan' && months > 0) {
            $('#emiAmount').val((due / months).toFixed(2));
        } else {
            $('#emiAmount').val('');
        }
    }

    function validatePaidAmount() {
        const total = parseFloat($('#totalPlotCost').val()) || 0;
        const paid = parseFloat(sanitizeAmount($('#paidAmount').val())) || 0;

        if (paid > total) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Paid Amount',
                text: 'Paid amount cannot exceed payable amount for this receipt.'
            });

            return false;
        }

        return true;
    }

    function setUpdateLoading(isLoading) {
        const button = $('#updatePaymentBtn');
        button.prop('disabled', isLoading);
        button.find('.btn-label').toggleClass('d-none', isLoading);
        button.find('.btn-loader').toggleClass('d-none', !isLoading);
    }

    $('#planType').on('change', function () {
        toggleFields();
        calculateAmounts();
    });

    $('#paymentMode').on('change', toggleFields);

    $('#paidAmount').on('input change blur', function () {
        const cleaned = sanitizeAmount($(this).val());
        if ($(this).val() !== cleaned) {
            $(this).val(cleaned);
        }
        calculateAmounts();
    });

    $('#emiMonths').on('keyup change', calculateAmounts);

    $('#editPaymentForm').on('submit', function (event) {
        $('#paidAmount').val(sanitizeAmount($('#paidAmount').val()));
        calculateAmounts();

        if (!validatePaidAmount()) {
            event.preventDefault();
            return false;
        }

        setUpdateLoading(true);
    });

    toggleFields();
    calculateAmounts();
});
</script>
