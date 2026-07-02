@extends('layouts.app')

@push('title')
    One Time Payment
@endpush
@section('content')
    <div class="container-fluid mt-4 one-time-payment-page">
        <div class="one-time-payment-hero mb-4">
            <div class="d-flex align-items-center gap-3">
                <span class="one-time-payment-hero-icon">
                    <i class="bi bi-cash-coin"></i>
                </span>
                <div>
                    <span class="text-success fw-bold text-uppercase small">Payment Collection</span>
                    <h3 class="fw-bold mb-1 text-dark">One Time Payment</h3>
                    <p class="text-muted mb-0 small">Select project, block and plot to collect pending payment.</p>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please check:</strong> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <form method="POST" action="{{ route('one-time-payment.store') }}" id="paymentForm">
            @csrf
            <div class="row">
                {{-- LEFT SIDE --}}
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm one-time-payment-card">
                        <div class="card-body p-4">
                            {{-- Hidden Fields --}}
                            <input type="hidden" name="customer_booking_id" id="customer_booking_id"
                                value="{{ old('customer_booking_id') }}">
                            <input type="hidden" name="plot_sale_detail_id" id="plot_sale_detail_id"
                                value="{{ old('plot_sale_detail_id') }}">
                            <div id="plot_sale_detail_ids_container"></div>
                            <input type="hidden" id="max_due_amount" value="0">

                            <div class="row">
                                 <input type="hidden" class="form-check-input" name="payment_plot_type"
                                                id="payment_plot_type_multiple" value="multiple" autocomplete="off" checked>
                                {{-- <div class="col-12 mb-3">
                                    <label class="form-label fw-semibold d-block">Payment For *</label>
                                    <div class="d-flex flex-wrap gap-4 border rounded-3 bg-white px-3 py-2">
                                         <div class="form-check">
                                            <input type="radio" class="form-check-input" name="payment_plot_type"
                                                id="payment_plot_type_multiple" value="multiple" autocomplete="off" checked>
                                            <label class="form-check-label fw-semibold" for="payment_plot_type_multiple">
                                                Multiple Plot
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="payment_plot_type"
                                                id="payment_plot_type_single" value="single" autocomplete="off" >
                                            <label class="form-check-label fw-semibold" for="payment_plot_type_single">
                                                Single Plot
                                            </label>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2" id="payment_plot_type_help">
                                        Select single plot payment or grouped multiple plot payment first.
                                    </small>
                                </div> --}}

                                {{-- Project --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Project *</label>
                                    <select id="project_id" class="form-select" required>
                                        <option value="">Select Project</option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Block --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Block *</label>
                                    <select id="block_id" class="form-select" required>
                                        <option value="">Select Block</option>
                                    </select>
                                </div>

                                {{-- Plot --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Booking / Plot Group *</label>
                                    <select id="plot_id" class="form-select">
                                        <option value="">Select booking group</option>
                                    </select>
                                    <small class="text-muted d-block mt-1" id="plot_group_hint">
                                        Select project and block to load booking groups.
                                    </small>
                                </div>

                                <div class="col-12">
                                    <div class="border rounded-3 p-3 mb-3 bg-white d-none" id="form_selected_plots_box">
                                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                            <div>
                                                <h6 class="fw-bold mb-1">Plot Payment Details</h6>
                                                <small class="text-muted" id="form_selected_plot_mode">
                                                    Verify plot details before entering payment amount.
                                                </small>
                                            </div>
                                            <span class="badge bg-success-subtle text-success border"
                                                id="form_selected_plot_count">0 Plots</span>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-hover align-middle mb-0 one-time-detail-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Plot</th>
                                                        <th>Area</th>
                                                        <th>Rate</th>
                                                        <th>PLC</th>
                                                        <th class="text-end">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="form_selected_plots">
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-3">
                                                            Select booking group to view plot details.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- Payment Type --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Payment Type</label>
                                    <input type="text" id="payment_type" class="form-control bg-light"
                                        placeholder="Auto filled after plot selection" readonly>
                                </div>

                                {{-- Booking --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Booking ID</label>
                                    <input type="text" id="booking_id" class="form-control bg-light"
                                        placeholder="Auto filled after plot selection" readonly>
                                </div>

                                {{-- Manual Receipt --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">
                                        Manual Receipt No
                                        <small class="text-muted">(Optional)</small>
                                    </label>
                                    <input type="text" name="manual_receipt_number"
                                        value="{{ old('manual_receipt_number') }}"
                                        class="form-control @error('manual_receipt_number') is-invalid @enderror"
                                        placeholder="Enter manual receipt number">
                                    @error('manual_receipt_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Customer ID --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Customer ID</label>
                                    <input type="text" id="customer_id" class="form-control bg-light"
                                        placeholder="Auto filled after plot selection" readonly>
                                </div>

                                {{-- Customer Name --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Customer Name</label>
                                    <input type="text" id="customer_name" class="form-control bg-light"
                                        placeholder="Auto filled after plot selection" readonly>
                                </div>

                                {{-- Amount --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Paid Amount *</label>
                                    <input type="text" inputmode="decimal" name="booking_amount" id="paid_amount"
                                        value="{{ old('booking_amount') }}"
                                        class="form-control @error('booking_amount') is-invalid @enderror"
                                        placeholder="Enter payment amount">
                                    @error('booking_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Payment Mode --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Payment Mode *</label>
                                    <select name="payment_mode" id="payment_mode" class="form-select">
                                        <option value="cash" {{ old('payment_mode') == 'cash' ? 'selected' : '' }}>Cash
                                        </option>
                                        <option value="cheque" {{ old('payment_mode') == 'cheque' ? 'selected' : '' }}>
                                            Cheque</option>
                                        <option value="dd" {{ old('payment_mode') == 'dd' ? 'selected' : '' }}>DD
                                        </option>
                                        <option value="neft_rtgs"
                                            {{ old('payment_mode') == 'neft_rtgs' ? 'selected' : '' }}>NEFT / RTGS</option>
                                        <option value="card" {{ old('payment_mode') == 'card' ? 'selected' : '' }}>Card
                                        </option>
                                    </select>
                                </div>

                                {{-- Bank Fields --}}
                                <div class="col-md-6 mb-3 bank-field d-none">
                                    <label class="form-label fw-semibold">Bank Name</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                                        class="form-control" placeholder="Enter bank name">
                                </div>

                                <div class="col-md-6 mb-3 bank-field d-none">
                                    <label class="form-label fw-semibold">Account Number</label>
                                    <input type="text" name="account_number" value="{{ old('account_number') }}"
                                        class="form-control" placeholder="Enter account number">
                                </div>

                                <div class="col-md-6 mb-3 bank-field d-none">
                                    <label class="form-label fw-semibold">Branch Name</label>
                                    <input type="text" name="branch_name" value="{{ old('branch_name') }}"
                                        class="form-control" placeholder="Enter branch name">
                                </div>

                                {{-- Cheque Fields --}}
                                <div class="col-md-6 mb-3 cheque-field d-none">
                                    <label class="form-label fw-semibold">Cheque Number</label>
                                    <input type="text" name="cheque_number" value="{{ old('cheque_number') }}"
                                        class="form-control" placeholder="Enter cheque number">
                                </div>

                                <div class="col-md-6 mb-3 cheque-field d-none">
                                    <label class="form-label fw-semibold">Cheque Date</label>
                                    <input type="date" name="cheque_date" value="{{ old('cheque_date') }}"
                                        class="form-control">
                                </div>

                                {{-- DD Fields --}}
                                <div class="col-md-6 mb-3 dd-field d-none">
                                    <label class="form-label fw-semibold">DD Number</label>
                                    <input type="text" name="dd_number" value="{{ old('dd_number') }}"
                                        class="form-control" placeholder="Enter DD number">
                                </div>

                                {{-- Transaction Fields --}}
                                <div class="col-md-6 mb-3 transaction-field d-none">
                                    <label class="form-label fw-semibold">Transaction Number</label>
                                    <input type="text" name="transaction_number"
                                        value="{{ old('transaction_number') }}" class="form-control"
                                        placeholder="Enter transaction number">
                                </div>

                                {{-- Remark --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Remark</label>
                                    <textarea name="remark" rows="2" class="form-control" placeholder="Enter payment remark">{{ old('remark') }}</textarea>
                                </div>

                                {{-- Submit --}}
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success px-4" id="submitPaymentBtn">
                                        <span class="btn-label">
                                            <i class="bi bi-check2-circle me-1"></i> Submit Payment
                                        </span>
                                        <span class="btn-loader d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                                aria-hidden="true"></span>
                                            Processing...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT SIDE --}}
                @include('payment.one-time-payment.summary')
            </div>
        </form>
    </div>
@endsection

@include('payment.one-time-payment.payment_script')
