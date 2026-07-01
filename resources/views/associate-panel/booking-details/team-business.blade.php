@extends('layouts.app')

@section('content')
    @php
        $totalBookings = $reports->count();
        $totalBusiness = (float) $reports->sum('amount');
        $multiPlotBookings = $reports->filter(fn ($report) => str_contains((string) $report->plot_no, ','))->count();
    @endphp

    <div class="container-fluid mt-4 transaction-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-bar-chart-line"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Business Details</span>
                        <h3 class="fw-bold mb-1 text-dark">Team Business Report</h3>
                        <p class="text-muted mb-0 small">Review team bookings, grouped plots and business value in one clean report.</p>
                    </div>
                </div>
                <a href="{{ route('associate-panel.booking-detail') }}" class="btn btn-outline-success">
                    <i class="bi bi-arrow-left me-1"></i> Back to Booking Details
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-secondary">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Total Bookings</small>
                        <h4 class="fw-bold mb-0">{{ $totalBookings }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-success">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Total Business</small>
                        <h4 class="fw-bold text-success mb-0">&#8377;{{ number_format($totalBusiness, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-info">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Multiple Plot Bookings</small>
                        <h4 class="fw-bold text-info mb-0">{{ $multiPlotBookings }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="transaction-card">
            <div class="transaction-card-body">
                <div class="transaction-section-title">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-section-title-icon"><i class="bi bi-list-check"></i></span>
                        <div>
                            <h5 class="fw-bold mb-1">Business Records</h5>
                            <small class="text-muted">Each row represents one booking. Multiple plots stay grouped in the same booking.</small>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $totalBookings }} Records</span>
                </div>

                <div class="transaction-table-wrap">
                    <table class="table table-hover align-middle mb-0 transaction-table w-100" id="reportTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Booking</th>
                                <th>Customer</th>
                                <th>Associate</th>
                                <th>Project</th>
                                <th>Plots</th>
                                <th>Business Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $key => $report)
                                @php
                                    $plotCount = collect(explode(',', (string) $report->plot_no))->map(fn ($plot) => trim($plot))->filter()->count();
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td class="fw-bold text-success">{{ $report->booking_code }}</td>
                                    <td>{{ $report->customer_name }}</td>
                                    <td>{{ $report->agent_name }}</td>
                                    <td>{{ $report->project_name }}</td>
                                    <td>
                                        <strong>{{ $report->plot_no }}</strong>
                                        @if ($plotCount > 1)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">
                                                {{ $plotCount }} Plots
                                            </span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-success">&#8377;{{ number_format($report->amount, 2) }}</td>
                                    <td>{{ $report->date }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No team business records found.
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
    <script>
        $(document).ready(function() {
            if ($('#reportTable tbody tr td[colspan]').length === 0) {
                $('#reportTable').DataTable({
                    pageLength: 10,
                    responsive: false,
                    scrollX: true,
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search business records..."
                    }
                });
            }
        });
    </script>
@endpush
