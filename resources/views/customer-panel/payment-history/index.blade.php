@extends('layouts.app')

@section('content')
    <div class="container-fluid customer-panel-page customer-payment-history-page">
        <div class="customer-profile-hero mb-4">
            <div class="customer-profile-main">
                <div class="customer-avatar profile-avatar">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <span class="customer-dashboard-kicker">Payment History</span>
                    <h3 class="mb-1">My Payment History</h3>
                    <p class="mb-0">Track receipts, grouped payments, plots, mode details and download receipt PDFs.</p>
                </div>
            </div>

            <div class="customer-profile-meta">
                <span class="badge bg-white text-success border rounded-pill px-3 py-2">
                    {{ $paymentRecords->count() }} Receipt{{ $paymentRecords->count() === 1 ? '' : 's' }}
                </span>
                <small>Multiple plot receipts are shown as one record</small>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="customer-stat-card success">
                    <div class="customer-stat-icon"><i class="bi bi-receipt-cutoff"></i></div>
                    <div>
                        <small>Total Receipts</small>
                        <h4>{{ $paymentRecords->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="customer-stat-card success">
                    <div class="customer-stat-icon"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <small>Confirmed Paid</small>
                        <h4>&#8377;{{ number_format($confirmedPaid, 0) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="customer-stat-card warning">
                    <div class="customer-stat-icon"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <small>Hold Amount</small>
                        <h4>&#8377;{{ number_format($holdAmount, 0) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="customer-stat-card danger">
                    <div class="customer-stat-icon"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <small>Plot Due</small>
                        <h4>&#8377;{{ number_format($plotDueTotal, 0) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="customer-section-card">
            <div class="customer-section-header">
                <div>
                    <h5 class="mb-1">Payment Records</h5>
                    <p class="mb-0">Each receipt is listed once, with all related plot/payment details inside the modal.</p>
                </div>
            </div>

            <div class="customer-section-body">
                @if ($paymentRecords->count())
                    <div class="table-responsive">
                        <table id="paymentHistoryTable" class="table table-hover align-middle nowrap w-100 customer-table payment-history-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Receipt</th>
                                    <th>Booking / Plots</th>
                                    <th>Payment Type</th>
                                    <th>Mode</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paymentRecords as $key => $record)
                                    @php
                                        $payment = $record->representative;
                                        $paymentAs = match ($record->transaction_category) {
                                            'booking_fee' => 'Booking Amount',
                                            'emi_payment' => 'EMI Payment',
                                            'one_time' => 'One Time Payment',
                                            default => ucwords(str_replace('_', ' ', $record->transaction_category ?? 'Payment')),
                                        };
                                        $statusText = $record->payment_status === 'mixed'
                                            ? 'Mixed'
                                            : ucfirst($record->payment_status ?? 'N/A');
                                        $statusClass = match ($record->payment_status) {
                                            'cleared', 'paid' => 'bg-success-subtle text-success border border-success-subtle',
                                            'pending', 'hold', 'mixed' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                            'bounced' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                            default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                        };
                                        $modalId = 'paymentDetailModal' . $record->id;
                                    @endphp

                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td style="min-width: 180px;">
                                            <strong class="d-block">{{ $record->receipt_number }}</strong>
                                            <small class="text-muted">
                                                S.No #{{ $record->id }}
                                                @if ($record->manual_receipt_number)
                                                    | Manual: {{ $record->manual_receipt_number }}
                                                @endif
                                            </small>
                                        </td>
                                        <td style="min-width: 240px;">
                                            <strong class="d-block">{{ $record->booking_codes }}</strong>
                                            <small class="text-muted d-block">
                                                {{ $record->project_names }} / Plot {{ $record->plot_numbers }}
                                            </small>
                                            @if ($record->plot_count > 1)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle mt-1">
                                                    {{ $record->plot_count }} Plots
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $paymentAs }}</td>
                                        <td>{{ strtoupper(str_replace('_', ' / ', $record->payment_mode ?? 'N/A')) }}</td>
                                        <td>
                                            <strong class="text-success">&#8377;{{ number_format($record->amount, 2) }}</strong>
                                        </td>
                                        <td>{{ $record->created_at ? $record->created_at->format('d M Y') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge rounded-pill px-3 py-2 {{ $statusClass }}">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3"
                                                    data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                                                    <i class="bi bi-eye me-1"></i> Details
                                                </button>
                                                <a href="{{ route('customer-panel.payment-history.receipt.download', $record->id) }}"
                                                    class="btn btn-success btn-sm rounded-pill px-3">
                                                    <i class="bi bi-download me-1"></i> Receipt
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="customer-empty-state">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <h5 class="mt-3">No Payment History Found</h5>
                        <p class="text-muted mb-0">You do not have any payment records yet.</p>
                    </div>
                @endif
            </div>
        </div>

        @foreach($paymentRecords as $record)
            @php
                $payment = $record->representative;
                $paymentAs = match ($record->transaction_category) {
                    'booking_fee' => 'Booking Amount',
                    'emi_payment' => 'EMI Payment',
                    'one_time' => 'One Time Payment',
                    default => ucwords(str_replace('_', ' ', $record->transaction_category ?? 'Payment')),
                };
                $modalId = 'paymentDetailModal' . $record->id;
                $statusClass = match ($record->payment_status) {
                    'cleared', 'paid' => 'bg-success-subtle text-success border border-success-subtle',
                    'pending', 'hold', 'mixed' => 'bg-warning-subtle text-warning border border-warning-subtle',
                    'bounced' => 'bg-danger-subtle text-danger border border-danger-subtle',
                    default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                };
            @endphp

            <div class="modal fade customer-payment-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 customer-receipt-modal">
                        <div class="customer-receipt-head">
                            <div class="customer-receipt-title">
                                <div class="customer-receipt-icon">
                                    <i class="bi bi-receipt-cutoff"></i>
                                </div>
                                <div>
                                    <span>Payment Receipt</span>
                                    <h5>{{ $record->receipt_number }}</h5>
                                    <small>S.No #{{ $record->id }} | {{ $paymentAs }}</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-0">
                            <div class="customer-receipt-summary">
                                <div>
                                    <small>Paid Amount</small>
                                    <strong>&#8377;{{ number_format($record->amount, 2) }}</strong>
                                </div>
                                <div>
                                    <small>Payment Mode</small>
                                    <strong>{{ strtoupper(str_replace('_', ' / ', $record->payment_mode ?? 'N/A')) }}</strong>
                                </div>
                                <div>
                                    <small>Payment Date</small>
                                    <strong>{{ $record->created_at ? $record->created_at->format('d M Y') : 'N/A' }}</strong>
                                </div>
                                <div class="customer-receipt-status">
                                    <span class="badge rounded-pill px-3 py-2 {{ $statusClass }}">
                                        {{ $record->payment_status === 'mixed' ? 'Mixed' : ucfirst($record->payment_status ?? 'N/A') }}
                                    </span>
                                    <span class="badge rounded-pill px-3 py-2 bg-light text-dark border">
                                        {{ $record->booking_status === 'mixed' ? 'Mixed' : ucfirst($record->booking_status ?? 'N/A') }}
                                    </span>
                                </div>
                            </div>

                            <div class="customer-receipt-body">
                                <div class="row g-4">
                                    <div class="col-lg-5">
                                        <div class="customer-receipt-panel">
                                            <div class="customer-receipt-panel-title">
                                                <i class="bi bi-cash-coin"></i>
                                                <span>Amount Breakup</span>
                                            </div>
                                            <div class="customer-receipt-line">
                                                <span>Total Receipt Amount</span>
                                                <strong>&#8377;{{ number_format($record->amount, 2) }}</strong>
                                            </div>
                                            <div class="customer-receipt-line">
                                                <span>Net Plot Payable</span>
                                                <strong>&#8377;{{ number_format($record->net_payable_amount, 2) }}</strong>
                                            </div>
                                            <div class="customer-receipt-line highlight">
                                                <span>Paid Amount</span>
                                                <strong>&#8377;{{ number_format($record->amount, 2) }}</strong>
                                            </div>
                                            <div class="customer-receipt-line">
                                                <span>Due Amount</span>
                                                <strong class="text-danger">&#8377;{{ number_format($record->due_amount, 2) }}</strong>
                                            </div>
                                        </div>

                                        <div class="customer-receipt-panel mt-4">
                                            <div class="customer-receipt-panel-title">
                                                <i class="bi bi-credit-card-2-front"></i>
                                                <span>Mode Detail</span>
                                            </div>
                                            <div class="customer-receipt-line">
                                                <span>Payment As</span>
                                                <strong>{{ $paymentAs }}</strong>
                                            </div>
                                            <div class="customer-receipt-line">
                                                <span>Plan Type</span>
                                                <strong>
                                                    {{ $record->plan_type === 'emi_plan' ? 'EMI Plan' : ($record->plan_type === 'mixed' ? 'Mixed Plan' : 'Full Payment') }}
                                                </strong>
                                            </div>
                                            @if ($record->plan_type === 'emi_plan')
                                                <div class="customer-receipt-line">
                                                    <span>EMI Months</span>
                                                    <strong>{{ $payment->emi_months ?? 'N/A' }}</strong>
                                                </div>
                                                <div class="customer-receipt-line">
                                                    <span>Monthly EMI</span>
                                                    <strong>&#8377;{{ number_format((float) ($payment->after_booking_payable_amount ?? 0), 2) }}</strong>
                                                </div>
                                            @endif
                                            @if ($record->payment_mode === 'cheque')
                                                <div class="customer-receipt-line">
                                                    <span>Cheque No</span>
                                                    <strong>{{ $payment->cheque_number ?? 'N/A' }}</strong>
                                                </div>
                                                <div class="customer-receipt-line">
                                                    <span>Cheque Status</span>
                                                    <strong>{{ ucfirst($payment->cheque_status ?? 'N/A') }}</strong>
                                                </div>
                                            @elseif ($record->payment_mode === 'dd')
                                                <div class="customer-receipt-line">
                                                    <span>DD Number</span>
                                                    <strong>{{ $payment->dd_number ?? 'N/A' }}</strong>
                                                </div>
                                            @elseif (in_array($record->payment_mode, ['neft_rtgs', 'card']))
                                                <div class="customer-receipt-line">
                                                    <span>Transaction No</span>
                                                    <strong>{{ $payment->transaction_number ?? 'N/A' }}</strong>
                                                </div>
                                            @endif
                                            @if (in_array($record->payment_mode, ['cheque', 'dd', 'neft_rtgs', 'card']))
                                                <div class="customer-receipt-line">
                                                    <span>Bank</span>
                                                    <strong>{{ $payment->bank_name ?? 'N/A' }}</strong>
                                                </div>
                                                <div class="customer-receipt-line">
                                                    <span>Branch</span>
                                                    <strong>{{ $payment->branch_name ?? 'N/A' }}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-lg-7">
                                        <div class="customer-receipt-panel">
                                            <div class="customer-receipt-panel-title">
                                                <i class="bi bi-pin-map"></i>
                                                <span>Booking & Plot Detail</span>
                                            </div>

                                            <div class="table-responsive transaction-mini-table">
                                                <table class="table table-hover align-middle mb-0 transaction-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Plot</th>
                                                            <th>Project / Block</th>
                                                            <th>Area</th>
                                                            <th>Total Cost</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($record->plots as $plotSale)
                                                            <tr>
                                                                <td class="fw-bold text-success">
                                                                    {{ $plotSale?->plotDetail?->plot_number ?? 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    <strong>{{ $plotSale?->project?->name ?? 'N/A' }}</strong>
                                                                    <small class="text-muted d-block">
                                                                        Block {{ $plotSale?->block?->block ?? 'N/A' }}
                                                                    </small>
                                                                </td>
                                                                <td>{{ number_format((float) ($plotSale?->plot_area ?? 0), 2) }} Sq.Ft.</td>
                                                                <td class="fw-bold">&#8377;{{ number_format((float) ($plotSale?->total_plot_cost ?? 0), 2) }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted py-3">
                                                                    Plot details not available.
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="customer-receipt-panel mt-4">
                                            <div class="customer-receipt-panel-title">
                                                <i class="bi bi-list-check"></i>
                                                <span>Receipt Payment Rows</span>
                                            </div>

                                            <div class="table-responsive transaction-mini-table">
                                                <table class="table table-hover align-middle mb-0 transaction-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Plot</th>
                                                            <th>Paid</th>
                                                            <th>Due</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($record->payments as $rowPayment)
                                                            <tr>
                                                                <td>{{ $rowPayment->plotSaleDetail?->plotDetail?->plot_number ?? 'N/A' }}</td>
                                                                <td class="fw-bold text-success">
                                                                    &#8377;{{ number_format((float) ($rowPayment->paid_amount ?? $rowPayment->booking_amount ?? 0), 2) }}
                                                                </td>
                                                                <td class="fw-bold text-danger">
                                                                    &#8377;{{ number_format((float) ($rowPayment->due_amount ?? 0), 2) }}
                                                                </td>
                                                                <td>{{ ucfirst($rowPayment->payment_status ?? 'N/A') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="customer-receipt-note mt-4">
                                                <span>Remark</span>
                                                <strong>{{ $payment->remark ?? 'No remark available' }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                            <a href="{{ route('customer-panel.payment-history.receipt.download', $record->id) }}"
                                class="btn btn-success rounded-pill px-4">
                                <i class="bi bi-download me-1"></i>
                                Download Receipt
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    @if ($paymentRecords->count())
        <script>
            $(document).ready(function() {
                $('#paymentHistoryTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    responsive: false,
                    scrollX: true,
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search payment..."
                    }
                });
            });
        </script>
    @endif
@endpush
