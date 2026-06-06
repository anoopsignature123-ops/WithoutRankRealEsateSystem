@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@section('content')
    <div class="container-fluid px-4 py-4" style="background-color: #f4f6f9; min-height: 100vh;">
        <div class="card border-0 rounded-4 mb-4 overflow-hidden position-relative shadow-sm"
            style="background-image: url('https://images.unsplash.com/photo-1497366754035-f200968a6e72?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'); background-size: cover; background-position: center;">

            <div class="position-absolute w-100 h-100"
                style="background: linear-gradient(135deg, rgba(48, 80, 58, 0.95) 0%, rgba(45, 146, 121, 0.9) 100%);">
            </div>

            <div class="position-absolute end-0 top-0 opacity-10 p-3">
                <i class="bi bi-person-badge" style="font-size: 8rem; color: #ffffff;"></i>
            </div>

            <div class="card-body p-4 position-relative z-index-1">
                <div class="row align-items-center">
                    <div class="col-lg-5 d-flex align-items-center gap-4">
                        <div class="shadow-lg rounded-4 d-flex align-items-center justify-content-center bg-white border border-3 border-white overflow-hidden flex-shrink-0"
                            style="width: 90px; height: 90px;">
                            @if ($associate->photo)
                                <img src="{{ getFileUrl($associate->photo) }}" alt="{{ $associate->associate_name }}"
                                    class="w-100 h-100 object-fit-cover">
                            @else
                                <span
                                    class="fs-2 fw-bold text-success">{{ strtoupper(substr($associate->associate_name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="text-white">
                            <span class="badge bg-white text-success px-3 py-1 rounded-pill fw-bold mb-2 text-uppercase"
                                style="font-size: 0.7rem; letter-spacing: 1px;">
                                Active Associate
                            </span>
                            <h3 class="fw-bold mb-0">{{ $associate->associate_name }}</h3>
                            <p class="mb-0 opacity-75">ID: <span
                                    class="fw-semibold">{{ $associate->associate_id ?? 'N/A' }}</span></p>
                        </div>
                    </div>

                    <div class="col-lg-7 mt-4 mt-lg-0">
                        <div class="row g-3">
                            @php
                                $details = [
                                    [
                                        'title' => 'Joining Date',
                                        'value' => $associate->created_at?->format('d M Y'),
                                        'icon' => 'bi-calendar-check',
                                    ],
                                    [
                                        'title' => 'Sponsor',
                                        'value' => $associate->sponsor->associate_name ?? 'Direct',
                                        'icon' => 'bi-people-fill',
                                    ],
                                    [
                                        'title' => 'Rank',
                                        'value' => $associate->rank->designation ?? 'N/A',
                                        'icon' => 'bi-award-fill',
                                    ],
                                ];
                            @endphp
                            @foreach ($details as $item)
                                <div class="col-md-4">
                                    <div class="p-3 rounded-4 h-100 text-white"
                                        style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <small class="fw-medium opacity-75">{{ $item['title'] }}</small>
                                            <i class="bi {{ $item['icon'] }}"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0">{{ $item['value'] }}</h6>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div> 
        <div class="row g-4 mb-4">
            {{-- My Direct --}}
            <div class="col-xl-4 col-md-6">
                <a href="{{ route('associate-panel.my-direct') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold">My Direct</small>
                                    <h4 class="fw-bold text-dark mb-0 mt-2">{{ $data['direct_count'] }}</h4>
                                </div>
                                <div class="bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center"
                                    style="width:56px;height:56px;">
                                    <i class="bi bi-person-plus-fill fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- My Team --}}
            <div class="col-xl-4 col-md-6">
                <a href="{{ route('associate-panel.my-tree') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold">My Team</small>
                                    <h4 class="fw-bold text-dark mb-0 mt-2">{{ $data['team_count'] }}</h4>
                                </div>
                                <div class="bg-success bg-opacity-10 text-success rounded-4 d-flex align-items-center justify-content-center"
                                    style="width:56px;height:56px;">
                                    <i class="bi bi-people-fill fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Self Business --}}
            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted text-uppercase fw-semibold">Self Business</small>
                                <h4 class="fw-bold text-dark mb-0 mt-2">
                                    ₹{{ number_format($data['total_business'], 2) }}
                                </h4>
                            </div>
                            <div class="bg-warning bg-opacity-10 text-warning rounded-4 d-flex align-items-center justify-content-center"
                                style="width:56px;height:56px;">
                                <i class="bi bi-currency-rupee fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Self Commission --}}
            <div class="col-xl-4 col-md-6">
                <a href="{{ route('associate-panel.payout-details', ['commission_type' => 'self']) }}"
                    class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold">Self Commission</small>
                                    <h4 class="fw-bold text-success mb-0 mt-2">
                                        ₹{{ number_format($payoutStats['self_commission'], 2) }}
                                    </h4>
                                </div>
                                <div class="bg-success bg-opacity-10 text-success rounded-4 d-flex align-items-center justify-content-center"
                                    style="width:56px;height:56px;">
                                    <i class="bi bi-person-check-fill fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Team Commission --}}
            <div class="col-xl-4 col-md-6">
                <a href="{{ route('associate-panel.payout-details', ['commission_type' => 'team']) }}"
                    class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold">Team Commission</small>
                                    <h4 class="fw-bold text-primary mb-0 mt-2">
                                        ₹{{ number_format($payoutStats['team_commission'], 2) }}
                                    </h4>
                                </div>
                                <div class="bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center"
                                    style="width:56px;height:56px;">
                                    <i class="bi bi-diagram-3-fill fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Total Commission --}}
            <div class="col-xl-4 col-md-6">
                <a href="{{ route('associate-panel.payout-details') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted text-uppercase fw-semibold">Total Commission</small>
                                    <h4 class="fw-bold text-warning mb-0 mt-2">
                                        ₹{{ number_format($payoutStats['total_payout'], 2) }}
                                    </h4>
                                </div>
                                <div class="bg-warning bg-opacity-10 text-warning rounded-4 d-flex align-items-center justify-content-center"
                                    style="width:56px;height:56px;">
                                    <i class="bi bi-wallet2 fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        </div>
        {{-- Chart Section --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm bg-white rounded-3 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">
                                    <i class="bi bi-bar-chart-fill text-theme-green me-2"></i>Business Comparison
                                </h5>
                                <small class="text-muted">Self vs Team business overview</small>
                            </div>
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="border rounded-3 px-3 py-2 bg-light">
                                    <small class="text-muted d-block">Self Confirmed</small>
                                    <span class="fw-bold text-success">
                                        ₹ {{ number_format($stats['self']['confirmed']) }}
                                    </span>
                                    <hr class="my-2">
                                    <small class="text-muted d-block">Self Pending</small>
                                    <span class="fw-bold text-danger">
                                        ₹ {{ number_format($stats['self']['pending']) }}
                                    </span>
                                </div>
                                <div class="border rounded-3 px-3 py-2 bg-light">
                                    <small class="text-muted d-block">Team Confirmed</small>
                                    <span class="fw-bold text-success">
                                        ₹ {{ number_format($stats['team']['confirmed']) }}
                                    </span>
                                    <hr class="my-2">
                                    <small class="text-muted d-block">Team Pending</small>
                                    <span class="fw-bold text-danger">
                                        ₹ {{ number_format($stats['team']['pending']) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 350px;">
                            <canvas id="businessStackedChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm bg-white rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">
                                    <i class="bi bi-pie-chart-fill text-theme-green me-2"></i>Business Breakdown
                                </h5>
                                <small class="text-muted">Confirmed vs Pending business</small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Total Business</small>
                                <h5 class="fw-bold text-dark mb-0">
                                    ₹ {{ number_format(($data['confirmed_sales'] ?? 0) + ($data['pending_sales'] ?? 0)) }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            {{-- Chart --}}
                            <div class="col-md-7">
                                <div style="height: 320px; position: relative;">
                                    <canvas id="businessDonutChart"></canvas>
                                </div>
                            </div>
                            {{-- Summary --}}
                            <div class="col-md-5">
                                <div class="d-flex flex-column gap-3">
                                    {{-- Confirmed --}}
                                    <div class="border rounded-4 p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted d-block mb-1">
                                                    Confirmed Business
                                                </small>
                                                <h5 class="fw-bold text-success mb-0">
                                                    ₹ {{ number_format($data['confirmed_sales'] ?? 0) }}
                                                </h5>
                                            </div>
                                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                                                style="width:50px;height:50px;">
                                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Pending --}}
                                    <div class="border rounded-4 p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted d-block mb-1">
                                                    Pending Business
                                                </small>
                                                <h5 class="fw-bold text-danger mb-0">
                                                    ₹ {{ number_format($data['pending_sales'] ?? 0) }}
                                                </h5>
                                            </div>
                                            <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center"
                                                style="width:50px;height:50px;">
                                                <i class="bi bi-clock-fill text-danger fs-4"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                        <h5 class="fw-bold mb-0 text-dark"><i
                                class="bi bi-receipt-cutoff text-theme-green me-2"></i>Recent
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
            const businessCtx = document.getElementById('businessStackedChart').getContext('2d');
            new Chart(businessCtx, {
                type: 'bar',
                data: {
                    labels: ['Self Business', 'Team Business'],
                    datasets: [{
                            label: 'Pending',
                            data: [
                                {{ $stats['self']['pending'] }},
                                {{ $stats['team']['pending'] }}
                            ],
                            backgroundColor: '#dc3545',
                            borderRadius: 8,
                            barThickness: 40
                        },
                        {
                            label: 'Confirmed',
                            data: [
                                {{ $stats['self']['confirmed'] }},
                                {{ $stats['team']['confirmed'] }}
                            ],
                            backgroundColor: '#198754',
                            borderRadius: 8,
                            barThickness: 40
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 13,
                                    weight: '600'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#212529',
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    let value = context.raw || 0;
                                    return context.dataset.label +
                                        ': ₹ ' + Number(value).toLocaleString('en-IN');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 13,
                                    weight: '600'
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹ ' + Number(value).toLocaleString('en-IN');
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

                        data: [

                            {{ $data['confirmed_sales'] ?? 0 }},

                            {{ $data['pending_sales'] ?? 0 }}

                        ],

                        backgroundColor: [

                            '#198754',
                            '#dc3545'

                        ],

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

                            labels: {

                                padding: 20,

                                usePointStyle: true,

                                pointStyle: 'circle',

                                font: {

                                    size: 13,
                                    weight: '600'

                                }

                            }

                        },

                        tooltip: {

                            callbacks: {

                                label: function(context) {

                                    return context.label +
                                        ': ₹ ' +
                                        Number(context.raw).toLocaleString('en-IN');

                                }

                            }

                        }

                    }

                }

            });
        });
    </script>
@endpush
