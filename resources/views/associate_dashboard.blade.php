@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@section('content')
    @php
        $associateName = $associate->associate_name ?? 'Associate';
        $initials = collect(explode(' ', trim($associateName)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('') ?: 'A';
        $totalBusiness = ($data['confirmed_sales'] ?? 0) + ($data['pending_sales'] ?? 0);
        $formatAmount = fn ($amount) => '&#8377;' . number_format((float) $amount, 2);
        $summaryCards = [
            [
                'label' => 'My Direct',
                'value' => number_format($data['direct_count'] ?? 0),
                'icon' => 'bi-person-plus-fill',
                'class' => 'success',
                'route' => route('associate-panel.my-direct'),
            ],
            [
                'label' => 'My Team',
                'value' => number_format($data['team_count'] ?? 0),
                'icon' => 'bi-people-fill',
                'class' => 'primary',
                'route' => route('associate-panel.my-tree'),
            ],
            [
                'label' => 'Self Business',
                'value' => $formatAmount($data['total_business'] ?? 0),
                'icon' => 'bi-graph-up-arrow',
                'class' => 'success',
                'route' => null,
            ],
            [
                'label' => 'Self Commission',
                'value' => $formatAmount($payoutStats['self_commission'] ?? 0),
                'icon' => 'bi-person-check-fill',
                'class' => 'success',
                'route' => route('associate-panel.payout-details', ['commission_type' => 'self']),
            ],
            [
                'label' => 'Team Commission',
                'value' => $formatAmount($payoutStats['team_commission'] ?? 0),
                'icon' => 'bi-diagram-3-fill',
                'class' => 'primary',
                'route' => route('associate-panel.payout-details', ['commission_type' => 'team']),
            ],
            [
                'label' => 'Total Commission',
                'value' => $formatAmount($payoutStats['total_payout'] ?? 0),
                'icon' => 'bi-wallet2',
                'class' => 'warning',
                'route' => route('associate-panel.payout-details'),
            ],
        ];
    @endphp

    <div class="container-fluid transaction-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon overflow-hidden">
                        @if ($associate->photo)
                            <img src="{{ getFileUrl($associate->photo) }}" alt="{{ $associateName }}"
                                class="w-100 h-100 object-fit-cover">
                        @else
                            {{ $initials }}
                        @endif
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Associate Panel</span>
                        <h3 class="fw-bold mb-1 text-dark">{{ $associateName }}</h3>
                        <p class="text-muted mb-0 small">
                            Associate ID: <strong>{{ $associate->associate_id ?? 'N/A' }}</strong>
                            <span class="mx-2">|</span>
                            Rank: <strong>{{ $associate->rank->designation ?? 'N/A' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <span class="transaction-count">
                        Sponsor: {{ $associate->sponsor->associate_name ?? 'Direct' }}
                    </span>
                    <span class="transaction-count">
                        Joined {{ $associate->created_at?->format('d M Y') ?? 'N/A' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @foreach ($summaryCards as $card)
                <div class="col-xl-4 col-md-6">
                    @if ($card['route'])
                        <a href="{{ $card['route'] }}" class="text-decoration-none">
                    @endif
                    <div class="customer-stat-card {{ $card['class'] }} h-100">
                        <div class="customer-stat-icon">
                            <i class="bi {{ $card['icon'] }}"></i>
                        </div>
                        <div>
                            <small>{{ $card['label'] }}</small>
                            <h4>{!! $card['value'] !!}</h4>
                        </div>
                    </div>
                    @if ($card['route'])
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-7">
                <div class="transaction-card h-100">
                    <div class="transaction-card-body">
                        <div class="transaction-section-title">
                            <div class="d-flex align-items-center gap-3">
                                <span class="transaction-section-title-icon"><i class="bi bi-bar-chart-fill"></i></span>
                                <div>
                                    <h5 class="fw-bold mb-1">Business Comparison</h5>
                                    <small class="text-muted">Self vs team confirmed and pending business.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="transaction-summary-box h-100">
                                    <small class="text-muted fw-bold text-uppercase">Self Confirmed</small>
                                    <h5 class="fw-bold text-success mb-2">{!! $formatAmount($stats['self']['confirmed'] ?? 0) !!}</h5>
                                    <small class="text-muted fw-bold text-uppercase">Self Pending</small>
                                    <h6 class="fw-bold text-danger mb-0">{!! $formatAmount($stats['self']['pending'] ?? 0) !!}</h6>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="transaction-summary-box h-100">
                                    <small class="text-muted fw-bold text-uppercase">Team Confirmed</small>
                                    <h5 class="fw-bold text-success mb-2">{!! $formatAmount($stats['team']['confirmed'] ?? 0) !!}</h5>
                                    <small class="text-muted fw-bold text-uppercase">Team Pending</small>
                                    <h6 class="fw-bold text-danger mb-0">{!! $formatAmount($stats['team']['pending'] ?? 0) !!}</h6>
                                </div>
                            </div>
                        </div>

                        <div style="height: 320px;">
                            <canvas id="businessStackedChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="transaction-card h-100">
                    <div class="transaction-card-body">
                        <div class="transaction-section-title">
                            <div class="d-flex align-items-center gap-3">
                                <span class="transaction-section-title-icon"><i class="bi bi-pie-chart-fill"></i></span>
                                <div>
                                    <h5 class="fw-bold mb-1">Business Breakdown</h5>
                                    <small class="text-muted">Confirmed vs pending business.</small>
                                </div>
                            </div>
                            <span class="transaction-count">{!! $formatAmount($totalBusiness) !!}</span>
                        </div>

                        <div style="height: 280px;">
                            <canvas id="businessDonutChart"></canvas>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-6">
                                <div class="transaction-summary-box h-100">
                                    <small class="text-muted fw-bold text-uppercase">Confirmed</small>
                                    <h5 class="fw-bold text-success mb-0">{!! $formatAmount($data['confirmed_sales'] ?? 0) !!}</h5>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="transaction-summary-box h-100">
                                    <small class="text-muted fw-bold text-uppercase">Pending</small>
                                    <h5 class="fw-bold text-danger mb-0">{!! $formatAmount($data['pending_sales'] ?? 0) !!}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="transaction-card mb-4">
            <div class="transaction-card-body">
                <div class="transaction-section-title">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-section-title-icon"><i class="bi bi-calendar-check"></i></span>
                        <div>
                            <h5 class="fw-bold mb-1">This Month Business</h5>
                            <small class="text-muted">Category wise pending, confirmed and total business.</small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive transaction-mini-table">
                    <table class="table table-hover align-middle mb-0 transaction-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Pending</th>
                                <th>Confirmed</th>
                                <th>Hold</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (['booking_fee' => 'Booking Amount', 'one_time' => 'One Time', 'emi_payment' => 'EMI Payment'] as $key => $label)
                                <tr>
                                    <td class="fw-bold">{{ $label }}</td>
                                    <td class="text-danger fw-bold">{!! $formatAmount($monthlyData[$key]['pending'] ?? 0) !!}</td>
                                    <td class="text-success fw-bold">{!! $formatAmount($monthlyData[$key]['confirmed'] ?? 0) !!}</td>
                                    <td class="text-warning fw-bold">{!! $formatAmount($monthlyData[$key]['hold'] ?? 0) !!}</td>
                                    <td class="fw-bold">{!! $formatAmount(($monthlyData[$key]['pending'] ?? 0) + ($monthlyData[$key]['confirmed'] ?? 0)) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- <div class="transaction-card transaction-history-card mb-4">
            <div class="transaction-history-head">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-section-title-icon"><i class="bi bi-receipt-cutoff"></i></span>
                    <div>
                        <h5 class="fw-bold mb-1">Recent Payment History</h5>
                        <small class="text-muted">Latest customer payments linked with your direct bookings.</small>
                    </div>
                </div>
                <span class="transaction-count">{{ $data['recent_ledgers']->count() }} Records</span>
            </div>

            <div class="transaction-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 transaction-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Booking / Plot</th>
                                <th>Mode</th>
                                <th>Type</th>
                                <th>Net Payable</th>
                                <th>Paid</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['recent_ledgers'] as $ledger)
                                @php
                                    $plotSale = $ledger->plotSaleDetail;
                                    $customer = $ledger->customerBooking;
                                    $paymentType = match ($ledger->transaction_category) {
                                        'booking_fee' => 'Booking Amount',
                                        'one_time' => 'One Time',
                                        'emi_payment' => 'EMI Payment',
                                        default => ucwords(str_replace('_', ' ', $ledger->transaction_category ?? $ledger->plan_type ?? 'Payment')),
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $ledger->created_at?->format('d M Y') ?? 'N/A' }}</td>
                                    <td>
                                        <strong>{{ $customer?->primaryDetail?->name ?? $customer?->customer_name ?? 'N/A' }}</strong>
                                        <small class="text-muted d-block">{{ $customer?->customer_code ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $plotSale?->booking_code ?? $customer?->booking_code ?? 'N/A' }}</strong>
                                        <small class="text-muted d-block">
                                            {{ $plotSale?->project?->name ?? 'N/A' }} /
                                            Plot {{ $plotSale?->plotDetail?->plot_number ?? 'N/A' }}
                                        </small>
                                    </td>
                                    <td>{{ strtoupper(str_replace('_', ' / ', $ledger->payment_mode ?? 'N/A')) }}</td>
                                    <td class="fw-bold">{{ $paymentType }}</td>
                                    <td class="fw-bold">{!! $formatAmount($ledger->net_payable_amount ?? 0) !!}</td>
                                    <td class="fw-bold text-success">{!! $formatAmount($ledger->paid_amount ?? $ledger->booking_amount ?? 0) !!}</td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            {{ ucfirst($ledger->payment_status ?? 'N/A') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div> --}}
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const currency = value => '\u20B9 ' + Number(value || 0).toLocaleString('en-IN');
            const businessCanvas = document.getElementById('businessStackedChart');
            const donutCanvas = document.getElementById('businessDonutChart');

            if (businessCanvas) {
                new Chart(businessCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['Self Business', 'Team Business'],
                        datasets: [{
                                label: 'Pending',
                                data: [{{ $stats['self']['pending'] ?? 0 }}, {{ $stats['team']['pending'] ?? 0 }}],
                                backgroundColor: '#dc3545',
                                borderRadius: 8,
                                barThickness: 38
                            },
                            {
                                label: 'Confirmed',
                                data: [{{ $stats['self']['confirmed'] ?? 0 }}, {{ $stats['team']['confirmed'] ?? 0 }}],
                                backgroundColor: '#198754',
                                borderRadius: 8,
                                barThickness: 38
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: context => `${context.dataset.label}: ${currency(context.raw)}`
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                ticks: { callback: value => currency(value) }
                            }
                        }
                    }
                });
            }

            if (donutCanvas) {
                new Chart(donutCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Confirmed', 'Pending'],
                        datasets: [{
                            data: [{{ $data['confirmed_sales'] ?? 0 }}, {{ $data['pending_sales'] ?? 0 }}],
                            backgroundColor: ['#198754', '#dc3545'],
                            hoverOffset: 8,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, pointStyle: 'circle' }
                            },
                            tooltip: {
                                callbacks: {
                                    label: context => `${context.label}: ${currency(context.raw)}`
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
