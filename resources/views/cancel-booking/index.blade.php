@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 transaction-page">

        {{-- Header --}}
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center gap-3">
                <span class="transaction-icon">
                    <i class="bi bi-x-octagon"></i>
                </span>
                <div>
                    <span class="text-success fw-bold text-uppercase small">Cancellation Desk</span>
                    <h3 class="fw-bold mb-1 text-dark">Cancel Booking</h3>
                    <p class="text-muted mb-0 small">Cancel selected plot booking and manage refund details.</p>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Form --}}
        <div class="transaction-card mb-4">
            <div class="transaction-card-body">

                <form method="POST" action="{{ route('cancel-booking.store') }}" id="cancelBookingForm">
                    @csrf

                    <input type="hidden" name="customer_booking_id" id="customerBookingId">
                    <input type="hidden" name="plot_sale_detail_id" id="plotSaleDetailId">

                    <div class="transaction-section-title">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon"><i class="bi bi-pin-map"></i></span>
                            <div>
                                <h5 class="fw-bold mb-1">Booking Selection</h5>
                                <small class="text-muted">Select project, block and booked plot.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Site Name</label>
                            <select id="projectId" class="form-select">
                                <option value="">Select Site</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Block</label>
                            <select id="blockId" class="form-select">
                                <option value="">Select Block</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Plot No</label>
                            <select id="plotSaleId" class="form-select">
                                <option value="">Select Plot</option>
                            </select>
                        </div>
                    </div>

                    <div class="transaction-summary-box transaction-readonly-grid mb-4">
                        <h6 class="fw-bold mb-3">
                            Customer & Payment Summary
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="small text-muted fw-bold text-uppercase">Booking ID</label>
                                <input type="text" id="bookingCode" class="form-control bg-white" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="small text-muted fw-bold text-uppercase">Customer ID</label>
                                <input type="text" id="customerCode" class="form-control bg-white" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="small text-muted fw-bold text-uppercase">Customer Name</label>
                                <input type="text" id="customerName" class="form-control bg-white" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="small text-muted fw-bold text-uppercase">Paid Amount</label>
                                <input type="text" id="paidAmount" class="form-control bg-white text-success fw-bold"
                                    readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="small text-muted fw-bold text-uppercase">Last Payment Date</label>
                                <input type="text" id="paymentDate" class="form-control bg-white" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="small text-muted fw-bold text-uppercase">Last Payment Mode</label>
                                <input type="text" id="paymentMode" class="form-control bg-white" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="transaction-section-title">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon"><i class="bi bi-cash-coin"></i></span>
                            <div>
                                <h5 class="fw-bold mb-1">Refund Details</h5>
                                <small class="text-muted">Enter deduction and refund payment details.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Deduction Amount</label>
                            <input type="number" step="0.01" name="deduction_amount" id="deductionAmount"
                                class="form-control" value="{{ old('deduction_amount') }}"
                                placeholder="Enter deduction amount">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Deduction (%)</label>
                            <input type="number" step="0.01" name="deduction_percentage" id="deductionPercentage"
                                class="form-control" value="{{ old('deduction_percentage') }}"
                                placeholder="Enter deduction %">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Refund Amount</label>
                            <input type="number" step="0.01" name="refund_amount" id="refundAmount"
                                class="form-control" value="{{ old('refund_amount') }}" placeholder="Refund amount">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pay Mode</label>
                            <select name="pay_mode" id="payMode" class="form-select">
                                <option value="">Select Pay Mode</option>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                                <option value="dd">DD</option>
                                <option value="neft_rtgs">NEFT / RTGS</option>
                                <option value="card">Card</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pay Date</label>
                            <input type="date" name="pay_date" class="form-control"
                                value="{{ old('pay_date', date('Y-m-d')) }}">
                        </div>

                        <div class="col-md-4 bank-field d-none">
                            <label class="form-label fw-semibold">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}"
                                placeholder="Enter bank name">
                        </div>

                        <div class="col-md-4 bank-field d-none">
                            <label class="form-label fw-semibold">Account No</label>
                            <input type="text" name="account_number" class="form-control"
                                value="{{ old('account_number') }}" placeholder="Enter account number">
                        </div>

                        <div class="col-md-4 bank-field d-none">
                            <label class="form-label fw-semibold">IFSC Code</label>
                            <input type="text" name="ifsc_code" class="form-control" value="{{ old('ifsc_code') }}"
                                placeholder="Enter IFSC code">
                        </div>

                        <div class="col-md-4 cheque-field d-none">
                            <label class="form-label fw-semibold">Cheque Date</label>
                            <input type="date" name="cheque_date" class="form-control"
                                value="{{ old('cheque_date') }}">
                        </div>
                    </div>

                    <div class="transaction-action-bar">
                        <button type="submit" class="btn btn-danger px-4" id="cancelBookingBtn">
                            <span class="btn-label"><i class="bi bi-x-circle me-1"></i> Cancel Booking</span>
                            <span class="btn-loader d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Payment History --}}
        <div class="transaction-card mb-4">
            <div class="transaction-card-body">
                <div class="transaction-section-title">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-section-title-icon"><i class="bi bi-receipt"></i></span>
                        <div>
                            <h5 class="fw-bold mb-1">Selected Plot Payment History</h5>
                            <small class="text-muted">Payment details will appear after plot selection.</small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive transaction-mini-table">
                    <table class="table table-hover align-middle mb-0 transaction-table">
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Pay Mode</th>
                                <th>Paid Amount</th>
                                <th>Status</th>
                                <th>Transaction No</th>
                                <th>Paid At</th>
                            </tr>
                        </thead>

                        <tbody id="paymentHistoryBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Select a plot to view payment history.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="transaction-card transaction-history-card mb-4">
            <div class="transaction-history-head">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-section-title-icon"><i class="bi bi-clock-history"></i></span>
                    <div>
                        <h5 class="fw-bold mb-1">Cancel Booking History</h5>
                        <small class="text-muted">All cancelled plot booking records.</small>
                    </div>
                </div>
                <span class="transaction-count">{{ $cancelHistories->count() }} Records</span>
            </div>

            <div class="transaction-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 transaction-table" id="cancelHistoryTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Project / Block / Plot</th>
                                <th>Deduction</th>
                                <th>Refund</th>
                                <th>Pay Mode</th>
                                <th>Pay Date</th>
                                <th>Bank Details</th>
                                <th>Cancelled At</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($cancelHistories as $key => $history)
                                @php
                                    $booking = $history->customerBooking;
                                    $plotSale = $history->plotSaleDetail;
                                @endphp

                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $plotSale?->booking_code ?? ($booking?->booking_code ?? '-') }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $booking?->customer_code ?? '-' }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $booking?->primaryDetail?->name ?? ($booking?->customer_name ?? '-') }}
                                        </small>
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $plotSale?->project?->name ?? '-' }}
                                        </div>
                                        <small class="text-muted">
                                            Block:
                                            {{ $plotSale?->block?->block ?? '-' }}
                                            |
                                            Plot:
                                            {{ $plotSale?->plotDetail?->plot_number ?? '-' }}
                                        </small>
                                    </td>

                                    <td>
                                        <div>
                                            &#8377;{{ number_format((float) ($history->deduction_amount ?? 0), 2) }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $history->deduction_percentage ?? 0 }}%
                                        </small>
                                    </td>

                                    <td class="fw-bold text-success">
                                        &#8377;{{ number_format((float) ($history->refund_amount ?? 0), 2) }}
                                    </td>

                                    <td>
                                        {{ $history->pay_mode ? strtoupper(str_replace('_', ' / ', $history->pay_mode)) : '-' }}
                                    </td>

                                    <td>
                                        {{ $history->pay_date ? \Carbon\Carbon::parse($history->pay_date)->format('d-m-Y') : '-' }}
                                    </td>

                                    <td>
                                        <div>{{ $history->bank_name ?? '-' }}</div>
                                        <small class="text-muted">
                                            A/C: {{ $history->account_number ?? '-' }}
                                            <br>
                                            IFSC: {{ $history->ifsc_code ?? '-' }}
                                        </small>

                                        @if ($history->cheque_date)
                                            <br>
                                            <small class="text-muted">
                                                Cheque Date:
                                                {{ \Carbon\Carbon::parse($history->cheque_date)->format('d-m-Y') }}
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $history->created_at ? $history->created_at->format('d-m-Y h:i A') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No cancel history found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@php
    $plotSalesForJs = $plotSales
        ->map(function ($sale) {
            return [
                'id' => $sale->id,
                'project_id' => $sale->project_id,
                'project_name' => $sale->project?->name,
                'block_id' => $sale->block_id,
                'block_name' => $sale->block?->block,
                'plot_detail_id' => $sale->plot_detail_id,
                'plot_number' => $sale->plotDetail?->plot_number,

                'customer_booking_id' => $sale->customer_booking_id,
                'booking_code' => $sale->booking_code ?? $sale->customerBooking?->booking_code,
                'customer_code' => $sale->customerBooking?->customer_code,
                'customer_name' =>
                    $sale->customerBooking?->primaryDetail?->name ?? $sale->customerBooking?->customer_name,

                'payments' => $sale->payments
                    ->map(function ($payment) {
                        return [
                            'payment_mode' => $payment->payment_mode,
                            'paid_amount' => $payment->paid_amount,
                            'booking_amount' => $payment->booking_amount,
                            'due_amount' => $payment->due_amount,
                            'booking_status' => $payment->booking_status,
                            'payment_status' => $payment->payment_status,
                            'transaction_number' => $payment->transaction_number,
                            'receipt_number' => $payment->receipt_number,
                            'created_at' => optional($payment->created_at)->format('d/m/Y'),
                        ];
                    })
                    ->toArray(),
            ];
        })
        ->toArray();
@endphp

@push('scripts')
    <script>
        if ($('#cancelHistoryTable tbody tr td').attr('colspan') === undefined) {
            $('#cancelHistoryTable').DataTable({
                pageLength: 10,
                responsive: true,
            });
        }
        const plotSales = @json($plotSalesForJs);

        function updateBlockOptions(projectId) {
            const blocks = plotSales
                .filter(sale => sale.project_id == projectId)
                .reduce((acc, sale) => {
                    if (!acc.some(block => block.id === sale.block_id)) {
                        acc.push({
                            id: sale.block_id,
                            name: sale.block_name
                        });
                    }

                    return acc;
                }, []);

            let blockHtml = '<option value="">Select Block</option>';

            blocks.forEach(block => {
                blockHtml += `<option value="${block.id}">${block.name}</option>`;
            });

            $('#blockId').html(blockHtml);
            $('#plotSaleId').html('<option value="">Select Plot</option>');
        }

        function updatePlotOptions(projectId, blockId) {
            const plots = plotSales.filter(
                sale => sale.project_id == projectId && sale.block_id == blockId
            );

            let plotHtml = '<option value="">Select Plot</option>';

            plots.forEach(plot => {
                plotHtml += `<option value="${plot.id}">${plot.plot_number}</option>`;
            });

            $('#plotSaleId').html(plotHtml);
        }

        function clearSelection() {
            $('#customerBookingId').val('');
            $('#plotSaleDetailId').val('');

            $('#bookingCode').val('');
            $('#customerCode').val('');
            $('#customerName').val('');
            $('#paidAmount').val('');
            $('#paymentDate').val('');
            $('#paymentMode').val('');

            $('#deductionAmount').val('');
            $('#deductionPercentage').val('');
            $('#refundAmount').val('');

            $('#paymentHistoryBody').html(`
        <tr>
            <td colspan="6" class="text-center text-muted py-4">
                Select a plot to view payment history.
            </td>
        </tr>
    `);
        }

        function loadPlotDetails(plotId) {
            const sale = plotSales.find(item => item.id == plotId);

            if (!sale) {
                clearSelection();
                return;
            }

            $('#customerBookingId').val(sale.customer_booking_id);
            $('#plotSaleDetailId').val(sale.id);

            $('#bookingCode').val(sale.booking_code || '');
            $('#customerCode').val(sale.customer_code || '');
            $('#customerName').val(sale.customer_name || '');

            const payments = sale.payments || [];

            if (payments.length === 0) {
                $('#paidAmount').val('0.00');
                $('#paymentDate').val('');
                $('#paymentMode').val('');

                $('#paymentHistoryBody').html(`
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    No payments found for this plot.
                </td>
            </tr>
        `);

                return;
            }

            let paidAmount = 0;

            payments.forEach(payment => {
                paidAmount += parseFloat(payment.paid_amount || payment.booking_amount || 0);
            });

            const latestPayment = payments[payments.length - 1];

            $('#paidAmount').val(paidAmount.toFixed(2));
            $('#paymentDate').val(latestPayment.created_at || '');
            $('#paymentMode').val((latestPayment.payment_mode || '').toUpperCase());

            $('#refundAmount').val(paidAmount.toFixed(2));

            let rows = '';

            payments.forEach(payment => {
                rows += `
            <tr>
                <td>${payment.receipt_number || 'N/A'}</td>
                <td>${(payment.payment_mode || 'N/A').toUpperCase()}</td>
                <td class="fw-semibold text-success">
                    Rs. ${parseFloat(payment.paid_amount || payment.booking_amount || 0).toFixed(2)}
                </td>
                <td>${payment.payment_status || 'N/A'}</td>
                <td>${payment.transaction_number || 'N/A'}</td>
                <td>${payment.created_at || 'N/A'}</td>
            </tr>
        `;
            });

            $('#paymentHistoryBody').html(rows);
        }

        function calculateRefund() {
            let paidAmount = parseFloat($('#paidAmount').val()) || 0;
            let deductionAmount = parseFloat($('#deductionAmount').val()) || 0;
            let deductionPercentage = parseFloat($('#deductionPercentage').val()) || 0;

            if (deductionPercentage > 0) {
                deductionAmount = (paidAmount * deductionPercentage) / 100;
                $('#deductionAmount').val(deductionAmount.toFixed(2));
            }

            let refundAmount = paidAmount - deductionAmount;

            if (refundAmount < 0) {
                refundAmount = 0;
            }

            $('#refundAmount').val(refundAmount.toFixed(2));
        }

        function togglePayFields() {
            let mode = $('#payMode').val();

            $('.bank-field, .cheque-field').addClass('d-none');

            if (['cheque', 'dd', 'neft_rtgs', 'card'].includes(mode)) {
                $('.bank-field').removeClass('d-none');
            }

            if (mode === 'cheque') {
                $('.cheque-field').removeClass('d-none');
            }
        }

        $(document).ready(function() {

            $('#projectId').on('change', function() {
                const projectId = $(this).val();

                if (!projectId) {
                    $('#blockId').html('<option value="">Select Block</option>');
                    $('#plotSaleId').html('<option value="">Select Plot</option>');
                    clearSelection();
                    return;
                }

                updateBlockOptions(projectId);
                clearSelection();
            });

            $('#blockId').on('change', function() {
                const projectId = $('#projectId').val();
                const blockId = $(this).val();

                if (!blockId) {
                    $('#plotSaleId').html('<option value="">Select Plot</option>');
                    clearSelection();
                    return;
                }

                updatePlotOptions(projectId, blockId);
                clearSelection();
            });

            $('#plotSaleId').on('change', function() {
                const plotId = $(this).val();

                loadPlotDetails(plotId);
            });

            $('#deductionAmount, #deductionPercentage').on('keyup change', function() {
                calculateRefund();
            });

            $('#payMode').on('change', function() {
                togglePayFields();
            });

            $('#cancelBookingForm').on('submit', function() {
                const button = $('#cancelBookingBtn');
                button.prop('disabled', true);
                button.find('.btn-label').addClass('d-none');
                button.find('.btn-loader').removeClass('d-none');
            });

            togglePayFields();
        });
    </script>
@endpush
