@extends('layouts.app')

@push('title')
    Agent Summary Details Report
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/report.css') }}">
@endpush

@section('content')
    <div class="container-fluid py-4">

        <div class="transaction-hero mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-person-lines-fill text-success"></i>
                    </span>

                    <div>
                        <span class="text-success fw-bold text-uppercase small">
                            Associate Summary Details
                        </span>
                        <h3 class="fw-bold text-dark mb-1">
                            Associate Summary Details Report
                        </h3>
                        <p class="text-muted small mb-0">
                            Associate direct business, team business and total business summary.
                        </p>
                    </div>
                </div>

                <a href="{{ route('agent-summary-details-report.export', request()->query()) }}"
                    class="btn btn-success rounded-pill px-4">
                    <i class="bi bi-file-earmark-excel me-1"></i>
                    Export Excel
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Total Associates</small>
                        <h4 class="fw-bold mb-0">{{ $summary['total_agents'] }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border border-primary-subtle shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Left Team Count</small>
                        <h4 class="fw-bold text-primary mb-0">{{ $summary['left_team_count'] }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border border-warning-subtle shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Right Team Count</small>
                        <h4 class="fw-bold text-warning mb-0">{{ $summary['right_team_count'] }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border border-danger-subtle shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Total Business</small>
                        <h4 class="fw-bold text-danger mb-0">
                            ₹{{ number_format($summary['grand_total'], 2) }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-6 col-md-6">
                <div class="card border border-primary-subtle shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Direct Business</small>
                        <h4 class="fw-bold text-primary mb-0">
                            ₹{{ number_format($summary['total_direct_business'], 2) }}
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-md-6">
                <div class="card border border-success-subtle shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Team Business</small>
                        <h4 class="fw-bold text-success mb-0">
                            ₹{{ number_format($summary['total_team_business'], 2) }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center"
                        style="width:44px;height:44px;">
                        <i class="bi bi-funnel"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold mb-1">Filter Report</h5>
                        <small class="text-muted">
                            Filter associate summary by direction and booking date range.
                        </small>
                    </div>
                </div>

                <form method="GET" id="summaryFilterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-semibold">Direction</label>
                            <select name="direction" class="form-select auto-filter">
                                <option value="">All Team</option>
                                <option value="left" {{ request('direction') == 'left' ? 'selected' : '' }}>
                                    Left Team
                                </option>
                                <option value="right" {{ request('direction') == 'right' ? 'selected' : '' }}>
                                    Right Team
                                </option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-semibold">From Date</label>
                            <input type="date" name="from_date" class="form-control auto-filter"
                                value="{{ request('from_date') }}">
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-semibold">To Date</label>
                            <input type="date" name="to_date" class="form-control auto-filter"
                                value="{{ request('to_date') }}">
                        </div>

                        <div class="col-xl-3 col-md-6 d-flex gap-2">
                            <a href="{{ route('agent-summary-details-report.index') }}"
                                class="btn btn-outline-secondary px-4">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">
                            <i class="bi bi-table text-success me-2"></i>
                            Associate Business Summary
                        </h5>
                        <small class="text-muted">
                            Direct and team business calculated by associate hierarchy.
                        </small>
                    </div>

                    <span class="badge bg-success-subtle text-success border rounded-pill px-3 py-2">
                        {{ $reports->count() }} Records
                    </span>
                </div>

                <div class="table-responsive">
                    <table id="agentSummaryTable" class="table table-hover align-middle nowrap w-100">
                        <thead class="table-success">
                            <tr>
                                <th>Sr.No</th>
                                <th>Associate</th>
                                <th>Direction</th>
                                <th>Direct Team</th>
                                <th>Team Count</th>
                                <th class="text-end">Direct Business</th>
                                <th class="text-end">Team Business</th>
                                <th class="text-end">Total Business</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($reports as $key => $report)
                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        <div class="fw-bold">{{ $report['associate_name'] }}</div>
                                        <small class="text-muted">{{ $report['associate_code'] }}</small>
                                    </td>

                                    <td>
                                        @if ($report['direction'] == 'left')
                                            <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-2">
                                                Left Team
                                            </span>
                                        @elseif ($report['direction'] == 'right')
                                            <span class="badge bg-warning-subtle text-warning border rounded-pill px-3 py-2">
                                                Right Team
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-2">
                                                N/A
                                            </span>
                                        @endif
                                    </td>

                                    <td>{{ $report['direct_team_count'] }}</td>

                                    <td>{{ $report['team_count'] }}</td>

                                    <td class="text-end text-primary fw-bold">
                                        ₹{{ number_format($report['direct_business'], 2) }}
                                    </td>

                                    <td class="text-end text-success fw-bold">
                                        ₹{{ number_format($report['team_business'], 2) }}
                                    </td>

                                    <td class="text-end text-danger fw-bold">
                                        ₹{{ number_format($report['total'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                                        <span class="text-muted">No associate summary records found.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end">Grand Total</td>
                                <td class="text-end text-primary">
                                    ₹{{ number_format($summary['total_direct_business'], 2) }}
                                </td>
                                <td class="text-end text-success">
                                    ₹{{ number_format($summary['total_team_business'], 2) }}
                                </td>
                                <td class="text-end text-danger">
                                    ₹{{ number_format($summary['grand_total'], 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('.auto-filter').on('change', function() {
                $('#summaryFilterForm').submit();
            });

            $('#agentSummaryTable').DataTable({
                pageLength: 10,
                ordering: true,
                responsive: false,
                scrollX: true,
                language: {
                    emptyTable: 'No associate summary records found.'
                }
            });
        });
    </script>
@endpush