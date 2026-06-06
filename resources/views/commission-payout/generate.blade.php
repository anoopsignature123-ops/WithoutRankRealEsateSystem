@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center g-3">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 text-success rounded-4 d-flex align-items-center justify-content-center me-3"
                                style="width:58px;height:58px;">
                                <i class="bi bi-cash-stack fs-2"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1">Generate Commission</h4>
                                <p class="text-muted mb-0">
                                    Select commission generate and preview associate-wise payout before generation.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 text-lg-end">
                        <a href="{{ route('commission-ledger.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-journal-text me-1"></i> Commission Ledger
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                <div class="row align-items-center g-3">
                    <div class="col-lg-8">
                        <h5 class="fw-bold mb-1">
                            <i class="bi bi-calendar-check me-2 text-success"></i>
                            Commission Generate
                        </h5>
                        <p class="text-muted small mb-0">
                            From Date is locked. Commission can be generated only from the next pending date.
                        </p>
                    </div>

                    <div class="col-lg-4 text-lg-end">
                        @if ($lastGeneratedDate)
                            <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                Last Generated:
                                {{ \Carbon\Carbon::parse($lastGeneratedDate)->format('d M Y') }}
                            </span>
                        @else
                            <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                First Generation
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form method="GET" action="{{ route('generate-commission.index') }}">
                    <div class="row g-3">

                        <div class="col-xl-4 col-lg-4 col-md-6">
                            <label class="form-label fw-semibold">From Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input type="date" value="{{ $fromDate }}" class="form-control bg-light" readonly>
                            </div>
                            <small class="text-muted">
                                System generated pending start date.
                            </small>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6">
                            <label class="form-label fw-semibold">
                                To Date <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-calendar-event text-muted"></i>
                                </span>

                                <input type="date" name="to_date" value="{{ request('to_date') }}"
                                    min="{{ $fromDate }}" max="{{ now()->format('Y-m-d') }}"
                                    class="form-control @error('to_date') is-invalid @enderror" required>

                                @error('to_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-12 d-flex align-items-center">

                            <div class="d-flex gap-2 w-100 mt-lg-2">
                                <button type="submit" class="btn btn-success flex-fill">
                                    <i class="bi bi-eye me-1"></i>
                                    Preview
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
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">Self Business</small>
                                    <h5 class="fw-bold mb-0">
                                        ₹{{ number_format($preview['summary']['self_business'], 2) }}
                                    </h5>
                                </div>
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                    style="width:44px;height:44px;">
                                    <i class="bi bi-person-check fs-4 text-secondary"></i>
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
                                    <small class="text-muted">Team Business</small>
                                    <h5 class="fw-bold mb-0">
                                        ₹{{ number_format($preview['summary']['team_business'], 2) }}
                                    </h5>
                                </div>
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                    style="width:44px;height:44px;">
                                    <i class="bi bi-diagram-3 fs-4 text-secondary"></i>
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
                                        ₹{{ number_format($preview['summary']['total_commission'], 2) }}
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
                                    <small class="text-muted">Total Records</small>
                                    <h5 class="fw-bold mb-0">
                                        {{ $preview['summary']['total_records'] }}
                                    </h5>
                                </div>
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                    style="width:44px;height:44px;">
                                    <i class="bi bi-journal-text fs-4 text-secondary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-8">
                            <h5 class="fw-bold mb-1">Commission Preview</h5>
                            <p class="text-muted small mb-0">
                                Period:
                                <strong>{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}</strong>
                                to
                                <strong>{{ \Carbon\Carbon::parse(request('to_date'))->format('d M Y') }}</strong>
                            </p>
                        </div>

                        <div class="col-lg-4 text-lg-end">
                            @if (count($preview['rows']) > 0)
                                <form method="POST" action="{{ route('generate-commission.store') }}"
                                    id="generateCommissionForm">
                                    @csrf
                                    <input type="hidden" name="to_date" value="{{ request('to_date') }}">

                                    <button type="submit" class="btn btn-success px-4">
                                        <i class="bi bi-check2-circle me-1"></i> Generate Commission
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if (count($preview['rows']) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Associate</th>
                                        <th>Rank</th>
                                        <th>From</th>
                                        <th>To</th>
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

                                            <td>{{ \Carbon\Carbon::parse($item['from_date'])->format('d M Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item['to_date'])->format('d M Y') }}</td>

                                            <td class="text-end">
                                                ₹{{ number_format($item['calculation']['self_business'], 2) }}</td>
                                            <td class="text-end">
                                                ₹{{ number_format($item['calculation']['team_business'], 2) }}</td>
                                            <td class="text-end">
                                                ₹{{ number_format($item['calculation']['self_commission'], 2) }}</td>
                                            <td class="text-end">
                                                ₹{{ number_format($item['calculation']['team_commission'], 2) }}</td>

                                            <td class="text-end fw-bold text-success">
                                                ₹{{ number_format($item['calculation']['total_commission'], 2) }}
                                            </td>

                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
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
                            <p class="text-muted mb-0">No eligible commission records found for selected date.</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <i class="bi bi-calendar2-week fs-1 text-muted"></i>
                    <h5 class="fw-bold mt-3">Select To Date to Preview Commission</h5>
                    <p class="text-muted mb-0">
                        After selecting date, system will calculate all eligible associate commissions.
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
                    title: 'Already Generated',
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

                let form = this;

                Swal.fire({
                    title: 'Generate Commission?',
                    text: 'Commission will be generated for selected period. This action cannot be reversed.',
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
