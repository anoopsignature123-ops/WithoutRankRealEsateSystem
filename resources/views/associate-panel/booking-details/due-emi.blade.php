@extends('layouts.app')

@push('title')
    Associate Panel | Due EMI Details
@endpush
@section('content')
    @php
        $totalRecords = $dueEmi->count();
        $totalDue = (float) $dueEmi->sum('due_amount');
        $totalEmi = (float) $dueEmi->sum('emi_amount');
        $pendingRecords = $dueEmi->where('status', 'Pending')->count();
    @endphp

    <div class="container-fluid mt-4 transaction-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-calendar2-week"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Business Details</span>
                        <h3 class="fw-bold mb-1 text-dark">Due EMI Amount</h3>
                        <p class="text-muted mb-0 small">
                            Track pending EMI dues, plot-wise bookings and installment progress.
                        </p>
                    </div>
                </div>
                <a href="{{ route('associate-panel.booking-detail') }}" class="btn btn-outline-success">
                    <i class="bi bi-arrow-left me-1"></i> Back to Booking Details
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-secondary">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">EMI Bookings</small>
                        <h4 class="fw-bold mb-0">{{ $totalRecords }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-danger">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Total Due</small>
                        <h4 class="fw-bold text-danger mb-0">&#8377;{{ number_format($totalDue, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-success">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Total Monthly EMI</small>
                        <h4 class="fw-bold text-success mb-0">&#8377;{{ number_format($totalEmi, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-warning">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Pending Accounts</small>
                        <h4 class="fw-bold text-warning mb-0">{{ $pendingRecords }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="transaction-card">
            <div class="transaction-card-body">
                <div class="transaction-section-title">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-section-title-icon"><i class="bi bi-receipt-cutoff"></i></span>
                        <div>
                            <h5 class="fw-bold mb-1">EMI Due Records</h5>
                            <small class="text-muted">
                                Each row represents one EMI plot booking.
                            </small>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $totalRecords }}
                        Records</span>
                </div>

                <div class="transaction-table-wrap">
                    <table class="table table-hover align-middle mb-0 transaction-table w-100" id="dueEmiTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Booking / Customer</th>
                                <th>Associate</th>
                                <th>Project / Plots</th>
                                <th>Plot Amount</th>
                                <th>Booking Amount</th>
                                <th>Total Due</th>
                                <th>Monthly EMI</th>
                                <th>Installments</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dueEmi as $key => $emi)
                                @php
                                    $modalId = 'emiHistoryModal' . $key;
                                    $statusClass =
                                        $emi->status === 'Pending'
                                            ? 'bg-danger-subtle text-danger border border-danger-subtle'
                                            : 'bg-success-subtle text-success border border-success-subtle';
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <strong class="text-success">{{ $emi->booking_code }}</strong>
                                        <small class="text-muted d-block">{{ $emi->customer_name }}</small>
                                    </td>
                                    <td>{{ $emi->associate_name }}</td>
                                    <td>
                                        <strong>{{ $emi->project_name }}</strong>
                                        <small class="text-muted d-block">Block {{ $emi->block_name }} / Plot
                                            {{ $emi->plot_no }}</small>
                                    </td>
                                    <td class="fw-bold">&#8377;{{ number_format($emi->plot_amount, 2) }}</td>
                                    <td class="fw-bold text-success">&#8377;{{ number_format($emi->booking_amount, 2) }}
                                    </td>
                                    <td class="fw-bold text-danger">&#8377;{{ number_format($emi->due_amount, 2) }}</td>
                                    <td class="fw-bold">&#8377;{{ number_format($emi->emi_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $emi->paid_installments }}
                                            Paid</span>
                                        <span
                                            class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ $emi->remaining_installments }}
                                            Due</span>
                                    </td>
                                    <td style="min-width: 170px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                <div class="progress-bar bg-success"
                                                    style="width: {{ $emi->progress_percent }}%"></div>
                                            </div>
                                            <small class="fw-bold">{{ $emi->emi_progress }}</small>
                                        </div>
                                    </td>
                                    <td><span class="badge {{ $statusClass }}">{{ $emi->status }}</span></td>
                                    <td>
                                        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#{{ $modalId }}">
                                            <i class="bi bi-eye me-1"></i> View EMI
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No due EMI records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @foreach ($dueEmi as $key => $emi)
            @php
                $modalId = 'emiHistoryModal' . $key;
            @endphp
            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 customer-receipt-modal">
                        <div class="customer-receipt-head">
                            <div class="customer-receipt-title">
                                <div class="customer-receipt-icon"><i class="bi bi-calendar-check"></i></div>
                                <div>
                                    <span>EMI History</span>
                                    <h5>{{ $emi->booking_code }}</h5>
                                    <small>{{ $emi->customer_name }} | Plot {{ $emi->plot_no }}</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-0">
                            <div class="customer-receipt-summary">
                                <div>
                                    <small>Total Due</small>
                                    <strong>&#8377;{{ number_format($emi->due_amount, 2) }}</strong>
                                </div>
                                <div>
                                    <small>Monthly EMI</small>
                                    <strong>&#8377;{{ number_format($emi->emi_amount, 2) }}</strong>
                                </div>
                                <div>
                                    <small>Paid</small>
                                    <strong>{{ $emi->paid_installments }}</strong>
                                </div>
                                <div>
                                    <small>Remaining</small>
                                    <strong>{{ $emi->remaining_installments }}</strong>
                                </div>
                            </div>

                            <div class="customer-receipt-body">
                                <div class="customer-receipt-panel">
                                    <div class="customer-receipt-panel-title">
                                        <i class="bi bi-clock-history"></i>
                                        <span>Installment Timeline</span>
                                    </div>
                                    <div class="table-responsive transaction-mini-table">
                                        <table class="table table-hover align-middle mb-0 transaction-table">
                                            <thead>
                                                <tr>
                                                    <th>EMI No.</th>
                                                    <th>EMI Amount</th>
                                                    <th>Status</th>
                                                    <th>Paid Date</th>
                                                    <th>Receipt No.</th>
                                                    <th>Payment Mode</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($emi->emi_history as $history)
                                                    <tr>
                                                        <td class="fw-bold">EMI {{ $history['month'] }}</td>
                                                        <td>&#8377;{{ number_format($history['emi_amount'], 2) }}</td>
                                                        <td>
                                                            @if ($history['status'] === 'Paid')
                                                                <span
                                                                    class="badge bg-success-subtle text-success border border-success-subtle">Paid</span>
                                                            @else
                                                                <span
                                                                    class="badge bg-danger-subtle text-danger border border-danger-subtle">Pending</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $history['paid_date'] }}</td>
                                                        <td>{{ $history['receipt_number'] }}</td>
                                                        <td>{{ strtoupper($history['payment_mode']) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                                data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            if ($('#dueEmiTable tbody tr td[colspan]').length === 0) {
                $('#dueEmiTable').DataTable({
                    pageLength: 10,
                    responsive: false,
                    scrollX: true,
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search EMI records..."
                    }
                });
            }
        });
    </script>
@endpush
