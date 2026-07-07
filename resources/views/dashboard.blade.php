@extends('layouts.app')
@push('title')
    Admin | Dashboard
@endpush
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/admin_dashborad.css') }}">
@endpush

@section('content')
    <div class="container-fluid dashboard-bg">

        {{-- Header Section --}}
        <div class="dashboard-welcome-card card border-0 rounded-4 mb-4 overflow-hidden position-relative shadow-sm">

            <div class="dashboard-welcome-overlay"></div>

            <div class="position-absolute end-0 top-0 opacity-10 p-3">
                <i class="bi bi-shield-check" style="font-size: 8rem; color: #ffffff;"></i>
            </div>

            <div class="card-body p-4 position-relative z-1">
                <div class="d-flex align-items-center gap-4 flex-wrap">

                    <div class="welcome-avatar-wrap">
                        <div class="welcome-profile overflow-hidden p-0">
                            {!! Auth::user()->profile_image
                                ? '<img src="' . getFileUrl(Auth::user()->profile_image) . '" alt="Profile" class="w-100 h-100 object-fit-cover">'
                                : '<span class="fs-2 fw-bold text-white">' . substr(Auth::user()->name ?? 'A', 0, 1) . '</span>' !!}
                        </div>
                    </div>

                    <div class="text-white">
                        <h3 class="fw-bold mb-1">
                            Welcome, {{ Auth::user()->name ?? 'Admin' }}
                        </h3>

                        <div class="d-flex align-items-center fw-medium text-white-75">
                            <span class="d-flex align-items-center">
                                <i class="bi bi-briefcase me-2"></i>
                                Managing Business Operations
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Cards Section --}}
        {{-- Cards Section --}}
        <div class="row g-4 mb-4">
            @php
                $cards = [
                    [
                        'title' => 'Total Projects',
                        'value' => $projectCount ?? 0,
                        'icon' => 'bi-buildings-fill',
                        'color' => 'primary',
                        'route' => route('projects.index'),
                    ],
                    [
                        'title' => 'Total Plots',
                        'value' => $totalPlot ?? 0,
                        'icon' => 'bi-grid-1x2-fill',
                        'color' => 'success',
                        'route' => route('plot-details.index'),
                    ],
                    [
                        'title' => 'Total Customers',
                        'value' => $totalCustomer ?? 0,
                        'icon' => 'bi-people-fill',
                        'color' => 'info',
                        'route' => route('customer-booking.index'),
                    ],
                    [
                        'title' => 'Total Associates',
                        'value' => $totalAssociate ?? 0,
                        'icon' => 'bi-person-badge-fill',
                        'color' => 'warning',
                        'route' => route('associate.index'),
                    ],
                    [
                        'title' => 'All Direct Associates',
                        'value' => $allDirectAssociate ?? 0,
                        'icon' => 'bi-person-lines-fill',
                        'color' => 'success',
                        'route' => route('direct-associate'),
                    ],
                    [
                        'title' => 'Left Direct Associates',
                        'value' => $leftDirectAssociate ?? 0,
                        'icon' => 'bi-arrow-left-circle-fill',
                        'color' => 'primary',
                        'route' => route('direct-associate', ['direction' => 'left']),
                    ],
                    [
                        'title' => 'Right Direct Associates',
                        'value' => $rightDirectAssociate ?? 0,
                        'icon' => 'bi-arrow-right-circle-fill',
                        'color' => 'warning',
                        'route' => route('direct-associate', ['direction' => 'right']),
                    ],
                    [
                        'title' => 'All Team Downline',
                        'value' => $allTeamCount ?? 0,
                        'icon' => 'bi-diagram-3-fill',
                        'color' => 'info',
                        'route' => route('associate-downline'),
                    ],
                    [
                        'title' => 'Left Team Count',
                        'value' => $leftTeamCount ?? 0,
                        'icon' => 'bi-diagram-2-fill',
                        'color' => 'primary',
                        'route' => route('associate-downline', ['direction' => 'left']),
                    ],
                    [
                        'title' => 'Right Team Count',
                        'value' => $rightTeamCount ?? 0,
                        'icon' => 'bi-diagram-2-fill',
                        'color' => 'warning',
                        'route' => route('associate-downline', ['direction' => 'right']),
                    ],
                ];
            @endphp

            @foreach ($cards as $index => $card)
                <div class="col-xxl-3 col-xl-4 col-md-6 animate-fade-in-up"
                    style="animation-delay: {{ 0.1 + $index * 0.05 }}s">

                    <a href="{{ $card['route'] }}"
                        class="card dashboard-stat-card border-0 rounded-4 overflow-hidden h-100 shadow-sm hover-lift position-relative text-decoration-none">

                        <div class="card-fill-overlay bg-{{ $card['color'] }}"></div>
                        <div class="premium-shine-layer"></div>

                        <div class="card-body p-4 position-relative" style="z-index: 2;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <small class="text-muted fw-semibold text-uppercase">
                                        {{ $card['title'] }}
                                    </small>

                                    <h1 class="fw-bold text-dark mt-2 mb-0 counter" data-count="{{ $card['value'] }}">
                                        {{ $card['value'] }}
                                    </h1>
                                </div>

                                <div
                                    class="rounded-4 bg-{{ $card['color'] }} bg-opacity-10 text-{{ $card['color'] }} d-flex align-items-center justify-content-center stat-icon-box">
                                    <i class="bi {{ $card['icon'] }} fs-3"></i>
                                </div>
                            </div>
                        </div>

                    </a>
                </div>
            @endforeach
        </div>

        {{-- Chart Section --}}
        <div class="row g-4 mb-4 align-items-stretch">

            <div class="col-lg-8">
                <div class="card dashboard-glass-card border-0 shadow-sm rounded-4 h-100 overflow-hidden">

                    <div class="card-header border-0 p-4 pb-0">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <h5 class="fw-bold mb-1">
                                    <i class="bi bi-graph-up-arrow text-success me-2"></i>
                                    Monthly EMI Collections & Dues
                                </h5>
                                <p class="text-muted small mb-0">
                                    View month-wise EMI collections and outstanding due amounts.
                                </p>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-light btn-sm rounded-circle dashboard-icon-btn"
                                    onclick="refreshChart()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>

                                <button type="button" class="btn btn-light btn-sm rounded-circle dashboard-icon-btn"
                                    onclick="toggleChartType()">
                                    <i class="bi bi-bar-chart"></i>
                                </button>

                                <button type="button" class="btn btn-light btn-sm rounded-circle dashboard-icon-btn"
                                    onclick="downloadChart()">
                                    <i class="bi bi-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="dashboard-inner-box success-box p-3 rounded-4 border">
                                    <small class="text-muted fw-semibold">Confirmed Amount</small>
                                    <h5 class="fw-bold text-success mb-0">
                                        &#8377; {{ number_format($businessConfirmedPayment ?? 0, 2) }}
                                    </h5>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="dashboard-inner-box p-3 rounded-4 border">
                                    <small class="text-muted fw-semibold">Due Amount</small>
                                    <h5 class="fw-bold text-dark mb-0">
                                        &#8377; {{ number_format($businesspendingPayment ?? 0, 2) }}
                                    </h5>
                                </div>
                            </div>
                        </div>

                        <div style="position: relative; min-height: 330px;">
                            <canvas id="mainChart"></canvas>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="card dashboard-glass-card border-0 shadow-sm rounded-4 overflow-hidden h-100">

                    <div class="card-header border-0 p-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-1">
                                    <i class="bi bi-pie-chart-fill text-success me-2"></i>
                                    Plot Status
                                </h5>

                                <p class="text-muted small mb-0">
                                    Live plot inventory overview
                                </p>
                            </div>

                            <div
                                class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center stat-icon-box">
                                <i class="bi bi-grid-3x3-gap-fill fs-5"></i>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-4 pt-3 pb-2">
                        <div style="height:260px; position:relative;">
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>

                    <div class="px-4 pb-4">
                        <div class="row g-3">

                            <div class="col-6">
                                <div class="dashboard-inner-box border rounded-4 p-3 text-center">
                                    <small class="text-muted d-block mb-1">Available</small>
                                    <h5 class="fw-bold text-success mb-0">{{ $available ?? 0 }}</h5>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="dashboard-inner-box border rounded-4 p-3 text-center">
                                    <small class="text-muted d-block mb-1">Occupied</small>
                                    <h5 class="fw-bold text-danger mb-0">
                                        {{ ($booked ?? 0) + ($hold ?? 0) + ($registry ?? 0) }}
                                    </h5>
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="text-center">
                                    <div class="small text-muted">Booked</div>
                                    <div class="fw-bold text-danger">{{ $booked ?? 0 }}</div>
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="text-center">
                                    <div class="small text-muted">Hold</div>
                                    <div class="fw-bold text-warning">{{ $hold ?? 0 }}</div>
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="text-center">
                                    <div class="small text-muted">Registry</div>
                                    <div class="fw-bold text-primary">{{ $registry ?? 0 }}</div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- Table Section --}}
        <div class="row g-4 mb-4">

            <div class="col-lg-8">
                <div class="card dashboard-glass-card border-0 shadow-sm rounded-4 h-100 overflow-hidden">

                    <div class="card-header border-0 pt-4 px-4 pb-0 d-flex align-items-center flex-wrap gap-2">
                        <h5 class="fw-bold mb-0 text-dark me-auto">
                            <i class="bi bi-calendar-check me-2 text-success"></i>
                            Current Month's EMI Dues
                        </h5>

                        <div class="dashboard-pending-badge px-3 py-1 rounded-pill d-flex align-items-center shadow-sm">
                            <span class="spinner-grow spinner-grow-sm me-2" style="width: 0.5rem; height: 0.5rem;"></span>
                            {{ $monthlyDues->count() }} Pending EMI
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="table-responsive dashboard-table-wrap">

                            <table class="table table-hover align-middle mb-0">

                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Booking ID</th>
                                        <th>Project</th>
                                        <th>Customer</th>
                                        <th>Plot No.</th>
                                        <th>Payment Type</th>
                                        <th>Mode</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($monthlyDues as $index => $due)
                                        @php
                                            $plotCount = (int) ($due->group_plot_count ?? 1);
                                            $projects =
                                                $due->group_projects ?: $due->plotSaleDetail?->project?->name ?? 'N/A';
                                            $blocks =
                                                $due->group_blocks ?: $due->plotSaleDetail?->block?->block ?? 'N/A';
                                            $plotNumbers =
                                                $due->group_plot_numbers ?:
                                                $due->plotSaleDetail?->plotDetail?->plot_number ?? 'N/A';
                                            $dueAmount = (float) ($due->group_due_amount ?? ($due->due_amount ?? 0));
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="text-muted small">#{{ $index + 1 }}</span>
                                            </td>

                                            <td>
                                                <strong class="text-dark">
                                                    {{ $due->plotSaleDetail?->booking_code ?? ($due->customerBooking?->booking_code ?? 'N/A') }}
                                                </strong>
                                            </td>

                                            <td>
                                                {{ $projects }}
                                                @if ($blocks !== 'N/A')
                                                    <small class="text-muted d-block">Block {{ $blocks }}</small>
                                                @endif
                                            </td>

                                            <td>
                                                <div class="fw-semibold">
                                                    {{ $due->customerBooking?->primaryDetail?->name ?? ($due->customerBooking?->customer_name ?? 'N/A') }}
                                                </div>
                                                <small class="text-muted">
                                                    {{ $due->customerBooking?->customer_code ?? '' }}
                                                </small>
                                            </td>

                                            <td>
                                                <span class="badge dashboard-light-badge rounded-pill px-3">
                                                    {{ $plotNumbers }}
                                                </span>
                                                @if ($plotCount > 1)
                                                    <span
                                                        class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 ms-1">
                                                        {{ $plotCount }} Plots
                                                    </span>
                                                @endif
                                            </td>

                                            <td>
                                                @if ($due->plan_type == 'emi_plan')
                                                    <span
                                                        class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                                                        EMI Plan
                                                    </span>
                                                @elseif ($due->plan_type == 'full_payment')
                                                    <span
                                                        class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                                        Full Payment
                                                    </span>
                                                @else
                                                    <span class="badge dashboard-light-badge rounded-pill px-3 py-2">
                                                        {{ $due->plan_type ? ucwords(str_replace('_', ' ', $due->plan_type)) : 'N/A' }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td>
                                                @php
                                                    $modeLabel = $due->payment_mode
                                                        ? ucwords(str_replace('_', ' / ', $due->payment_mode))
                                                        : 'N/A';
                                                @endphp

                                                <span class="badge dashboard-light-badge rounded-pill px-3 py-2">
                                                    {{ $modeLabel }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="text-danger fw-bold">
                                                    &#8377;{{ number_format($dueAmount, 2) }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="text-muted">
                                                    <i class="bi bi-clock-history me-1"></i>
                                                    {{ $due->emi_date ? \Carbon\Carbon::parse($due->emi_date)->format('d M, Y') : 'N/A' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-5">
                                                <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
                                                No pending dues for this month
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>

                        </div>
                    </div>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="card dashboard-glass-card border-0 shadow-sm rounded-4 overflow-hidden h-100">

                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-1">
                                    <i class="bi bi-wallet2 me-2"></i>
                                    Total Earnings
                                </h5>

                                <small class="text-muted">
                                    Business payment overview
                                </small>
                            </div>

                            <div class="bg-white bg-opacity-25 rounded-3 p-3">
                                <i class="bi bi-cash-stack fs-3"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="dashboard-inner-box rounded-4 p-3 mb-4 text-center">
                            <small class="text-muted d-block mb-1">
                                Total Business Volume
                            </small>
                            <h3 class="fw-bold text-success mb-0">
                                {{-- &#8377; {{ number_format($confirmedPayment + $holdPayment + $pendingPayment, 2) }} --}}
                                &#8377; {{ number_format($confirmedPayment, 2) }}
                            </h3>
                        </div>

                        <div class="d-flex flex-column gap-3">

                            <div class="earning-box success">
                                <div class="icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>

                                <div class="flex-grow-1">
                                    <small class="text-muted">Confirmed Payment</small>
                                    <h5 class="fw-bold mb-0 text-success">
                                        &#8377; {{ number_format($confirmedPayment, 2) }}
                                    </h5>
                                </div>

                                <div class="badge bg-success-subtle text-success px-3 py-2">
                                    Received
                                </div>
                            </div>

                            <div class="earning-box warning">
                                <div class="icon">
                                    <i class="bi bi-pause-circle-fill"></i>
                                </div>

                                <div class="flex-grow-1">
                                    <small class="text-muted">Hold Payment</small>
                                    <h5 class="fw-bold mb-0 text-warning">
                                        &#8377; {{ number_format($holdPayment, 2) }}
                                    </h5>
                                </div>

                                <div class="badge bg-warning-subtle text-warning px-3 py-2">
                                    Cheque/DD
                                </div>
                            </div>

                            <div class="earning-box warning">
                                <div class="icon">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>

                                <div class="flex-grow-1">
                                    <small class="text-muted">Pending Payment</small>
                                    <h5 class="fw-bold mb-0 text-warning">
                                        &#8377; {{ number_format($pendingPayment, 2) }}
                                    </h5>
                                </div>

                                <div class="badge bg-warning-subtle text-warning px-3 py-2">
                                    Pending
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection

@push('scripts')
    @php
        $safeVisitorsData = $visitorsData ?? [
            'labels' => [],
            'monthlyPaidAmount' => [],
            'monthlyDueAmount' => [],
        ];
    @endphp

    <script>
        const chartData = @json($safeVisitorsData);

        const mainChartCanvas = document.getElementById('mainChart');
        const pieChartCanvas = document.getElementById('pieChart');

        let myMainChart = null;

        if (mainChartCanvas) {
            const mainChartCtx = mainChartCanvas.getContext('2d');

            const formatINR = function(value) {
                return 'Rs. ' + Number(value || 0).toLocaleString('en-IN');
            };

            const getChartConfig = function(type) {
                return {
                    type: type,
                    data: {
                        labels: chartData.labels || [],
                        datasets: [{
                                label: 'Confirmed Amount',
                                data: chartData.monthlyPaidAmount || [],
                                backgroundColor: type === 'bar' ? '#20c997' : 'rgba(32, 201, 151, 0.18)',
                                borderColor: '#20c997',
                                borderWidth: 3,
                                borderRadius: 8,
                                borderSkipped: false,
                                tension: 0.45,
                                fill: type === 'line',
                                pointBackgroundColor: '#20c997',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: type === 'line' ? 4 : 0,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'Due Amount',
                                data: chartData.monthlyDueAmount || [],
                                backgroundColor: type === 'bar' ? '#e9ecef' : 'rgba(108, 117, 125, 0.15)',
                                borderColor: '#adb5bd',
                                borderWidth: 3,
                                borderRadius: 8,
                                borderSkipped: false,
                                tension: 0.45,
                                fill: type === 'line',
                                pointBackgroundColor: '#adb5bd',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: type === 'line' ? 4 : 0,
                                pointHoverRadius: 6
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
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 22,
                                    font: {
                                        weight: '600'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#111827',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                padding: 12,
                                cornerRadius: 10,
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + formatINR(context.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return formatINR(value);
                                    }
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                };
            };

            myMainChart = new Chart(mainChartCtx, getChartConfig('bar'));

            window.downloadChart = function() {
                const link = document.createElement('a');
                link.href = mainChartCanvas.toDataURL('image/png');
                link.download = 'business-overview.png';
                link.click();
            };

            window.refreshChart = function() {
                window.location.reload();
            };

            window.toggleChartType = function() {
                const newType = myMainChart.config.type === 'bar' ? 'line' : 'bar';

                myMainChart.destroy();
                myMainChart = new Chart(mainChartCtx, getChartConfig(newType));
            };
        }

        if (pieChartCanvas) {
            const pieChartCtx = pieChartCanvas.getContext('2d');

            new Chart(pieChartCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Booked', 'Hold', 'Registry', 'Available'],
                    datasets: [{
                        data: [
                            {{ $booked ?? 0 }},
                            {{ $hold ?? 0 }},
                            {{ $registry ?? 0 }},
                            {{ $available ?? 0 }}
                        ],
                        backgroundColor: [
                            '#dc3545',
                            '#ffc107',
                            '#0d6efd',
                            '#198754'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 14,
                        borderRadius: 8,
                        spacing: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '74%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 18,
                                font: {
                                    weight: '600'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#111827',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            padding: 12,
                            cornerRadius: 10
                        }
                    }
                },
                plugins: [{
                    id: 'centerText',
                    beforeDraw: function(chart) {
                        const width = chart.width;
                        const height = chart.height;
                        const ctx = chart.ctx;

                        const totalPlots =
                            {{ ($booked ?? 0) + ($hold ?? 0) + ($registry ?? 0) + ($available ?? 0) }};

                        ctx.save();

                        ctx.font = 'bold 32px sans-serif';
                        ctx.textBaseline = 'middle';
                        ctx.fillStyle = '#111827';

                        const totalText = String(totalPlots);
                        const totalTextX = Math.round((width - ctx.measureText(totalText).width) / 2);
                        const totalTextY = height / 2.28;

                        ctx.fillText(totalText, totalTextX, totalTextY);

                        ctx.font = '600 13px sans-serif';
                        ctx.fillStyle = '#6b7280';

                        const labelText = 'TOTAL';
                        const labelTextX = Math.round((width - ctx.measureText(labelText).width) / 2);
                        const labelTextY = height / 1.65;

                        ctx.fillText(labelText, labelTextX, labelTextY);

                        ctx.restore();
                    }
                }]
            });
        }
    </script>
@endpush
