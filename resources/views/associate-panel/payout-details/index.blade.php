@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success rounded-4 d-flex align-items-center justify-content-center me-3"
                            style="width:58px;height:58px;">
                            <i class="bi bi-cash-stack fs-2"></i>
                        </div>

                        <div>
                            <h4 class="fw-bold mb-1">My Payout Details</h4>
                            <p class="text-muted mb-0">
                                View your self and team commission payout records.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 text-lg-end">
                    <div class="d-flex justify-content-lg-end flex-wrap gap-2">
                        <a href="{{ route('associate-panel.payout-details.export.excel', request()->query()) }}"
                            class="btn btn-outline-success px-3">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </a>

                        <a href="{{ route('associate-panel.payout-details.export.pdf', request()->query()) }}"
                            class="btn btn-outline-danger px-3">
                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <small class="text-muted">Total Records</small>
                    <h5 class="fw-bold mb-0">{{ $commissions->count() }}</h5>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <small class="text-muted">Self Commission</small>
                    <h5 class="fw-bold text-success mb-0">
                        ₹{{ number_format($commissions->where('commission_type', 'self')->sum('commission_amount'), 2) }}
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <small class="text-muted">Team Commission</small>
                    <h5 class="fw-bold text-success mb-0">
                        ₹{{ number_format($commissions->where('commission_type', 'team')->sum('commission_amount'), 2) }}
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <small class="text-muted">Total Commission</small>
                    <h5 class="fw-bold mb-0">
                        ₹{{ number_format($commissions->sum('commission_amount'), 2) }}
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('associate-panel.payout-details') }}">
                <div class="row g-3">

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

                    <div class="col-xl-2 col-md-12">
                        <label class="form-label d-none d-xl-block">&nbsp;</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <button class="btn btn-success w-100">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>

                            <div class="col-6">
                                <a href="{{ route('associate-panel.payout-details') }}" class="btn btn-light border w-100">
                                   <i class="fa-solid fa-arrow-rotate-left"></i>
                                    Reset
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 p-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <h5 class="fw-bold mb-1">Payout Records</h5>
                    <p class="text-muted small mb-0">
                        Self and team commission with booking and plot details.
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                        {{ $commissions->count() }} Records
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0" id="associatePayoutTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Generated</th>
                            <th>Source</th>
                            <th>Customer</th>
                            <th>Booking</th>
                            <th>Plot</th>
                            <th>Type</th>
                            <th class="text-end">Business</th>
                            <th class="text-end">%</th>
                            <th class="text-end">Commission</th>
                            <th>Status</th>
                            <th class="text-center">Download</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($commissions as $key => $row)
                            <tr>
                                <td>{{ $key + 1 }}</td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $row->generated_date ? \Carbon\Carbon::parse($row->generated_date)->format('d M Y') : '-' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $row->generation?->from_date ? \Carbon\Carbon::parse($row->generation->from_date)->format('d M') : '-' }}
                                        -
                                        {{ $row->generation?->to_date ? \Carbon\Carbon::parse($row->generation->to_date)->format('d M Y') : '-' }}
                                    </small>
                                </td>

                                <td>
                                    <div class="fw-semibold">{{ $row->sourceAssociate?->associate_name ?? '-' }}</div>
                                    <small class="text-muted">{{ $row->sourceAssociate?->associate_id ?? '-' }}</small>
                                </td>

                                <td>{{ $row->customerBooking?->primaryDetail?->name ?? '-' }}</td>

                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                        {{ $row->customerBooking?->booking_code ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $row->plotSaleDetail?->plotDetail?->plot_number ?? '-' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $row->plotSaleDetail?->project?->name ?? '-' }}
                                        /
                                        {{ $row->plotSaleDetail?->block?->block ?? '-' }}
                                    </small>
                                    <small class="d-block text-muted">
                                        {{ $row->plotSaleDetail?->plotDetail?->plot_area ?? '-' }} Sqft
                                    </small>
                                </td>

                                <td>
                                    @if ($row->commission_type == 'self')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                            Self
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                            Team
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end">₹{{ number_format($row->payment_amount, 2) }}</td>
                                <td class="text-end">{{ number_format($row->commission_percent, 2) }}%</td>

                                <td class="text-end fw-bold text-success">
                                    ₹{{ number_format($row->commission_amount, 2) }}
                                </td>

                                <td>
                                    @if ($row->status == 'paid')
                                        <span class="badge bg-success rounded-pill px-3 py-2">Paid</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2">
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('associate-panel.payout-details.single.excel', $row->id) }}"
                                            class="btn btn-sm btn-light border text-success">
                                            <i class="bi bi-file-earmark-excel"></i>
                                        </a>

                                        <a href="{{ route('associate-panel.payout-details.single.pdf', $row->id) }}"
                                            class="btn btn-sm btn-light border text-danger">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No payout records found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($commissions->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="7" class="text-end">Total</th>
                                <th class="text-end">
                                    ₹{{ number_format($commissions->sum('payment_amount'), 2) }}
                                </th>
                                <th></th>
                                <th class="text-end text-success">
                                    ₹{{ number_format($commissions->sum('commission_amount'), 2) }}
                                </th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

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
                autoWidth: false,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    lengthMenu: 'Show _MENU_ records',
                    info: 'Showing _START_ to _END_ of _TOTAL_ records',
                    paginate: {
                        previous: 'Previous',
                        next: 'Next'
                    }
                }
            });
        }
    });
</script>
@endpush