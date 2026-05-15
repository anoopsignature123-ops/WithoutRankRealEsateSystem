@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1"> EMI GeneRate</h3>
                <small class="text-muted">EMI GeneRate</small>
            </div>

        </div>
        <div class="card shadow-sm border-0">
            <div class="card-body">

                {{-- Search --}}
                <form method="GET">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="mb-2">Date</label>
                            <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="mb-2">Customer Id</label>
                            <select name="customer_id" class="form-select">
                                <option value="">Select Customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->customer_code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mt-4">
                            <button class="btn btn-success">Search</button>
                        </div>
                    </div>
                </form>

                {{-- Listing --}}
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Agent Id</th>
                                <th>Customer Id</th>
                                <th>Customer Name</th>
                                <th>Booking Id</th>
                                <th>Plot No</th>
                                <th>Total Cost</th>
                                <th>Paid Amt.</th>
                                <th>Due Amt.</th>
                                <th>Duration</th>
                                <th>Inst Amt.</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($records as $row)
                                @php
                                    $payment = $row->payment;
                                    $totalCost = $row->plotSaleDetail?->total_plot_cost ?? 0;
                                    $paid = $payment?->booking_amount ?? 0;
                                    $due = $payment?->due_amount ?? 0;
                                @endphp

                                <tr>
                                    <td>{{ $row->associate?->associate_id ?? '-' }}</td>
                                    <td>{{ $row->customer_code }}</td>
                                    <td>{{ $row->primaryDetail?->name }}</td>
                                    <td>{{ $row->booking_code }}</td>
                                    <td>{{ $row->plotSaleDetail?->plotDetail?->plot_number }}</td>
                                    <td>{{ $totalCost }}</td>
                                    <td>{{ $paid }}</td>
                                    <td class="due-amount">{{ $due }}</td>
                                    <td>
                                        <input type="number" class="form-control emi-month" min="1"
                                            value="{{ $payment?->emi_months ?? '' }}" placeholder="Enter months">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control emi-amount" readonly
                                            placeholder="EMI Amount">
                                    </td>
                                    {{-- Action --}}
                                    <td>
                                        <form method="POST" action="{{ route('admin.generate-emi.store', $row->id) }}">
                                            @csrf
                                            <input type="hidden" name="emi_months" class="hidden-emi-month">
                                            <input type="hidden" name="emi_amount" class="hidden-emi-amount">
                                            <button class="btn btn-sm btn-success">Generate EMI</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
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
            function calculateEmi(row) {
                let dueAmount = parseFloat(row.find('.due-amount').text()) || 0;
                let months = parseInt(row.find('.emi-month').val()) || 0;
                let emiAmount = 0;

                if (months > 0) {
                    emiAmount = dueAmount / months;
                }

                row.find('.emi-amount').val(emiAmount.toFixed(2));
                row.find('.hidden-emi-month').val(months);
                row.find('.hidden-emi-amount').val(emiAmount.toFixed(2));
            }

            $('.emi-month').on('keyup change', function() {
                let row = $(this).closest('tr');
                calculateEmi(row);
            });

            $('.emi-month').each(function() {
                let row = $(this).closest('tr');
                calculateEmi(row);
            });
        });
    </script>
@endpush
