@extends('layouts.app')

@push('title')
    Associate Team New Booking Details
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
                        <i class="bi bi-people-fill text-success"></i>
                    </span>

                    <div>
                        <span class="text-success fw-bold text-uppercase small">
                            Associate Team New Booking Details
                        </span>
                        <h3 class="fw-bold text-dark mb-1">
                            Associate Team New Booking Details
                        </h3>
                        <p class="text-muted small mb-0">
                            View selected associate and full team new booking details.
                        </p>
                    </div>
                </div>

                <a href="{{ route('associate-team-new-booking-details-report.export', request()->query()) }}"
                    class="btn btn-success rounded-pill px-4">
                    <i class="bi bi-file-earmark-excel me-1"></i>
                    Export Excel
                </a>
            </div>
        </div>

        @if (request()->has('search'))
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <small class="text-muted fw-semibold">Total Bookings</small>
                            <h4 class="fw-bold mb-0">{{ $summary['total_records'] }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border border-primary-subtle shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <small class="text-muted fw-semibold">Total Plots</small>
                            <h4 class="fw-bold text-primary mb-0">{{ $summary['total_plots'] }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border border-success-subtle shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <small class="text-muted fw-semibold">Paid Amount</small>
                            <h4 class="fw-bold text-success mb-0">
                                ₹{{ number_format($summary['paid_amount'], 2) }}
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border border-danger-subtle shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <small class="text-muted fw-semibold">Due Amount</small>
                            <h4 class="fw-bold text-danger mb-0">
                                ₹{{ number_format($summary['due_amount'], 2) }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        @endif

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
                            Filter booking by associate team and booking date range.
                        </small>
                    </div>
                </div>

                <form method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-4 col-md-6">
                            <label class="form-label fw-semibold">Associate ID</label>
                            <select name="associate_id" class="form-select">
                                <option value="">All Associates</option>
                                @foreach ($associates as $associate)
                                    <option value="{{ $associate->id }}"
                                        {{ request('associate_id') == $associate->id ? 'selected' : '' }}>
                                        {{ $associate->associate_id }} - {{ $associate->associate_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-semibold">From Date</label>
                            <input type="date" name="from_date" class="form-control"
                                value="{{ request('from_date') }}">
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-semibold">To Date</label>
                            <input type="date" name="to_date" class="form-control"
                                value="{{ request('to_date') }}">
                        </div>

                        <div class="col-xl-2 col-md-6 d-flex gap-2">
                            <button type="submit" name="search" value="1" class="btn btn-success flex-fill">
                                <i class="bi bi-search me-1"></i>
                                Search
                            </button>

                            <a href="{{ route('associate-team-new-booking-details-report.index') }}"
                                class="btn btn-outline-secondary px-4">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if (request()->has('search'))
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">
                                <i class="bi bi-table text-success me-2"></i>
                                Associate Team Booking Details
                            </h5>
                            <small class="text-muted">
                                Click Total Plot to view all booked plot details.
                            </small>
                        </div>

                        <span class="badge bg-success-subtle text-success border rounded-pill px-3 py-2">
                            {{ $reports->count() }} Records
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table id="associateTeamBookingTable" class="table table-hover align-middle nowrap w-100">
                            <thead class="table-success">
                                <tr>
                                    <th>SNo</th>
                                    <th>Agent</th>
                                    <th>Position</th>
                                    <th>Customer</th>
                                    <th>Booking ID</th>
                                    <th>Project</th>
                                    <th>Block</th>
                                    <th>Plot No</th>
                                    <th>Total Plot</th>
                                    <th>Plan Type</th>
                                    <th>Payment Type</th>
                                    <th class="text-end">Total Cost</th>
                                    <th>Paymode</th>
                                    <th class="text-end">Paid Amt.</th>
                                    <th class="text-end">Due Amt.</th>
                                    <th>Date</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($reports as $key => $report)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>

                                        <td>
                                            <div class="fw-bold">{{ $report['agent_code'] }}</div>
                                            <small class="text-muted">{{ $report['agent_name'] }}</small>
                                        </td>

                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-2">
                                                {{ $report['position'] . ' - ' . $report['commission'] }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="fw-bold">{{ $report['customer_code'] }}</div>
                                            <small class="text-muted">{{ $report['customer_name'] }}</small>
                                        </td>

                                        <td class="fw-semibold">{{ $report['booking_code'] }}</td>

                                        <td>{{ $report['project'] }}</td>

                                        <td>{{ $report['block'] }}</td>

                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill">
                                                {{ $report['plots'] }}
                                            </span>
                                        </td>

                                        <td>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                data-bs-toggle="modal"
                                                data-bs-target="#plotDetailsModal{{ $key }}">
                                                <i class="bi bi-eye me-1"></i>
                                                {{ $report['plot_count'] }} Plot(s)
                                            </button>
                                        </td>

                                        <td>{{ $report['plan_type'] }}</td>

                                        <td>{{ $report['payment_type'] }}</td>

                                        <td class="text-end fw-bold text-primary">
                                            ₹{{ number_format($report['total_cost'], 2) }}
                                        </td>

                                        <td>{{ $report['payment_mode'] }}</td>

                                        <td class="text-end fw-bold text-success">
                                            ₹{{ number_format($report['paid_amount'], 2) }}
                                        </td>

                                        <td class="text-end fw-bold text-danger">
                                            ₹{{ number_format($report['due_amount'], 2) }}
                                        </td>

                                        <td>{{ $report['date'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="16" class="text-center py-5">
                                            <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                                            <span class="text-muted">No associate team booking records found.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="11" class="text-end">Total</td>
                                    <td class="text-end text-primary">
                                        ₹{{ number_format($summary['total_cost'], 2) }}
                                    </td>
                                    <td></td>
                                    <td class="text-end text-success">
                                        ₹{{ number_format($summary['paid_amount'], 2) }}
                                    </td>
                                    <td class="text-end text-danger">
                                        ₹{{ number_format($summary['due_amount'], 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @foreach ($reports as $key => $report)
                <div class="modal fade" id="plotDetailsModal{{ $key }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content border-0 rounded-4 shadow">
                            <div class="modal-header bg-success text-white rounded-top-4">
                                <div>
                                    <h5 class="modal-title fw-bold">
                                        Plot Details - {{ $report['booking_code'] }}
                                    </h5>
                                    <small>{{ $report['customer_code'] }} - {{ $report['customer_name'] }}</small>
                                </div>

                                <button type="button" class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body p-4">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <div class="border rounded-4 p-3 bg-light">
                                            <small class="text-muted fw-semibold">Agent</small>
                                            <div class="fw-bold">{{ $report['agent_code'] }} - {{ $report['agent_name'] }}</div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="border rounded-4 p-3 bg-light">
                                            <small class="text-muted fw-semibold">Total Plot</small>
                                            <div class="fw-bold text-primary">{{ $report['plot_count'] }} Plot(s)</div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="border rounded-4 p-3 bg-light">
                                            <small class="text-muted fw-semibold">Total Cost</small>
                                            <div class="fw-bold text-success">
                                                ₹{{ number_format($report['total_cost'], 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle mb-0">
                                        <thead class="table-success">
                                            <tr>
                                                <th>#</th>
                                                <th>Plot No</th>
                                                <th>Block</th>
                                                <th>PLC Type</th>
                                                <th>Area</th>
                                                <th>Rate</th>
                                                <th class="text-end">Plot Cost</th>
                                                <th class="text-end">Other Charges</th>
                                                <th class="text-end">Discount</th>
                                                <th class="text-end">Final Amount</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($report['plot_details'] as $plotIndex => $plot)
                                                <tr>
                                                    <td>{{ $plotIndex + 1 }}</td>
                                                    <td class="fw-bold">{{ $plot['plot_no'] }}</td>
                                                    <td>{{ $plot['block'] }}</td>
                                                    <td>{{ $plot['plot_type'] }}</td>
                                                    <td>{{ $plot['area'] }}</td>
                                                    <td>₹{{ number_format($plot['rate'], 2) }}</td>
                                                    <td class="text-end">₹{{ number_format($plot['plot_cost'], 2) }}</td>
                                                    <td class="text-end">₹{{ number_format($plot['other_charges'], 2) }}</td>
                                                    <td class="text-end">₹{{ number_format($plot['discount'], 2) }}</td>
                                                    <td class="text-end fw-bold text-success">
                                                        ₹{{ number_format($plot['final_amount'], 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>

                                        <tfoot>
                                            <tr class="table-light fw-bold">
                                                <td colspan="6" class="text-end">Total</td>
                                                <td class="text-end">
                                                    ₹{{ number_format(collect($report['plot_details'])->sum('plot_cost'), 2) }}
                                                </td>
                                                <td class="text-end">
                                                    ₹{{ number_format(collect($report['plot_details'])->sum('other_charges'), 2) }}
                                                </td>
                                                <td class="text-end">
                                                    ₹{{ number_format(collect($report['plot_details'])->sum('discount'), 2) }}
                                                </td>
                                                <td class="text-end text-success">
                                                    ₹{{ number_format(collect($report['plot_details'])->sum('final_amount'), 2) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <i class="bi bi-search fs-1 text-muted d-block mb-2"></i>
                    <h5 class="fw-bold mb-1">Search Associate Team Booking</h5>
                    <p class="text-muted mb-0">
                        Select associate or date range and click search to view booking details.
                    </p>
                </div>
            </div>
        @endif

    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            if ($('#associateTeamBookingTable').length) {
                $('#associateTeamBookingTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    responsive: false,
                    scrollX: true,
                    language: {
                        emptyTable: 'No associate team booking records found.'
                    }
                });
            }
        });
    </script>
@endpush