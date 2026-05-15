@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Find Receipt & Reprint</h3>
                <small class="text-muted">Find Receipt & Reprint</small>
            </div>
        </div>

        <div class="card p-4">
            <form method="POST" action="{{ route('admin.receipt-reprint.search') }}">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label>Plot No</label>
                        <select name="plot_id" id="plot_select" class="form-select">
                            <option value="">Select Plot</option>
                            @foreach ($plots as $plot)
                                <option value="{{ $plot->id }}">{{ $plot->plot_number }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Customer</label>
                        <select name="customer_id" id="customer_id" class="form-select">
                            <option value="">Select Customer</option>
                        </select>
                    </div>

                    <div class="col-md-2 mt-4">
                        <button class="btn btn-success">Open Receipt</button>
                    </div>
                </div>
            </form>

            @isset($receipts)
                <div class="table-responsive mt-4">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Customer No</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Receipt No</th>
                                <th>Date</th>
                                <th>Download</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($receipts as $key => $receipt)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $receipt->customerBooking->customer_code }}</td>
                                    <td>{{ $receipt->customerBooking->primaryDetail?->name }}</td>
                                    <td>₹{{ $receipt->booking_amount }}</td>
                                    <td>{{ $receipt->receipt_number }}</td>
                                    <td>{{ $receipt->created_at->format('d-M-Y') }}</td>
                                    <td>
                                        <a target="_blank" href="{{ route('admin.receipt-reprint.download', $receipt->id) }}"
                                            class="btn btn-sm btn-primary">PDF</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endisset
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#plot_select').change(function() {
                let plotId = $(this).val();
                $('#customer_id').html('<option value="">Select Customer</option>');

                if (!plotId) return;

                $.get("{{ route('admin.receipt-reprint.customers', ':id') }}".replace(':id', plotId), function(res) {
                    $.each(res, function(index, customer) {
                        $('#customer_id').append(`<option value="${customer.id}">${customer.text}</option>`);
                    });
                });
            });
        });
    </script>
@endpush