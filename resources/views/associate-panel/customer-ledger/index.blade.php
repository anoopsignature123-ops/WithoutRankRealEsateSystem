@extends('layouts.app')

@section('content')

    <div class="container-fluid mt-4">

        {{-- Search Card --}}
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-white">
                <h5 class="fw-bold mb-0">
                    Customer Ledger Search
                </h5>
            </div>

            <div class="card-body">

                <form action="{{ route('associate-panel.customer-ledger') }}" method="GET">

                    <div class="row g-3">

                        <div class="col-md-3">

                            <label class="form-label fw-bold">
                                Project
                            </label>

                            <select name="project_id" id="project_id" class="form-select">

                                <option value="">
                                    Select Project
                                </option>

                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}"
                                        {{ request('project_id') == $project->id ? 'selected' : '' }}>

                                        {{ $project->name }}

                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div class="col-md-3">

                            <label class="form-label fw-bold">
                                Block
                            </label>

                            <select name="block_id" id="block_id" class="form-select">

                                <option value="">
                                    Select Block
                                </option>

                                @foreach ($blocks as $block)
                                    <option value="{{ $block->id }}"
                                        {{ request('block_id') == $block->id ? 'selected' : '' }}>

                                        {{ $block->block }}

                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div class="col-md-3">

                            <label class="form-label fw-bold">
                                Plot
                            </label>

                            <select name="plot_id" id="plot_id" class="form-select">

                                <option value="">
                                    Select Plot
                                </option>

                                @foreach ($plots as $plot)
                                    <option value="{{ $plot->id }}"
                                        {{ request('plot_id') == $plot->id ? 'selected' : '' }}>

                                        {{ $plot->plot_number }}

                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div class="col-md-3">

                            <label class="form-label fw-bold">
                                Booking ID
                            </label>

                            <input type="text" name="booking_id" id="booking_id" class="form-control"
                                value="{{ request('booking_id') }}" placeholder="Booking ID">

                        </div>

                        <div class="col-md-12">

                            <button type="submit" class="btn btn-primary">

                                <i class="fas fa-search me-1"></i>
                                Search

                            </button>

                            <a href="{{ route('associate-panel.customer-ledger') }}" class="btn btn-secondary">

                                Reset

                            </a>

                        </div>

                    </div>

                </form>

            </div>

        </div>

        @if ($ledgerData)

            @php
                $firstPayment = $ledgerData->payments->first();
                $paidAmount = $ledgerData->payments->where('payment_status', 'booked')->sum('net_payable_amount');
                $dueAmount = $ledgerData->plot_amount - $paidAmount;
                $emiMonths = $firstPayment?->emi_months ?? 0;
                $installmentAmount = $firstPayment?->after_booking_payable_amount ?? 0;
            @endphp

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 fw-bold">
                        Customer Info
                    </h5>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">Project Name</label>
                            <div>{{ $ledgerData->project_name }}</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">Block</label>
                            <div>{{ $ledgerData->block_name }}</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">Plot No</label>
                            <div>{{ $ledgerData->plot_no }}</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">Plot Type</label>
                            <div>Normal</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">Customer Id</label>
                            <div>{{ $ledgerData->customer_id }}</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">Customer Name</label>
                            <div>{{ $ledgerData->customer_name }}</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">Contact No</label>
                            <div>
                                {{ $ledgerData->booking->primaryDetail?->mobile ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">Address</label>
                            <div>
                                {{ $ledgerData->booking->primaryDetail?->address ?? '-' }}
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 fw-bold">
                        Payment Info
                    </h5>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Plan Type
                            </label>

                            <div>
                                @if ($firstPayment?->plan_type == 'emi_plan')
                                    <span class="badge bg-warning text-dark">
                                        EMI Plan
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        Full Payment
                                    </span>
                                @endif
                            </div>

                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Lucky Draw Coupon
                            </label>
                            <div>20000.00</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Booking Id
                            </label>
                            <div>{{ $ledgerData->booking->booking_code }}</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Total Plot Cost
                            </label>

                            <div class="fw-bold text-primary">
                                ₹{{ number_format($ledgerData->plot_amount, 2) }}
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                PLC Amount
                            </label>
                            <div>₹0.00</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Other Charges
                            </label>
                            <div>₹0.00</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Agent Discount
                            </label>
                            <div>₹0.00</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Allotment Amount
                            </label>
                            <div>₹0.00</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Booking Amount
                            </label>

                            <div class="fw-bold text-success">
                                ₹{{ number_format($firstPayment?->booking_amount ?? 0, 2) }}
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Installment Amount
                            </label>

                            <div class="fw-bold text-info">
                                ₹{{ number_format($installmentAmount, 2) }}
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Total No Of Installment
                            </label>

                            <div>{{ $emiMonths }}</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Paid Amount
                            </label>

                            <div class="fw-bold text-success">
                                ₹{{ number_format($paidAmount, 2) }}
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Booking Date
                            </label>

                            <div>
                                {{ $ledgerData->booking->created_at?->format('d-M-Y') }}
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Due Amount
                            </label>

                            <div class="fw-bold text-danger">
                                ₹{{ number_format($dueAmount, 2) }}
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Agent ID
                            </label>

                            <div>
                                {{ $ledgerData->booking->associate_id }}
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="fw-bold">
                                Development Charge
                            </label>

                            <div>₹0.00</div>
                        </div>

                    </div>

                    {{-- EMI VIEW --}}
                    @if ($firstPayment?->plan_type == 'emi_plan')
                        @php
                            $totalInstallments = $firstPayment->emi_months ?? 0;
                            $monthlyEmiAmount = $firstPayment->after_booking_payable_amount ?? 0;
                            $bookingDate = \Carbon\Carbon::parse($firstPayment->created_at);
                            $paidEmiPayments = $ledgerData->payments
                                ->where('transaction_category', 'emi_payment')
                                ->where('payment_status', 'booked')
                                ->values();
                            $paidCount = $paidEmiPayments->count();
                        @endphp

                        <div class="mt-4">
                            <button class="btn btn-warning" type="button" data-bs-toggle="collapse"
                                data-bs-target="#emiDetails">
                                <i class="fas fa-eye me-1"></i>
                                View EMI
                            </button>
                        </div>

                        <div class="collapse mt-4" id="emiDetails">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Installment</th>
                                            <th>EMI Amount</th>
                                            <th>Due Date</th>
                                            <th>Receipt No</th>
                                            <th>Payment Mode</th>
                                            <th>Paid Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for ($i = 1; $i <= $totalInstallments; $i++)
                                            @php
                                                $emiPayment = $paidEmiPayments[$i - 1] ?? null;
                                                $dueDate = $bookingDate->copy()->addMonths($i)->format('d-M-Y');
                                            @endphp
                                            <tr>
                                                <td>{{ $i }}</td>
                                                <td>EMI {{ $i }}</td>
                                                <td class="fw-bold text-primary">
                                                    ₹{{ number_format($monthlyEmiAmount, 2) }}
                                                </td>
                                                <td>{{ $dueDate }}</td>
                                                <td>{{ $emiPayment ? $emiPayment->receipt_number : '-' }}</td>
                                                <td>{{ $emiPayment ? ucfirst($emiPayment->payment_mode) : '-' }}</td>
                                                <td>{{ $emiPayment ? $emiPayment->created_at->format('d-M-Y') : '-' }}</td>
                                                <td>
                                                    @if ($emiPayment)
                                                        <span class="badge bg-success">Paid</span>
                                                    @else
                                                        <span class="badge bg-danger">Unpaid</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                </div>

            </div>

            {{-- Payment History --}}
            <div class="card shadow-sm border-0">

                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 fw-bold">
                        Payment History
                    </h5>
                </div>

                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle" id="ledgerTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Book. Id</th>
                                    <th>Receipt No</th>
                                    <th>Discount</th>
                                    <th>Payment Type</th>
                                    <th>Paid Amt.</th>
                                    <th>Pay Mode</th>
                                    <th>Date</th>
                                    <th>Payout Inc/Exc</th>
                                    <th>Payment Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $grandTotal = 0; @endphp
                                @foreach ($ledgerData->payments as $payment)
                                    @php $grandTotal += $payment->net_payable_amount; @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $ledgerData->booking->booking_code }}</td>
                                        <td>{{ $payment->receipt_number }}</td>
                                        <td>₹0.00</td>
                                        <td>
                                            @if ($payment->transaction_category == 'booking_fee')
                                                <span class="badge bg-primary">Booking Amount</span>
                                            @elseif($payment->transaction_category == 'emi_payment')
                                                <span class="badge bg-warning text-dark">EMI Amount</span>
                                            @else
                                                <span class="badge bg-info">One Time</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-success">
                                            ₹{{ number_format($payment->net_payable_amount, 2) }}
                                        </td>
                                        <td>{{ ucfirst($payment->payment_mode) }}</td>
                                        <td>{{ $payment->created_at->format('d-M-Y') }}</td>
                                        <td>
                                            @if ($payment->payment_status == 'booked')
                                                <span class="badge bg-success">Included</span>
                                            @else
                                                <span class="badge bg-danger">Not Proceeded</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($payment->payment_status == 'booked')
                                                <span class="badge bg-success">Clear</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Hold</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">Total</th>
                                    <th class="text-success">₹{{ number_format($grandTotal, 2) }}</th>
                                    <th colspan="4"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>

            </div>

        @endif

    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            $('#project_id').change(function() {
                let projectId = $(this).val();
                $.get('/associate-panel/get-blocks/' + projectId, function(data) {
                    $('#block_id').html('<option value="">Select Block</option>');
                    $.each(data, function(key, value) {
                        $('#block_id').append('<option value="' + value.id + '">' + value
                            .block + '</option>');
                    });
                });
            });

            $('#block_id').change(function() {
                let blockId = $(this).val();
                $.get('/associate-panel/get-plots/' + blockId, function(data) {
                    $('#plot_id').html('<option value="">Select Plot</option>');
                    $.each(data, function(key, value) {
                        $('#plot_id').append('<option value="' + value.id + '">' + value
                            .plot_number + '</option>');
                    });
                });
            });

            $('#plot_id').change(function() {
                let plotId = $(this).val();
                $.get('/associate-panel/get-booking-by-plot/' + plotId, function(data) {
                    $('#booking_id').val(data.booking_id);
                });
            });

            if ($('#ledgerTable').length) {
                $('#ledgerTable').DataTable({
                    responsive: true,
                    pageLength: 10
                });
            }

        });
    </script>
@endpush
