@extends('layouts.app')

@section('content')
    <div class="container-fluid customer-panel-page customer-booking-history-page">
        <div class="customer-profile-hero mb-4">
            <div class="customer-profile-main">
                <div class="customer-avatar profile-avatar">
                    <i class="bi bi-journal-bookmark"></i>
                </div>
                <div>
                    <span class="customer-dashboard-kicker">Booking History</span>
                    <h3 class="mb-1">My Booking History</h3>
                    <p class="mb-0">All booking groups linked with your customer account.</p>
                </div>
            </div>

            <div class="customer-profile-meta">
                <span class="badge bg-white text-success border rounded-pill px-3 py-2">
                    {{ $bookings->count() }} Booking{{ $bookings->count() === 1 ? '' : 's' }}
                </span>
                <small>Customer ID: {{ $customer->customer_code ?? 'N/A' }}</small>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="customer-stat-card success">
                    <div class="customer-stat-icon"><i class="bi bi-house-check"></i></div>
                    <div>
                        <small>Booking Groups</small>
                        <h4>{{ $bookings->count() }}</h4>
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
                        <small>Confirmed Paid</small>
                        <h4>&#8377;{{ number_format($totalPaid, 0) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="customer-stat-card danger">
                    <div class="customer-stat-icon"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <small>Total Due</small>
                        <h4>&#8377;{{ number_format($totalDue, 0) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="customer-section-card">
            <div class="customer-section-header">
                <div>
                    <h5 class="mb-1">Booking Records</h5>
                    <p class="mb-0">Multiple plots booked under the same booking code are shown as one record.</p>
                </div>
            </div>

            <div class="customer-section-body">
                @if ($bookings->count())
                    <div class="table-responsive">
                        <table id="bookingHistoryTable" class="table table-hover align-middle nowrap w-100 customer-table booking-history-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Booking</th>
                                    <th>Project / Plots</th>
                                    <th>Total Area</th>
                                    <th>Total Cost</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $key => $booking)
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
                                        $modalId = 'bookingHistoryModal' . $booking->id;
                                    @endphp
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td style="min-width: 180px;">
                                            <strong class="d-block text-success">{{ $booking->booking_code }}</strong>
                                            <small class="text-muted">{{ $booking->payment_count }} payment record(s)</small>
                                        </td>
                                        <td style="min-width: 260px;">
                                            <strong class="d-block">{{ $booking->project_name }}</strong>
                                            <small class="text-muted d-block">
                                                Block {{ $booking->block_name }} / Plot {{ $booking->plot_numbers }}
                                            </small>
                                            @if ($booking->plot_count > 1)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle mt-1">
                                                    {{ $booking->plot_count }} Plots
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($booking->total_area, 2) }} Sq.Ft.</td>
                                        <td><strong>&#8377;{{ number_format($booking->total_cost_amount, 2) }}</strong></td>
                                        <td><strong class="text-success">&#8377;{{ number_format($booking->confirmed_paid_amount, 2) }}</strong></td>
                                        <td>
                                            <strong class="{{ $booking->due_amount_value > 0 ? 'text-danger' : 'text-success' }}">
                                                &#8377;{{ number_format($booking->due_amount_value, 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill px-3 py-2 {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3"
                                                data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                                                <i class="bi bi-eye me-1"></i> Details
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="customer-empty-state">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <h5 class="mt-3">No Booking History Found</h5>
                        <p class="text-muted mb-0">You do not have any booked plots yet.</p>
                    </div>
                @endif
            </div>
        </div>

        @foreach ($bookings as $booking)
            @php
                $modalId = 'bookingHistoryModal' . $booking->id;
            @endphp
            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 customer-receipt-modal">
                        <div class="customer-receipt-head">
                            <div class="customer-receipt-title">
                                <div class="customer-receipt-icon">
                                    <i class="bi bi-journal-bookmark"></i>
                                </div>
                                <div>
                                    <span>Booking Detail</span>
                                    <h5>{{ $booking->booking_code }}</h5>
                                    <small>{{ $booking->plot_count }} Plot{{ $booking->plot_count === 1 ? '' : 's' }}</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-0">
                            <div class="customer-receipt-summary">
                                <div>
                                    <small>Total Cost</small>
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
                                    <small>Paid Progress</small>
                                    <strong>{{ $booking->paid_percent }}%</strong>
                                </div>
                            </div>

                            <div class="customer-receipt-body">
                                <div class="customer-receipt-panel">
                                    <div class="customer-receipt-panel-title">
                                        <i class="bi bi-map"></i>
                                        <span>Plot Details</span>
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
                                                        <td>{{ $plotStatus }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="customer-receipt-panel mt-4">
                                    <div class="customer-receipt-panel-title">
                                        <i class="bi bi-wallet2"></i>
                                        <span>Payment Summary</span>
                                    </div>
                                    <div class="customer-progress mb-3">
                                        <span style="width: {{ $booking->paid_percent }}%"></span>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="customer-info-card">
                                                <small>Plan Type</small>
                                                <strong>
                                                    {{ $booking->plan_type === 'emi_plan' ? 'EMI Plan' : ($booking->plan_type === 'mixed' ? 'Mixed Plan' : 'Full Payment') }}
                                                </strong>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="customer-info-card">
                                                <small>Total Payments</small>
                                                <strong>{{ $booking->payment_count }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="customer-info-card">
                                                <small>Latest Payment Status</small>
                                                <strong>{{ ucfirst($booking->latest_payment_status ?? 'N/A') }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="customer-info-card">
                                                <small>Booking Date</small>
                                                <strong>
                                                    {{ $booking->booking_date
                                                        ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y')
                                                        : ($booking->created_at ? \Carbon\Carbon::parse($booking->created_at)->format('d M Y') : 'N/A') }}
                                                </strong>
                                            </div>
                                        </div>
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

@push('scripts')
    @if ($bookings->count())
        <script>
            $(document).ready(function() {
                $('#bookingHistoryTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    responsive: false,
                    scrollX: true,
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search booking..."
                    }
                });
            });
        </script>
    @endif
@endpush
