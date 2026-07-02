@extends('layouts.app')

@push('title')
    Plot Payment
@endpush
@section('content')
    <div class="container-fluid mt-4 edit-payment-page">
        <div class="edit-payment-hero mb-4">
            <div class="d-flex align-items-center gap-3">
                <span class="edit-payment-hero-icon">
                    <i class="bi bi-pencil-square"></i>
                </span>
                <div>
                    <span class="text-success fw-bold text-uppercase small">Payment Correction</span>
                    <h3 class="fw-bold mb-1 text-dark">Edit Payment Details</h3>
                    <p class="text-muted mb-0 small">Select a payment receipt and update payment details carefully.</p>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please check:</strong> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="edit-payment-filter-card mb-4">
            <div class="edit-payment-filter-title">
                <span><i class="bi bi-funnel"></i></span>
                <div>
                    <h6 class="fw-bold mb-0">Find Payment</h6>
                    <small class="text-muted">Only pending or hold receipts can be edited here.</small>
                </div>
            </div>

            @if ($payments->isEmpty())
                <div class="alert alert-info border-0 shadow-sm mb-3">
                    <strong>No editable payment found.</strong>
                    Paid and cleared receipts are locked. New data will appear here when a payment is in
                    pending or hold status, for example cheque or DD payments before clearance.
                </div>
            @endif

            <form method="GET" action="{{ route('edit-payment-details.index') }}" id="paymentFilterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-10">
                        <label class="form-label fw-semibold">Select Payment</label>
                        <select name="selected_payment" id="paymentSelect" class="form-select">
                            <option value="">Select Payment Receipt</option>
                            @foreach ($payments as $payment)
                                @php
                                    $booking = $payment->customerBooking;
                                    $plotSale = $payment->plotSaleDetail;
                                    $plotCount = (int) ($payment->group_plot_count ?? 1);
                                    $plotNumbers = $payment->group_plot_numbers ?: ($plotSale?->plotDetail?->plot_number ?? 'N/A');
                                @endphp
                                <option value="{{ $payment->id }}"
                                    {{ request('selected_payment') == $payment->id ? 'selected' : '' }}>
                                    {{ $payment->receipt_number ?? 'N/A' }}
                                    | {{ $booking?->customer_code ?? 'N/A' }}
                                    | {{ $booking?->primaryDetail?->name ?? $booking?->customer_name ?? 'N/A' }}
                                    | Plot {{ $plotNumbers }}{{ $plotCount > 1 ? ' (Multiple - '.$plotCount.' Plots)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <a href="{{ route('edit-payment-details.index') }}" class="btn btn-outline-success w-100 edit-payment-reset-btn">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        @if ($selectedPayment)
            <div class="modal fade edit-payment-modal" id="editPaymentModal" tabindex="-1"
                aria-labelledby="editPaymentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="d-flex align-items-center gap-3">
                                <span class="edit-payment-modal-icon">
                                    <i class="bi bi-pencil-square"></i>
                                </span>
                                <div>
                                    <span class="text-success fw-bold text-uppercase small">Payment Correction</span>
                                    <h5 class="modal-title fw-bold mb-0" id="editPaymentModalLabel">
                                        Edit Payment Details
                                    </h5>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            @include('plot-payment.form')
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="edit-payment-table-card">
            <div class="edit-payment-table-head">
                <div class="d-flex align-items-center gap-3">
                    <span class="edit-payment-table-icon">
                        <i class="bi bi-receipt-cutoff"></i>
                    </span>
                    <div>
                    <h5 class="fw-bold mb-1">Payment Records</h5>
                    <small class="text-muted">All payment receipts are listed below.</small>
                    </div>
                </div>
                <span class="edit-payment-count">{{ $payments->count() }} Records</span>
            </div>

            <div class="edit-payment-table-wrap">
                <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 edit-payment-table" id="paymentEditTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Receipt</th>
                            <th>Customer</th>
                            <th>Booking / Plot</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Mode</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th width="110">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($payments as $key => $payment)
                            @php
                                $booking = $payment->customerBooking;
                                $plotSale = $payment->plotSaleDetail;
                                $plotCount = (int) ($payment->group_plot_count ?? 1);
                                $plotNumbers = $payment->group_plot_numbers ?: ($plotSale?->plotDetail?->plot_number ?? '-');
                                $projects = $payment->group_projects ?: ($plotSale?->project?->name ?? '-');
                                $blocks = $payment->group_blocks ?: ($plotSale?->block?->block ?? '-');
                                $paymentType = match ($payment->transaction_category) {
                                    'booking_fee' => 'Booking Amount',
                                    'emi_payment' => 'EMI Payment',
                                    'one_time' => 'One Time Payment',
                                    default => 'Payment',
                                };
                                $amount = (float) ($payment->group_paid_amount ?? $payment->paid_amount ?? $payment->booking_amount ?? 0);
                                $status = $payment->group_payment_status ?? $payment->payment_status ?? 'pending';
                                $bookingStatus = $payment->group_booking_status ?? $payment->booking_status ?? 'N/A';
                                $statusClass = match ($status) {
                                    'hold' => 'warning',
                                    'pending', 'mixed' => 'secondary',
                                    default => 'warning',
                                };
                            @endphp

                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <strong>{{ $payment->receipt_number ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $payment->manual_receipt_number ?? 'Manual: -' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $booking?->customer_code ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $booking?->primaryDetail?->name ?? $booking?->customer_name ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $plotSale?->booking_code ?? $booking?->booking_code ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $projects }} /
                                        Block {{ $blocks }} /
                                        Plot {{ $plotNumbers }}
                                    </small>
                                    @if ($plotCount > 1)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">
                                            {{ $plotCount }} Plots
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $paymentType }}</td>
                                <td class="fw-bold text-success">&#8377;{{ number_format($amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border">
                                        {{ strtoupper(str_replace('_', ' / ', $payment->payment_mode ?? '-')) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusClass }} {{ in_array($statusClass, ['warning', 'secondary']) ? 'text-dark' : '' }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ ucfirst($bookingStatus) }}</small>
                                </td>
                                <td>{{ $payment->created_at?->format('d-M-Y') ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('edit-payment-details.index', ['selected_payment' => $payment->id]) }}"
                                        class="btn btn-sm btn-outline-success edit-payment-action">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    No pending or hold payment records found.
                                    <span class="d-block small mt-1">
                                        Paid and cleared receipts are not editable from this section.
                                    </span>
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

@push('scripts')
    @include('plot-payment.script')

    <script>
        $(document).ready(function() {
            $('#paymentSelect').on('change', function() {
                if ($(this).val()) {
                    $('#paymentFilterForm').submit();
                }
            });

            if ($('#paymentEditTable tbody tr td').attr('colspan') === undefined) {
                $('#paymentEditTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                });
            }

            @if ($selectedPayment || $errors->any())
                const modalElement = document.getElementById('editPaymentModal');
                if (modalElement) {
                    $('#editPaymentModal select').each(function() {
                        if ($(this).data('select2')) {
                            $(this).select2('destroy');
                        }

                        $(this).select2({
                            width: '100%',
                            dropdownParent: $('#editPaymentModal')
                        });
                    });

                    const editPaymentModal = new bootstrap.Modal(modalElement);
                    editPaymentModal.show();
                }
            @endif
        });
    </script>
@endpush
