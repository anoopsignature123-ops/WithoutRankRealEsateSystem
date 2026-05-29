@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/admin_dashborad.css') }}">
@endpush
@section('content')
    <div class="container-fluid py-4">
        {{-- >>>>>>>>>>>>> Header Section >>>>>>>>> --}}
        <div class="card border-0 rounded-4 mb-4 overflow-hidden position-relative shadow-sm"
            style="background-image: url('https://images.unsplash.com/photo-1497366754035-f200968a6e72?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'); background-size: cover; background-position: center;">

            <div class="position-absolute w-100 h-100"
                style="background: linear-gradient(135deg, rgba(48, 80, 58, 0.95) 0%, rgba(45, 146, 121, 0.85) 100%);">
            </div>

            <div class="position-absolute end-0 top-0 opacity-10 p-3">
                <i class="bi bi-shield-check" style="font-size: 8rem; color: #ffffff;"></i>
            </div>

            <div class="card-body p-4 position-relative z-index-1">
                <div class="d-flex align-items-center gap-4">

                    <div class="rounded-4 d-flex align-items-center justify-content-center shadow-lg flex-shrink-0"
                        style="width: 70px; height: 70px; background:   backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3);">
                        <div class="welcome-profile overflow-hidden p-0">
                            <div class="welcome-profile overflow-hidden p-0">
                                {!! Auth::user()->image
                                    ? '<img src="' . getFileUrl(Auth::user()->image) . '" alt="Profile" class="w-100 h-100 object-fit-cover">'
                                    : '<span class="fs-2 fw-bold text-white">' . substr(Auth::user()->name ?? 'A', 0, 1) . '</span>' !!}
                            </div>
                        </div>
                    </div>

                    <div class="text-white">
                        <h3 class="fw-bold mb-1" style="letter-spacing: 0.5px;">
                            Welcome, {{ Auth::user()->name ?? 'Admin' }}
                        </h3>
                        <div class="d-flex align-items-center fw-medium text-white-75">
                            {{-- <span class="d-flex align-items-center">
                                <i class="bi bi-calendar-check me-2"></i> Super Admin
                            </span>
                            <span class="mx-3 opacity-50">|</span> --}}
                            <span class="d-flex align-items-center">
                                <i class="bi bi-briefcase me-2"></i> Managing Business Operations
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- >>>>>>>>>> Chard Section >>>>>>>>>>>>>>  --}}
        <div class="row g-4 mb-4">
            @php
                $cards = [
                    [
                        'title' => 'Total Projects',
                        'value' => $projectCount,
                        'icon' => 'bi-buildings-fill',
                        'color' => 'primary',
                        'route' => route('projects.index'),
                    ],
                    [
                        'title' => 'Total Plots',
                        'value' => $totalPlot,
                        'icon' => 'bi-grid-1x2-fill',
                        'color' => 'success',
                        'route' => route('plot-details.index'),
                    ],
                    [
                        'title' => 'Total Customers',
                        'value' => $totalCustomer,
                        'icon' => 'bi-people-fill',
                        'color' => 'info',
                        'route' => route('customer-booking.index'),
                    ],
                    [
                        'title' => 'Total Associates',
                        'value' => $totalAssociate,
                        'icon' => 'bi-person-badge-fill',
                        'color' => 'warning',
                        'route' => route('associate.index'),
                    ],
                ];
            @endphp
            @foreach ($cards as $index => $card)
                <div class="col-xxl-3 col-xl-4 col-md-6 animate-fade-in-up"
                    style="animation-delay: {{ 0.1 + $index * 0.05 }}s">
                    <a href="{{ $card['route'] }}"
                        class="card border-0 rounded-4 overflow-hidden h-100 shadow-sm hover-lift position-relative">
                        <div class="card-fill-overlay bg-{{ $card['color'] }}"></div>
                        <div class="premium-shine-layer"></div>
                        <div class="card-body p-4 position-relative" style="z-index: 2;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <small class="text-muted fw-semibold text-uppercase">{{ $card['title'] }}</small>
                                    <h1 class="fw-bold text-dark mt-2 mb-0 counter" data-count="{{ $card['value'] }}">
                                        {{ $card['value'] }}
                                    </h1>
                                </div>
                                <div class="rounded-4 bg-{{ $card['color'] }} bg-opacity-10 text-{{ $card['color'] }} d-flex align-items-center justify-content-center"
                                    style="width: 60px; height: 60px;">
                                    <i class="bi {{ $card['icon'] }} fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        {{-- >>>>>>>>>> Chart Section >>>>>>>>>>>>>>  --}}
        <div class="row g-4 mb-4 align-items-stretch">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Visitors Overview</h5>
                        <div class="d-flex gap-3 text-muted">
                            <i class="bi bi-arrow-clockwise" title="Refresh" onclick="refreshChart()"
                                style="cursor:pointer"></i>
                            <i class="bi bi-bar-chart" title="Toggle Chart Type" onclick="toggleChartType()"
                                style="cursor:pointer"></i>
                            <i class="bi bi-download" title="Download" onclick="downloadChart()" style="cursor:pointer"></i>
                        </div>
                    </div>
                    <div style="flex-grow: 1; position: relative; min-height: 300px;">
                        <canvas id="mainChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 d-flex flex-column">
                    <h5 class="fw-bold mb-3">Plot Status</h5>
                    <div style="flex-grow: 1; position: relative; min-height: 200px;">
                        <canvas id="pieChart"></canvas>
                    </div>
                    <div class="mt-3 pt-3 border-top d-flex justify-content-around text-center">
                        <div>
                            <small class="text-muted d-block">Occupied</small>
                            <span class="fw-bold">{{ ($booked ?? 0) + ($registry ?? 0) }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Available</small>
                            <span class="fw-bold text-success">{{ $available ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- >>>>>>>>>> Table Section >>>>>>>>>>>>>>  --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex align-items-center">
                        <h5 class="fw-bold mb-0 text-dark me-auto"> <i
                                class="bi bi-calendar-check me-2 text-success"></i>Current Month's Dues
                        </h5>
                        <div class="px-3 py-1 rounded-pill d-flex align-items-center shadow-sm"
                            style="background-color: #d1e7dd; color: #0f5132; font-weight: 600; font-size: 0.85rem; border: 1px solid #badbcc;">
                            <span class="spinner-grow spinner-grow-sm me-2"
                                style="width: 0.5rem; height: 0.5rem;"></span>{{ $monthlyDues->count() }} Pending Due
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Booking Id</th>
                                        <th>Project</th>
                                        <th>Customer</th>
                                        <th>Plot No.</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($monthlyDues as $index => $due)
                                        <tr>
                                            <td><span class="text-muted small">#{{ $index + 1 }}</span></td>
                                            <td><strong
                                                    class="text-dark">{{ $due->customerBooking->booking_code ?? 'N/A' }}</strong>
                                            </td>
                                            <td>{{ $due->plotSaleDetail->project->name ?? 'N/A' }}</td>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ $due->customerBooking->primaryDetail->name ?? 'N/A' }}</div>
                                                <small
                                                    class="text-muted">{{ $due->customerBooking->customer_code ?? '' }}</small>
                                            </td>
                                            <td><span
                                                    class="badge bg-light text-dark border">{{ $due->plotSaleDetail->plotDetail->plot_number ?? 'N/A' }}</span>
                                            </td>
                                            <td><span class="text-danger fw-bold">₹
                                                    {{ number_format($due->due_amount, 2) }}</span></td>
                                            <td><span class="text-muted"><i
                                                        class="bi bi-clock-history me-1"></i>{{ $due->emi_date ? $due->emi_date->format('d M, Y') : 'N/A' }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-5">
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
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-wallet2 me-2"></i>Financial Summary</h5>
                    <div class="d-flex flex-column gap-3">

                        <div class="p-3 border rounded-4 d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 p-3 rounded-3 me-3 text-danger">
                                <i class="bi bi-graph-down fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Total Outstanding</small>
                                <span class="fs-5 fw-bold text-dark">₹ {{ number_format($totalOutstanding, 2) }}</span>
                            </div>
                        </div>

                        <div class="p-3 border rounded-4 d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3 text-warning">
                                <i class="bi bi-exclamation-triangle fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Total Overdue</small>
                                <span class="fs-5 fw-bold text-dark">₹ {{ number_format($totalOverdue, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        const ctx = document.getElementById('mainChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(25, 135, 84, 0.3)');
        gradient.addColorStop(1, 'rgba(25, 135, 84, 0)');

        const chartData = @json($visitorsData);
        const getChartConfig = (type) => ({
            type: type,
            data: {
                labels: chartData.labels,
                datasets: [{
                        label: 'Registered Users',
                        data: chartData.registered,
                        backgroundColor: '#20c997',
                        borderRadius: 4
                    },
                    {
                        label: 'Guest Visitors',
                        data: chartData.guests,
                        backgroundColor: '#e9ecef',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        let myMainChart = new Chart(document.getElementById('mainChart'), getChartConfig('bar'));

        function downloadChart() {
            const link = document.createElement('a');
            link.href = document.getElementById('mainChart').toDataURL('image/png');
            link.download = 'visitors-overview.png';
            link.click();
        }

        function refreshChart() {
            window.location.reload();
        }

        function toggleChartType() {
            const newType = myMainChart.config.type === 'bar' ? 'line' : 'bar';
            myMainChart.destroy();
            myMainChart = new Chart(document.getElementById('mainChart'), getChartConfig(newType));
        }
        new Chart(document.getElementById('pieChart'), {
            type: 'doughnut',
            data: {
                labels: ['Booked', 'Hold', 'Registry', 'Available'],
                datasets: [{
                    data: [{{ $booked ?? 0 }}, {{ $hold ?? 0 }}, {{ $registry ?? 0 }},
                        {{ $available ?? 0 }}
                    ],
                    backgroundColor: ['#dc3545', '#ffc107', '#6f42c1', '#198754'],
                    hoverOffset: 15,
                    borderRadius: 10,
                    spacing: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                }
            },
            plugins: [{
                id: 'centerText',
                beforeDraw: function(chart) {
                    const width = chart.width,
                        height = chart.height,
                        ctx = chart.ctx;
                    ctx.restore();
                    ctx.font = "bold 25px sans-serif";
                    ctx.textBaseline = "middle";
                    ctx.fillStyle = "#333";
                    const text =
                        "{{ ($booked ?? 0) + ($hold ?? 0) + ($registry ?? 0) + ($available ?? 0) }}";
                    const textX = Math.round((width - ctx.measureText(text).width) / 2);
                    const textY = height / 2.3;
                    ctx.fillText(text, textX, textY);
                    ctx.font = "12px sans-serif";
                    ctx.fillStyle = "#888";
                    const label = "TOTAL PLOTS";
                    const labelX = Math.round((width - ctx.measureText(label).width) / 2);
                    const labelY = height / 1.7;
                    ctx.fillText(label, labelX, labelY);
                    ctx.save();
                }
            }]
        });
    </script>
@endpush
