@extends('layouts.app')

@push('title')
    Customer List
@endpush
@section('content')
    @php
        $totalBookings = $customers->sum(fn($customer) => ($customer->booked_plots ?? collect())->count());
        $referenceCount = $customers->filter(fn($customer) => filled($customer->customer_id))->count();
    @endphp

    <div class="container-fluid mt-4 customer-list-page">
        <div class="customer-list-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="customer-list-hero-icon">
                        <i class="bi bi-people"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Customer Directory</span>
                        <h3 class="fw-bold mb-1 text-dark">Customer List</h3>
                        <p class="text-muted mb-0 small">View customer contact details and booked plot summary.</p>
                    </div>
                </div>

                <span class="customer-list-count">{{ $customers->count() }} Customers</span>
            </div>
        </div>

        <div class="customer-list-stats mb-4">
            <div class="customer-list-stat">
                <small>Total Customers</small>
                <strong>{{ $customers->count() }}</strong>
            </div>
            <div class="customer-list-stat success">
                <small>Total Bookings</small>
                <strong>{{ $totalBookings }}</strong>
            </div>
            <div class="customer-list-stat info">
                <small>Reference Customers</small>
                <strong>{{ $referenceCount }}</strong>
            </div>
        </div>

        <div class="customer-list-table-card">
            <div class="customer-list-table-head">
                <div class="d-flex align-items-center gap-3">
                    <span class="customer-list-table-icon">
                        <i class="bi bi-person-lines-fill"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-1">Customer Records</h5>
                        <small class="text-muted">Customers with booked plots are listed below.</small>
                    </div>
                </div>
                <span class="customer-list-count">{{ $customers->count() }} Records</span>
            </div>

            <div class="customer-list-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 customer-list-table" id="customerListTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Reference</th>
                                <th>Contact</th>
                                <th>Address</th>
                                <th>Password</th>
                                <th>Bookings</th>
                                <th width="110">Plots</th>
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
                                    $parentCustomer = $customer->parentCustomer;
                                    $plots = $customer->booked_plots ?? collect();
                                    $bookedPlotCount = $plots->where('admin_booking_status', 'booked')->count();
                                    $holdPlotCount = $plots->where('admin_booking_status', 'hold')->count();
                                    $customerName = ucfirst($primary?->name ?? 'N/A');
                                @endphp

                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <strong>{{ $customer->customer_code ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $customerName }}
                                            @if ($customer->customer_type)
                                                | {{ ucwords(str_replace('_', ' ', $customer->customer_type)) }}
                                            @endif
                                        </small>
                                    </td>

                                    <td>
                                        @if ($parentCustomer)
                                            <span class="badge bg-light text-dark border">
                                                {{ $parentCustomer->customer_code }}
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                Self
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <strong>+91 {{ $contact?->mobile_number ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $contact?->email ?? 'N/A' }}</small>
                                    </td>

                                    <td>
                                        <span class="customer-list-address" title="{{ $address }}">
                                            {{ $address }}
                                        </span>
                                    </td>

                                    <td>
                                        <span
                                            style="display:inline-flex;align-items:center;padding:5px 12px;background:#eef6ff;border:1px solid #b6d4fe;border-radius:50px;font-family:Consolas,monospace;font-size:13px;font-weight:700;color:#0d6efd;">
                                            <i class="bi bi-key-fill me-1"></i>
                                            {{ $customer?->plain_password ?? 'N/A' }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <span
                                                class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                                                <i class="bi bi-grid-3x3-gap me-1"></i>
                                                {{ $plots->count() }} {{ $plots->count() === 1 ? 'Plot' : 'Plots' }}
                                            </span>

                                            @if ($bookedPlotCount > 0)
                                                <span
                                                    class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    {{ $bookedPlotCount }} Booked
                                                </span>
                                            @endif

                                            @if ($holdPlotCount > 0)
                                                <span
                                                    class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2">
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ $holdPlotCount }} Hold
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                            data-bs-target="#plotModal{{ $customer->id }}">
                                            <i class="bi bi-eye me-1"></i> View
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="bi bi-people fs-2 d-block mb-2"></i>
                                        No customers found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @foreach ($customers as $customer)
            @php
                $primary = $customer->primaryDetail;
                $plots = $customer->booked_plots ?? collect();
            @endphp

            <div class="modal fade customer-list-modal" id="plotModal{{ $customer->id }}" tabindex="-1"
                aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="d-flex align-items-center gap-3">
                                <span class="customer-list-modal-icon">
                                    <i class="bi bi-grid-3x3-gap"></i>
                                </span>
                                <div>
                                    <span class="text-success fw-bold text-uppercase small">Booked Plot Details</span>
                                    <h5 class="modal-title fw-bold mb-0">
                                        {{ $customer->customer_code ?? 'N/A' }} - {{ $primary?->name ?? 'N/A' }}
                                    </h5>
                                </div>
                            </div>

                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="customer-list-modal-summary mb-3">
                                <div>
                                    <small>Customer ID</small>
                                    <strong>{{ $customer->customer_code ?? 'N/A' }}</strong>
                                </div>
                                <div>
                                    <small>Customer Name</small>
                                    <strong>{{ $primary?->name ?? 'N/A' }}</strong>
                                </div>
                                <div>
                                    <small>Total Bookings</small>
                                    <strong>{{ $plots->count() }}</strong>
                                </div>
                            </div>

                            @if ($plots->count() > 0)
                                <div class="table-responsive customer-list-modal-table-wrap">
                                    <table class="table table-hover align-middle mb-0 customer-list-modal-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Booking ID</th>
                                                <th>Project</th>
                                                <th>Block</th>
                                                <th>Plot No</th>
                                                <th>Plot Area</th>
                                                <th>Plot Rate</th>
                                                <th>Total Cost</th>
                                                <th>Status</th>
                                                <th>Booking Date</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($plots as $plotKey => $plot)
                                                @php
                                                    $adminBookingStatus = strtolower(
                                                        $plot->admin_booking_status ?? 'N/A',
                                                    );
                                                @endphp
                                                <tr>
                                                    <td>{{ $plotKey + 1 }}</td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border">
                                                            {{ $plot->booking_code ?? 'N/A' }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $plot->project?->name ?? 'N/A' }}</td>
                                                    <td>{{ $plot->block?->block ?? ($plot->block?->name ?? 'N/A') }}</td>
                                                    <td class="fw-bold text-success">
                                                        {{ $plot->plotDetail?->plot_number ?? ($plot->plotDetail?->plot_no ?? 'N/A') }}
                                                    </td>
                                                    <td>{{ $plot->plot_area ?? ($plot->plotDetail?->plot_area ?? 'N/A') }}
                                                    </td>
                                                    <td>&#8377;{{ number_format((float) ($plot->plot_rate ?? ($plot->rate ?? 0)), 2) }}
                                                    </td>
                                                    <td class="fw-bold">
                                                        &#8377;{{ number_format((float) ($plot->total_plot_cost ?? ($plot->total_amount ?? 0)), 2) }}
                                                    </td>
                                                    <td>
                                                        @if ($adminBookingStatus === 'booked')
                                                            <span
                                                                class="badge bg-success-subtle text-success border border-success-subtle">Booked</span>
                                                        @elseif ($adminBookingStatus === 'hold')
                                                            <span
                                                                class="badge bg-warning-subtle text-warning border border-warning-subtle">Hold</span>
                                                        @elseif ($plot->status === 'cancelled')
                                                            <span
                                                                class="badge bg-danger-subtle text-danger border">Cancelled</span>
                                                        @elseif ($plot->status === 'transferred')
                                                            <span
                                                                class="badge bg-warning-subtle text-warning border">Transferred</span>
                                                        @elseif ($plot->status === 'changed')
                                                            <span
                                                                class="badge bg-info-subtle text-info border">Changed</span>
                                                        @else
                                                            <span
                                                                class="badge bg-secondary-subtle text-secondary border">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $plot->booking_date ? \Carbon\Carbon::parse($plot->booking_date)->format('d-M-Y') : 'N/A' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    No booked plot found.
                                </div>
                            @endif
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            if ($('#customerListTable tbody tr td').attr('colspan') === undefined) {
                const table = $('#customerListTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                    lengthMenu: [5, 10, 25, 50],
                    columnDefs: [{
                        targets: 0,
                        orderable: false,
                        searchable: false
                    }]
                });

                table.on('order.dt search.dt draw.dt', function() {
                    table.column(0, {
                        search: 'applied',
                        order: 'applied'
                    }).nodes().each(function(cell, i) {
                        cell.innerHTML = i + 1;
                    });
                }).draw();
            }
        });
    </script>
@endpush
