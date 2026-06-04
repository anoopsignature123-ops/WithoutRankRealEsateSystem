@php
    $payment = $payment ?? null;
@endphp

<div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">

    <div class="card-header bg-success text-white py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center"
                style="width:48px;height:48px;">
                <i class="bi bi-wallet2 fs-4"></i>
            </div>

            <div>
                <h5 class="fw-bold mb-0">Payment Details</h5>
                <small class="text-white-50">
                    Select payment plan, payment mode and review booking amount.
                </small>
            </div>
        </div>
    </div>

    <div class="card-body p-4">

        <div class="alert alert-success bg-success-subtle border-success-subtle text-success rounded-4 mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-cash-stack fs-3"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Total Payable Amount</h6>
                        <small>Final amount calculated from selected plot sale details.</small>
                    </div>
                </div>

                <div class="fs-5 fw-bold">
                    ₹ {{ number_format($plotSale->total_plot_cost ?? 0, 2) }}
                </div>
            </div>
        </div>

        <div class="row g-3">

            <input type="hidden" id="totalPlotCost" value="{{ $plotSale->total_plot_cost ?? 0 }}">
            <input type="hidden" name="plot_sale_detail_id"
                value="{{ old('plot_sale_detail_id', request('plot_sale_detail_id')) }}">

            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Plan Type <span class="text-danger">*</span>
                </label>

                <select name="plan_type" id="planType"
                    class="form-select @error('plan_type') is-invalid @enderror">
                    <option value="">Select Plan Type</option>
                    <option value="full_payment"
                        {{ old('plan_type', $payment?->plan_type) == 'full_payment' ? 'selected' : '' }}>
                        Full Payment
                    </option>
                    <option value="emi_plan"
                        {{ old('plan_type', $payment?->plan_type) == 'emi_plan' ? 'selected' : '' }}>
                        EMI Plan
                    </option>
                </select>

                @error('plan_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 common-field d-none">
                <label class="form-label fw-semibold">
                    Pay Mode <span class="text-danger">*</span>
                </label>

                <select name="payment_mode" id="paymentMode"
                    class="form-select @error('payment_mode') is-invalid @enderror">
                    <option value="cash"
                        {{ old('payment_mode', $payment?->payment_mode) == 'cash' ? 'selected' : '' }}>
                        Cash
                    </option>
                    <option value="cheque"
                        {{ old('payment_mode', $payment?->payment_mode) == 'cheque' ? 'selected' : '' }}>
                        Cheque
                    </option>
                    <option value="dd"
                        {{ old('payment_mode', $payment?->payment_mode) == 'dd' ? 'selected' : '' }}>
                        DD
                    </option>
                    <option value="neft_rtgs"
                        {{ old('payment_mode', $payment?->payment_mode) == 'neft_rtgs' ? 'selected' : '' }}>
                        NEFT / RTGS
                    </option>
                    <option value="card"
                        {{ old('payment_mode', $payment?->payment_mode) == 'card' ? 'selected' : '' }}>
                        Card
                    </option>
                </select>

                @error('payment_mode')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12 common-field d-none">
                <div class="card border-0 bg-light rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-success mb-3">
                            <i class="bi bi-receipt me-1"></i>
                            Booking Amount Information
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Booking Amount <span class="text-danger">*</span>
                                </label>

                                <input type="number" id="bookingAmount" name="booking_amount"
                                    class="form-control @error('booking_amount') is-invalid @enderror"
                                    value="{{ old('booking_amount', $payment?->booking_amount) }}"
                                    placeholder="Enter booking amount">

                                @error('booking_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Due Amount <span class="text-danger">*</span>
                                </label>

                                <input type="number" id="dueAmount" name="due_amount"
                                    class="form-control bg-white @error('due_amount') is-invalid @enderror"
                                    value="{{ old('due_amount', $payment?->due_amount) }}" readonly>

                                @error('due_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 full-field d-none">
                                <label class="form-label fw-semibold">
                                    Net Payable Amount <span class="text-danger">*</span>
                                </label>

                                <input type="number" id="netPayable" name="net_payable_amount"
                                    class="form-control bg-white fw-semibold text-success @error('net_payable_amount') is-invalid @enderror"
                                    value="{{ old('net_payable_amount', $payment?->net_payable_amount) }}" readonly>

                                @error('net_payable_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 emi-field d-none">
                                <label class="form-label fw-semibold">
                                    EMI Months <span class="text-danger">*</span>
                                </label>

                                <input type="number" id="emiMonths" name="emi_months"
                                    class="form-control @error('emi_months') is-invalid @enderror"
                                    value="{{ old('emi_months', $payment?->emi_months) }}"
                                    placeholder="Enter EMI months">

                                @error('emi_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 emi-field d-none">
                                <label class="form-label fw-semibold">
                                    After Booking Payable <span class="text-danger">*</span>
                                </label>

                                <input type="number" id="afterBookingAmount" name="after_booking_payable_amount"
                                    class="form-control bg-white fw-semibold text-primary @error('after_booking_payable_amount') is-invalid @enderror"
                                    value="{{ old('after_booking_payable_amount', $payment?->after_booking_payable_amount) }}"
                                    readonly>

                                @error('after_booking_payable_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 emi-field d-none">
                                <label class="form-label fw-semibold">Remark</label>

                                <input type="text" name="remark"
                                    class="form-control @error('remark') is-invalid @enderror"
                                    value="{{ old('remark', $payment?->remark) }}"
                                    placeholder="Enter remark">

                                @error('remark')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-success mb-3">
                            <i class="bi bi-bank me-1"></i>
                            Bank / Transaction Details
                        </h6>

                        <div class="row g-3">

                            <div class="col-md-6 bank-field d-none">
                                <label class="form-label fw-semibold">
                                    A/C Number <span class="text-danger">*</span>
                                </label>

                                <input type="text" name="account_number"
                                    class="form-control @error('account_number') is-invalid @enderror"
                                    value="{{ old('account_number', $payment?->account_number) }}"
                                    placeholder="Enter account number">

                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 bank-detail-field d-none">
                                <label class="form-label fw-semibold">
                                    Bank Name <span class="text-danger">*</span>
                                </label>

                                <input type="text" name="bank_name"
                                    class="form-control @error('bank_name') is-invalid @enderror"
                                    value="{{ old('bank_name', $payment?->bank_name) }}"
                                    placeholder="Enter bank name">

                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 bank-detail-field d-none">
                                <label class="form-label fw-semibold">
                                    Branch Name <span class="text-danger">*</span>
                                </label>

                                <input type="text" name="branch_name"
                                    class="form-control @error('branch_name') is-invalid @enderror"
                                    value="{{ old('branch_name', $payment?->branch_name) }}"
                                    placeholder="Enter branch name">

                                @error('branch_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 instrument-field d-none">
                                <label class="form-label fw-semibold" id="instrumentDateLabel">
                                    Cheque Date <span class="text-danger">*</span>
                                </label>

                                <input type="date" name="cheque_date"
                                    class="form-control @error('cheque_date') is-invalid @enderror"
                                    value="{{ old('cheque_date', $payment?->cheque_date ? \Carbon\Carbon::parse($payment->cheque_date)->format('Y-m-d') : '') }}">

                                @error('cheque_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 cheque-number-field d-none">
                                <label class="form-label fw-semibold">
                                    Cheque No. <span class="text-danger">*</span>
                                </label>

                                <input type="text" name="cheque_number"
                                    class="form-control @error('cheque_number') is-invalid @enderror"
                                    value="{{ old('cheque_number', $payment?->cheque_number) }}"
                                    placeholder="Enter cheque number">

                                @error('cheque_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 dd-number-field d-none">
                                <label class="form-label fw-semibold">
                                    DD No. <span class="text-danger">*</span>
                                </label>

                                <input type="text" name="dd_number"
                                    class="form-control @error('dd_number') is-invalid @enderror"
                                    value="{{ old('dd_number', $payment?->dd_number) }}"
                                    placeholder="Enter DD number">

                                @error('dd_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 neft-number-field d-none">
                                <label class="form-label fw-semibold" id="transactionNumberLabel">
                                    NEFT / RTGS No. <span class="text-danger">*</span>
                                </label>

                                <input type="text" id="transactionNumber" name="transaction_number"
                                    class="form-control @error('transaction_number') is-invalid @enderror"
                                    value="{{ old('transaction_number', $payment?->transaction_number) }}"
                                    placeholder="Enter NEFT / RTGS number">

                                @error('transaction_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 payment-bank-empty">
                                <div class="alert alert-light border rounded-4 mb-0">
                                    <i class="bi bi-info-circle me-1 text-success"></i>
                                    Bank details will appear according to selected payment mode.
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div id="paymentSummary" class="card border-0 shadow-sm mt-4 d-none rounded-4">
            <div class="card-body p-3" id="paymentSummaryBody"></div>
        </div>

    </div>
</div>