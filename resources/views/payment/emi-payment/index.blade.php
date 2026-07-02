@extends('layouts.app')

@push('title')
    EMI Payment
@endpush
@section('content')
    <div class="container-fluid mt-4 emi-payment-page">
        <div class="emi-payment-hero mb-4">
            <div class="d-flex align-items-center gap-3">
                <span class="emi-payment-hero-icon">
                    <i class="bi bi-calendar2-week"></i>
                </span>
                <div>
                    <span class="text-success fw-bold text-uppercase small">EMI Collection</span>
                    <h3 class="fw-bold mb-1 text-dark">EMI Payment</h3>
                    <p class="text-muted mb-0 small">Select project, block and plot to collect pending EMI.</p>
                </div>
            </div>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please check:</strong> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('emi-payment.store') }}" id="emiPaymentForm">
            @csrf
            <div class="row">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm emi-payment-card">
                        <div class="card-body p-4">
                            <input type="hidden" name="customer_booking_id" id="customer_booking_id">
                            <input type="hidden" name="plot_sale_detail_id" id="plot_sale_detail_id">
                            <div id="plot_sale_detail_ids_container"></div>
                            <input type="hidden" id="monthly_emi_value">
                            <input type="hidden" id="max_due_amount" value="0">

                            <div class="row">
                                 <input type="hidden" class="form-check-input" name="payment_plot_type"
                                                id="emi_plot_type_multiple" value="multiple" autocomplete="off">
                                {{-- <div class="col-12 mb-3">
                                    <label class="form-label fw-semibold d-block">Payment For <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-4 border rounded-3 bg-white px-3 py-2">
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="payment_plot_type"
                                                id="emi_plot_type_single" value="single" autocomplete="off" checked>
                                            <label class="form-check-label fw-semibold" for="emi_plot_type_single">
                                                Single Plot
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="payment_plot_type"
                                                id="emi_plot_type_multiple" value="multiple" autocomplete="off">
                                            <label class="form-check-label fw-semibold" for="emi_plot_type_multiple">
                                                Multiple Plot
                                            </label>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2" id="payment_plot_type_help">
                                        Select single EMI or grouped multiple plot EMI first.
                                    </small>
                                </div> --}}

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Project <span class="text-danger">*</span></label>
                                    <select id="project_id" class="form-select">
                                        <option value="">Select Project</option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Block <span class="text-danger">*</span></label>
                                    <select id="block_id" class="form-select">
                                        <option value="">Select Block</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Booking / Plot Group <span class="text-danger">*</span></label>
                                    <select id="plot_id" class="form-select">
                                        <option value="">Select booking group</option>
                                    </select>
                                    <small class="text-muted d-block mt-1" id="plot_group_hint">
                                        Select project and block to load EMI groups.
                                    </small>
                                </div>

                                <div class="col-12">
                                    <div class="border rounded-3 p-3 mb-3 bg-white d-none" id="form_selected_plots_box">
                                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                            <div>
                                                <h6 class="fw-bold mb-1">EMI Plot Details</h6>
                                                <small class="text-muted" id="form_selected_plot_mode">
                                                    Verify EMI plot details before entering amount.
                                                </small>
                                            </div>
                                            <span class="badge bg-success-subtle text-success border"
                                                id="form_selected_plot_count">0 Plots</span>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-hover align-middle mb-0 emi-detail-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Plot</th>
                                                        <th>Area</th>
                                                        <th>Monthly EMI</th>
                                                        <th>EMI Progress</th>
                                                        <th class="text-end">Due</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="form_selected_plots">
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-3">
                                                            Select booking group to view EMI details.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Booking ID</label>
                                    <input id="booking_id" class="form-control bg-light" readonly
                                        placeholder="Auto filled after plot selection">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Customer ID</label>
                                    <input id="customer_id" class="form-control bg-light" readonly
                                        placeholder="Auto filled after plot selection">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Customer Name</label>
                                    <input id="customer_name" class="form-control bg-light" readonly
                                        placeholder="Auto filled after plot selection">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">EMI Payment Amount <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="decimal" name="booking_amount" id="booking_amount_input"
                                        value="{{ old('booking_amount') }}"
                                        class="form-control @error('booking_amount') is-invalid @enderror"
                                        placeholder="Enter EMI amount">
                                    @error('booking_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        Minimum EMI:
                                        <span id="minimum_emi" class="text-success fw-bold">&#8377;0.00</span>
                                    </small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                                    <select name="payment_mode" id="payment_mode" class="form-select">
                                        <option value="cash">Cash</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="dd">DD</option>
                                        <option value="neft_rtgs">NEFT / RTGS</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3 bank-field d-none">
                                    <label class="form-label fw-semibold">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" placeholder="Enter bank name">
                                </div>

                                <div class="col-md-6 mb-3 bank-field d-none">
                                    <label class="form-label fw-semibold">Account Number</label>
                                    <input type="text" name="account_number" class="form-control" placeholder="Enter account number">
                                </div>

                                <div class="col-md-6 mb-3 bank-field d-none">
                                    <label class="form-label fw-semibold">Branch Name</label>
                                    <input type="text" name="branch_name" class="form-control" placeholder="Enter branch name">
                                </div>

                                <div class="col-md-6 mb-3 cheque-field d-none">
                                    <label class="form-label fw-semibold">Cheque Number</label>
                                    <input type="text" name="cheque_number" class="form-control" placeholder="Enter cheque number">
                                </div>

                                <div class="col-md-6 mb-3 cheque-field d-none">
                                    <label class="form-label fw-semibold">Cheque Date</label>
                                    <input type="date" name="cheque_date" class="form-control">
                                </div>

                                <div class="col-md-6 mb-3 dd-field d-none">
                                    <label class="form-label fw-semibold">DD Number</label>
                                    <input type="text" name="dd_number" class="form-control" placeholder="Enter DD number">
                                </div>

                                <div class="col-md-6 mb-3 transaction-field d-none">
                                    <label class="form-label fw-semibold">Transaction Number</label>
                                    <input type="text" name="transaction_number" class="form-control"
                                        placeholder="Enter transaction number">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Remark</label>
                                    <textarea name="remark" rows="3" class="form-control" placeholder="Enter remark">{{ old('remark') }}</textarea>
                                </div>

                                <div class="col-md-12">
                                    <div class="emi-payment-action-bar">
                                        <div class="emi-payment-status-note">
                                            <i class="bi bi-info-circle"></i>
                                            <span>Cash/Card/NEFT will be marked Paid. Cheque/DD will stay Hold until clearance.</span>
                                        </div>

                                        <button type="submit" class="btn btn-success px-4" id="submitEmiPaymentBtn">
                                            <span class="btn-label">
                                                <i class="bi bi-check2-circle me-1"></i> Submit EMI Payment
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
                </div>
                @include('payment.emi-payment.summary')
            </div>
        </form>
    </div>
@endsection

@include('payment.emi-payment.script')
