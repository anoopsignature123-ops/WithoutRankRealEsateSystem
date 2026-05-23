@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@section('content')
    <div class="container-fluid px-4 py-4" style="background-color: #f4f6f9; min-height: 100vh;">

        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap align-items-center gap-4">
                            <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle fw-bold fs-4"
                                style="width: 60px; height: 60px;">
                                {{ substr($associate->associate_name, 0, 2) }}
                            </div>
                            <div class="flex-grow-1">
                                <span class="badge bg-success-subtle text-success mb-1 px-2 py-1 text-uppercase fw-bold"
                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">Active Associate</span>
                                <h4 class="mb-0 fw-bold text-dark">{{ $associate->associate_name }}</h4>
                                <p class="text-muted mb-0">ID: {{ $associate->associate_id ?? 'N/A' }} | Manage your
                                    business workspace</p>
                            </div>
                            <div class="d-flex gap-4 border-start ps-4 ms-2">
                                @php
                                    $info = [
                                        'Joining Date' => $associate->created_at?->format('d M Y') ?? 'N/A',
                                        'Sponsor' => $associate->sponsor->associate_name ?? 'Direct',
                                        'Rank' => $associate->rank->designation ?? 'N/A',
                                    ];
                                @endphp
                                @foreach ($info as $label => $value)
                                    <div class="text-center">
                                        <p class="text-muted small mb-0" style="font-size: 0.80rem;">{{ $label }}</p>
                                        <p class="text-dark fw-bold mb-0 mt-1">{{ $value }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @foreach (['My Direct' => ['count' => $data['direct_count'], 'icon' => 'bi-person-plus-fill'], 'My Team' => ['count' => $data['team_count'], 'icon' => 'bi-people-fill'], 'Self Business' => ['count' => '₹' . number_format($data['total_business'], 2), 'icon' => 'bi-currency-rupee']] as $title => $item)
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm bg-white rounded-3 p-3 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-uppercase text-muted small fw-bold mb-1">{{ $title }}</p>
                                <h2 class="display-6 fw-bold text-dark mb-0">{{ $item['count'] }}</h2>
                            </div>
                            <div class="rounded-3 p-3 badge-theme-subtle"><i class="bi {{ $item['icon'] }} fs-3"></i></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm bg-white rounded-3 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0 text-dark"><i
                                class="bi bi-bar-chart-fill text-theme-green me-2"></i>Business Comparison</h5>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 250px;"><canvas id="businessStackedChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm bg-white rounded-3 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0 text-dark"><i
                                class="bi bi-pie-chart-fill text-theme-green me-2"></i>Business Breakdown</h5>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 250px;"><canvas id="businessDonutChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-white rounded-3 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-check text-theme-green me-2"></i>This
                            Month Business</h5>
                    </div>
                    <div class="card-body p-4 table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Category</th>
                                    <th>Pending</th>
                                    <th>Confirmed</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (['booking_fee' => 'Booking Amount', 'one_time' => 'One Time', 'emi_payment' => 'EMI Payment'] as $key => $label)
                                    <tr>
                                        <td class="fw-bold">{{ $label }}</td>
                                        <td>{{ number_format($monthlyData[$key]['pending'], 2) }}</td>
                                        <td>{{ number_format($monthlyData[$key]['confirmed'], 2) }}</td>
                                        <td class="fw-bold">
                                            {{ number_format($monthlyData[$key]['pending'] + $monthlyData[$key]['confirmed'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-white rounded-3">
                    <div class="card-header bg-transparent border-0 p-4">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt-cutoff text-theme-green me-2"></i>Recent
                            Payment History</h5>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover mb-0 text-sm">
                            <thead class="bg-light text-muted text-uppercase">
                                <tr>
                                    <th class="ps-4 py-3">Pay Date</th>
                                    <th class="py-3">Plot No</th>
                                    <th class="py-3">Payment Mode</th>
                                    <th class="py-3">Type</th>
                                    <th class="py-3">Payable Amount</th>
                                    <th class="py-3">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['recent_ledgers'] as $ledger)
                                    <tr>
                                        <td class="ps-4">{{ $ledger->created_at->format('d-m-Y') }}</td>
                                        <td>{{ $ledger->customerBooking->plotSaleDetail->plotDetail->plot_number ?? 'N/A' }}
                                        </td>
                                        <td>{{ $ledger->payment_mode }}</td>
                                        <td class="fw-bold">{{ ucfirst(str_replace('_', ' ', $ledger->plan_type)) }}</td>
                                        <td class="fw-bold text-success">
                                            ₹{{ number_format($ledger->net_payable_amount, 2) }}</td>
                                        <td class="fw-bold text-success">₹{{ number_format($ledger->booking_amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center p-4">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            new Chart(document.getElementById('businessStackedChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Self Business', 'Team Business'],
                    datasets: [{
                            label: 'Pending',
                            data: [{{ $stats['self']['pending'] }}, {{ $stats['team']['pending'] }}],
                            backgroundColor: '#dc3545',
                            borderRadius: 5
                        },
                        {
                            label: 'Confirmed',
                            data: [{{ $stats['self']['confirmed'] }},
                                {{ $stats['team']['confirmed'] }}
                            ],
                            backgroundColor: '#198754',
                            borderRadius: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                afterLabel: function(context) {
                                    let index = context.dataIndex;
                                    let pending = context.chart.data.datasets[0].data[index];
                                    let confirmed = context.chart.data.datasets[1].data[index];
                                    let total = parseFloat(pending) + parseFloat(confirmed);
                                    return 'Total: ₹' + total.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('businessDonutChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Confirmed', 'Pending'],
                    datasets: [{
                        data: [{{ $data['confirmed_sales'] ?? 0 }},
                            {{ $data['pending_sales'] ?? 0 }}
                        ],
                        backgroundColor: ['#0f8a53', '#e2e8f0'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '78%'
                }
            });
        });
    </script>
@endpush
