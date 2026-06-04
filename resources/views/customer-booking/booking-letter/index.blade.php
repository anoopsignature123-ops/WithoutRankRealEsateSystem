@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        {{-- Page Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-4 d-flex align-items-center justify-content-center"
                            style="width:58px;height:58px;">
                            <i class="bi bi-file-earmark-text fs-3"></i>
                        </div>

                        <div>
                            <h3 class="fw-bold mb-1 text-dark">
                                Allotment & Agreement Letter
                            </h3>
                            <p class="text-muted mb-0 small">
                                Generate and download customer allotment and agreement documents.
                            </p>
                        </div>
                    </div>

                    <form method="GET" id="filterForm" style="min-width: 300px;">
                        <select name="booking_id" id="bookingFilter" class="form-select rounded-pill shadow-sm">
                            <option value="">Search / Select Booking</option>

                            @foreach ($bookingList as $item)
                                <option value="{{ $item->id }}"
                                    {{ request('booking_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->booking_code }}
                                    {{ $item->primaryDetail?->name ? ' / ' . $item->primaryDetail->name : '' }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                </div>
            </div>
        </div>

        {{-- Listing --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

            <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">
                            Booking Letter Records
                        </h5>
                        <small class="text-muted">
                            Customer booking records available for letter generation.
                        </small>
                    </div>

                    <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                        Total Records: {{ $bookings->count() }}
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0" id="letterTable">
                        <thead class="table-light">
                            <tr>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Project</th>
                                <th>Block</th>
                                <th>Plot No</th>
                                <th>Plot Rate</th>
                                <th>Plot Area</th>
                                <th>Plan Type</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($bookings as $row)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-success">
                                            {{ $row->booking_code ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ $row->primaryDetail?->name ?? '-' }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $row->primaryDetail?->correspondenceDetail?->telephone_no ?? '' }}
                                        </small>
                                    </td>

                                    <td>
                                        {{ $row->plotSaleDetail?->project?->name ?? ($row->plotSaleDetail?->plotDetail?->block?->project?->name ?? '-') }}
                                    </td>

                                    <td>
                                        {{ $row->plotSaleDetail?->block?->block ?? ($row->plotSaleDetail?->plotDetail?->block?->block ?? '-') }}
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                            {{ $row->plotSaleDetail?->plotDetail?->plot_number ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        ₹{{ number_format((float) ($row->plotSaleDetail?->plot_rate ?? 0), 2) }}
                                    </td>

                                    <td>
                                        {{ $row->plotSaleDetail?->plot_area ?? '-' }} Sq.ft
                                    </td>

                                    <td>
                                        @if ($row->payment?->plan_type == 'emi_plan')
                                            <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                                EMI Plan
                                            </span>
                                        @else
                                            <span
                                                class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                                Full Payment
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div class="d-inline-flex gap-2">

                                            <a href="{{ route('booking-letter.allotement.pdf', $row->id) }}"
                                                target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                <i class="bi bi-file-earmark-pdf me-1"></i>
                                                Allotment
                                            </a>

                                            <a href="{{ route('booking-letter.agreement.pdf', $row->id) }}" target="_blank"
                                                class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                <i class="bi bi-file-earmark-text me-1"></i>
                                                Agreement
                                            </a>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-muted"></i>
                                        No records found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>

        </div>

    </div>
@endsection

@push('styles')
    <style>
        #letterTable th,
        #letterTable td {
            vertical-align: middle;
        }

        #letterTable thead th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .4px;
            font-weight: 700;
            color: #475569;
        }

        #letterTable tbody td {
            padding-top: 14px;
            padding-bottom: 14px;
        }

        #letterTable tbody tr:hover {
            background: #fafafa;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {

            if ($('#letterTable tbody tr td').attr('colspan') == undefined) {
                $('#letterTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                    ordering: true
                });
            }

            $('#bookingFilter').on('change', function() {
                $('#filterForm').submit();
            });

        });
    </script>
@endpush
