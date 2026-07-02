@extends('layouts.app')

@push('title')
    Generate Commission
@endpush

@section('content')
    @php
        $availablePeriods = $periodOptions->where('is_generated', false);
        $generatedPeriods = $periodOptions->where('is_generated', true);
        $nextPendingPeriod = $availablePeriods->first();
    @endphp

    <div class="container-fluid mt-4 transaction-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-cash-stack"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Commission Center</span>
                        <h3 class="fw-bold mb-1 text-dark">Generate Commission</h3>
                        <p class="text-muted mb-0 small">
                            Select a month, preview eligible associate payout, then generate commission.
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('commission-ledger.index') }}" class="btn btn-outline-success">
                        <i class="bi bi-journal-text me-1"></i> Commission Ledger
                    </a>
                </div>
            </div>
        </div>

        @if ($warning)
            <div class="alert alert-warning border-0 shadow-sm">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ $warning }}
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="transaction-summary-box h-100">
                    <small class="text-muted fw-semibold text-uppercase">Available Months</small>
                    <h4 class="fw-bold text-success mb-0">{{ $availablePeriods->count() }}</h4>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="transaction-summary-box h-100">
                    <small class="text-muted fw-semibold text-uppercase">Generated Months</small>
                    <h4 class="fw-bold mb-0">{{ $generatedPeriods->count() }}</h4>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="transaction-summary-box h-100">
                    <small class="text-muted fw-semibold text-uppercase">Next Pending</small>
                    <h5 class="fw-bold mb-0">
                        {{ $nextPendingPeriod['label'] ?? 'No Pending Month' }}
                    </h5>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="transaction-summary-box h-100">
                    <small class="text-muted fw-semibold text-uppercase">Last Generated</small>
                    <h5 class="fw-bold mb-0">
                        {{ $lastGeneratedDate ? \Carbon\Carbon::parse($lastGeneratedDate)->format('d M Y') : 'Not Yet' }}
                    </h5>
                </div>
            </div>
        </div>

        <div class="transaction-card mb-4">
            <div class="transaction-card-body">
                <div class="transaction-section-title">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-section-title-icon">
                            <i class="bi bi-calendar2-check"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-1">Select Commission Date</h5>
                            <small class="text-muted">
                                Select date after last generated commission date.
                            </small>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('generate-commission.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-5 col-md-6">
                            <label class="form-label fw-semibold">
                                Commission Date <span class="text-danger">*</span>
                            </label>

                            <input type="date" name="commission_date"
                                class="form-control @error('commission_date') is-invalid @enderror"
                                value="{{ old('commission_date', $commissionDate ?? '') }}"
                                min="{{ $lastGeneratedDate ? \Carbon\Carbon::parse($lastGeneratedDate)->addDay()->format('Y-m-d') : '' }}"
                                max="{{ now()->format('Y-m-d') }}" required>

                            @error('commission_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="text-muted d-block mt-1">
                                Commission will be calculated after last generated date up to selected date.
                            </small>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-semibold">Commission Date Range</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input type="text" class="form-control bg-light"
                                    value="{{ $selectedPeriod['range_label'] ?? 'Select commission date to preview' }}"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success flex-fill">
                                    <i class="bi bi-eye me-1"></i> Preview
                                </button>
                                <a href="{{ route('generate-commission.index') }}" class="btn btn-light border flex-fill">
                                    <i class="fa-solid fa-arrow-rotate-left"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($preview)
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="transaction-summary-box h-100 bg-white">
                        <small class="text-muted fw-semibold text-uppercase">Self Business</small>
                        <h4 class="fw-bold mb-0">&#8377;{{ number_format($preview['summary']['self_business'], 2) }}</h4>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="transaction-summary-box h-100 bg-white">
                        <small class="text-muted fw-semibold text-uppercase">Team Business</small>
                        <h4 class="fw-bold mb-0">&#8377;{{ number_format($preview['summary']['team_business'], 2) }}</h4>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="transaction-summary-box h-100 bg-white">
                        <small class="text-muted fw-semibold text-uppercase">Total Commission</small>
                        <h4 class="fw-bold text-success mb-0">
                            &#8377;{{ number_format($preview['summary']['total_commission'], 2) }}
                        </h4>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="transaction-summary-box h-100 bg-white">
                        <small class="text-muted fw-semibold text-uppercase">Payout Records</small>
                        <h4 class="fw-bold mb-0">{{ $preview['summary']['total_records'] }}</h4>
                    </div>
                </div>
            </div>

            <div class="transaction-card overflow-hidden mb-4">
                <div class="transaction-card-body pb-0">
                    <div class="transaction-section-title">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon">
                                <i class="bi bi-list-check"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold mb-1">Commission Preview</h5>
                                <small class="text-muted">
                                    Period: <strong>{{ $selectedPeriod['range_label'] ?? '-' }}</strong>
                                </small>
                            </div>
                        </div>

                        @if (count($preview['rows']) > 0)
                            <form method="POST" action="{{ route('generate-commission.store') }}"
                                id="generateCommissionForm">
                                @csrf
                                <input type="hidden" name="commission_date" value="{{ $commissionDate }}">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check2-circle me-1"></i> Generate Commission
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if (count($preview['rows']) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Associate</th>
                                    <th>Rank</th>
                                    <th>Period</th>
                                    <th class="text-end">Self Business</th>
                                    <th class="text-end">Team Business</th>
                                    <th class="text-end">Self Comm.</th>
                                    <th class="text-end">Team Comm.</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Records</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($preview['rows'] as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $item['associate']->associate_name }}</div>
                                            <small class="text-muted">{{ $item['associate']->associate_id }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                                {{ $item['associate']->rank?->designation ?? '-' }}
                                            </span>
                                            <small class="d-block text-muted mt-1">
                                                {{ number_format($item['associate']->rank?->commission ?? 0, 2) }}%
                                            </small>
                                        </td>
                                        <td>
                                            <div>{{ \Carbon\Carbon::parse($item['from_date'])->format('d M Y') }}</div>
                                            <small class="text-muted">
                                                to {{ \Carbon\Carbon::parse($item['to_date'])->format('d M Y') }}
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            &#8377;{{ number_format($item['calculation']['self_business'], 2) }}</td>
                                        <td class="text-end">
                                            &#8377;{{ number_format($item['calculation']['team_business'], 2) }}</td>
                                        <td class="text-end">
                                            &#8377;{{ number_format($item['calculation']['self_commission'], 2) }}</td>
                                        <td class="text-end">
                                            &#8377;{{ number_format($item['calculation']['team_commission'], 2) }}</td>
                                        <td class="text-end fw-bold text-success">
                                            &#8377;{{ number_format($item['calculation']['total_commission'], 2) }}
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-success-subtle text-success border rounded-pill px-3 py-2">
                                                {{ count($item['calculation']['rows']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-info-circle fs-1 text-muted"></i>
                        <h5 class="fw-bold mt-3">No commission found</h5>
                        <p class="text-muted mb-0">No ungenerated eligible commission records found for selected month.</p>
                    </div>
                @endif
            </div>
        @else
            <div class="transaction-card">
                <div class="transaction-card-body text-center py-5">
                    <i class="bi bi-calendar2-week fs-1 text-muted"></i>
                    <h5 class="fw-bold mt-3">Select Date to Preview Commission</h5>
                    <p class="text-muted mb-0">
                        Commission will continue from the next date after last generated commission.
                    </p>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Commission Generated',
                    text: @json(session('success')),
                    confirmButtonColor: '#198754'
                });
            @endif

            @if ($warning)
                Swal.fire({
                    icon: 'warning',
                    title: 'Commission Period',
                    text: @json($warning),
                    confirmButtonColor: '#198754'
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: @json($errors->first()),
                    confirmButtonColor: '#dc3545'
                });
            @endif

            $('#generateCommissionForm').on('submit', function(e) {
                e.preventDefault();

                const form = this;

                Swal.fire({
                    title: 'Generate Commission?',
                    text: 'Commission will be generated for the selected month. This action cannot be reversed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Generate',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
