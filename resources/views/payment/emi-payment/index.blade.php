@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">EMI Payment</h3>
                        <p class="text-muted mb-0 small"> Manage EMI Payment </p>
                    </div>
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('emi-payment.store') }}">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <input type="hidden" name="customer_booking_id" id="customer_booking_id">
                            <input type="hidden" name="plot_sale_detail_id" id="plot_sale_detail_id">
                            <div class="row">
                                {{-- Project --}}
                                <div class="col-md-6 mb-3">
                                    <label class="fw-semibold">Project *</label>
                                    <select id="project_id" class="form-select">
                                        <option value="">Select Project</option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Block --}}
                                <div class="col-md-6 mb-3">
                                    <label class="fw-semibold">Block *</label>
                                    <select id="block_id" class="form-select">
                                        <option value="">Select Block</option>
                                    </select>
                                </div>

                                {{-- Plot --}}
                                <div class="col-md-6 mb-3">
                                    <label class="fw-semibold">Plot *</label>
                                    <select id="plot_id" class="form-select">
                                        <option value="">Select Plot</option>
                                    </select>
                                </div>

                                {{-- Booking --}}
                                <div class="col-md-6 mb-3">
                                    <label class="fw-semibold">Booking ID</label>
                                    <input id="booking_id" class="form-control bg-light" readonly placeholder="Auto fill">
                                </div>

                                {{-- Customer --}}
                                <div class="col-md-6 mb-3">
                                    <label class="fw-semibold">Customer ID</label>
                                    <input id="customer_id" class="form-control bg-light" readonly placeholder="Auto fill">
                                </div>

                                {{-- Customer Name --}}
                                <div class="col-md-6 mb-3">
                                    <label class="fw-semibold">Customer Name</label>
                                    <input id="customer_name" class="form-control bg-light" readonly
                                        placeholder="Auto fill">
                                </div>

                                {{-- EMI Amount --}}
                                <div class="col-md-6 mb-3">
                                    <label class="fw-semibold">EMI Paid Amount *</label>
                                    <input type="number" name="booking_amount" class="form-control"
                                        placeholder="Enter EMI amount" value="{{ old('booking_amount') }}">
                                </div>

                                {{-- Payment Mode --}}
                                <div class="col-md-6 mb-3">
                                    <label class="fw-semibold">Payment Mode *</label>
                                    <select name="payment_mode" id="payment_mode" class="form-select">
                                        <option value="cash">Cash</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="dd">DD</option>
                                        <option value="neft_rtgs">NEFT / RTGS</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>

                                {{-- Bank Fields --}}
                                <div class="col-md-6 mb-3 bank-field d-none">
                                    <label class="fw-semibold">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control"
                                        placeholder="Enter bank name">
                                </div>

                                <div class="col-md-6 mb-3 bank-field d-none">
                                    <label class="fw-semibold">Account Number</label>
                                    <input type="text" name="account_number" class="form-control"
                                        placeholder="Enter account number">
                                </div>

                                <div class="col-md-6 mb-3 bank-field d-none">
                                    <label class="fw-semibold">Branch Name</label>
                                    <input type="text" name="branch_name" class="form-control"
                                        placeholder="Enter branch name">
                                </div>

                                {{-- Cheque Fields --}}
                                <div class="col-md-6 mb-3 cheque-field d-none">
                                    <label class="fw-semibold">Cheque Number</label>
                                    <input type="text" name="cheque_number" class="form-control"
                                        placeholder="Enter cheque number">
                                </div>

                                <div class="col-md-6 mb-3 cheque-field d-none">
                                    <label class="fw-semibold">Cheque Date</label>
                                    <input type="date" name="cheque_date" class="form-control">
                                </div>

                                {{-- DD Fields --}}
                                <div class="col-md-6 mb-3 dd-field d-none">
                                    <label class="fw-semibold">DD Number</label>
                                    <input type="text" name="dd_number" class="form-control"
                                        placeholder="Enter DD number">
                                </div>

                                {{-- Transaction Fields --}}
                                <div class="col-md-6 mb-3 transaction-field d-none">
                                    <label class="fw-semibold">Transaction Number</label>
                                    <input type="text" name="transaction_number" class="form-control"
                                        placeholder="Enter transaction number">
                                </div>

                                {{-- Remark --}}
                                <div class="col-md-12 mb-3">
                                    <label class="fw-semibold">Remark</label>
                                    <textarea name="remark" rows="3" class="form-control" placeholder="Enter remark">{{ old('remark') }}</textarea>
                                </div>

                                <div class="col-md-12">
                                    <button class="btn btn-success px-4">Submit EMI Payment</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @include('payment.emi-payment.summary')
            </div>
        </form>
    </div>
@endsection

@include('payment.emi-payment.script')
