@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <h3 class="fw-bold mb-1 text-dark">EMI Generation</h3>
                        <p class="text-muted mb-0 small">Calculate and generate customer EMI schedules</p>
                    </div>
                    <div class="col-md-7">
                        <form method="GET" class="d-flex justify-content-md-end gap-2 align-items-end flex-wrap">
                            <div style="width: 180px;">
                                <label class="mb-1 fw-semibold text-secondary small">Date</label>
                                <input type="date" class="form-control" name="date" value="{{ request('date', date('Y-m-d')) }}">
                            </div>
                            <div style="width: 250px;">
                                <label class="mb-1 fw-semibold text-secondary small">Customer</label>
                                <select name="customer_id" class="form-select">
                                    <option value="">-- Select Customer --</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->customer_code . ' ('. $customer->primaryDetail?->name . ')'}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="emiTable">
                        <thead class="table-light">
                            <tr>
                                <th>Agent ID</th>
                                <th>Customer ID</th>
                                <th>Customer Name</th>
                                <th>Booking ID</th>
                                <th>Plot No</th>
                                <th>Total Cost</th>
                                <th>Paid Amt.</th>
                                <th>Due Amt.</th>
                                <th width="120">Duration (Mo)</th>
                                <th width="150">Inst. Amt.</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $row)
                                @php
                                    $payment = $row->payment;
                                    $totalCost = $row->plotSaleDetail?->total_plot_cost ?? 0;
                                    $paid = $payment?->booking_amount ?? 0;
                                    $due = $payment?->due_amount ?? 0;
                                @endphp
                                <tr>
                                    <td><span class="badge bg-light text-dark border">{{ $row->associate?->associate_id ?? '-' }}</span></td>
                                    <td>{{ $row->customer_code }}</td>
                                    <td class="fw-medium">{{ $row->primaryDetail?->name }}</td>
                                    <td>{{ $row->booking_code }}</td>
                                    <td>{{ $row->plotSaleDetail?->plotDetail?->plot_number ?? '-' }}</td>
                                    <td>₹{{ number_format($totalCost, 2) }}</td>
                                    <td class="text-success">₹{{ number_format($paid, 2) }}</td>
                                    <td class="fw-bold due-amount">{{ number_format($due, 2) }}</td>
                                    <td>
                                        <input type="number" class="form-control emi-month" min="1" value="{{ $payment?->emi_months ?? '' }}" placeholder="Months">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control emi-amount bg-light" readonly placeholder="0.00">
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('generate-emi.store', $row->id) }}">
                                            @csrf
                                            <input type="hidden" name="emi_months" class="hidden-emi-month">
                                            <input type="hidden" name="emi_amount" class="hidden-emi-amount">
                                            <button class="btn btn-sm btn-success rounded-pill px-3">Generate</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-muted">No EMI records found.</td>
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
            function calculateEmi(row) {
                let dueAmount = parseFloat(row.find('.due-amount').text().replace(/,/g, '')) || 0;
                let months = parseInt(row.find('.emi-month').val()) || 0;
                let emiAmount = (months > 0) ? (dueAmount / months) : 0;

                row.find('.emi-amount').val(emiAmount.toFixed(2));
                row.find('.hidden-emi-month').val(months);
                row.find('.hidden-emi-amount').val(emiAmount.toFixed(2));
            }

            $('.emi-month').on('keyup change', function() {
                calculateEmi($(this).closest('tr'));
            });

            // Initial calculation
            $('.emi-month').each(function() {
                calculateEmi($(this).closest('tr'));
            });
        });
    </script>
@endpush