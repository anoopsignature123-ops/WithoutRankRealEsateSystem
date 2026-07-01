@extends('layouts.app')

@section('content')
    <div class="container-fluid transaction-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-journal-bookmark"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Business Details</span>
                        <h3 class="fw-bold mb-1 text-dark">Booking Details List</h3>
                        <p class="text-muted mb-0 small">View customer bookings, plot details and payment records from your business team.</p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('associate-panel.due-emi-amount') }}" class="btn btn-outline-success">
                        <i class="bi bi-exclamation-circle me-1"></i> Due EMI Amount
                    </a>
                    <a href="{{ route('associate-panel.team-business-report') }}" class="btn btn-success">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i> Team Business Report
                    </a>
                </div>
            </div>
        </div>

        <div class="transaction-card mb-4">
            <div class="transaction-card-body">
                <div class="transaction-section-title">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-section-title-icon"><i class="bi bi-funnel"></i></span>
                        <div>
                            <h5 class="fw-bold mb-1">Search Criteria</h5>
                            <small class="text-muted">Filter by project, block, plot, customer, booking and date range.</small>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('associate-panel.booking-detail') }}">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Project Name</label>
                            <select name="project_id" id="project_id" class="form-select">
                                <option value="">Select Project</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Block</label>
                            <select name="block_id" id="block_id" class="form-select">
                                <option value="">Select Block</option>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Plot</label>
                            <select name="plot_id" id="plot_id" class="form-select">
                                <option value="">Select Plot</option>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Customer ID</label>
                            <input type="text" name="customer_id" id="customer_id" class="form-control"
                                placeholder="Auto-filled after plot selection" value="{{ request('customer_id') }}" readonly>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Booking ID</label>
                            <input type="text" name="booking_id" id="booking_id" class="form-control"
                                placeholder="Auto-filled after plot selection" value="{{ request('booking_id') }}" readonly>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>

                        <div class="col-lg-3 col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-success flex-fill">
                                <i class="bi bi-search me-1"></i> Search
                            </button>
                            <a href="{{ route('associate-panel.booking-detail') }}" class="btn btn-outline-secondary flex-fill">
                                <i class="bi bi-arrow-clockwise me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="transaction-card transaction-history-card mb-4">
            <div class="transaction-history-head">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-section-title-icon"><i class="bi bi-table"></i></span>
                    <div>
                        <h5 class="fw-bold mb-1">Booking Details List</h5>
                        <small class="text-muted">Grouped by receipt so multiple plot payments appear in one row.</small>
                    </div>
                </div>
                <span class="transaction-count">{{ $bookings->count() }} Records</span>
            </div>

            <div class="transaction-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 transaction-table w-100" id="bookingTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Receipt / Booking</th>
                                <th>Customer</th>
                                <th>Associate</th>
                                <th>Project / Plots</th>
                                <th>Plan</th>
                                <th>Payment Type</th>
                                <th>Payable</th>
                                <th>Paid</th>
                                <th>Date</th>
                                <th>Mode</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $key => $record)
                                @php
                                    $paymentType = match ($record->payment_type) {
                                        'booking_fee' => 'Booking Amount',
                                        'one_time' => 'One Time',
                                        'emi_payment' => 'EMI Payment',
                                        'mixed' => 'Mixed',
                                        default => ucwords(str_replace('_', ' ', $record->payment_type ?? '-')),
                                    };
                                    $planType = $record->plan_type === 'emi_plan'
                                        ? 'EMI Plan'
                                        : ($record->plan_type === 'mixed' ? 'Mixed' : 'Full Payment');
                                    $statusClass = match ($record->payment_status) {
                                        'paid', 'cleared' => 'bg-success-subtle text-success border border-success-subtle',
                                        'pending', 'hold', 'mixed' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                        default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                    };
                                    $modalId = 'bookingDetailModal' . $record->id;
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <strong>{{ $record->receipt_number }}</strong>
                                        <small class="text-muted d-block">{{ $record->booking_code }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $record->customer_name }}</strong>
                                        <small class="text-muted d-block">{{ $record->customer_code }}</small>
                                    </td>
                                    <td>{{ $record->associate_name }}</td>
                                    <td>
                                        <strong>{{ $record->project_name }}</strong>
                                        <small class="text-muted d-block">
                                            Block {{ $record->block_name }} / Plot {{ $record->plot_numbers }}
                                        </small>
                                        @if ($record->plot_count > 1)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle mt-1">
                                                {{ $record->plot_count }} Plots
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $planType }}</td>
                                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $paymentType }}</span></td>
                                    <td class="fw-bold">&#8377;{{ number_format($record->payable_amount, 2) }}</td>
                                    <td class="fw-bold text-success">&#8377;{{ number_format($record->paid_amount, 2) }}</td>
                                    <td>{{ $record->created_at?->format('d M Y') ?? '-' }}</td>
                                    <td>{{ strtoupper(str_replace('_', ' / ', $record->payment_mode ?? '-')) }}</td>
                                    <td>
                                        <span class="badge {{ $statusClass }}">{{ ucfirst($record->payment_status ?? '-') }}</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-success btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                                            <i class="bi bi-eye me-1"></i> Details
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No booking details found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @foreach ($bookings as $record)
            @php
                $modalId = 'bookingDetailModal' . $record->id;
            @endphp
            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 customer-receipt-modal">
                        <div class="customer-receipt-head">
                            <div class="customer-receipt-title">
                                <div class="customer-receipt-icon"><i class="bi bi-journal-bookmark"></i></div>
                                <div>
                                    <span>Booking Payment Detail</span>
                                    <h5>{{ $record->booking_code }}</h5>
                                    <small>{{ $record->receipt_number }} | {{ $record->plot_count }} Plot{{ $record->plot_count === 1 ? '' : 's' }}</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-0">
                            <div class="customer-receipt-summary">
                                <div>
                                    <small>Payable</small>
                                    <strong>&#8377;{{ number_format($record->payable_amount, 2) }}</strong>
                                </div>
                                <div>
                                    <small>Paid</small>
                                    <strong>&#8377;{{ number_format($record->paid_amount, 2) }}</strong>
                                </div>
                                <div>
                                    <small>Customer</small>
                                    <strong>{{ $record->customer_name }}</strong>
                                </div>
                                <div>
                                    <small>Mode</small>
                                    <strong>{{ strtoupper(str_replace('_', ' / ', $record->payment_mode ?? '-')) }}</strong>
                                </div>
                            </div>

                            <div class="customer-receipt-body">
                                <div class="customer-receipt-panel">
                                    <div class="customer-receipt-panel-title">
                                        <i class="bi bi-map"></i>
                                        <span>Plot Details</span>
                                    </div>
                                    <div class="table-responsive transaction-mini-table">
                                        <table class="table table-hover align-middle mb-0 transaction-table">
                                            <thead>
                                                <tr>
                                                    <th>Plot</th>
                                                    <th>Project / Block</th>
                                                    <th>Area</th>
                                                    <th>Rate</th>
                                                    <th>Total Cost</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($record->plots as $plotSale)
                                                    <tr>
                                                        <td class="fw-bold text-success">{{ $plotSale?->plotDetail?->plot_number ?? '-' }}</td>
                                                        <td>
                                                            <strong>{{ $plotSale?->project?->name ?? '-' }}</strong>
                                                            <small class="text-muted d-block">Block {{ $plotSale?->block?->block ?? '-' }}</small>
                                                        </td>
                                                        <td>{{ number_format((float) ($plotSale?->plot_area ?? 0), 2) }} Sq.Ft.</td>
                                                        <td>&#8377;{{ number_format((float) ($plotSale?->plot_rate ?? 0), 2) }}</td>
                                                        <td class="fw-bold">&#8377;{{ number_format((float) ($plotSale?->total_plot_cost ?? $plotSale?->final_payable ?? 0), 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-3">No plot details found.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="customer-receipt-panel mt-4">
                                    <div class="customer-receipt-panel-title">
                                        <i class="bi bi-receipt"></i>
                                        <span>Payment Rows</span>
                                    </div>
                                    <div class="table-responsive transaction-mini-table">
                                        <table class="table table-hover align-middle mb-0 transaction-table">
                                            <thead>
                                                <tr>
                                                    <th>Plot</th>
                                                    <th>Payment Type</th>
                                                    <th>Paid</th>
                                                    <th>Due</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($record->payments as $payment)
                                                    <tr>
                                                        <td>{{ $payment->plotSaleDetail?->plotDetail?->plot_number ?? '-' }}</td>
                                                        <td>{{ ucwords(str_replace('_', ' ', $payment->transaction_category ?? '-')) }}</td>
                                                        <td class="fw-bold text-success">&#8377;{{ number_format((float) ($payment->paid_amount ?? $payment->booking_amount ?? 0), 2) }}</td>
                                                        <td class="fw-bold text-danger">&#8377;{{ number_format((float) ($payment->due_amount ?? 0), 2) }}</td>
                                                        <td>{{ ucfirst($payment->payment_status ?? '-') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
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
            if ($('#bookingTable tbody tr td[colspan]').length === 0) {
                $('#bookingTable').DataTable({
                    pageLength: 10,
                    responsive: false,
                    scrollX: true,
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search booking..."
                    }
                });
            }

            function populateSelect(url, targetElement, defaultText, selectedValue = null) {
                $(targetElement).html(`<option value="">${defaultText}</option>`);
                $.get(url, function(data) {
                    data.forEach(item => {
                        const isSelected = (item.id == selectedValue) ? 'selected' : '';
                        const label = item.block || item.plot_number || item.name || item.id;
                        $(targetElement).append(`<option value="${item.id}" ${isSelected}>${label}</option>`);
                    });
                });
            }

            $('#project_id').change(function() {
                $('#customer_id, #booking_id').val('');
                $('#plot_id').html('<option value="">Select Plot</option>');
                populateSelect('/associate-panel/get-blocks/' + $(this).val(), '#block_id', 'Select Block');
            });

            $('#block_id').change(function() {
                $('#customer_id, #booking_id').val('');
                populateSelect('/associate-panel/get-plots/' + $(this).val(), '#plot_id', 'Select Plot');
            });

            $('#plot_id').change(function() {
                $('#customer_id, #booking_id').val('');
                if (!$(this).val()) {
                    return;
                }
                $.get('/associate-panel/get-booking-by-plot/' + $(this).val(), function(data) {
                    $('#customer_id').val(data.customer_id || '');
                    $('#booking_id').val(data.booking_id || '');
                });
            });

            @if (request('project_id'))
                populateSelect('/associate-panel/get-blocks/{{ request('project_id') }}', '#block_id',
                    'Select Block', '{{ request('block_id') }}');
            @endif
            @if (request('block_id'))
                populateSelect('/associate-panel/get-plots/{{ request('block_id') }}', '#plot_id',
                    'Select Plot', '{{ request('plot_id') }}');
            @endif
        });
    </script>
@endpush
