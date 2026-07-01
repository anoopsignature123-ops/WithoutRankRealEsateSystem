@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 transaction-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-journal-text"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Business Details</span>
                        <h3 class="fw-bold mb-1 text-dark">Customer Ledger Search</h3>
                        <p class="text-muted mb-0 small">Search booking ledger with customer, plot and payment history details.</p>
                    </div>
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
                            <small class="text-muted">Select project, block and plot to auto-fill the booking ID.</small>
                        </div>
                    </div>
                </div>

                <form action="{{ route('associate-panel.customer-ledger') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Project</label>
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
                                @foreach ($blocks as $block)
                                    <option value="{{ $block->id }}" {{ request('block_id') == $block->id ? 'selected' : '' }}>
                                        {{ $block->block }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Plot</label>
                            <select name="plot_id" id="plot_id" class="form-select">
                                <option value="">Select Plot</option>
                                @foreach ($plots as $plot)
                                    <option value="{{ $plot->id }}" {{ request('plot_id') == $plot->id ? 'selected' : '' }}>
                                        {{ $plot->plot_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Booking ID</label>
                            <input type="text" name="booking_id" id="booking_id" class="form-control"
                                value="{{ request('booking_id') }}" placeholder="Auto-filled after plot selection" readonly>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-search me-1"></i> Search Ledger
                            </button>
                            <a href="{{ route('associate-panel.customer-ledger') }}" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-arrow-clockwise me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($ledgerData)
            @php
                $firstPayment = $ledgerData->first_payment;
                $bookingPayment = $ledgerData->booking_payment;
                $planType = $firstPayment?->plan_type === 'emi_plan' ? 'EMI Plan' : 'Full Payment';
                $emiMonths = (int) ($bookingPayment?->emi_months ?? $firstPayment?->emi_months ?? 0);
                $installmentAmount = (float) ($bookingPayment?->after_booking_payable_amount ?? $firstPayment?->after_booking_payable_amount ?? 0);
                $bookingDate = $bookingPayment?->created_at ?? $ledgerData->booking->created_at;
            @endphp

            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="transaction-card h-100 border-start border-4 border-secondary">
                        <div class="transaction-card-body py-3">
                            <small class="text-muted fw-semibold">Total Plot Cost</small>
                            <h4 class="fw-bold mb-0">&#8377;{{ number_format($ledgerData->plot_amount, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="transaction-card h-100 border-start border-4 border-success">
                        <div class="transaction-card-body py-3">
                            <small class="text-muted fw-semibold">Confirmed Paid</small>
                            <h4 class="fw-bold text-success mb-0">&#8377;{{ number_format($ledgerData->paid_amount, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="transaction-card h-100 border-start border-4 border-warning">
                        <div class="transaction-card-body py-3">
                            <small class="text-muted fw-semibold">Hold / Pending</small>
                            <h4 class="fw-bold text-warning mb-0">&#8377;{{ number_format($ledgerData->hold_amount, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="transaction-card h-100 border-start border-4 border-danger">
                        <div class="transaction-card-body py-3">
                            <small class="text-muted fw-semibold">Due Amount</small>
                            <h4 class="fw-bold text-danger mb-0">&#8377;{{ number_format($ledgerData->due_amount, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="transaction-card mb-4">
                <div class="transaction-card-body">
                    <div class="transaction-section-title">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon"><i class="bi bi-person-vcard"></i></span>
                            <div>
                                <h5 class="fw-bold mb-1">Customer & Booking Details</h5>
                                <small class="text-muted">{{ $ledgerData->booking->booking_code }} | {{ $ledgerData->customer_id }}</small>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $planType }}</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <label class="small text-muted fw-semibold">Customer Name</label>
                            <div class="fw-bold">{{ $ledgerData->customer_name }}</div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="small text-muted fw-semibold">Contact No</label>
                            <div class="fw-bold">{{ $ledgerData->booking->primaryDetail?->mobile ?? '-' }}</div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="small text-muted fw-semibold">Associate</label>
                            <div class="fw-bold">{{ $ledgerData->associate_name }}</div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="small text-muted fw-semibold">Booking Date</label>
                            <div class="fw-bold">{{ $ledgerData->booking->created_at?->format('d M Y') ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <label class="small text-muted fw-semibold">Address</label>
                            <div class="fw-bold">{{ $ledgerData->booking->primaryDetail?->address ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="transaction-card mb-4">
                <div class="transaction-card-body">
                    <div class="transaction-section-title">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon"><i class="bi bi-map"></i></span>
                            <div>
                                <h5 class="fw-bold mb-1">Plot Details</h5>
                                <small class="text-muted">Multiple plot booking stays grouped under the same ledger.</small>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            {{ $ledgerData->plots->count() }} Plot{{ $ledgerData->plots->count() === 1 ? '' : 's' }}
                        </span>
                    </div>

                    <div class="transaction-table-wrap">
                        <table class="table table-hover align-middle mb-0 transaction-table">
                            <thead>
                                <tr>
                                    <th>Plot</th>
                                    <th>Project / Block</th>
                                    <th>Area</th>
                                    <th>Rate</th>
                                    <th>PLC</th>
                                    <th>Total Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ledgerData->plots as $plotSale)
                                    <tr>
                                        <td class="fw-bold text-success">{{ $plotSale->plotDetail?->plot_number ?? '-' }}</td>
                                        <td>
                                            <strong>{{ $plotSale->project?->name ?? '-' }}</strong>
                                            <small class="text-muted d-block">Block {{ $plotSale->block?->block ?? '-' }}</small>
                                        </td>
                                        <td>{{ number_format((float) ($plotSale->plot_area ?? 0), 2) }} Sq.Ft.</td>
                                        <td>&#8377;{{ number_format((float) ($plotSale->plot_rate ?? 0), 2) }}</td>
                                        <td>&#8377;{{ number_format((float) ($plotSale->plc_amount ?? 0), 2) }}</td>
                                        <td class="fw-bold">&#8377;{{ number_format((float) ($plotSale->total_plot_cost ?? $plotSale->final_payable ?? 0), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($firstPayment?->plan_type === 'emi_plan')
                <div class="transaction-card mb-4">
                    <div class="transaction-card-body">
                        <div class="transaction-section-title">
                            <div class="d-flex align-items-center gap-3">
                                <span class="transaction-section-title-icon"><i class="bi bi-calendar2-week"></i></span>
                                <div>
                                    <h5 class="fw-bold mb-1">EMI Summary</h5>
                                    <small class="text-muted">Installment progress is calculated receipt-wise.</small>
                                </div>
                            </div>
                            <button class="btn btn-outline-success btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#emiDetailsModal">
                                <i class="bi bi-eye me-1"></i> View EMI
                            </button>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted fw-semibold">Total Installments</small>
                                    <h5 class="fw-bold mb-0">{{ $emiMonths }}</h5>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted fw-semibold">Installment Amount</small>
                                    <h5 class="fw-bold text-success mb-0">&#8377;{{ number_format($installmentAmount, 2) }}</h5>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted fw-semibold">Paid Installments</small>
                                    <h5 class="fw-bold text-info mb-0">{{ $ledgerData->emi_installments->count() }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="transaction-card">
                <div class="transaction-card-body">
                    <div class="transaction-section-title">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon"><i class="bi bi-receipt"></i></span>
                            <div>
                                <h5 class="fw-bold mb-1">Payment History</h5>
                                <small class="text-muted">Grouped by receipt number, so multiple plot receipts show once.</small>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            {{ $ledgerData->receipt_groups->count() }} Receipt{{ $ledgerData->receipt_groups->count() === 1 ? '' : 's' }}
                        </span>
                    </div>

                    <div class="transaction-table-wrap">
                        <table class="table table-hover align-middle mb-0 transaction-table w-100" id="ledgerTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Receipt</th>
                                    <th>Plots</th>
                                    <th>Payment Type</th>
                                    <th>Paid Amount</th>
                                    <th>Due</th>
                                    <th>Mode</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ledgerData->receipt_groups as $group)
                                    @php
                                        $paymentType = match ($group->payment_type) {
                                            'booking_fee' => 'Booking Amount',
                                            'emi_payment' => 'EMI Amount',
                                            'one_time' => 'One Time',
                                            'mixed' => 'Mixed',
                                            default => ucwords(str_replace('_', ' ', $group->payment_type ?? '-')),
                                        };
                                        $statusClass = match ($group->payment_status) {
                                            'paid', 'cleared' => 'bg-success-subtle text-success border border-success-subtle',
                                            'hold', 'pending', 'mixed' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                            default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                        };
                                        $modalId = 'receiptLedgerModal' . $group->id;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $group->receipt_number }}</strong>
                                            @if ($group->manual_receipt_number)
                                                <small class="text-muted d-block">Manual: {{ $group->manual_receipt_number }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $group->plots }}</td>
                                        <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $paymentType }}</span></td>
                                        <td class="fw-bold text-success">&#8377;{{ number_format($group->paid_amount, 2) }}</td>
                                        <td class="fw-bold text-danger">&#8377;{{ number_format($group->due_amount, 2) }}</td>
                                        <td>{{ strtoupper(str_replace('_', ' / ', $group->payment_mode ?? '-')) }}</td>
                                        <td>{{ $group->created_at?->format('d M Y') ?? '-' }}</td>
                                        <td><span class="badge {{ $statusClass }}">{{ ucfirst($group->payment_status ?? '-') }}</span></td>
                                        <td>
                                            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#{{ $modalId }}">
                                                <i class="bi bi-eye me-1"></i> Details
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @foreach ($ledgerData->receipt_groups as $group)
                <div class="modal fade" id="receiptLedgerModal{{ $group->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 customer-receipt-modal">
                            <div class="customer-receipt-head">
                                <div class="customer-receipt-title">
                                    <div class="customer-receipt-icon"><i class="bi bi-receipt"></i></div>
                                    <div>
                                        <span>Receipt Detail</span>
                                        <h5>{{ $group->receipt_number }}</h5>
                                        <small>{{ $ledgerData->booking->booking_code }} | {{ $group->plots }}</small>
                                    </div>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="customer-receipt-summary">
                                    <div>
                                        <small>Paid Amount</small>
                                        <strong>&#8377;{{ number_format($group->paid_amount, 2) }}</strong>
                                    </div>
                                    <div>
                                        <small>Due Amount</small>
                                        <strong>&#8377;{{ number_format($group->due_amount, 2) }}</strong>
                                    </div>
                                    <div>
                                        <small>Mode</small>
                                        <strong>{{ strtoupper(str_replace('_', ' / ', $group->payment_mode ?? '-')) }}</strong>
                                    </div>
                                    <div>
                                        <small>Status</small>
                                        <strong>{{ ucfirst($group->payment_status ?? '-') }}</strong>
                                    </div>
                                </div>
                                <div class="customer-receipt-body">
                                    <div class="customer-receipt-panel">
                                        <div class="customer-receipt-panel-title">
                                            <i class="bi bi-list-check"></i>
                                            <span>Payment Rows</span>
                                        </div>
                                        <div class="table-responsive transaction-mini-table overflow-auto">
                                            <table class="table table-hover align-middle mb-0 transaction-table w-100 ">
                                                <thead>
                                                    <tr>
                                                        <th>Plot</th>
                                                        <th>Type</th>
                                                        <th>Paid</th>
                                                        <th>Due</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($group->payments as $payment)
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

            @if ($firstPayment?->plan_type === 'emi_plan')
                <div class="modal fade" id="emiDetailsModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 customer-receipt-modal">
                            <div class="customer-receipt-head">
                                <div class="customer-receipt-title">
                                    <div class="customer-receipt-icon"><i class="bi bi-calendar-check"></i></div>
                                    <div>
                                        <span>EMI Ledger</span>
                                        <h5>{{ $ledgerData->booking->booking_code }}</h5>
                                        <small>{{ $ledgerData->customer_name }} | Plot {{ $ledgerData->plot_no }}</small>
                                    </div>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="customer-receipt-body">
                                    <div class="table-responsive transaction-mini-table">
                                        <table class="table table-hover align-middle mb-0 transaction-table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Installment</th>
                                                    <th>EMI Amount</th>
                                                    <th>Due Date</th>
                                                    <th>Receipt No</th>
                                                    <th>Mode</th>
                                                    <th>Paid Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @for ($i = 1; $i <= $emiMonths; $i++)
                                                    @php
                                                        $emiPayment = $ledgerData->emi_installments->get($i - 1);
                                                        $dueDate = $bookingDate ? $bookingDate->copy()->addMonths($i)->format('d M Y') : '-';
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $i }}</td>
                                                        <td class="fw-bold">EMI {{ $i }}</td>
                                                        <td>&#8377;{{ number_format($installmentAmount, 2) }}</td>
                                                        <td>{{ $dueDate }}</td>
                                                        <td>{{ $emiPayment?->receipt_number ?? '-' }}</td>
                                                        <td>{{ strtoupper($emiPayment?->payment_mode ?? '-') }}</td>
                                                        <td>{{ $emiPayment?->created_at?->format('d M Y') ?? '-' }}</td>
                                                        <td>
                                                            @if ($emiPayment)
                                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Paid</span>
                                                            @else
                                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Unpaid</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endfor
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light border-0">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @elseif (request()->filled('booking_id'))
            <div class="alert alert-warning border-0 shadow-sm">
                No ledger found for this booking in your business team.
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            function populateSelect(url, target, placeholder, selectedValue = null) {
                $(target).html(`<option value="">${placeholder}</option>`);
                $.get(url, function(data) {
                    data.forEach(function(item) {
                        const label = item.block || item.plot_number || item.name || item.id;
                        const selected = item.id == selectedValue ? 'selected' : '';
                        $(target).append(`<option value="${item.id}" ${selected}>${label}</option>`);
                    });
                });
            }

            $('#project_id').change(function() {
                $('#booking_id').val('');
                $('#plot_id').html('<option value="">Select Plot</option>');
                if (!$(this).val()) {
                    $('#block_id').html('<option value="">Select Block</option>');
                    return;
                }
                populateSelect('/associate-panel/get-blocks/' + $(this).val(), '#block_id', 'Select Block');
            });

            $('#block_id').change(function() {
                $('#booking_id').val('');
                if (!$(this).val()) {
                    $('#plot_id').html('<option value="">Select Plot</option>');
                    return;
                }
                populateSelect('/associate-panel/get-plots/' + $(this).val(), '#plot_id', 'Select Plot');
            });

            $('#plot_id').change(function() {
                $('#booking_id').val('');
                if (!$(this).val()) {
                    return;
                }
                $.get('/associate-panel/get-booking-by-plot/' + $(this).val(), function(data) {
                    $('#booking_id').val(data.booking_id || '');
                });
            });

            if ($('#ledgerTable tbody tr td[colspan]').length === 0) {
                $('#ledgerTable').DataTable({
                    pageLength: 10,
                    responsive: false,
                    scrollX: true,
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search payment history..."
                    }
                });
            }
        });
    </script>
@endpush
