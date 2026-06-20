@extends('layouts.app')

@section('content')
    <div class="container-fluid customer-panel-page customer-plot-booking-page">
        <div class="customer-profile-hero mb-4">
            <div class="customer-profile-main">
                <div class="customer-avatar profile-avatar">
                    <i class="bi bi-house-check"></i>
                </div>
                <div>
                    <span class="customer-dashboard-kicker">Plot Booking</span>
                    <h3 class="mb-1">My Plot Booking</h3>
                    <p class="mb-0">View booked plot details, payment progress and status.</p>
                </div>
            </div>

            <div class="customer-profile-meta">
                <span class="badge bg-white text-success border rounded-pill px-3 py-2">
                    Total Plots: {{ $plots->count() }}
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
            @forelse($plots as $plot)
                @php
                    $plotNumber = $plot->plotDetail?->plot_number ?? $plot->plotDetail?->plot_no ?? 'N/A';
                    $statusLabel = match ($plot->plotDetail?->status) {
                        'registry' => 'Registered',
                        'hold' => 'On Hold',
                        'booked' => 'Booked',
                        default => ucfirst($plot->latest_booking_status ?? 'Booked'),
                    };
                    $statusClass = match ($statusLabel) {
                        'Registered' => 'bg-primary-subtle text-primary border border-primary-subtle',
                        'On Hold', 'Hold' => 'bg-warning-subtle text-warning border border-warning-subtle',
                        default => 'bg-success-subtle text-success border border-success-subtle',
                    };
                    $modalId = 'plotBookingModal' . $plot->id;
                @endphp

                <div class="col-xl-4 col-lg-6">
                    <div class="customer-plot-booking-card">
                        <div class="customer-plot-booking-head">
                            <div>
                                <small>Booking Code</small>
                                <h5>{{ $plot->booking_code ?? 'N/A' }}</h5>
                            </div>
                            <span class="badge rounded-pill px-3 py-2 {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>

                        <div class="customer-plot-visual">
                            <div>
                                <small>Plot No</small>
                                <strong>{{ $plotNumber }}</strong>
                            </div>
                            <i class="bi bi-house-door"></i>
                        </div>

                        <div class="customer-plot-booking-body">
                            <div class="customer-receipt-line">
                                <span>Project</span>
                                <strong>{{ $plot->project?->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="customer-receipt-line">
                                <span>Block</span>
                                <strong>{{ $plot->block?->block ?? $plot->block?->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="customer-receipt-line">
                                <span>Area / Rate</span>
                                <strong>{{ $plot->plot_area ?? $plot->plotDetail?->plot_area ?? 'N/A' }} / &#8377;{{ number_format((float) ($plot->plot_rate ?? 0), 2) }}</strong>
                            </div>

                            <div class="customer-progress mt-3">
                                <span style="width: {{ $plot->paid_percent }}%"></span>
                            </div>
                            <div class="d-flex justify-content-between mt-2 customer-plot-progress-text">
                                <span>{{ $plot->paid_percent }}% Paid</span>
                                <span>&#8377;{{ number_format($plot->due_amount_value, 2) }} Due</span>
                            </div>

                            <div class="row g-2 mt-3">
                                <div class="col-6">
                                    <div class="customer-plot-mini-stat">
                                        <small>Total Cost</small>
                                        <strong>&#8377;{{ number_format($plot->total_cost_amount, 2) }}</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="customer-plot-mini-stat">
                                        <small>Paid</small>
                                        <strong class="text-success">&#8377;{{ number_format($plot->confirmed_paid_amount, 2) }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="button" class="btn btn-success rounded-pill px-4 w-100"
                                    data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                                    <i class="bi bi-eye me-1"></i> View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="customer-empty-state">
                        <i class="bi bi-house-x fs-1 text-muted"></i>
                        <h5 class="mt-3">No Plot Booking Found</h5>
                        <p class="text-muted mb-0">You don't have any plot bookings yet.</p>
                    </div>
                </div>
            @endforelse
        </div>

        @foreach($plots as $plot)
            @php
                $plotNumber = $plot->plotDetail?->plot_number ?? $plot->plotDetail?->plot_no ?? 'N/A';
                $modalId = 'plotBookingModal' . $plot->id;
            @endphp

            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 customer-receipt-modal">
                        <div class="customer-receipt-head">
                            <div class="customer-receipt-title">
                                <div class="customer-receipt-icon">
                                    <i class="bi bi-house-check"></i>
                                </div>
                                <div>
                                    <span>Plot Booking Detail</span>
                                    <h5>{{ $plot->booking_code ?? 'N/A' }}</h5>
                                    <small>Plot {{ $plotNumber }}</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-0">
                            <div class="customer-receipt-summary">
                                <div>
                                    <small>Total Plot Cost</small>
                                    <strong>&#8377;{{ number_format($plot->total_cost_amount, 2) }}</strong>
                                </div>
                                <div>
                                    <small>Confirmed Paid</small>
                                    <strong>&#8377;{{ number_format($plot->confirmed_paid_amount, 2) }}</strong>
                                </div>
                                <div>
                                    <small>Due Amount</small>
                                    <strong>&#8377;{{ number_format($plot->due_amount_value, 2) }}</strong>
                                </div>
                                <div>
                                    <small>Payments</small>
                                    <strong>{{ $plot->payment_count }}</strong>
                                </div>
                            </div>

                            <div class="customer-receipt-body">
                                <div class="customer-receipt-panel">
                                    <div class="customer-receipt-panel-title">
                                        <i class="bi bi-map"></i>
                                        <span>Plot Information</span>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="customer-info-card">
                                                <small>Project</small>
                                                <strong>{{ $plot->project?->name ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="customer-info-card">
                                                <small>Block</small>
                                                <strong>{{ $plot->block?->block ?? $plot->block?->name ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="customer-info-card">
                                                <small>Plot Number</small>
                                                <strong>{{ $plotNumber }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="customer-info-card">
                                                <small>Booking Date</small>
                                                <strong>
                                                    {{ $plot->booking_date
                                                        ? \Carbon\Carbon::parse($plot->booking_date)->format('d M Y')
                                                        : ($plot->created_at ? $plot->created_at->format('d M Y') : 'N/A') }}
                                                </strong>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="customer-info-card">
                                                <small>Plot Area</small>
                                                <strong>{{ $plot->plot_area ?? $plot->plotDetail?->plot_area ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="customer-info-card">
                                                <small>Plot Rate</small>
                                                <strong>&#8377;{{ number_format((float) ($plot->plot_rate ?? 0), 2) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="customer-receipt-panel mt-4">
                                    <div class="customer-receipt-panel-title">
                                        <i class="bi bi-wallet2"></i>
                                        <span>Payment Progress</span>
                                    </div>
                                    <div class="customer-progress mb-3">
                                        <span style="width: {{ $plot->paid_percent }}%"></span>
                                    </div>
                                    <div class="customer-receipt-line">
                                        <span>Paid Percentage</span>
                                        <strong>{{ $plot->paid_percent }}%</strong>
                                    </div>
                                    <div class="customer-receipt-line">
                                        <span>Hold Amount</span>
                                        <strong>&#8377;{{ number_format($plot->hold_amount, 2) }}</strong>
                                    </div>
                                    <div class="customer-receipt-line">
                                        <span>Latest Payment Status</span>
                                        <strong>{{ ucfirst($plot->latest_payment_status ?? 'N/A') }}</strong>
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
