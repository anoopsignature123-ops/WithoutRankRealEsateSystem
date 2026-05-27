@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Customer List</h3>
                        <p class="text-muted mb-0 small"> View all customers and their booking details </p>
                    </div>
                    
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover align-middle" id="customerListTable">

                        <thead>

                            <tr>
                                <th>#</th>
                                <th>Customer ID</th>
                                <th>Reference Customer</th>
                                <th>Customer Name</th>
                                <th>Address</th>
                                <th>Contact No</th>
                                <th>Email</th>
                                <th>Booking Status</th>
                                {{-- <th>Action</th> --}}
                            </tr>

                        </thead>

                        <tbody>

                            @forelse ($customers as $key => $customer)
                                @php
                                    $primary = $customer->primaryDetail;

                                    $contact = $primary?->correspondenceDetail;

                                    $address =
                                        $primary?->permanent_address ??
                                        ($primary?->city ? $primary->city . ', ' . $primary->state : 'N/A');

                                    $parentCustomer = \App\Models\CustomerBooking::find($customer->customer_id);
                                @endphp

                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        {{ $customer->customer_code ?? 'N/A' }}
                                    </td>
                                    <td>
                                        @if ($parentCustomer)
                                            <span class="badge bg-info">
                                                {{ $parentCustomer->customer_code }}
                                            </span>
                                        @else
                                            <span class="text-muted">
                                                Self
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ ucfirst($primary?->name ?? 'N/A') }}
                                    </td>

                                    <td>
                                        {{ $address }}
                                    </td>

                                    <td>
                                        {{ $contact?->telephone_no ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $contact?->email ?? 'N/A' }}
                                    </td>

                                    <td>

                                        <span class="badge bg-primary px-3 py-2">

                                            Booked
                                            {{ $customer->total_bookings }}

                                            {{ $customer->total_bookings > 1 ? 'Plots' : 'Plot' }}

                                        </span>

                                    </td>

                                    {{-- <td>

                                        <a href="{{ route('customer-booking.edit', [$customer->id, 'step' => 1]) }}"
                                            class="btn btn-sm btn-success">

                                            <i class="fa fa-edit"></i>

                                        </a>

                                    </td> --}}

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="9" class="text-center text-muted py-4">

                                        No customers found

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

            if (
                $('#customerListTable tbody tr td')
                .attr('colspan') == undefined
            ) {

                $('#customerListTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                });
            }
        });
    </script>
@endpush
