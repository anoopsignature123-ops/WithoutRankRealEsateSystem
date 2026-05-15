@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">One Time Payment</h3>
                <small class="text-muted">Select project, block and plot</small>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.one-time-payment.store') }}" id="paymentForm">
            @csrf
            <div class="row">
                {{-- LEFT SIDE --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            {{-- Hidden Fields --}}
                            <input type="hidden" name="customer_booking_id" id="customer_booking_id"
                                value="{{ old('customer_booking_id') }}">
                            <input type="hidden" name="plot_sale_detail_id" id="plot_sale_detail_id"
                                value="{{ old('plot_sale_detail_id') }}">

                            <div class="row">
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
                                    <label class="form-label fw-semibold">Plot *</label>
                                    <select id="plot_id" class="form-select" required>
                                        <option value="">Select Plot</option>
                                    </select>
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
                                    <input type="text" name="manual_receipt_number" value="{{ old('manual_receipt_number') }}"
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
                                    <input type="number" step="0.01" name="booking_amount" id="paid_amount"
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
                                        <option value="cash" {{ old('payment_mode') == 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="cheque" {{ old('payment_mode') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                        <option value="dd" {{ old('payment_mode') == 'dd' ? 'selected' : '' }}>DD</option>
                                        <option value="neft_rtgs" {{ old('payment_mode') == 'neft_rtgs' ? 'selected' : '' }}>NEFT / RTGS</option>
                                        <option value="card" {{ old('payment_mode') == 'card' ? 'selected' : '' }}>Card</option>
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
                                    <input type="text" name="transaction_number" value="{{ old('transaction_number') }}"
                                        class="form-control" placeholder="Enter transaction number">
                                </div>

                                {{-- Remark --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Remark</label>
                                    <textarea name="remark" rows="2" class="form-control"
                                        placeholder="Enter payment remark">{{ old('remark') }}</textarea>
                                </div>

                                {{-- Submit --}}
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success px-4">Submit Payment</button>
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