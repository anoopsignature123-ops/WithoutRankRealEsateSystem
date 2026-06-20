@extends('layouts.app')

@section('content')
    @php
        $primary = $customer->primaryDetail;
        $contact = $primary?->correspondenceDetail;
        $customerName = $primary?->name ?? $customer->customer_name ?? 'Customer';
        $initial = strtoupper(substr($customerName, 0, 1));
    @endphp

    <div class="container-fluid customer-panel-page customer-dashboard-page">
        <div class="customer-dashboard-top mb-4">
            <div class="customer-dashboard-welcome">
                <div class="customer-avatar dashboard-avatar">{{ $initial }}</div>
                <div>
                    <span class="customer-dashboard-kicker">Customer Panel</span>
                    <h3 class="mb-1">Welcome back, {{ $customerName }}</h3>
                    <p class="mb-0">
                        Customer ID: <strong>{{ $customer->customer_code ?? 'N/A' }}</strong>
                        @if ($contact?->mobile_number)
                            <span class="mx-2">|</span> {{ $contact->mobile_number }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="customer-dashboard-balance">
                <small>Total Plot Value</small>
                <strong>&#8377;{{ number_format($totalPlotCost, 2) }}</strong>
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
                        <small>Total Bookings</small>
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
                        <p class="mb-0">Open your important customer sections.</p>
                    </div>

                    <div class="customer-action-list">
                        <a href="{{ route('customer-panel.profile') }}" class="customer-action-item">
                            <i class="bi bi-person-circle"></i>
                            <span>
                                <strong>My Profile</strong>
                                <small>View personal and account details</small>
                            </span>
                        </a>
                        <a href="{{ route('customer-panel.my-plot-booking') }}" class="customer-action-item">
                            <i class="bi bi-house-check"></i>
                            <span>
                                <strong>My Plot Booking</strong>
                                <small>Check booked plot information</small>
                            </span>
                        </a>
                        <a href="{{ route('customer-panel.payment-history') }}" class="customer-action-item">
                            <i class="bi bi-wallet2"></i>
                            <span>
                                <strong>Payment History</strong>
                                <small>Track receipts and payment status</small>
                            </span>
                        </a>
                        <a href="{{ route('customer-panel.support') }}" class="customer-action-item">
                            <i class="bi bi-headset"></i>
                            <span>
                                <strong>Support</strong>
                                <small>Raise and track support tickets</small>
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="customer-section-card mb-4">
                    <div class="customer-section-header">
                        <div>
                            <h5 class="mb-1">Recent Plot Bookings</h5>
                            <p class="mb-0">Latest plot bookings linked with your account.</p>
                        </div>
                        <a href="{{ route('customer-panel.booking-history') }}" class="btn btn-success btn-sm rounded-pill px-3">
                            View History
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table customer-table dashboard-table mb-0">
                            <thead>
                                <tr>
                                    <th>Booking Code</th>
                                    <th>Project</th>
                                    <th>Plot No</th>
                                    <th>Total Cost</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestPlots as $plot)
                                    <tr>
                                        <td><strong>{{ $plot->booking_code ?? 'N/A' }}</strong></td>
                                        <td>{{ $plot->project?->name ?? 'N/A' }}</td>
                                        <td class="text-success fw-bold">
                                            {{ $plot->plotDetail?->plot_number ?? $plot->plotDetail?->plot_no ?? 'N/A' }}
                                        </td>
                                        <td>&#8377;{{ number_format($plot->total_plot_cost ?? $plot->final_payable ?? 0, 2) }}</td>
                                        <td>
                                            {{ $plot->booking_date
                                                ? \Carbon\Carbon::parse($plot->booking_date)->format('d M Y')
                                                : ($plot->created_at ? $plot->created_at->format('d M Y') : 'N/A') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No recent plot booking found.</td>
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
                        <a href="{{ route('customer-panel.payment-history') }}" class="btn btn-outline-success btn-sm rounded-pill px-3">
                            View Payments
                        </a>
                    </div>

                    <div class="customer-payment-list">
                        @forelse ($latestPayments as $payment)
                            <div class="customer-payment-item">
                                <div>
                                    <strong>{{ $payment->receipt_number ?? 'Receipt N/A' }}</strong>
                                    <small>
                                        {{ $payment->payment_date
                                            ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y')
                                            : ($payment->created_at ? $payment->created_at->format('d M Y') : 'N/A') }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <strong class="text-success">&#8377;{{ number_format($payment->paid_amount ?? $payment->booking_amount ?? 0, 2) }}</strong>
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
