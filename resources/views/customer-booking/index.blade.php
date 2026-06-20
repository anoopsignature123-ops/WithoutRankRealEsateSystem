@extends('layouts.app')

@section('content')
    @php
        $completedCount = $customers->where('status', 'completed')->count();
        $pendingCount = $customers->where('status', 'pending')->count();
        $draftCount = $customers->where('status', 'draft')->count();
    @endphp

    <div class="container-fluid mt-4 customer-booking-admin-page">
        <div class="customer-booking-admin-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="customer-booking-admin-hero-icon">
                        <i class="bi bi-journal-check"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Booking Desk</span>
                        <h3 class="fw-bold mb-1 text-dark">Customer Booking</h3>
                        <p class="text-muted mb-0 small">Manage customer profiles, plot bookings and booking progress.</p>
                    </div>
                </div>

                @can('customer-booking-modify')
                    <a href="{{ route('customer-booking.create') }}" class="btn btn-success customer-booking-admin-primary">
                        <i class="bi bi-plus-circle me-1"></i> Add New Customer
                    </a>
                @endcan
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="customer-booking-admin-stats mb-4">
            <div class="customer-booking-admin-stat">
                <small>Total Customers</small>
                <strong>{{ $customers->count() }}</strong>
            </div>
            <div class="customer-booking-admin-stat success">
                <small>Completed</small>
                <strong>{{ $completedCount }}</strong>
            </div>
            <div class="customer-booking-admin-stat warning">
                <small>Pending</small>
                <strong>{{ $pendingCount }}</strong>
            </div>
            <div class="customer-booking-admin-stat info">
                <small>Draft</small>
                <strong>{{ $draftCount }}</strong>
            </div>
        </div>

        <div class="customer-booking-admin-table-card">
            <div class="customer-booking-admin-table-head">
                <div class="d-flex align-items-center gap-3">
                    <span class="customer-booking-admin-table-icon">
                        <i class="bi bi-people"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-1">Booking Records</h5>
                        <small class="text-muted">All customer booking records are listed below.</small>
                    </div>
                </div>

                <span class="customer-booking-admin-count">{{ $customers->count() }} Records</span>
            </div>

            <div class="customer-booking-admin-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 customer-booking-admin-table" id="customerBookingTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Booking / Plot</th>
                                <th>Customer Type</th>
                                <th>Associate</th>
                                <th>Status</th>
                                @can('customer-booking-modify')
                                    <th width="170">Action</th>
                                @endcan
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($customers as $key => $customer)
                                @php
                                    $primary = $customer->primaryDetail;
                                    $plotSale = $customer->plotSaleDetail;
                                    $profileImage = $primary?->customerDocument?->profile_picture;
                                    $customerName = ucfirst($primary?->name ?? ($customer->customer_name ?? 'N/A'));
                                    $mobileNumber = $primary?->correspondenceDetail?->mobile_number ?? 'No contact';
                                    $status = $customer->status ?? 'draft';
                                    $statusMeta = match ($status) {
                                        'completed' => ['Completed', 'success', 'bi-check-circle'],
                                        'pending' => ['Pending', 'warning', 'bi-clock-history'],
                                        default => ['Incomplete', 'danger', 'bi-exclamation-circle'],
                                    };
                                    $editStep = $customer->status === 'completed' ? 1 : $customer->current_step;
                                @endphp

                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if ($profileImage)
                                                <img src="{{ asset('storage/' . $profileImage) }}"
                                                    width="46" height="46"
                                                    class="rounded-circle border object-fit-cover" alt="Customer">
                                            @else
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($customerName) }}&background=198754&color=ffffff"
                                                    width="46" height="46" class="rounded-circle border" alt="Customer">
                                            @endif

                                            <div>
                                                <strong class="d-block">{{ $customerName }}</strong>
                                                <small class="text-muted">{{ $customer->customer_code ?? 'N/A' }} | {{ $mobileNumber }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <strong>{{ $plotSale?->booking_code ?? $customer->booking_code ?? 'Not booked' }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $plotSale?->project?->name ?? '-' }} /
                                            {{ $plotSale?->block?->block ?? '-' }} /
                                            Plot {{ $plotSale?->plotDetail?->plot_number ?? '-' }}
                                        </small>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ ucwords(str_replace('_', ' ', $customer->customer_type ?? 'N/A')) }}
                                        </span>
                                    </td>

                                    <td>
                                        <strong>{{ $customer->associate_code ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $customer->associate_name ?? 'N/A' }}</small>
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $statusMeta[1] }} {{ $statusMeta[1] === 'warning' ? 'text-dark' : '' }}">
                                            <i class="bi {{ $statusMeta[2] }} me-1"></i>{{ $statusMeta[0] }}
                                        </span>
                                        <br>
                                        <small class="text-muted">Step {{ $customer->current_step ?? 1 }}</small>
                                    </td>

                                    @can('customer-booking-modify')
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('customer-booking.edit', [$customer->id, 'step' => $editStep]) }}"
                                                    class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                                </a>

                                                <form action="{{ route('customer-booking.destroy', $customer->id) }}" method="POST"
                                                    class="customer-booking-delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->can('customer-booking-modify') ? 7 : 6 }}"
                                        class="text-center text-muted py-5">
                                        <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                                        No customer booking found.
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
            if ($('#customerBookingTable tbody tr td').attr('colspan') === undefined) {
                $('#customerBookingTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                    ordering: true,
                });
            }

            $(document).on('click', '.delete-btn', function() {
                const button = $(this);
                const form = button.closest('form');

                Swal.fire({
                    title: 'Delete customer booking?',
                    text: 'This customer booking and related details will be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'
                        );
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
