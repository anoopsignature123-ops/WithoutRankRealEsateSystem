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
                            <i class="bi bi-journal-check fs-3"></i>
                        </div>

                        <div>
                            <h3 class="fw-bold mb-1 text-dark">
                                Customer Booking Management
                            </h3>
                            <p class="text-muted mb-0 small">
                                Manage customer bookings and booking status.
                            </p>
                        </div>
                    </div>

                    @can('customer-booking-modify')
                        <a href="{{ route('customer-booking.create') }}"
                            class="btn btn-success rounded-pill px-4">
                            <i class="bi bi-plus-circle me-1"></i>
                            Add New Customer
                        </a>
                    @endcan

                </div>
            </div>
        </div>

        {{-- Customer Booking Table --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

            <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">
                            Booking List
                        </h5>

                        <small class="text-muted">
                            Customer booking records with associate and status details.
                        </small>
                    </div>

                    <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                        Total Records: {{ $customers->count() }}
                    </span>
                </div>
            </div>

            <div class="card-body p-4">

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="customerBookingTable">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Customer ID</th>
                                <th>Customer Type</th>
                                <th>Associate</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($customers as $key => $customer)
                                @php
                                    $primary = $customer->primaryDetail;
                                    $profileImage = $primary?->customerDocument?->profile_picture;
                                    $customerName = ucfirst($primary?->name ?? ($customer->customer_name ?? 'N/A'));
                                @endphp

                                <tr>
                                    <td>
                                        <span class="text-muted small">
                                            #{{ $key + 1 }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if ($profileImage)
                                                <img src="{{ asset('storage/' . $profileImage) }}"
                                                    width="46"
                                                    height="46"
                                                    class="rounded-circle border object-fit-cover"
                                                    alt="Customer">
                                            @else
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($customerName) }}&background=198754&color=ffffff"
                                                    width="46"
                                                    height="46"
                                                    class="rounded-circle border"
                                                    alt="Customer">
                                            @endif

                                            <div>
                                                <div class="fw-bold text-dark">
                                                    {{ $customerName }}
                                                </div>

                                                <small class="text-muted">
                                                    {{ $primary?->correspondenceDetail?->telephone_no ?? 'No contact' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                            {{ $customer->customer_code ?? 'N/A' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                            {{ ucwords(str_replace('_', ' ', $customer->customer_type ?? 'N/A')) }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ $customer->associate_name ?? 'N/A' }}
                                        </div>

                                        @if (!empty($customer->associate_code))
                                            <small class="text-muted">
                                                {{ $customer->associate_code }}
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($customer->status == 'draft')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2">
                                                <i class="bi bi-exclamation-circle me-1"></i>
                                                Incomplete
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Completed
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @can('customer-booking-modify')
                                            <div class="d-inline-flex gap-2">

                                                <a href="{{ route('customer-booking.edit', [
                                                    $customer->id,
                                                    'step' => $customer->status == 'completed' ? 1 : $customer->current_step,
                                                ]) }}"
                                                    class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                    <i class="bi bi-pencil-square me-1"></i>
                                                    Edit
                                                </a>

                                                <form action="{{ route('customer-booking.destroy', $customer->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-3 delete-btn">
                                                        <i class="bi bi-trash me-1"></i>
                                                        Delete
                                                    </button>
                                                </form>

                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-journal-x fs-1 d-block mb-2 text-muted"></i>
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

@push('styles')
    <style>
        #customerBookingTable th,
        #customerBookingTable td {
            vertical-align: middle;
        }

        #customerBookingTable thead th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .4px;
            font-weight: 700;
            color: #475569;
        }

        #customerBookingTable tbody td {
            padding-top: 14px;
            padding-bottom: 14px;
        }

        #customerBookingTable tbody tr:hover {
            background: #fafafa;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {

            if ($('#customerBookingTable tbody tr td').attr('colspan') == undefined) {
                $('#customerBookingTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                    ordering: true,
                });
            }

            $('.delete-btn').click(function() {
                let form = $(this).closest('form');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This customer booking will be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

        });
    </script>
@endpush