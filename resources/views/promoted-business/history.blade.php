@extends('layouts.app')
@push('title')
    Promotion Business History
@endpush
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/report.css') }}">
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="transaction-hero mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-4">

                <div class="d-flex align-items-center gap-3">

                    <span class="transaction-icon">
                        <i class="bi bi-clock-history text-success"></i>
                    </span>

                    <div>
                        <span class="text-success fw-bold text-uppercase small">
                            Promotion History
                        </span>

                        <h3 class="fw-bold text-dark mb-1">
                            Promotion History
                        </h3>

                        <p class="text-muted small mb-0">
                            Track all associate rank upgrades with self business, team business and promotion records.
                        </p>
                    </div>

                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">

                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">

                        <input type="text" name="associate_name" value="{{ request('associate_name') }}"
                            class="form-control" placeholder="Associate Name / ID" style="width:220px;">

                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control"
                            style="width:170px;">

                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control"
                            style="width:170px;">

                        <button class="btn btn-success rounded-pill px-4">
                            <i class="bi bi-search me-1"></i>
                            Search
                        </button>

                        <a href="{{ route('promoted.history') }}" class="btn btn-outline-secondary rounded-pill px-3">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>

                    </form>

                    <a href="{{ route('promoted.index') }}" class="btn btn-outline-success rounded-pill px-4">

                        <i class="bi bi-arrow-left me-1"></i>

                        Back

                    </a>

                </div>

            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Total Promotions</small>
                        <h4 class="fw-bold mb-0">{{ $summary['total_promotions'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border border-primary-subtle shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Self Business</small>
                        <h4 class="fw-bold text-primary mb-0">
                            &#8377;{{ number_format($summary['total_self_business'] ?? 0, 2) }}
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border border-success-subtle shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Team Business</small>
                        <h4 class="fw-bold text-success mb-0">
                            &#8377;{{ number_format($summary['total_team_business'] ?? 0, 2) }}
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border border-warning-subtle shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Total Business</small>
                        <h4 class="fw-bold text-warning mb-0">
                            &#8377;{{ number_format($summary['total_business'] ?? 0, 2) }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">
                            <i class="bi bi-table text-success me-2"></i> Promotion Records
                        </h5>
                        <small class="text-muted">Old rank to new rank upgrade details.</small>
                    </div>

                    <span class="badge bg-success-subtle text-success border rounded-pill px-3 py-2">
                        {{ $histories->count() }} Records
                    </span>
                </div>

                @if ($histories->count() > 0)
                    <div class="table-responsive">
                        <table id="promotionHistoryTable" class="table table-hover align-middle nowrap w-100">
                            <thead class="table-success">
                                <tr>
                                    <th>#</th>
                                    <th>Associate</th>
                                    <th>Rank Upgrade</th>
                                    <th class="text-end">Self Business</th>
                                    <th class="text-end">Team Business</th>
                                    <th class="text-end">Total Business</th>
                                    <th>Promotion Date</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($histories as $key => $history)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>

                                        <td>
                                            <div class="fw-bold">{{ $history->associate?->associate_name ?? 'N/A' }}</div>
                                            <small
                                                class="text-muted">{{ $history->associate?->associate_id ?? 'N/A' }}</small>
                                        </td>

                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                                {{ $history->oldRank?->designation ?? 'N/A' }}
                                            </span>
                                            <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                            <span class="badge bg-success rounded-pill px-3 py-2">
                                                {{ $history->newRank?->designation ?? 'N/A' }}
                                            </span>
                                        </td>

                                        <td class="text-end">&#8377;{{ number_format($history->self_business ?? 0, 2) }}
                                        </td>
                                        <td class="text-end">&#8377;{{ number_format($history->team_business ?? 0, 2) }}
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            &#8377;{{ number_format($history->total_business ?? 0, 2) }}
                                        </td>

                                        <td>
                                            {{ $history->promotion_date
                                                ? \Carbon\Carbon::parse($history->promotion_date)->format('d-M-Y')
                                                : $history->created_at?->format('d-M-Y') }}
                                        </td>

                                        <td>{{ $history->remarks ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="3" class="text-end">Total</td>
                                    <td class="text-end">
                                        &#8377;{{ number_format($summary['total_self_business'] ?? 0, 2) }}</td>
                                    <td class="text-end">
                                        &#8377;{{ number_format($summary['total_team_business'] ?? 0, 2) }}</td>
                                    <td class="text-end text-success">
                                        &#8377;{{ number_format($summary['total_business'] ?? 0, 2) }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                        <h6 class="fw-bold mb-1">No promotion history found</h6>
                        <p class="text-muted mb-0">Promotion records will appear here after rank upgrade.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            if ($('#promotionHistoryTable').length) {
                $('#promotionHistoryTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    responsive: false,
                    scrollX: true
                });
            }
        });
    </script>
@endpush
