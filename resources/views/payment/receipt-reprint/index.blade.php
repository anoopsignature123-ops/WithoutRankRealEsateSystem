@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Find Receipt & Reprint </h3>
                        <p class="text-muted mb-0 small"> Search payments, check status, and download receipts </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card p-4">
            <form method="POST" action="{{ route('receipt-reprint.search') }}">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label class="fw-bold mb-1">Plot No <span class="text-danger">*</span></label>
                        <select name="plot_id" id="plot_select" class="form-select @error('plot_id') is-invalid @enderror">
                            <option value="">Select Plot</option>
                            @foreach ($plots as $plot)
                                <option value="{{ $plot->id }}"
                                    {{ (old('plot_id') ?? ($plot_id ?? '')) == $plot->id ? 'selected' : '' }}>
                                    {{ $plot->plot_number }}
                                </option>
                            @endforeach
                        </select>
                        @error('plot_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="fw-bold mb-1">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customer_id"
                            class="form-select @error('customer_id') is-invalid @enderror">
                            <option value="">Select Customer</option>
                        </select>
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2 mt-4">
                        <button type="submit" class="btn btn-success w-100">Search Receipts</button>
                    </div>
                </div>
            </form>

            @isset($receipts)
                <div class="table-responsive mt-5">
                    <h5 class="fw-bold mb-3 text-secondary">Search Results ({{ count($receipts) }})</h5>
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>SN</th>
                                <th>Customer No</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Payment Mode</th>
                                <th>Plan Type</th>
                                <th>Receipt No</th>
                                <th>Date</th>
                                <th class="text-center">Download</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($receipts as $key => $receipt)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td><span
                                            class="badge bg-secondary">{{ $receipt->customerBooking->customer_code ?? 'N/A' }}</span>
                                    </td>
                                    <td class="fw-bold">{{ $receipt->customerBooking->primaryDetail?->name ?? 'N/A' }}</td>
                                    <td class="text-success fw-bold">₹{{ number_format($receipt->booking_amount ?? 0, 2) }}
                                    </td>
                                    <td>
                                        @if (strtolower($receipt->payment_mode ?? '') == 'cash')
                                            <span class="badge bg-success">Cash</span>
                                        @elseif(strtolower($receipt->payment_mode ?? '') == 'cheque')
                                            <span class="badge bg-warning text-dark">Cheque</span>
                                        @else
                                            <span
                                                class="badge bg-info text-dark">{{ strtoupper($receipt->payment_mode ?? 'N/A') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ ($receipt->plan_type ?? '') == 'emi_plan' ? 'EMI Payment' : 'Full Payment' }}
                                    </td>
                                    <td>{{ $receipt->receipt_number ?? 'N/A' }}</td>
                                    <td>{{ $receipt->created_at ? $receipt->created_at->format('d-M-Y') : 'N/A' }}</td>
                                    <td class="text-center">
                                        <a target="_blank" href="{{ route('receipt-reprint.download', $receipt->id) }}"
                                            class="btn btn-sm btn-outline-danger px-3 py-1.5 fw-semibold d-inline-flex align-items-center shadow-sm gap-1"
                                            style="border-radius: 6px; transition: all 0.2s ease;">
                                            <i class="bi bi-file-earmark-pdf-fill fs-6"></i>
                                            <span>Download Receipt</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No records found for the selected
                                        criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endisset
        </div>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script>
        $(document).ready(function() {
            $('#plot_select').change(function() {
                let plotId = $(this).val();
                $('#customer_id').html('<option value="">Select Customer</option>');

                if (!plotId) return;

                let url = "{{ route('receipt-reprint.customers', ':id') }}".replace(':id', plotId);
                $.get(url, function(res) {
                    let oldCustomerId = "{{ old('customer_id') ?? ($customer_id ?? '') }}";

                    if (res && res.length > 0) {
                        $.each(res, function(index, customer) {
                            let selected = (oldCustomerId == customer.id) ? 'selected' : '';
                            $('#customer_id').append(
                                `<option value="${customer.id}" ${selected}>${customer.text}</option>`
                            );
                        });
                    }
                });
            });

            if ($('#plot_select').val()) {
                $('#plot_select').trigger('change');
            }
        });
    </script>
@endpush
