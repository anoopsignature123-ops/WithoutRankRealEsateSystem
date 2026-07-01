@extends('layouts.app')

@section('content')
    @php
        $primary = $customer->primaryDetail;
        $contact = $primary?->correspondenceDetail;
        $customerName = $primary?->name ?? ($customer->customer_name ?? 'Customer');
        $initials = collect(explode(' ', trim($customerName)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('') ?: 'C';
    @endphp

    <div class="container-fluid customer-panel-page customer-dashboard-page">
        <div class="customer-dashboard-top mb-4">
            <div class="customer-dashboard-welcome">
                @if (!empty($customer->primaryDocument->profile_picture))
                    <img src="{{ getFileUrl($customer->primaryDocument->profile_picture) }}" alt="{{ $customerName }}"
                        class="dashboard-avatar rounded-circle border">
                @else
                    <div class="customer-avatar dashboard-avatar">{{ $initials }}</div>
                @endif

                <div class="flex-grow-1">
                    <span class="customer-dashboard-kicker">Customer Panel</span>
                    <h3 class="mb-1">Welcome back, {{ $customerName }}</h3>
                    <p class="mb-0">
                        Customer ID: <strong>{{ $customer->customer_code ?? 'N/A' }}</strong>
                        @if ($contact?->mobile_number)
                            <span class="mx-2">|</span>{{ $contact->mobile_number }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="customer-dashboard-balance">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <small>Total Plot Value</small>
                        <strong>&#8377;{{ number_format($totalPlotCost, 2) }}</strong>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        {{ $bookingGroups->count() }} Booking{{ $bookingGroups->count() === 1 ? '' : 's' }}
                    </span>
                </div>

                <div class="customer-progress mt-3">
                    <span style="width: {{ $paidPercent }}%"></span>
                </div>

                <div class="d-flex justify-content-between mt-2">
                    <span>{{ $paidPercent }}% Paid</span>
                    <span>&#8377;{{ number_format($dueAmount, 2) }} Due</span>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="customer-stat-card success">
                    <div class="customer-stat-icon"><i class="bi bi-house-check"></i></div>
                    <div>
                        <small>Booked Plots</small>
                        <h4>{{ $totalBooking }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="customer-stat-card primary">
                    <div class="customer-stat-icon"><i class="bi bi-wallet2"></i></div>
                    <div>
                        <small>Total Paid</small>
                        <h4>&#8377;{{ number_format($totalPaid, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="customer-stat-card danger">
                    <div class="customer-stat-icon"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <small>Due Amount</small>
                        <h4>&#8377;{{ number_format($dueAmount, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="customer-stat-card warning">
                    <div class="customer-stat-icon"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <small>Pending Payments</small>
                        <h4>{{ $pendingPayments }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-4">
                <div class="customer-section-card h-100">
                    <div class="customer-section-header d-block">
                        <h5 class="mb-1">Quick Actions</h5>
                        <p class="mb-0">Open important customer sections quickly.</p>
                    </div>

                    <div class="customer-action-list">
                        <a href="{{ route('customer-panel.profile') }}" class="customer-action-item">
                            <i class="bi bi-person-circle"></i>
                            <span>
                                <strong>My Profile</strong>
                                <small>Personal and account details</small>
                            </span>
                        </a>

                        <a href="{{ route('customer-panel.my-plot-booking') }}" class="customer-action-item">
                            <i class="bi bi-pin-map"></i>
                            <span>
                                <strong>My Plot Booking</strong>
                                <small>Booked plots and cost details</small>
                            </span>
                        </a>

                        <a href="{{ route('customer-panel.payment-history') }}" class="customer-action-item">
                            <i class="bi bi-receipt"></i>
                            <span>
                                <strong>Payment History</strong>
                                <small>Receipts, status and downloads</small>
                            </span>
                        </a>

                        <a href="{{ route('customer-panel.support') }}" class="customer-action-item">
                            <i class="bi bi-headset"></i>
                            <span>
                                <strong>Support</strong>
                                <small>Raise and track support requests</small>
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="customer-section-card mb-4">
                    <div class="customer-section-header">
                        <div>
                            <h5 class="mb-1">Recent Bookings</h5>
                            <p class="mb-0">Grouped booking summary with multiple plot support.</p>
                        </div>
                        <a href="{{ route('customer-panel.booking-history') }}"
                            class="btn btn-success btn-sm rounded-pill px-3">
                            View History
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table customer-table dashboard-table customer-dashboard-booking-table mb-0">
                            <thead>
                                <tr>
                                    <th>Booking</th>
                                    <th>Project / Plots</th>
                                    <th>Total Cost</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Plan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestBookings as $booking)
                                    <tr>
                                        <td>
                                            <strong class="text-success">{{ $booking['booking_code'] }}</strong>
                                            <small class="text-muted d-block">
                                                {{ $booking['booking_date']
                                                    ? \Carbon\Carbon::parse($booking['booking_date'])->format('d M Y')
                                                    : ($booking['created_at']
                                                        ? \Carbon\Carbon::parse($booking['created_at'])->format('d M Y')
                                                        : 'N/A') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $booking['project'] }}</div>
                                            <small class="text-muted d-block">
                                                Block {{ $booking['block'] }} / Plot {{ $booking['plots'] }}
                                            </small>
                                            @if ($booking['plot_count'] > 1)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle mt-1">
                                                    {{ $booking['plot_count'] }} Plots
                                                </span>
                                            @endif
                                        </td>
                                        <td class="fw-bold">&#8377;{{ number_format($booking['total_cost'], 2) }}</td>
                                        <td class="text-success fw-bold">&#8377;{{ number_format($booking['paid'], 2) }}</td>
                                        <td class="text-danger fw-bold">&#8377;{{ number_format($booking['due'], 2) }}</td>
                                        <td>
                                            @if ($booking['plan_type'] === 'emi_plan')
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">EMI</span>
                                            @elseif ($booking['plan_type'] === 'mixed')
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Mixed</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Full</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No booking found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="customer-section-card">
                    <div class="customer-section-header">
                        <div>
                            <h5 class="mb-1">Recent Payments</h5>
                            <p class="mb-0">Latest receipts and payment status.</p>
                        </div>
                        <a href="{{ route('customer-panel.payment-history') }}"
                            class="btn btn-outline-success btn-sm rounded-pill px-3">
                            View Payments
                        </a>
                    </div>

                    <div class="customer-payment-list">
                        @forelse ($latestPayments as $payment)
                            <div class="customer-payment-item">
                                <div>
                                    <strong>{{ $payment->receipt_number ?? 'Receipt N/A' }}</strong>
                                    <small>
                                        {{ $payment->created_at ? $payment->created_at->format('d M Y') : 'N/A' }}
                                        @if ($payment->plotSaleDetail?->plotDetail?->plot_number)
                                            | Plot {{ $payment->plotSaleDetail->plotDetail->plot_number }}
                                        @endif
                                    </small>
                                </div>
                                <div class="text-end">
                                    <strong class="text-success">
                                        &#8377;{{ number_format($payment->paid_amount ?? ($payment->booking_amount ?? 0), 2) }}
                                    </strong>
                                    <span class="badge rounded-pill bg-light text-dark border">
                                        {{ ucfirst($payment->payment_status ?? 'N/A') }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">No recent payment found.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
