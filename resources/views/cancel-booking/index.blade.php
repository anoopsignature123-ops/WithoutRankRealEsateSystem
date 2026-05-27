@extends('layouts.app')
@section('content')
    <div class="container-fluid mt-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Cancel Booking </h3>
                        <p class="text-muted mb-0 small"> Cancel Booking </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
               

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('cancel-booking.store') }}">
                    @csrf
                    <input type="hidden" name="customer_booking_id" id="customerBookingId">
                    <input type="hidden" name="plot_sale_detail_id" id="plotSaleDetailId">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Site Name</label>
                            <select id="projectId" class="form-select">
                                <option value="">Select Site</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Block</label>
                            <select id="blockId" class="form-select">
                                <option value="">Select Block</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Plot No</label>
                            <select id="plotSaleId" class="form-select">
                                <option value="">Select Plot</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Booking Id</label>
                            <input type="text" id="bookingCode" class="form-control" readonly
                                placeholder="Booking ID will show here">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Customer Id</label>
                            <input type="text" id="customerCode" class="form-control" readonly
                                placeholder="Customer ID will show here">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Customer Name</label>
                            <input type="text" id="customerName" class="form-control" readonly
                                placeholder="Customer name will show here">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Paid Amount</label>
                            <input type="text" id="paidAmount" class="form-control" readonly
                                placeholder="Paid amount will show here">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Payment Date</label>
                            <input type="text" id="paymentDate" class="form-control" readonly
                                placeholder="Payment date will show here">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Payment Mode</label>
                            <input type="text" id="paymentMode" class="form-control" readonly
                                placeholder="Payment mode will show here">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Deduction Amount</label>
                            <input type="number" step="0.01" name="deduction_amount" class="form-control"
                                value="{{ old('deduction_amount') }}" placeholder="Enter deduction amount">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Deduction (%)</label>
                            <input type="number" step="0.01" name="deduction_percentage" class="form-control"
                                value="{{ old('deduction_percentage') }}" placeholder="Enter deduction %">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Refund Amount</label>
                            <input type="number" step="0.01" name="refund_amount" class="form-control"
                                value="{{ old('refund_amount') }}" placeholder="Enter refund amount">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Pay Mode</label>
                            <select name="pay_mode" class="form-select">
                                <option value="">Select Pay Mode</option>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                                <option value="dd">DD</option>
                                <option value="neft_rtgs">NEFT / RTGS</option>
                                <option value="card">Card</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Pay Date</label>
                            <input type="date" name="pay_date" class="form-control" value="{{ old('pay_date') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}"
                                placeholder="Enter bank name">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Account No</label>
                            <input type="text" name="account_number" class="form-control"
                                value="{{ old('account_number') }}" placeholder="Enter account number">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">IFSC Code</label>
                            <input type="text" name="ifsc_code" class="form-control" value="{{ old('ifsc_code') }}"
                                placeholder="Enter IFSC code">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Cheque Date</label>
                            <input type="date" name="cheque_date" class="form-control"
                                value="{{ old('cheque_date') }}" placeholder="Select cheque date">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-danger px-4">Cancel Booking</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Selected Plot Payment History</h5>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>Pay Mode</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Transaction / Receipt</th>
                                <th>Paid At</th>
                            </tr>
                        </thead>
                        <tbody id="paymentHistoryBody">
                            <tr>
                                <td colspan="5" class="text-center">Select a plot to view payment history.</td>
                            </tr>
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
                'booking_code' => $sale->customerBooking->booking_code ?? null,
                'customer_code' => $sale->customerBooking->customer_code ?? null,
                'customer_name' => $sale->customerBooking->primaryDetail->name ?? null,
                'payments' => $sale->customerBooking->payments
                    ->map(function ($payment) {
                        return [
                            'payment_mode' => $payment->payment_mode,
                            'booking_amount' => $payment->booking_amount,
                            'due_amount' => $payment->due_amount,
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
            const plots = plotSales.filter(sale => sale.project_id == projectId && sale.block_id == blockId);
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
            $('#paymentHistoryBody').html(
                '<tr><td colspan="5" class="text-center">Select a plot to view payment history.</td></tr>');
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
                $('#paymentHistoryBody').html(
                    '<tr><td colspan="5" class="text-center">No payments found for this plot.</td></tr>');
                return;
            }

            let paidAmount = 0;
            payments.forEach(payment => {
                paidAmount += parseFloat(payment.booking_amount || 0);
            });

            const latestPayment = payments[payments.length - 1];
            $('#paidAmount').val(paidAmount.toFixed(2));
            $('#paymentDate').val(latestPayment.created_at || '');
            $('#paymentMode').val(latestPayment.payment_mode || '');

            let rows = '';
            payments.forEach(payment => {
                rows += `<tr>
                    <td>${payment.payment_mode || 'N/A'}</td>
                    <td>${payment.booking_amount || payment.due_amount || '0.00'}</td>
                    <td>${payment.payment_status || 'N/A'}</td>
                    <td>${payment.transaction_number || payment.receipt_number || 'N/A'}</td>
                    <td>${payment.created_at || 'N/A'}</td>
                </tr>`;
            });
            $('#paymentHistoryBody').html(rows);
        }

        $(document).ready(function() {
            $('#projectId').change(function() {
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

            $('#blockId').change(function() {
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

            $('#plotSaleId').change(function() {
                const plotId = $(this).val();
                loadPlotDetails(plotId);
            });
        });
    </script>
@endpush
