@php
    $payment = $payment ?? null;
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4 border-bottom pb-2">
            Payment Details
        </h5>
        <div class="d-flex justify-content-end align-items-center">
            <span class="fw-semibold fs-6">
                Total Payable Amount
            </span>
            <span class="fw-bold fs-6 text-success ms-2">
                ₹ {{ number_format($plotSale->total_plot_cost ?? 0, 2) }}
            </span>
        </div>
        <div class="row">
            <input type="hidden" id="totalPlotCost" value="{{ $plotSale->total_plot_cost ?? 0 }}">
            <input type="hidden" name="plot_sale_detail_id" value="{{ request('plot_sale_detail_id') }}">
            {{-- Plan Type --}}
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">
                    Plan Type
                </label>
                <select name="plan_type" id="planType" class="form-select @error('plan_type') is-invalid @enderror">
                    <option value="">
                        Select Plan Type
                    </option>
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
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            {{-- Pay Mode --}}
            <div class="col-md-6 mb-3 common-field d-none">

                <label class="form-label fw-semibold">
                    Pay Mode
                </label>

                <select name="payment_mode" id="paymentMode" class="form-select">

                    <option value="cash"
                        {{ old('payment_mode', $payment?->payment_mode) == 'cash' ? 'selected' : '' }}>
                        Cash
                    </option>

                    <option value="cheque"
                        {{ old('payment_mode', $payment?->payment_mode) == 'cheque' ? 'selected' : '' }}>
                        Cheque
                    </option>

                    <option value="dd" {{ old('payment_mode', $payment?->payment_mode) == 'dd' ? 'selected' : '' }}>
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

            </div>


            {{-- Booking Amount --}}
            <div class="col-md-6 mb-3 common-field d-none">

                <label class="form-label fw-semibold">
                    Booking Amount
                </label>

                <input type="number" id="bookingAmount" name="booking_amount" class="form-control"
                    value="{{ old('booking_amount', $payment?->booking_amount) }}" placeholder="Enter booking amount">

            </div>


            {{-- Due Amount --}}
            <div class="col-md-6 mb-3 common-field d-none">

                <label class="form-label fw-semibold">
                    Due Amount
                </label>

                <input type="number" id="dueAmount" name="due_amount" class="form-control"
                    value="{{ old('due_amount', $payment?->due_amount) }}" readonly>

            </div>

            <input type="hidden" id="transactionNumber" name="transaction_number"
                value="{{ old('transaction_number', $payment?->transaction_number) }}">


            {{-- Net Payable --}}
            <div class="col-md-6 mb-3 full-field d-none">

                <label class="form-label fw-semibold">
                    Net Payable Amount
                </label>

                <input type="number" id="netPayable" name="net_payable_amount" class="form-control"
                    value="{{ old('net_payable_amount', $payment?->net_payable_amount) }}" readonly>

            </div>


            {{-- EMI --}}
            <div class="col-md-6 mb-3 emi-field d-none">

                <label class="form-label fw-semibold">
                    EMI Months
                </label>

                <input type="number" id="emiMonths" name="emi_months" class="form-control"
                    value="{{ old('emi_months', $payment?->emi_months) }}" placeholder="Enter EMI months">
                @error('emi_months')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-6 mb-3 emi-field d-none">

                <label class="form-label fw-semibold">
                    After Booking Payable
                </label>

                <input type="number" id="afterBookingAmount" name="after_booking_payable_amount" class="form-control"
                    value="{{ old('after_booking_payable_amount', $payment?->after_booking_payable_amount) }}"
                    readonly>

            </div>


            {{-- EMI Remark --}}
            <div class="col-md-6 mb-3 emi-field d-none">

                <label class="form-label fw-semibold">
                    Remark
                </label>

                <input type="text" name="remark" class="form-control"
                    value="{{ old('remark', $payment?->remark) }}" placeholder="Enter remark">

            </div>


            {{-- A/C Number --}}
            <div class="col-md-6 mb-3 bank-field d-none">

                <label class="form-label fw-semibold">
                    A/C Number
                </label>

                <input type="text" name="account_number" class="form-control"
                    value="{{ old('account_number', $payment?->account_number) }}" placeholder="Enter account number">

            </div>


            {{-- Bank --}}
            <div class="col-md-6 mb-3 bank-detail-field d-none">

                <label class="form-label fw-semibold">
                    Bank Name
                </label>

                <input type="text" name="bank_name" class="form-control"
                    value="{{ old('bank_name', $payment?->bank_name) }}" placeholder="Enter bank name">

            </div>


            {{-- Branch --}}
            <div class="col-md-6 mb-3 bank-detail-field d-none">

                <label class="form-label fw-semibold">
                    Branch Name
                </label>

                <input type="text" name="branch_name" class="form-control"
                    value="{{ old('branch_name', $payment?->branch_name) }}" placeholder="Enter branch name">

            </div>


            {{-- Instrument Date --}}
            <div class="col-md-6 mb-3 instrument-field d-none">

                <label class="form-label fw-semibold">
                    Cheque Date
                </label>

                <input type="date" name="cheque_date" class="form-control"
                    value="{{ old('cheque_date', $payment?->cheque_date) }}">

            </div>

            {{-- Cheque Number --}}
            <div class="col-md-6 mb-3 instrument-field cheque-number-field d-none">

                <label class="form-label fw-semibold">
                    Cheque Number
                </label>

                <input type="text" name="cheque_number" class="form-control"
                    value="{{ old('cheque_number', $payment?->cheque_number) }}" placeholder="Enter cheque number">

            </div>

            {{-- DD Number --}}
            <div class="col-md-6 mb-3 instrument-field dd-number-field d-none">

                <label class="form-label fw-semibold">
                    DD Number
                </label>

                <input type="text" name="dd_number" class="form-control"
                    value="{{ old('dd_number', $payment?->dd_number) }}" placeholder="Enter DD number">

            </div>

        </div>

        <div id="paymentSummary" class="card border-info mt-3 d-none">
            <div class="card-body p-3" id="paymentSummaryBody"></div>
        </div>

    </div>

</div>
