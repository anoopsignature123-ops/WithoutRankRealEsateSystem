@extends('layouts.app')

@section('content')
    @php
        $primary = $customer->primaryDetail;
        $contact = $primary?->correspondenceDetail;

        $customerName = $primary?->name ?? $customer->customer_name ?? 'Customer';
        $email = $contact?->email ?? 'N/A';
        $mobile = $contact?->mobile_number ? '+91 ' . $contact->mobile_number : 'N/A';
        $address = $primary?->permanent_address ?? 'N/A';
        $initial = strtoupper(substr($customerName, 0, 1));
    @endphp

    <div class="container-fluid customer-panel-page customer-profile-page">
        <div class="customer-profile-hero mb-4">
            <div class="customer-profile-main">
                <div class="customer-avatar profile-avatar">{{ $initial }}</div>
                <div>
                    <span class="customer-dashboard-kicker">My Profile</span>
                    <h3 class="mb-1">{{ $customerName }}</h3>
                    <p class="mb-0">
                        Customer Code: <strong>{{ $customer->customer_code ?? 'N/A' }}</strong>
                    </p>
                </div>
            </div>

            <div class="customer-profile-meta">
                <span class="badge bg-white text-success border rounded-pill px-3 py-2">
                    {{ ucwords(str_replace('_', ' ', $customer->customer_type ?? 'Customer')) }}
                </span>
                <small>Joined {{ $customer->created_at?->format('d M Y') ?? 'N/A' }}</small>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-4">
                <div class="customer-section-card mb-4">
                    <div class="customer-section-header d-block">
                        <h5 class="mb-1">Contact Details</h5>
                        <p class="mb-0">Basic customer contact information.</p>
                    </div>

                    <div class="customer-profile-list">
                        <div class="customer-profile-list-item">
                            <i class="bi bi-person"></i>
                            <span>
                                <small>Name</small>
                                <strong>{{ $customerName }}</strong>
                            </span>
                        </div>
                        <div class="customer-profile-list-item">
                            <i class="bi bi-phone"></i>
                            <span>
                                <small>Mobile</small>
                                <strong>{{ $mobile }}</strong>
                            </span>
                        </div>
                        <div class="customer-profile-list-item">
                            <i class="bi bi-envelope"></i>
                            <span>
                                <small>Email</small>
                                <strong>{{ $email }}</strong>
                            </span>
                        </div>
                        <div class="customer-profile-list-item">
                            <i class="bi bi-geo-alt"></i>
                            <span>
                                <small>Address</small>
                                <strong>{{ $address }}</strong>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="customer-section-card">
                    <div class="customer-section-header d-block">
                        <h5 class="mb-1">Quick Links</h5>
                        <p class="mb-0">Jump to your customer records.</p>
                    </div>

                    <div class="customer-action-list">
                        <a href="{{ route('customer-panel.my-plot-booking') }}" class="customer-action-item">
                            <i class="bi bi-house-check"></i>
                            <span>
                                <strong>My Plot Booking</strong>
                                <small>View booked plots</small>
                            </span>
                        </a>
                        <a href="{{ route('customer-panel.payment-history') }}" class="customer-action-item">
                            <i class="bi bi-wallet2"></i>
                            <span>
                                <strong>Payment History</strong>
                                <small>View receipts and status</small>
                            </span>
                        </a>
                        <a href="{{ route('customer-panel.support') }}" class="customer-action-item">
                            <i class="bi bi-headset"></i>
                            <span>
                                <strong>Support</strong>
                                <small>Raise support ticket</small>
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="row g-3 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="customer-stat-card success">
                            <div class="customer-stat-icon"><i class="bi bi-house-check"></i></div>
                            <div>
                                <small>Bookings</small>
                                <h4>{{ $totalBooking }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="customer-stat-card primary">
                            <div class="customer-stat-icon"><i class="bi bi-bank"></i></div>
                            <div>
                                <small>Plot Cost</small>
                                <h4>&#8377;{{ number_format($totalPlotCost, 0) }}</h4>
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
                                <h4>&#8377;{{ number_format($dueAmount, 0) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="customer-section-card mb-4">
                    <div class="customer-section-header">
                        <div>
                            <h5 class="mb-1">Account Summary</h5>
                            <p class="mb-0">Payment progress and account totals.</p>
                        </div>
                        <span class="badge bg-success rounded-pill px-3 py-2">{{ $paidPercent }}% Paid</span>
                    </div>

                    <div class="customer-section-body">
                        <div class="customer-progress mb-3">
                            <span style="width: {{ $paidPercent }}%"></span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="customer-info-card">
                                    <small>Total Plot Cost</small>
                                    <strong>&#8377;{{ number_format($totalPlotCost, 2) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="customer-info-card">
                                    <small>Total Paid Amount</small>
                                    <strong class="text-success">&#8377;{{ number_format($totalPaid, 2) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="customer-info-card">
                                    <small>Remaining Due Amount</small>
                                    <strong class="text-danger">&#8377;{{ number_format($dueAmount, 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="customer-section-card h-100">
                            <div class="customer-section-header d-block">
                                <h5 class="mb-1">Latest Plot</h5>
                                <p class="mb-0">Most recent plot booking.</p>
                            </div>
                            <div class="customer-section-body">
                                @if ($latestPlot)
                                    <div class="customer-profile-mini">
                                        <span>Booking Code</span>
                                        <strong>{{ $latestPlot->booking_code ?? 'N/A' }}</strong>
                                    </div>
                                    <div class="customer-profile-mini">
                                        <span>Project</span>
                                        <strong>{{ $latestPlot->project?->name ?? 'N/A' }}</strong>
                                    </div>
                                    <div class="customer-profile-mini">
                                        <span>Plot No</span>
                                        <strong class="text-success">
                                            {{ $latestPlot->plotDetail?->plot_number ?? $latestPlot->plotDetail?->plot_no ?? 'N/A' }}
                                        </strong>
                                    </div>
                                    <a href="{{ route('customer-panel.my-plot-booking') }}" class="btn btn-outline-success rounded-pill px-4 mt-3">
                                        View Plot Details
                                    </a>
                                @else
                                    <div class="text-center text-muted py-4">No plot booking found.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="customer-section-card h-100">
                            <div class="customer-section-header d-block">
                                <h5 class="mb-1">Latest Payment</h5>
                                <p class="mb-0">Most recent payment record.</p>
                            </div>
                            <div class="customer-section-body">
                                @if ($latestPayment)
                                    <div class="customer-profile-mini">
                                        <span>Receipt No</span>
                                        <strong>{{ $latestPayment->receipt_number ?? 'N/A' }}</strong>
                                    </div>
                                    <div class="customer-profile-mini">
                                        <span>Amount</span>
                                        <strong class="text-success">
                                            &#8377;{{ number_format($latestPayment->paid_amount ?? $latestPayment->booking_amount ?? 0, 2) }}
                                        </strong>
                                    </div>
                                    <div class="customer-profile-mini">
                                        <span>Status</span>
                                        <strong>{{ ucfirst($latestPayment->payment_status ?? 'N/A') }}</strong>
                                    </div>
                                    <a href="{{ route('customer-panel.payment-history') }}" class="btn btn-outline-success rounded-pill px-4 mt-3">
                                        View Payment History
                                    </a>
                                @else
                                    <div class="text-center text-muted py-4">No payment found.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
