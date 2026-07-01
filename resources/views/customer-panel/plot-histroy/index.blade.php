@extends('layouts.app')

@section('content')
    <div class="container-fluid customer-panel-page customer-plot-booking-page">
        <div class="customer-profile-hero mb-4">
            <div class="customer-profile-main">
                <div class="customer-avatar profile-avatar">
                    <i class="bi bi-pin-map"></i>
                </div>
                <div>
                    <span class="customer-dashboard-kicker">Plot Booking</span>
                    <h3 class="mb-1">My Plot Booking</h3>
                    <p class="mb-0">View grouped bookings, plot details, payment progress and status.</p>
                </div>
            </div>

            <div class="customer-profile-meta">
                <span class="badge bg-white text-success border rounded-pill px-3 py-2">
                    {{ $plots->count() }} Plot{{ $plots->count() === 1 ? '' : 's' }}
                </span>
                <span class="badge bg-white text-success border rounded-pill px-3 py-2">
                    {{ $bookings->count() }} Booking{{ $bookings->count() === 1 ? '' : 's' }}
                </span>
                <small>Confirmed paid excludes hold payments</small>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="customer-stat-card success">
                    <div class="customer-stat-icon"><i class="bi bi-grid"></i></div>
                    <div>
                        <small>Total Plots</small>
                        <h4>{{ $plots->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="customer-stat-card primary">
                    <div class="customer-stat-icon"><i class="bi bi-bank"></i></div>
                    <div>
                        <small>Total Cost</small>
                        <h4>&#8377;{{ number_format($totalCost, 0) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="customer-stat-card success">
                    <div class="customer-stat-icon"><i class="bi bi-wallet2"></i></div>
                    <div>
                        <small>Paid</small>
                        <h4>&#8377;{{ number_format($totalPaid, 0) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="customer-stat-card danger">
                    <div class="customer-stat-icon"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <small>Due</small>
                        <h4>&#8377;{{ number_format($totalDue, 0) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            @forelse($bookings as $booking)
                @php
                    $statusLabel = match ($booking->plot_status) {
                        'registry' => 'Registered',
                        'hold' => 'On Hold',
                        'mixed' => 'Mixed Status',
                        'booked' => 'Booked',
                        default => ucfirst($booking->latest_booking_status ?? 'Booked'),
                    };
                    $statusClass = match ($statusLabel) {
                        'Registered' => 'bg-primary-subtle text-primary border border-primary-subtle',
                        'On Hold', 'Hold', 'Mixed Status' => 'bg-warning-subtle text-warning border border-warning-subtle',
                        default => 'bg-success-subtle text-success border border-success-subtle',
                    };
                    $planLabel = match ($booking->plan_type) {
                        'emi_plan' => 'EMI Plan',
                        'mixed' => 'Mixed Plan',
                        default => 'Full Payment',
                    };
                    $modalId = 'plotBookingModal' . $booking->id;
                @endphp

                <div class="col-xl-4 col-lg-6">
                    <div class="customer-plot-booking-card">
                        <div class="customer-plot-booking-head">
                            <div>
                                <small>Booking Code</small>
                                <h5>{{ $booking->booking_code }}</h5>
                            </div>
                            <span class="badge rounded-pill px-3 py-2 {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>

                        <div class="customer-plot-visual">
                            <div>
                                <small>{{ $booking->plot_count > 1 ? 'Plots' : 'Plot No' }}</small>
                                <strong>{{ $booking->plot_numbers }}</strong>
                            </div>
                            <i class="bi bi-houses"></i>
                        </div>

                        <div class="customer-plot-booking-body">
                            <div class="customer-receipt-line">
                                <span>Project</span>
                                <strong>{{ $booking->project_name }}</strong>
                            </div>
                            <div class="customer-receipt-line">
                                <span>Block</span>
                                <strong>{{ $booking->block_name }}</strong>
                            </div>
                            <div class="customer-receipt-line">
                                <span>Plan</span>
                                <strong>{{ $planLabel }}</strong>
                            </div>
                            <div class="customer-receipt-line">
                                <span>Total Area</span>
                                <strong>{{ number_format($booking->total_area, 2) }} Sq.Ft.</strong>
                            </div>

                            <div class="customer-progress mt-3">
                                <span style="width: {{ $booking->paid_percent }}%"></span>
                            </div>
                            <div class="d-flex justify-content-between mt-2 customer-plot-progress-text">
                                <span>{{ $booking->paid_percent }}% Paid</span>
                                <span>&#8377;{{ number_format($booking->due_amount_value, 2) }} Due</span>
                            </div>

                            <div class="row g-2 mt-3">
                                <div class="col-6">
                                    <div class="customer-plot-mini-stat">
                                        <small>Total Cost</small>
                                        <strong>&#8377;{{ number_format($booking->total_cost_amount, 2) }}</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="customer-plot-mini-stat">
                                        <small>Paid</small>
                                        <strong class="text-success">&#8377;{{ number_format($booking->confirmed_paid_amount, 2) }}</strong>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-success rounded-pill px-4 w-100 mt-4"
                                data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                                <i class="bi bi-eye me-1"></i> View Details
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="customer-empty-state">
                        <i class="bi bi-house-x fs-1 text-muted"></i>
                        <h5 class="mt-3">No Plot Booking Found</h5>
                        <p class="text-muted mb-0">You do not have any plot bookings yet.</p>
                    </div>
                </div>
            @endforelse
        </div>

        @foreach($bookings as $booking)
            @php
                $modalId = 'plotBookingModal' . $booking->id;
            @endphp

            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 customer-receipt-modal">
                        <div class="customer-receipt-head">
                            <div class="customer-receipt-title">
                                <div class="customer-receipt-icon">
                                    <i class="bi bi-pin-map"></i>
                                </div>
                                <div>
                                    <span>Plot Booking Detail</span>
                                    <h5>{{ $booking->booking_code }}</h5>
                                    <small>{{ $booking->plot_count }} Plot{{ $booking->plot_count === 1 ? '' : 's' }}</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-0">
                            <div class="customer-receipt-summary">
                                <div>
                                    <small>Total Plot Cost</small>
                                    <strong>&#8377;{{ number_format($booking->total_cost_amount, 2) }}</strong>
                                </div>
                                <div>
                                    <small>Confirmed Paid</small>
                                    <strong>&#8377;{{ number_format($booking->confirmed_paid_amount, 2) }}</strong>
                                </div>
                                <div>
                                    <small>Due Amount</small>
                                    <strong>&#8377;{{ number_format($booking->due_amount_value, 2) }}</strong>
                                </div>
                                <div>
                                    <small>Payments</small>
                                    <strong>{{ $booking->payment_count }}</strong>
                                </div>
                            </div>

                            <div class="customer-receipt-body">
                                <div class="customer-receipt-panel">
                                    <div class="customer-receipt-panel-title">
                                        <i class="bi bi-map"></i>
                                        <span>Plot Information</span>
                                    </div>

                                    <div class="table-responsive transaction-mini-table">
                                        <table class="table table-hover align-middle mb-0 transaction-table">
                                            <thead>
                                                <tr>
                                                    <th>Plot</th>
                                                    <th>Project / Block</th>
                                                    <th>Area</th>
                                                    <th>Rate</th>
                                                    <th>Total Cost</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($booking->plots as $plot)
                                                    @php
                                                        $plotNumber = $plot->plotDetail?->plot_number ?? $plot->plotDetail?->plot_no ?? 'N/A';
                                                        $plotStatus = ucfirst($plot->plotDetail?->status ?? 'booked');
                                                    @endphp
                                                    <tr>
                                                        <td class="fw-bold text-success">{{ $plotNumber }}</td>
                                                        <td>
                                                            <strong>{{ $plot->project?->name ?? 'N/A' }}</strong>
                                                            <small class="text-muted d-block">
                                                                Block {{ $plot->block?->block ?? $plot->block?->name ?? 'N/A' }}
                                                            </small>
                                                        </td>
                                                        <td>{{ number_format((float) ($plot->plot_area ?? $plot->plotDetail?->plot_area ?? 0), 2) }} Sq.Ft.</td>
                                                        <td>&#8377;{{ number_format((float) ($plot->plot_rate ?? 0), 2) }}</td>
                                                        <td class="fw-bold">&#8377;{{ number_format((float) ($plot->total_plot_cost ?? $plot->final_payable ?? 0), 2) }}</td>
                                                        <td>
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                                {{ $plotStatus }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="customer-receipt-panel mt-4">
                                    <div class="customer-receipt-panel-title">
                                        <i class="bi bi-wallet2"></i>
                                        <span>Payment Progress</span>
                                    </div>
                                    <div class="customer-progress mb-3">
                                        <span style="width: {{ $booking->paid_percent }}%"></span>
                                    </div>
                                    <div class="customer-receipt-line">
                                        <span>Paid Percentage</span>
                                        <strong>{{ $booking->paid_percent }}%</strong>
                                    </div>
                                    <div class="customer-receipt-line">
                                        <span>Hold Amount</span>
                                        <strong>&#8377;{{ number_format($booking->hold_amount, 2) }}</strong>
                                    </div>
                                    <div class="customer-receipt-line">
                                        <span>Latest Payment Status</span>
                                        <strong>{{ ucfirst($booking->latest_payment_status ?? 'N/A') }}</strong>
                                    </div>
                                    <div class="customer-receipt-line">
                                        <span>Booking Date</span>
                                        <strong>
                                            {{ $booking->booking_date
                                                ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y')
                                                : ($booking->created_at ? \Carbon\Carbon::parse($booking->created_at)->format('d M Y') : 'N/A') }}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                            <a href="{{ route('customer-panel.payment-history') }}" class="btn btn-success rounded-pill px-4">
                                View Payment History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
