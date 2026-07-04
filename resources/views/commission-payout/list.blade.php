@extends('layouts.app')

@push('title')
    Commission Ledger
@endpush
@section('content')
    @php
        $generatedPeriods = $generatedPeriods ?? collect();
        $selfCommission = (float) $commissions->where('commission_type', 'self')->sum('commission_amount');
        $teamCommission = (float) $commissions->where('commission_type', 'team')->sum('commission_amount');
    @endphp

    <div class="container-fluid mt-4 transaction-page">

        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-journal-check"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Commission Center</span>
                        <h3 class="fw-bold mb-1 text-dark">Commission Ledger</h3>
                        <p class="text-muted mb-0 small">
                            Generated self and team commission records with period-wise filters.
                        </p>
                    </div>
                </div>

                <div class="d-flex justify-content-lg-end flex-wrap gap-2">
                    <a href="{{ route('commission-ledger.export.excel', request()->query()) }}"
                        class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-excel me-1"></i> Excel
                    </a>

                    <a href="{{ route('commission-ledger.export.pdf', request()->query()) }}"
                        class="btn btn-outline-danger">
                        <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                    </a>

                    <a href="{{ route('generate-commission.index') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i> Generate Commission
                    </a>
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Total Records</small>
                                <h5 class="fw-bold mb-0">{{ $commissions->count() }}</h5>
                            </div>
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                style="width:44px;height:44px;">
                                <i class="bi bi-list-check fs-4 text-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Self Commission</small>
                                <h5 class="fw-bold text-success mb-0">
                                    &#8377;{{ number_format($selfCommission, 2) }}
                                </h5>
                            </div>
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                style="width:44px;height:44px;">
                                <i class="bi bi-briefcase fs-4 text-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Team Commission</small>
                                <h5 class="fw-bold text-info mb-0">
                                    &#8377;{{ number_format($teamCommission, 2) }}
                                </h5>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                style="width:44px;height:44px;">
                                <i class="bi bi-currency-rupee fs-4 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Total Commission</small>
                                <h5 class="fw-bold text-success mb-0">
                                    &#8377;{{ number_format($commissions->sum('commission_amount'), 2) }}
                                </h5>
                            </div>
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                style="width:44px;height:44px;">
                                <i class="bi bi-hourglass-split fs-4 text-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-funnel me-2 text-success"></i>
                    Search Commission Records
                </h5>
                <p class="text-muted small mb-0">
                    Filter records by associate, commission type and generated date.
                </p>
            </div>

            <div class="card-body p-4">
                <form method="GET" action="{{ route('commission-ledger.index') }}">
                    <div class="row g-3 align-items-end">

                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label fw-semibold">Associate ID / Name</label>
                            <input type="text" name="associate_id" value="{{ request('associate_id') }}"
                                class="form-control" placeholder="Enter associate id or name">
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6">
                            <label class="form-label fw-semibold">Commission Type</label>
                            <select name="commission_type" class="form-select">
                                <option value="">All Types</option>
                                <option value="self" {{ request('commission_type') == 'self' ? 'selected' : '' }}>Self
                                </option>
                                <option value="team" {{ request('commission_type') == 'team' ? 'selected' : '' }}>Team
                                </option>
                            </select>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6">
                            <label class="form-label fw-semibold">Generated Period</label>
                            <select name="period_month" class="form-select">
                                <option value="">All Periods</option>
                                @foreach ($generatedPeriods as $period)
                                    <option value="{{ $period['value'] }}"
                                        {{ request('period_month') == $period['value'] ? 'selected' : '' }}>
                                        {{ $period['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6">
                            <label class="form-label fw-semibold">From Date</label>
                            <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                        </div>

                        <div class="col-xl-2 col-lg-4 col-md-6">
                            <label class="form-label fw-semibold">To Date</label>
                            <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                        </div>

                        <div class="col-xl-3 col-lg-8 col-md-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success flex-fill">
                                    <i class="bi bi-search me-1"></i> Search
                                </button>

                                <a href="{{ route('commission-ledger.index') }}" class="btn btn-light border flex-fill">
                                    <i class="fa-solid fa-arrow-rotate-left me-1"></i> Reset
                                </a>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 p-4">
                <div class="row align-items-center g-3">
                    <div class="col-lg-8">
                        <h5 class="fw-bold mb-1">Commission Records</h5>
                        <p class="text-muted small mb-0">
                            Associate-wise commission ledger details with booking and plot information.
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
                    <table class="table table-hover table-bordered align-middle mb-0" id="commissionTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Generated</th>
                                <th>Associate</th>
                                <th>Source</th>
                                <th>Customer</th>
                                <th>Booking</th>
                                <th>Plot</th>
                                <th>Type</th>
                                <th class="text-end">Business</th>
                                <th class="text-end">%</th>
                                <th class="text-end">Commission</th>
                                {{-- <th>Status</th> --}}
                                {{-- <th class="text-center">Download</th> --}}
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
                                        <div class="fw-semibold">
                                            {{ $row->associate?->associate_name ?? '-' }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $row->associate?->associate_id ?? '-' }}
                                        </small>
                                        <small class="d-block text-success">
                                            {{ $row->associate?->rank?->designation ?? '-' }}
                                            @if ($row->associate?->rank)
                                                ({{ number_format((float) $row->associate->rank->commission, 2) }}%)
                                            @endif
                                        </small>
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $row->sourceAssociate?->associate_name ?? '-' }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $row->sourceAssociate?->associate_id ?? '-' }}
                                        </small>
                                        <small class="d-block text-muted">
                                            {{ $row->sourceAssociate?->rank?->designation ?? '-' }}
                                            @if ($row->sourceAssociate?->rank)
                                                ({{ number_format((float) $row->sourceAssociate->rank->commission, 2) }}%)
                                            @endif
                                        </small>
                                    </td>

                                    <td>
                                        {{ $row->customerBooking?->primaryDetail?->name ?? '-' }}
                                    </td>

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
                                            <span
                                                class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                                Self
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                                Team
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        &#8377;{{ number_format($row->payment_amount, 2) }}
                                    </td>

                                    <td class="text-end">
                                        <div class="fw-semibold">{{ number_format($row->commission_percent, 2) }}%</div>
                                        @if ($row->commission_type === 'team')
                                            <small class="text-muted">
                                                {{ number_format((float) ($row->associate?->rank?->commission ?? 0), 2) }}%
                                                -
                                                {{ number_format((float) ($row->sourceAssociate?->rank?->commission ?? 0), 2) }}%
                                            </small>
                                        @else
                                            <small class="text-muted">Self rank %</small>
                                        @endif
                                    </td>

                                    <td class="text-end fw-bold text-success">
                                        &#8377;{{ number_format($row->commission_amount, 2) }}
                                    </td>

                                    {{-- <td>
                                        @if ($row->status == 'paid')
                                            <span class="badge bg-success rounded-pill px-3 py-2">
                                                Paid
                                            </span>
                                        @else
                                            <span
                                                class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2">
                                                Pending
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('commission-ledger.single.excel', $row->id) }}"
                                                class="btn btn-sm btn-light border text-success" title="Download Excel">
                                                <i class="bi bi-file-earmark-excel"></i>
                                            </a>

                                            <a href="{{ route('commission-ledger.single.pdf', $row->id) }}"
                                                class="btn btn-sm btn-light border text-danger" title="Download PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        </div>
                                    </td> --}}
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No commission records found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if ($commissions->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="8" class="text-end">Total</th>
                                    <th class="text-end">
                                        &#8377;{{ number_format($commissions->sum('payment_amount'), 2) }}
                                    </th>
                                    <th></th>
                                    <th class="text-end text-success">
                                        &#8377;{{ number_format($commissions->sum('commission_amount'), 2) }}
                                    </th>
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
            if ($('#commissionTable tbody tr td[colspan]').length === 0) {
                $('#commissionTable').DataTable({
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
