@php
    $payment = $selectedPayment;
    $booking = $payment->customerBooking;
    $plotSale = $payment->plotSaleDetail;
    $totalPlotCost = (float) ($plotSale?->total_plot_cost ?? 0);
    $paidAmount = (float) ($payment?->paid_amount ?? $payment?->booking_amount ?? 0);
    $dueAmount = max(0, $totalPlotCost - $paidAmount);
@endphp

<div class="edit-payment-modal-form">
    <div class="edit-payment-form-head">
        <div>
            <span class="text-success fw-bold text-uppercase small">Selected Receipt</span>
            <h5 class="fw-bold mb-1">{{ $payment?->receipt_number ?? 'N/A' }}</h5>
            <small class="text-muted">Update payment amount, plan and payment instrument details.</small>
        </div>
        <span class="badge bg-light text-dark border">
            {{ ucfirst($payment?->payment_status ?? 'Pending') }}
        </span>
    </div>

    <form method="POST" action="{{ route('edit-payment-details.update', $payment->id) }}" id="editPaymentForm">
        @csrf
        @method('PUT')

        <input type="hidden" id="totalPlotCost" value="{{ $totalPlotCost }}">
        <input type="hidden" name="net_payable_amount" id="netPayableAmount"
            value="{{ old('net_payable_amount', $payment?->net_payable_amount ?? $dueAmount) }}">

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Customer</label>
                <input type="text" class="form-control bg-light" readonly
                    value="{{ $booking?->customer_code }} - {{ $booking?->primaryDetail?->name ?? $booking?->customer_name }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Booking ID</label>
                <input type="text" class="form-control bg-light" readonly
                    value="{{ $plotSale?->booking_code ?? $booking?->booking_code }}">
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Plot</label>
                <input type="text" class="form-control bg-light" readonly
                    value="{{ $plotSale?->project?->name }} / {{ $plotSale?->block?->block }} / Plot {{ $plotSale?->plotDetail?->plot_number }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Receipt No</label>
                <input type="text" class="form-control bg-light" readonly
                    value="{{ old('receipt_number', $payment?->receipt_number) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Manual Receipt No</label>
                <input type="text" name="manual_receipt_number" class="form-control"
                    value="{{ old('manual_receipt_number', $payment?->manual_receipt_number) }}"
                    placeholder="Enter manual receipt number">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Payment Type</label>
                <select name="plan_type" id="planType" class="form-select">
                    <option value="full_payment" {{ old('plan_type', $payment?->plan_type) == 'full_payment' ? 'selected' : '' }}>
                        Full Payment
                    </option>
                    <option value="emi_plan" {{ old('plan_type', $payment?->plan_type) == 'emi_plan' ? 'selected' : '' }}>
                        EMI Plan
                    </option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Paid Amount</label>
                <input type="text" inputmode="decimal" name="paid_amount" id="paidAmount"
                    class="form-control @error('paid_amount') is-invalid @enderror"
                    value="{{ old('paid_amount', $paidAmount) }}">
                @error('paid_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Due Amount</label>
                <input type="text" readonly name="due_amount" id="dueAmount" class="form-control bg-light"
                    value="{{ old('due_amount', number_format($dueAmount, 2, '.', '')) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Pay Mode</label>
                <select name="payment_mode" id="paymentMode" class="form-select">
                    <option value="cash" {{ old('payment_mode', $payment?->payment_mode) == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="cheque" {{ old('payment_mode', $payment?->payment_mode) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="dd" {{ old('payment_mode', $payment?->payment_mode) == 'dd' ? 'selected' : '' }}>DD</option>
                    <option value="neft_rtgs" {{ old('payment_mode', $payment?->payment_mode) == 'neft_rtgs' ? 'selected' : '' }}>NEFT / RTGS</option>
                    <option value="card" {{ old('payment_mode', $payment?->payment_mode) == 'card' ? 'selected' : '' }}>Card</option>
                </select>
            </div>

            <div class="col-md-6 emi-field d-none">
                <label class="form-label fw-semibold">EMI Months</label>
                <input type="number" name="emi_months" id="emiMonths" class="form-control"
                    value="{{ old('emi_months', $payment?->emi_months) }}">
            </div>

            <div class="col-md-6 emi-field d-none">
                <label class="form-label fw-semibold">EMI Amount</label>
                <input type="text" readonly id="emiAmount" name="after_booking_payable_amount"
                    class="form-control bg-light"
                    value="{{ old('after_booking_payable_amount', $payment?->after_booking_payable_amount) }}">
            </div>

            <div class="col-md-6 bank-field d-none">
                <label class="form-label fw-semibold">Account Number</label>
                <input type="text" name="account_number" class="form-control"
                    value="{{ old('account_number', $payment?->account_number) }}">
            </div>

            <div class="col-md-6 bank-field d-none">
                <label class="form-label fw-semibold">Bank Name</label>
                <input type="text" name="bank_name" class="form-control"
                    value="{{ old('bank_name', $payment?->bank_name) }}">
            </div>

            <div class="col-md-6 bank-field d-none">
                <label class="form-label fw-semibold">Branch Name</label>
                <input type="text" name="branch_name" class="form-control"
                    value="{{ old('branch_name', $payment?->branch_name) }}">
            </div>

            <div class="col-md-6 cheque-field d-none">
                <label class="form-label fw-semibold">Cheque Number</label>
                <input type="text" name="cheque_number" class="form-control"
                    value="{{ old('cheque_number', $payment?->cheque_number) }}">
            </div>

            <div class="col-md-6 cheque-field d-none">
                <label class="form-label fw-semibold">Cheque Date</label>
                <input type="date" name="cheque_date" class="form-control"
                    value="{{ old('cheque_date', $payment?->cheque_date?->format('Y-m-d')) }}">
            </div>

            <div class="col-md-6 dd-field d-none">
                <label class="form-label fw-semibold">DD Number</label>
                <input type="text" name="dd_number" class="form-control"
                    value="{{ old('dd_number', $payment?->dd_number) }}">
            </div>

            <div class="col-md-6 transaction-field d-none">
                <label class="form-label fw-semibold">Transaction Number</label>
                <input type="text" name="transaction_number" class="form-control"
                    value="{{ old('transaction_number', $payment?->transaction_number) }}">
            </div>

            <div class="col-12 mt-2">
                <div class="edit-payment-modal-actions">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                <button type="submit" class="btn btn-success px-4" id="updatePaymentBtn">
                    <span class="btn-label">
                        <i class="bi bi-save me-1"></i> Update Payment
                    </span>
                    <span class="btn-loader d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Updating...
                    </span>
                </button>
                </div>
            </div>
        </div>
    </form>
</div>
