@extends('layouts.app')

@section('content')
    @php
        $totalRecords = $commissions->count();
        $selfCommission = (float) $commissions->where('commission_type', 'self')->sum('commission_amount');
        $teamCommission = (float) $commissions->where('commission_type', 'team')->sum('commission_amount');
        $totalCommission = (float) $commissions->sum('commission_amount');
        $pendingCommission = (float) $commissions->where('status', 'pending')->sum('commission_amount');
    @endphp

    <div class="container-fluid mt-4 transaction-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-cash-stack"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Associate Business</span>
                        <h3 class="fw-bold mb-1 text-dark">My Payout Details</h3>
                        <p class="text-muted mb-0 small">View self and team commission payout records with booking and plot details.</p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('associate-panel.payout-details.export.excel', request()->query()) }}" class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-excel me-1"></i> Excel
                    </a>
                    <a href="{{ route('associate-panel.payout-details.export.pdf', request()->query()) }}" class="btn btn-outline-danger">
                        <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-secondary">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Total Records</small>
                        <h4 class="fw-bold mb-0">{{ $totalRecords }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-success">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Self Commission</small>
                        <h4 class="fw-bold text-success mb-0">&#8377;{{ number_format($selfCommission, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-info">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Team Commission</small>
                        <h4 class="fw-bold text-info mb-0">&#8377;{{ number_format($teamCommission, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-warning">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Pending Payout</small>
                        <h4 class="fw-bold text-warning mb-0">&#8377;{{ number_format($pendingCommission, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="transaction-card mb-4">
            <div class="transaction-card-body">
                <div class="transaction-section-title">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-section-title-icon"><i class="bi bi-funnel"></i></span>
                        <div>
                            <h5 class="fw-bold mb-1">Search Criteria</h5>
                            <small class="text-muted">Filter payout by commission type, status and generated date range.</small>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('associate-panel.payout-details') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-semibold">Commission Type</label>
                            <select name="commission_type" class="form-select">
                                <option value="">All Types</option>
                                <option value="self" {{ request('commission_type') == 'self' ? 'selected' : '' }}>Self</option>
                                <option value="team" {{ request('commission_type') == 'team' ? 'selected' : '' }}>Team</option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>

                        <div class="col-xl-2 col-md-6">
                            <label class="form-label fw-semibold">From Date</label>
                            <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                        </div>

                        <div class="col-xl-2 col-md-6">
                            <label class="form-label fw-semibold">To Date</label>
                            <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                        </div>

                        <div class="col-xl-2 col-md-12 d-flex gap-2">
                            <button class="btn btn-success flex-fill">
                                <i class="bi bi-search me-1"></i> Search
                            </button>
                            <a href="{{ route('associate-panel.payout-details') }}" class="btn btn-outline-secondary flex-fill">
                                <i class="bi bi-arrow-clockwise me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="transaction-card">
            <div class="transaction-card-body">
                <div class="transaction-section-title">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-section-title-icon"><i class="bi bi-list-check"></i></span>
                        <div>
                            <h5 class="fw-bold mb-1">Payout Records</h5>
                            <small class="text-muted">Self and team commission with booking, payment and plot information.</small>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $totalRecords }} Records</span>
                </div>

                <div class="transaction-table-wrap">
                    <table class="table table-hover align-middle mb-0 transaction-table w-100" id="associatePayoutTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Generated / Period</th>
                                <th>Source</th>
                                <th>Customer / Booking</th>
                                <th>Plot</th>
                                <th>Type</th>
                                <th>Business</th>
                                <th>Rate</th>
                                <th>Commission</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($commissions as $key => $row)
                                @php
                                    $modalId = 'payoutDetailModal' . $row->id;
                                    $typeClass = $row->commission_type === 'self'
                                        ? 'bg-success-subtle text-success border border-success-subtle'
                                        : 'bg-primary-subtle text-primary border border-primary-subtle';
                                    $statusClass = $row->status === 'paid'
                                        ? 'bg-success-subtle text-success border border-success-subtle'
                                        : 'bg-warning-subtle text-warning border border-warning-subtle';
                                    $periodFrom = $row->generation?->from_date ? \Carbon\Carbon::parse($row->generation->from_date)->format('d M Y') : '-';
                                    $periodTo = $row->generation?->to_date ? \Carbon\Carbon::parse($row->generation->to_date)->format('d M Y') : '-';
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <strong>{{ $row->generated_date ? \Carbon\Carbon::parse($row->generated_date)->format('d M Y') : '-' }}</strong>
                                        <small class="text-muted d-block">{{ $periodFrom }} to {{ $periodTo }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $row->sourceAssociate?->associate_name ?? '-' }}</strong>
                                        <small class="text-muted d-block">{{ $row->sourceAssociate?->associate_id ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $row->customerBooking?->primaryDetail?->name ?? '-' }}</strong>
                                        <small class="text-muted d-block">{{ $row->customerBooking?->booking_code ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $row->plotSaleDetail?->plotDetail?->plot_number ?? '-' }}</strong>
                                        <small class="text-muted d-block">
                                            {{ $row->plotSaleDetail?->project?->name ?? '-' }} / Block {{ $row->plotSaleDetail?->block?->block ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $typeClass }}">{{ ucfirst($row->commission_type ?? '-') }}</span>
                                    </td>
                                    <td class="fw-bold">&#8377;{{ number_format((float) $row->payment_amount, 2) }}</td>
                                    <td>{{ number_format((float) $row->commission_percent, 2) }}%</td>
                                    <td class="fw-bold text-success">&#8377;{{ number_format((float) $row->commission_amount, 2) }}</td>
                                    <td><span class="badge {{ $statusClass }}">{{ ucfirst($row->status ?? '-') }}</span></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#{{ $modalId }}">
                                                <i class="bi bi-eye me-1"></i> Details
                                            </button>
                                            <div class="btn-group">
                                                <a href="{{ route('associate-panel.payout-details.single.excel', $row->id) }}"
                                                    class="btn btn-sm btn-light border text-success" title="Download Excel">
                                                    <i class="bi bi-file-earmark-excel"></i>
                                                </a>
                                                <a href="{{ route('associate-panel.payout-details.single.pdf', $row->id) }}"
                                                    class="btn btn-sm btn-light border text-danger" title="Download PDF">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No payout records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($commissions->count() > 0)
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-end">Total</th>
                                    <th>&#8377;{{ number_format((float) $commissions->sum('payment_amount'), 2) }}</th>
                                    <th></th>
                                    <th class="text-success">&#8377;{{ number_format($totalCommission, 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        @foreach ($commissions as $row)
            @php
                $periodFrom = $row->generation?->from_date ? \Carbon\Carbon::parse($row->generation->from_date)->format('d M Y') : '-';
                $periodTo = $row->generation?->to_date ? \Carbon\Carbon::parse($row->generation->to_date)->format('d M Y') : '-';
            @endphp
            <div class="modal fade" id="payoutDetailModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 customer-receipt-modal">
                        <div class="customer-receipt-head">
                            <div class="customer-receipt-title">
                                <div class="customer-receipt-icon"><i class="bi bi-cash-stack"></i></div>
                                <div>
                                    <span>Payout Detail</span>
                                    <h5>{{ ucfirst($row->commission_type ?? '-') }} Commission</h5>
                                    <small>{{ $row->customerBooking?->booking_code ?? '-' }} | {{ $periodFrom }} to {{ $periodTo }}</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-0">
                            <div class="customer-receipt-summary">
                                <div>
                                    <small>Business Amount</small>
                                    <strong>&#8377;{{ number_format((float) $row->payment_amount, 2) }}</strong>
                                </div>
                                <div>
                                    <small>Commission Rate</small>
                                    <strong>{{ number_format((float) $row->commission_percent, 2) }}%</strong>
                                </div>
                                <div>
                                    <small>Commission</small>
                                    <strong>&#8377;{{ number_format((float) $row->commission_amount, 2) }}</strong>
                                </div>
                                <div>
                                    <small>Status</small>
                                    <strong>{{ ucfirst($row->status ?? '-') }}</strong>
                                </div>
                            </div>

                            <div class="customer-receipt-body">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="customer-receipt-panel h-100">
                                            <div class="customer-receipt-panel-title">
                                                <i class="bi bi-person"></i>
                                                <span>Associate Source</span>
                                            </div>
                                            <div class="customer-receipt-line">
                                                <span>Payout To</span>
                                                <strong>{{ $row->associate?->associate_name ?? $associate?->associate_name ?? '-' }}</strong>
                                            </div>
                                            <div class="customer-receipt-line">
                                                <span>Source Associate</span>
                                                <strong>{{ $row->sourceAssociate?->associate_name ?? '-' }}</strong>
                                            </div>
                                            <div class="customer-receipt-line">
                                                <span>Source ID</span>
                                                <strong>{{ $row->sourceAssociate?->associate_id ?? '-' }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="customer-receipt-panel h-100">
                                            <div class="customer-receipt-panel-title">
                                                <i class="bi bi-house-check"></i>
                                                <span>Booking Detail</span>
                                            </div>
                                            <div class="customer-receipt-line">
                                                <span>Customer</span>
                                                <strong>{{ $row->customerBooking?->primaryDetail?->name ?? '-' }}</strong>
                                            </div>
                                            <div class="customer-receipt-line">
                                                <span>Booking ID</span>
                                                <strong>{{ $row->customerBooking?->booking_code ?? '-' }}</strong>
                                            </div>
                                            <div class="customer-receipt-line">
                                                <span>Receipt</span>
                                                <strong>{{ $row->payment?->receipt_number ?? '-' }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="customer-receipt-panel">
                                            <div class="customer-receipt-panel-title">
                                                <i class="bi bi-map"></i>
                                                <span>Plot Detail</span>
                                            </div>
                                            <div class="table-responsive transaction-mini-table">
                                                <table class="table table-hover align-middle mb-0 transaction-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Project</th>
                                                            <th>Block</th>
                                                            <th>Plot</th>
                                                            <th>Area</th>
                                                            <th>Payment Mode</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>{{ $row->plotSaleDetail?->project?->name ?? '-' }}</td>
                                                            <td>{{ $row->plotSaleDetail?->block?->block ?? '-' }}</td>
                                                            <td class="fw-bold text-success">{{ $row->plotSaleDetail?->plotDetail?->plot_number ?? '-' }}</td>
                                                            <td>{{ $row->plotSaleDetail?->plotDetail?->plot_area ?? $row->plotSaleDetail?->plot_area ?? '-' }} Sq.Ft.</td>
                                                            <td>{{ strtoupper(str_replace('_', ' / ', $row->payment?->payment_mode ?? '-')) }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-0">
                            <a href="{{ route('associate-panel.payout-details.single.excel', $row->id) }}" class="btn btn-outline-success rounded-pill px-4">
                                <i class="bi bi-file-earmark-excel me-1"></i> Excel
                            </a>
                            <a href="{{ route('associate-panel.payout-details.single.pdf', $row->id) }}" class="btn btn-outline-danger rounded-pill px-4">
                                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                            </a>
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
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
            if ($('#associatePayoutTable tbody tr td[colspan]').length === 0) {
                $('#associatePayoutTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    responsive: false,
                    scrollX: true,
                    autoWidth: false,
                    lengthMenu: [10, 25, 50, 100],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search payout records..."
                    }
                });
            }
        });
    </script>
@endpush
