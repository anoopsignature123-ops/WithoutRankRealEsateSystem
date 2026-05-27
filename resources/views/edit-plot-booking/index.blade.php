@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Edit Plot Booking </h3>
                        <p class="text-muted mb-0 small"> View and edit customer plot booking details </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-body">

                <div class="d-flex align-items-center mb-3">

                    <i class="fa fa-search text-success me-2"></i>

                    <h5 class="mb-0 fw-bold">
                        Search Filters
                    </h5>

                </div>

                <div class="row g-3 align-items-end">

                    {{-- Booking ID --}}
                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Booking ID
                        </label>

                        <input type="text" class="form-control" id="bookingSearch" placeholder="Enter booking ID...">

                    </div>

                    {{-- Plot No --}}
                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Plot Number
                        </label>

                        <input type="text" class="form-control" id="plotSearch" placeholder="Enter plot number...">

                    </div>

                    {{-- Customer Name --}}
                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Customer Name
                        </label>

                        <input type="text" class="form-control" id="customerSearch" placeholder="Enter customer name...">

                    </div>

                    {{-- Buttons --}}
                    <div class="col-md-3">

                        <div class="d-flex gap-2">

                            <button class="btn btn-success flex-fill" id="searchBtn">

                                <i class="fa fa-search me-1"></i>
                                Search

                            </button>

                            <button class="btn btn-outline-secondary flex-fill" id="resetBtn">

                                <i class="fa fa-refresh me-1"></i>
                                Reset

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Table --}}
        <div class="card shadow-sm border-0">

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover align-middle" id="editPlotBookingTable">

                        <thead>

                            <tr>

                                <th>#</th>
                                <th>Booking Id</th>
                                <th>Project Name</th>
                                <th>Customer Id</th>
                                <th>Customer Name</th>
                                <th>Block</th>
                                <th>Plot No</th>
                                <th>Plot Cost</th>
                                <th>Plan Type</th>
                                <th>Agent Id</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($customers as $key => $customer)
                                @php

                                    $primary = $customer->primaryDetail;

                                    $plotSale = $customer->plotSaleDetail;

                                    $payment = $customer->payment;

                                @endphp

                                <tr>

                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        {{ $customer->booking_code ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $plotSale?->project?->name ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $customer->parentCustomer?->customer_code ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ strtoupper($primary?->name ?? 'N/A') }}
                                    </td>

                                    <td>
                                        {{ $plotSale?->block?->block ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $plotSale?->plotDetail?->plot_number ?? 'N/A' }}
                                    </td>

                                    <td>
                                        ₹{{ number_format($plotSale?->plot_cost ?? 0, 2) }}
                                    </td>

                                    <td>

                                        <span
                                            class="badge 
                                            {{ $payment?->plan_type == 'emi_plan' ? 'bg-info' : 'bg-primary' }}">

                                            {{ $payment?->plan_type == 'emi_plan' ? 'EMI Plan' : 'Full Payment' }}

                                        </span>

                                    </td>

                                    <td>
                                        {{ $customer->associate?->associate_id ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $plotSale?->booking_date ?? 'N/A' }}
                                    </td>

                                    <td>

                                        <span
                                            class="badge 
                                            {{ $payment?->payment_status == 'booked'
                                                ? 'bg-success'
                                                : ($payment?->payment_status == 'hold'
                                                    ? 'bg-warning text-dark'
                                                    : ($payment?->payment_status == 'emi'
                                                        ? 'bg-info'
                                                        : 'bg-secondary')) }}">

                                            {{ $payment?->payment_status == 'booked'
                                                ? 'Booked'
                                                : ($payment?->payment_status == 'hold'
                                                    ? 'Hold'
                                                    : ($payment?->payment_status == 'emi'
                                                        ? 'EMI'
                                                        : 'N/A')) }}

                                        </span>

                                    </td>

                                    <td>

                                        <a href="{{ route('customer-booking.edit', [$customer->id, 'step' => $customer->current_step]) }}"
                                            class="btn btn-sm btn-success">

                                            <i class="fa fa-edit"></i>

                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="13" class="text-center text-muted py-4">

                                        No bookings found

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



@push('scripts')
    <script>
        $(document).ready(function() {

            let table = $('#editPlotBookingTable')
                .DataTable({

                    pageLength: 10,

                    responsive: true,

                });


            $('#searchBtn').click(function() {

                table.column(1)
                    .search(
                        $('#bookingSearch').val()
                    );

                table.column(4)
                    .search(
                        $('#customerSearch').val()
                    );

                table.column(6)
                    .search(
                        $('#plotSearch').val()
                    );

                table.draw();

            });
            $('#resetBtn').click(function() {

                $('#bookingSearch').val('');

                $('#plotSearch').val('');

                $('#customerSearch').val('');

                $('#editPlotBookingTable').DataTable().search('').draw();

            });
        });
    </script>
@endpush
