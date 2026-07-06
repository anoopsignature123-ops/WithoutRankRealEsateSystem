@php
    $payment = $payment ?? null;
    $selectedPlotSales = ($selectedPlotSales ?? collect())->values();
    if ($selectedPlotSales->isEmpty() && isset($plotSale) && $plotSale) {
        $selectedPlotSales = collect([$plotSale]);
    }
    $selectedPlotSales = $selectedPlotSales->take(1)->values();
    $totalBookingPayable = (float) $selectedPlotSales->sum('total_plot_cost');
    $totalBookingArea = (float) $selectedPlotSales->sum('plot_area');
    $selectedBookingCode = $selectedPlotSales->first()?->booking_code ?? '-';
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
                        <small>
                            Booking Code:
                            <strong>{{ $selectedBookingCode }}</strong>
                            | Payment will be collected for this selected plot only.
                        </small>
                    </div>
                </div>

                <div class="d-flex align-items-center flex-wrap gap-2">
                    @if ($selectedPlotSales->count() > 0)
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3"
                            data-bs-toggle="modal" data-bs-target="#viewPaymentPlotModal">
                            <i class="bi bi-eye me-1"></i>View Plot Details
                        </button>
                    @endif
                    <div class="fs-5 fw-bold">
                        &#8377; {{ number_format($totalBookingPayable, 2) }}
                    </div>
                </div>
            </div>
        </div>

        @if ($selectedPlotSales->isEmpty())
            <div class="alert alert-warning rounded-4 mb-0">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-exclamation-triangle fs-4"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Plot selection required</h6>
                        <small>Please complete plot sale details before saving payment.</small>
                    </div>
                </div>
            </div>
        @else

        @if ($selectedPlotSales->count() > 0)
            <div class="card border rounded-4 mb-4 overflow-hidden">
                <div class="card-body p-3 bg-light">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <h6 class="fw-bold text-success mb-0">
                            <i class="bi bi-houses me-1"></i>
                            Selected Plot Details
                        </h6>
                        <span class="badge bg-success-subtle text-success border rounded-pill px-3 py-2">
                            {{ $selectedPlotSales->count() }} {{ $selectedPlotSales->count() === 1 ? 'Plot' : 'Plots' }}
                        </span>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <div class="border rounded-3 bg-white p-3 h-100">
                                <small class="text-muted fw-semibold">Booking Code</small>
                                <div class="fw-bold">{{ $selectedBookingCode }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 bg-white p-3 h-100">
                                <small class="text-muted fw-semibold">Total Area</small>
                                <div class="fw-bold">{{ number_format($totalBookingArea, 2) }} Sq.Ft.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 bg-white p-3 h-100">
                                <small class="text-muted fw-semibold">Total Payable</small>
                                <div class="fw-bold text-success">&#8377; {{ number_format($totalBookingPayable, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Project / Block / Plot</th>
                                    <th class="text-end">Area</th>
                                    <th class="text-end">Plot Cost</th>
                                    <th class="text-end">Total Payable</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($selectedPlotSales as $sale)
                                    <tr>
                                        <td>
                                            <strong>{{ $sale->project?->name ?? '-' }}</strong>
                                            <small class="text-muted d-block">
                                                {{ $sale->block?->block ?? '-' }} / Plot {{ $sale->plotDetail?->plot_number ?? '-' }}
                                            </small>
                                        </td>
                                        <td class="text-end">{{ number_format((float) $sale->plot_area, 2) }}</td>
                                        <td class="text-end">&#8377; {{ number_format((float) $sale->plot_cost, 2) }}</td>
                                        <td class="text-end fw-bold text-success">&#8377; {{ number_format((float) $sale->total_plot_cost, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="modal fade" id="viewPaymentPlotModal" tabindex="-1" aria-labelledby="viewPaymentPlotModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewPaymentPlotModalLabel">Selected Plot Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <span class="badge bg-success rounded-pill">{{ $selectedBookingCode }}</span>
                            <small class="text-muted d-block mt-2">Review selected plot details before saving payment.</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small text-uppercase">
                                        <th>Plot</th>
                                        <th>Project / Block</th>
                                        <th class="text-end">Area</th>
                                        <th class="text-end">Plot Cost</th>
                                        <th class="text-end">PLC</th>
                                        <th class="text-end">Final Payable</th>
                                        <th class="text-end">Total Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedPlotSales as $sale)
                                        <tr>
                                            <td>
                                                <strong>{{ $sale->plotDetail?->plot_number ?? '-' }}</strong>
                                                <div class="text-muted small">Plot {{ $sale->plotDetail?->plot_number ?? '-' }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $sale->project?->name ?? '-' }}</div>
                                                <small class="text-muted">Block {{ $sale->block?->block ?? '-' }}</small>
                                            </td>
                                            <td class="text-end">{{ number_format((float) $sale->plot_area, 2) }} Sq.Ft</td>
                                            <td class="text-end">&#8377; {{ number_format((float) $sale->plot_cost, 2) }}</td>
                                            <td class="text-end">&#8377; {{ number_format((float) $sale->plc_amount, 2) }}</td>
                                            <td class="text-end">&#8377; {{ number_format((float) $sale->final_payable, 2) }}</td>
                                            <td class="text-end fw-bold">&#8377; {{ number_format((float) $sale->total_plot_cost, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <div class="text-end">
                                <div class="text-muted small">Total Payable</div>
                                <div class="fw-bold fs-5">&#8377; {{ number_format($totalBookingPayable, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">

            <input type="hidden" id="totalPlotCost" value="{{ $totalBookingPayable }}">
            @foreach ($selectedPlotSales as $sale)
                <input type="hidden" name="plot_sale_detail_ids[]" value="{{ $sale->id }}">
            @endforeach
            <input type="hidden" name="plot_sale_detail_id"
                value="{{ old('plot_sale_detail_id', request('plot_sale_detail_id', $selectedPlotSales->first()?->id)) }}">

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
        @endif

    </div>
</div>
