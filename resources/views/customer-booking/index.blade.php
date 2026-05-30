@extends('layouts.app')
@section('content')
    <div class="container-fluid mt-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Customer Booking Management</h3>
                        <p class="text-muted mb-0 small">Manage customer bookings</p>
                    </div>
                    @can('customer-booking-modify')
                        <a href="{{ route('customer-booking.create') }}" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i>Add New Customer
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-hover" id="customerBookingTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Photo</th>
                                <th>Customer Name</th>
                                <th>Customer Id</th>
                                <th>Project</th>
                                <th>Block</th>
                                <th>Plot Number</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $key => $customer)
                                @php
                                    $primary = $customer->primaryDetail;
                                    $plotSale = $customer->plotSaleDetail;
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        @if ($primary?->customerDocument?->profile_picture)
                                            <img src="{{ asset('storage/' . $primary->customerDocument->profile_picture) }}"
                                                width="45" height="45"
                                                class="rounded-circle border object-fit-cover">
                                        @else
                                            <img src="https://ui-avatars.com/api/?name={{ $primary?->name }}" width="45"
                                                class="rounded-circle border">
                                        @endif
                                    </td>
                                    <td class="fw-semibold"> {{ ucfirst($primary?->name) ?? 'N/A' }}</td>
                                    <td class="">{{ ucfirst($customer->customer_code) ?? 'N/A' }}</td>
                                    <td>{{ $plotSale?->project?->name ?? 'N/A' }}</td>
                                    <td>{{ $plotSale?->block?->block ?? 'N/A' }}</td>
                                    <td>{{ $plotSale?->plotDetail?->plot_number ?? 'N/A' }}</td>
                                    <td>{{ $plotSale?->booking_date ?? 'N/A' }}</td>
                                    <td>
                                        @if ($customer->status == 'draft')
                                            <span class="badge bg-warning text-dark">
                                                Incomplete
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                Completed
                                            </span>
                                        @endif

                                    </td>

                                    <td>
                                        @can('customer-booking-modify')
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('customer-booking.edit', [
                                                    $customer->id,
                                                    'step' => $customer->status == 'completed' ? 1 : $customer->current_step,
                                                ]) }}"
                                                    class="btn btn-sm btn-success"><i class="fa fa-edit"></i>
                                                </a>
                                                <form
                                                    action="{{ route('customer-booking.destroy', $customer->id) }}"method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        No customer booking found
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
            if ($('#customerBookingTable tbody tr td').attr('colspan') == undefined) {
                $('#customerBookingTable').DataTable();
            }
            $('.delete-btn').click(function() {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This customer booking will be deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
