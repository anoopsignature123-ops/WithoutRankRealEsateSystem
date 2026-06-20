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
                    <p class="mb-0">All plot bookings linked with your customer account.</p>
                </div>
            </div>

            <div class="customer-profile-meta">
                <span class="badge bg-white text-success border rounded-pill px-3 py-2">
                    Total Bookings: {{ $bookings->count() }}
                </span>
                <small>Customer ID: {{ $customer->customer_code ?? 'N/A' }}</small>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="customer-stat-card success">
                    <div class="customer-stat-icon"><i class="bi bi-house-check"></i></div>
                    <div>
                        <small>Total Bookings</small>
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
                    <p class="mb-0">Confirmed paid amount excludes cheque/DD payments that are still on hold.</p>
                </div>
            </div>

            <div class="customer-section-body">
                @if ($bookings->count())
                    <div class="table-responsive">
                        <table id="bookingHistoryTable" class="table table-hover align-middle nowrap w-100 customer-table booking-history-table">
                            <thead>
                                <tr>
                                    <th>Sr.No</th>
                                    <th>Booking</th>
                                    <th>Project / Block</th>
                                    <th>Plot Details</th>
                                    <th>Total Cost</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Booking Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $key => $booking)
                                @php
                                    $plotStatus = $booking->plotDetail?->status;
                                    $statusLabel = match ($plotStatus) {
                                        'registry' => 'Registered',
                                        'hold' => 'On Hold',
                                        'booked' => 'Booked',
                                        default => ucfirst($booking->latest_booking_status ?? 'Booked'),
                                    };
                                    $statusClass = match ($statusLabel) {
                                        'Registered' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        'On Hold', 'Hold' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                        default => 'bg-success-subtle text-success border border-success-subtle',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td style="min-width: 170px;">
                                        <strong class="d-block">{{ $booking->booking_code ?? 'N/A' }}</strong>
                                        <small class="text-muted">{{ $booking->payment_count }} payment record(s)</small>
                                    </td>
                                    <td style="min-width: 220px;">
                                        <strong class="d-block">{{ $booking->project?->name ?? 'N/A' }}</strong>
                                        <small class="text-muted">
                                            Block: {{ $booking->block?->block ?? $booking->block?->name ?? 'N/A' }}
                                        </small>
                                    </td>
                                    <td style="min-width: 170px;">
                                        <strong class="text-success d-block">
                                            Plot {{ $booking->plotDetail?->plot_number ?? $booking->plotDetail?->plot_no ?? 'N/A' }}
                                        </strong>
                                        <small class="text-muted">
                                            Area: {{ $booking->plot_area ?? $booking->plotDetail?->plot_area ?? 'N/A' }}
                                            | Rate: &#8377;{{ number_format((float) ($booking->plot_rate ?? $booking->rate ?? 0), 2) }}
                                        </small>
                                    </td>
                                    <td>
                                        <strong>&#8377;{{ number_format($booking->total_cost_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            &#8377;{{ number_format($booking->confirmed_paid_amount, 2) }}
                                        </strong>
                                    </td>
                                    <td>
                                        <strong class="{{ $booking->due_amount_value > 0 ? 'text-danger' : 'text-success' }}">
                                            &#8377;{{ number_format($booking->due_amount_value, 2) }}
                                        </strong>
                                    </td>
                                    <td>
                                        {{ $booking->booking_date
                                            ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y')
                                            : ($booking->created_at ? $booking->created_at->format('d M Y') : 'N/A') }}
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill px-3 py-2 {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
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
