@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 transaction-page">

        {{-- Page Header --}}
        <div class="transaction-hero mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-icon"><i class="bi bi-cash-stack"></i></span>
                        <div>
                            <span class="text-success fw-bold text-uppercase small">Transfer Desk</span>
                            <h3 class="fw-bold mb-1 text-dark">Payment Transfer Management</h3>
                            <p class="text-muted mb-0 small">Select plot payments and transfer selected payment entries to another plot booking.</p>
                        </div>
                    </div>

                     <a href="{{ route('plot-transfer.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Back
                    </a>
                </div>
        </div>

        {{-- Main Form Card --}}
        <div class="transaction-card mb-4">
            <div class="transaction-card-body">

                <form id="paymentTransferForm">
                    @csrf

                    {{-- Source Selection --}}
                    <div class="transaction-section-title">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon"><i class="bi bi-pin-map"></i></span>
                            <div>
                                <h5 class="fw-bold mb-1">Source Plot Selection</h5>
                                <small class="text-muted">Choose source plot to load payment entries.</small>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Project
                                </label>
                                <select id="projectId" class="form-select">
                                    <option value="">Select Project</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Block
                                </label>
                                <select id="blockId" class="form-select">
                                    <option value="">Select Block</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Plot
                                </label>
                                <select id="plotId" class="form-select">
                                    <option value="">Select Plot</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Source Details --}}
                    <div id="sourceDetailsCard" class="transaction-summary-box transaction-readonly-grid mb-4 d-none">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold mb-0 text-dark">
                                Current Plot & Customer Details
                            </h6>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                Source Details
                            </span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="small text-muted fw-bold text-uppercase">
                                    Booking ID
                                </label>
                                <input type="text" id="sourceBookingCode" class="form-control bg-white" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="small text-muted fw-bold text-uppercase">
                                    Customer ID
                                </label>
                                <input type="text" id="sourceCustomerCode" class="form-control bg-white" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="small text-muted fw-bold text-uppercase">
                                    Customer Name
                                </label>
                                <input type="text" id="sourceCustomerName" class="form-control bg-white" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="small text-muted fw-bold text-uppercase">
                                    Project
                                </label>
                                <input type="text" id="sourceProject" class="form-control bg-white" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="small text-muted fw-bold text-uppercase">
                                    Block
                                </label>
                                <input type="text" id="sourceBlock" class="form-control bg-white" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="small text-muted fw-bold text-uppercase">
                                    Plot
                                </label>
                                <input type="text" id="sourcePlot" class="form-control bg-white" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- Payment List --}}
                    <div id="paymentListCard" class="transaction-card mb-4 d-none">
                        <div class="transaction-section-title p-3 mb-0">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">
                                        Payment Entries
                                    </h6>
                                    <small class="text-muted">
                                        Select one or multiple payments to transfer.
                                    </small>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    Select Payments
                                </span>
                            </div>
                        </div>

                        <div class="p-0">
                            <div class="table-responsive transaction-mini-table">
                                <table class="table table-hover align-middle mb-0 transaction-table">
                                    <thead>
                                        <tr>
                                            <th width="50" class="text-center">
                                                <input type="checkbox" id="selectAllPayments" class="form-check-input">
                                            </th>
                                            <th>Receipt</th>
                                            <th>Date</th>
                                            <th>Plan Type</th>
                                            <th>Category</th>
                                            <th>Mode</th>
                                            <th>Booking Status</th>
                                            <th>Payment Status</th>
                                            <th class="text-end">Paid Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="paymentListBody">
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                No payment found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Transfer Details --}}
                    <div id="transferCard" class="transaction-card d-none">
                        <div class="transaction-section-title p-3 mb-0">
                            <h6 class="fw-bold mb-1 text-dark">
                                Transfer Payment To
                            </h6>
                            <small class="text-muted">
                                Choose the new customer and plot booking where selected payments will be moved.
                            </small>
                        </div>

                        <div class="transaction-card-body">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Customer <span class="text-danger">*</span>
                                    </label>
                                    <select id="newCustomerBookingId" class="form-select">
                                        <option value="">Select Customer</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Plot Booking <span class="text-danger">*</span>
                                    </label>
                                    <select id="newPlotSaleDetailId" class="form-select">
                                        <option value="">Select Plot Booking</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Transfer Date
                                    </label>
                                    <input type="date" id="transferDate" class="form-control"
                                        value="{{ date('Y-m-d') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Remark
                                    </label>
                                    <input type="text" id="remark" class="form-control"
                                        placeholder="Enter remark">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">
                                        Transfer Reason
                                    </label>
                                    <textarea id="transferReason" rows="3" class="form-control" placeholder="Enter transfer reason"></textarea>
                                </div>

                                <div class="col-md-12">
                                    <button type="button" id="transferPaymentBtn" class="btn btn-success px-4">
                                        <span class="btn-label"><i class="bi bi-arrow-left-right me-1"></i> Transfer Selected Payments</span>
                                        <span class="btn-loader d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Transferring...
                                        </span>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                </form>

            </div>
        </div>

        {{-- Transfer History --}}
        <div class="transaction-card transaction-history-card">
            <div class="transaction-history-head">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-section-title-icon"><i class="bi bi-clock-history"></i></span>
                        <div>
                            <h5 class="fw-bold mb-1 text-dark">Payment Transfer History</h5>
                            <small class="text-muted">Track all transferred payment entries with old and new booking details.</small>
                        </div>
                    </div>
                </div>
                <span class="transaction-count">{{ $histories->count() }} Records</span>
            </div>

            <div class="transaction-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 transaction-table" id="paymentTransferHistoryTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Receipt</th>
                                <th>Old Booking</th>
                                <th>New Booking</th>
                                <th>Old Customer</th>
                                <th>New Customer</th>
                                <th class="text-end">Amount</th>
                                <th>Date</th>
                                <th>Reason</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($histories as $key => $history)
                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        {{ $history->customerPayment?->receipt_number ?? '-' }}
                                    </td>

                                    <td>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            {{ $history->old_booking_code ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            {{ $history->new_booking_code ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $history->old_customer_code ?? '-' }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $history->old_customer_name ?? '-' }}
                                        </small>
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $history->new_customer_code ?? '-' }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $history->new_customer_name ?? '-' }}
                                        </small>
                                    </td>

                                    <td class="fw-bold text-success text-end">
                                        &#8377;{{ number_format((float) $history->transfer_amount, 2) }}
                                    </td>

                                    <td>
                                        {{ $history->transfer_date ? $history->transfer_date->format('d-m-Y') : '-' }}
                                    </td>

                                    <td>
                                        {{ $history->transfer_reason ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No transfer history found.
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

@include('payment_transfer.scripts')
