@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 transaction-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-file-earmark-richtext"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Booking Documents</span>
                        <h3 class="fw-bold mb-1 text-dark">Allotment & Agreement Letter</h3>
                        <p class="text-muted mb-0 small">Generate and download customer allotment and agreement letters.</p>
                    </div>
                </div>

                <span class="transaction-count">
                    {{ $bookings->count() }} Records
                </span>
            </div>
        </div>

        <div class="transaction-card mb-4">
            <div class="transaction-card-body">
                <div class="transaction-section-title">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-section-title-icon">
                            <i class="bi bi-search"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-1">Find Booking</h5>
                            <small class="text-muted">Select a booking to filter letter records.</small>
                        </div>
                    </div>
                </div>

                <form method="GET" id="filterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-9 col-md-8">
                            <label class="form-label fw-semibold">Booking</label>
                            <select name="booking_id" id="bookingFilter" class="form-select">
                                <option value="">All Bookings</option>
                                @foreach ($bookingList as $item)
                                    <option value="{{ $item->id }}"
                                        {{ request('booking_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->booking_code }}
                                        {{ $item->primaryDetail?->name ? ' | ' . $item->primaryDetail->name : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-4">
                            <a href="{{ route('booking-letter.index') }}" class="btn btn-outline-success w-100">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="transaction-card transaction-history-card mb-4">
            <div class="transaction-history-head">
                <div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-section-title-icon">
                            <i class="bi bi-folder2-open"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-1">Letter Records</h5>
                            <small class="text-muted">Customer bookings available for document download.</small>
                        </div>
                    </div>
                </div>

                <span class="transaction-count">
                    {{ $bookings->count() }} Records
                </span>
            </div>

            <div class="transaction-table-wrap">
                <table class="table transaction-table align-middle mb-0" id="letterTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Booking</th>
                            <th>Customer</th>
                            <th>Project / Plot</th>
                            <th>Total Cost</th>
                            <th>Total Area</th>
                            <th>Plan</th>
                            <th class="text-center">Download</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($bookings as $row)
                            @php
                                $plotSale = $row;
                                $letterPlotSales = $plotSale->relationLoaded('letterPlotSales')
                                    ? $plotSale->letterPlotSales
                                    : collect([$plotSale]);
                                $booking = $plotSale->customerBooking;
                                $payments = $letterPlotSales->flatMap(function ($sale) {
                                    return $sale->payments;
                                });
                                $projectName = $letterPlotSales->pluck('project.name')->filter()->unique()->implode(', ') ?: '-';
                                $blockName = $letterPlotSales->pluck('block.block')->filter()->unique()->implode(', ') ?: '-';
                                $plotNumbers = $letterPlotSales->pluck('plotDetail.plot_number')->filter()->implode(', ') ?: '-';
                                $plotCount = $letterPlotSales->count();
                                $totalArea = $letterPlotSales->sum(fn ($sale) => (float) ($sale->plot_area ?? 0));
                                $totalCost = $letterPlotSales->sum(fn ($sale) => (float) ($sale->total_plot_cost ?? 0));
                                $planTypes = $payments->pluck('plan_type')->filter()->unique()->values();
                                $planType = $planTypes->count() > 1 ? 'mixed' : $planTypes->first();
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold text-success">{{ $plotSale->booking_code ?? ($booking?->booking_code ?? '-') }}</div>
                                    <small class="text-muted">Customer Booking: {{ $booking?->booking_code ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $booking?->primaryDetail?->name ?? '-' }}</div>
                                    <small class="text-muted">
                                        {{ ('+91 ' . $booking?->primaryDetail?->correspondenceDetail?->mobile_number) ?? 'Mobile not added' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $projectName }}</div>
                                    <small class="text-muted">
                                        Block {{ $blockName }} / Plot {{ $plotNumbers }}
                                    </small>
                                    @if ($plotCount > 1)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle mt-1">
                                            {{ $plotCount }} Plots
                                        </span>
                                    @endif
                                </td>
                                <td class="fw-bold">
                                    &#8377;{{ number_format($totalCost, 2) }}
                                </td>
                                <td>
                                    {{ number_format($totalArea, 2) }} Sq.ft
                                </td>
                                <td>
                                    @if ($planType == 'mixed')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            Mixed
                                        </span>
                                    @elseif ($planType == 'emi_plan')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            EMI Plan
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            Full Payment
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex flex-wrap justify-content-center gap-2">
                                        <a href="{{ route('booking-letter.allotement.pdf', ['id' => $booking?->id, 'plot_sale_detail_id' => $plotSale->id]) }}"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>
                                            Allotment
                                        </a>

                                        <a href="{{ route('booking-letter.agreement.pdf', ['id' => $booking?->id, 'plot_sale_detail_id' => $plotSale->id]) }}"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-file-earmark-text me-1"></i>
                                            Agreement
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-muted"></i>
                                    No letter records found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const hasRecords = {{ $bookings->count() > 0 ? 'true' : 'false' }};

            if (hasRecords) {
                $('#letterTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                    ordering: true,
                    columnDefs: [{
                        orderable: false,
                        targets: [7]
                    }]
                });
            }

            $('#bookingFilter').on('change', function() {
                $('#filterForm').submit();
            });
        });
    </script>
@endpush
