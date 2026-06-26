@push('scripts')
<script>
$(document).ready(function () {
    $('#projectId').on('change', function () {

        let projectId = $(this).val();

        clearSelection();

        $('#blockId').html('<option value="">Loading...</option>');
        $('#plotSaleId').html('<option value="">Select Plot</option>');

        if (!projectId) {
            $('#blockId').html('<option value="">Select Block</option>');
            return;
        }

        $.get(`/plot-transfer/blocks/${projectId}`, function (res) {

            let options = '<option value="">Select Block</option>';

            $.each(res, function (index, block) {
                options += `
                    <option value="${block.id}">
                        ${block.block}
                    </option>
                `;
            });

            $('#blockId').html(options);
        });
    });
    $('#blockId').on('change', function () {

        let blockId = $(this).val();

        clearSelection();

        $('#plotSaleId').html('<option value="">Loading...</option>');

        if (!blockId) {
            $('#plotSaleId').html('<option value="">Select Plot</option>');
            return;
        }

        $.get(`/plot-transfer/plots/${blockId}`, function (res) {

            let options = '<option value="">Select Plot</option>';

            if (res.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Plot Found',
                    text: 'Is block me transfer ke liye koi booked plot nahi hai.'
                });
            }

            $.each(res, function (index, plot) {
                options += `
                    <option value="${plot.id}">
                        ${plot.plot_number}
                    </option>
                `;
            });

            $('#plotSaleId').html(options);
        });
    });
    $('#plotSaleId').on('change', function () {

        let plotId = $(this).val();

        clearSelection(false);

        if (!plotId) {
            return;
        }

        $.get(`/plot-transfer/booking/${plotId}`, function (r) {

            if (!r) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Booking Not Found',
                    text: 'Selected plot ki booking details nahi mili.'
                });
                return;
            }

            $('#bookingCode').val(r.booking_id);
            $('#customerCode').val(r.customer_id);
            $('#customerName').val(r.customer_name);

            $('#customerBookingId').val(r.customer_booking_id);
            $('#plotSaleDetailId').val(r.plot_sale_id);

            $('#transferSection').removeClass('d-none');
            $('#currentOwner').val(r.customer_name);

            loadTransferCustomers(r.customer_booking_id);
            renderBookingDetails(r);

            $('#bookingDetailsCard').removeClass('d-none');
        });
    });
    $('#transferBtn').on('click', function () {

        let customerBookingId = $('#customerBookingId').val();
        let plotSaleDetailId = $('#plotSaleDetailId').val();
        let newCustomerBookingId = $('#newCustomerBookingId').val();
        let transferCharge = $('#transferCharge').val();
        let transferDate = $('#transferDate').val();
        let transferReason = $('#transferReason').val();

        if (!plotSaleDetailId) {
            Swal.fire('Error', 'Please select plot first.', 'error');
            return;
        }

        if (!newCustomerBookingId) {
            Swal.fire('Error', 'Please select customer.', 'error');
            return;
        }

        Swal.fire({
            title: 'Transfer Plot?',
            text: 'Are you sure you want to transfer this plot?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes Transfer',
            confirmButtonColor: '#198754'
        }).then((result) => {

            if (result.isConfirmed) {
                setTransferLoading(true);

                $.ajax({
                    url: "{{ route('plot-transfer.store') }}",
                    type: "POST",

                    data: {
                        _token: "{{ csrf_token() }}",
                        customer_booking_id: customerBookingId,
                        plot_sale_detail_id: plotSaleDetailId,
                        new_customer_booking_id: newCustomerBookingId,
                        transfer_charge: transferCharge,
                        transfer_date: transferDate,
                        transfer_reason: transferReason
                    },

                    success: function (res) {
                        Swal.fire(
                            'Success',
                            res.message,
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },

                    error: function (xhr) {
                        setTransferLoading(false);
                        Swal.fire(
                            'Error',
                            xhr.responseJSON?.message || 'Transfer failed.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    $('#transferCharge').on('input change blur', function () {
        $(this).val(sanitizeAmount($(this).val()));
    });

});

function loadTransferCustomers(bookingId)
{
    $.get(`/plot-transfer/customers/${bookingId}`, function (customers) {

        let options = '<option value="">Select Customer</option>';

        $.each(customers, function (index, customer) {
            options += `
                <option value="${customer.id}">
                    ${customer.name}
                </option>
            `;
        });

        $('#newCustomerBookingId').html(options);
    });
}
function renderBookingDetails(r)
{
    let paymentStatusClass =
        r.payment_status.toLowerCase() === 'cleared'
            ? 'bg-success'
            : 'bg-warning text-dark';

    let bookingStatusClass =
        r.booking_status.toLowerCase() === 'booked'
            ? 'bg-success'
            : 'bg-warning text-dark';

    $('#bookingDetailsContent').html(`
        <table class="table table-bordered mb-0">

            <tr>
                <th>Project</th>
                <td>${r.project_name}</td>

                <th>Plot Rate</th>
                <td>Rs. ${r.plot_rate}</td>
            </tr>

            <tr>
                <th>Block</th>
                <td>${r.block_name}</td>

                <th>Plot Area</th>
                <td>${r.plot_area}</td>
            </tr>

            <tr>
                <th>Plot No</th>
                <td>${r.plot_number}</td>

                <th>Plan Type</th>
                <td>
                    ${
                        r.plan_type === 'emi_plan'
                        ? '<span class="badge bg-primary">EMI Plan</span>'
                        : '<span class="badge bg-success">Full Payment</span>'
                    }
                </td>
            </tr>

            <tr class="table-light">
                <th colspan="4" class="text-center">
                    PAYMENT DETAILS
                </th>
            </tr>

            <tr>
                <th>Payment Status</th>
                <td>
                    <span class="badge ${paymentStatusClass}">
                        ${r.payment_status}
                    </span>
                </td>

                <th>Booking Status</th>
                <td>
                    <span class="badge ${bookingStatusClass}">
                        ${r.booking_status}
                    </span>
                </td>
            </tr>

            <tr>
                <th>Payment Mode</th>
                <td>${r.payment_mode}</td>

                <th>Booking Amount</th>
                <td>Rs. ${r.booking_amount}</td>
            </tr>

            <tr>
                <th>Total Cost</th>
                <td>Rs. ${r.total_plot_cost}</td>

                <th>Total Paid</th>
                <td class="text-success fw-bold">
                    Rs. ${r.total_paid}
                </td>
            </tr>

            <tr>
                <th>Balance Due</th>
                <td colspan="3" class="text-danger fw-bold">
                    Rs. ${r.remaining_amount}
                </td>
            </tr>

            ${
                r.plan_type === 'emi_plan'
                ? `
                    <tr>
                        <th>EMI Summary</th>
                        <td colspan="3">
                            <span class="badge bg-primary">
                                Total EMI : ${r.emi_months}
                            </span>

                            <span class="badge bg-success">
                                Paid EMI : ${r.paid_emis}
                            </span>

                            <span class="badge bg-danger">
                                Due EMI : ${r.due_months}
                            </span>
                        </td>
                    </tr>
                `
                : `
                    <tr>
                        <th>Plan</th>
                        <td colspan="3">
                            <span class="badge bg-success">
                                Full Payment
                            </span>
                        </td>
                    </tr>
                `
            }

        </table>
    `);
}
function clearSelection(clearPlot = true)
{
    $('#bookingCode').val('');
    $('#customerCode').val('');
    $('#customerName').val('');

    $('#customerBookingId').val('');
    $('#plotSaleDetailId').val('');

    $('#currentOwner').val('');

    $('#newCustomerBookingId').html(
        '<option value="">Select Customer</option>'
    );

    $('#transferCharge').val(0);
    $('#transferReason').val('');

    $('#bookingDetailsCard').addClass('d-none');
    $('#transferSection').addClass('d-none');

    if (clearPlot) {
        $('#plotSaleId').html('<option value="">Select Plot</option>');
    }
}

function setTransferLoading(isLoading)
{
    const button = $('#transferBtn');
    button.prop('disabled', isLoading);
    button.find('.btn-label').toggleClass('d-none', isLoading);
    button.find('.btn-loader').toggleClass('d-none', !isLoading);
}

function sanitizeAmount(value)
{
    value = String(value || '').replace(/[^\d.]/g, '');
    const firstDot = value.indexOf('.');

    if (firstDot !== -1) {
        value = value.substring(0, firstDot + 1) + value.substring(firstDot + 1).replace(/\./g, '');
    }

    return value;
}
</script>
@endpush
