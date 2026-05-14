@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Edit Payment Details</h3>
                <small class="text-muted">Manage plot payment records and update payment details.</small>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form id="bookingSelectionForm" method="GET" action="{{ route('admin.edit-payment-details.index') }}">
                    <div class="row gy-3">
                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Customer ID</label>
                            <select id="customerSelect" name="selected_booking" class="form-select">
                                <option value="">Select Customer</option>
                                @foreach ($bookings as $booking)
                                    @php $primary = $booking->primaryDetail; @endphp
                                    <option value="{{ $booking->id }}"
                                        {{ optional($selectedBooking)->id == $booking->id ? 'selected' : '' }}>
                                        {{ $booking->customer_code ?? 'N/A' }} -
                                        {{ ucfirst($primary?->name ?? ($booking->customer_name ?? 'N/A')) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Plot No</label>
                            <select id="plotSelect" class="form-select">
                                <option value="">Select Plot</option>
                                @foreach ($bookings as $booking)
                                    @php $plotSale = $booking->plotSaleDetail; @endphp
                                    <option value="{{ $booking->id }}"
                                        {{ optional($selectedBooking)->id == $booking->id ? 'selected' : '' }}>
                                        {{ $plotSale?->plotDetail?->plot_number ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Booking ID</label>
                            <input type="text" class="form-control" value="{{ $selectedBooking->booking_code ?? '' }}"
                                readonly>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Project Name</label>
                            <input type="text" class="form-control"
                                value="{{ optional($selectedBooking?->plotSaleDetail?->project)->name ?? '' }}" readonly>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Receipt No</label>
                            <input type="text" class="form-control"
                                value="{{ $selectedBooking?->payment?->receipt_number ?? '' }}" readonly>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Paid Amount</label>
                            <input type="text" class="form-control"
                                value="{{ $selectedBooking?->payment?->booking_amount ?? '' }}" readonly>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Pay Mode</label>
                            <input type="text" class="form-control"
                                value="{{ ucfirst($selectedBooking?->payment?->payment_mode ?? '') }}" readonly>
                        </div>

                        <div class="col-lg-12">
                            <button type="button" class="btn btn-success" id="loadBookingBtn"
                                {{ $selectedBooking ? '' : 'disabled' }}>
                                Load Details
                            </button>
                        </div>
                    </div>
                </form>

                @if ($selectedBooking)
                    <div class="mt-4">
                        <form method="POST"
                            action="{{ route('admin.edit-payment-details.update', $selectedBooking->id) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="plot_sale_detail_id"
                                value="{{ $selectedBooking->plotSaleDetail?->id }}">
                            <div class="row gy-3">
                                <div class="col-lg-4">
                                    <label class="form-label fw-semibold">Receipt No</label>
                                    <input type="text" name="receipt_number" class="form-control"
                                        value="{{ old('receipt_number', $selectedBooking->payment?->receipt_number ?? '') }}">
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label fw-semibold">Paid Amount</label>
                                    <input type="number" name="booking_amount" class="form-control"
                                        value="{{ old('booking_amount', $selectedBooking->payment?->booking_amount ?? '') }}">
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label fw-semibold">Pay Mode</label>
                                    <select name="payment_mode" class="form-select">
                                        <option value="cash"
                                            {{ old('payment_mode', $selectedBooking->payment?->payment_mode) == 'cash' ? 'selected' : '' }}>
                                            Cash</option>
                                        <option value="cheque"
                                            {{ old('payment_mode', $selectedBooking->payment?->payment_mode) == 'cheque' ? 'selected' : '' }}>
                                            Cheque</option>
                                        <option value="dd"
                                            {{ old('payment_mode', $selectedBooking->payment?->payment_mode) == 'dd' ? 'selected' : '' }}>
                                            DD</option>
                                        <option value="neft_rtgs"
                                            {{ old('payment_mode', $selectedBooking->payment?->payment_mode) == 'neft_rtgs' ? 'selected' : '' }}>
                                            NEFT/RTGS</option>
                                        <option value="card"
                                            {{ old('payment_mode', $selectedBooking->payment?->payment_mode) == 'card' ? 'selected' : '' }}>
                                            Card</option>
                                    </select>
                                </div>

                                <div class="col-lg-12">
                                    <button type="submit" class="btn btn-success">Update</button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-hover" id="plotPaymentTable">
                        <thead class="table-success">
                            <tr>
                                <th>#</th>
                                <th>Booking Id</th>
                                <th>Customer Id</th>
                                <th>Customer Name</th>
                                <th>Payment Type</th>
                                <th>Paid Amount</th>
                                <th>Pay Mode</th>
                                <th>Date</th>
                                <th>Receipt No</th>
                                <th>Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookings as $key => $booking)
                                @php
                                    $primary = $booking->primaryDetail;
                                    $plotSale = $booking->plotSaleDetail;
                                    $payment = $booking->payment;
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $booking->booking_code ?? 'N/A' }}</td>
                                    <td>{{ $booking->customer_code ?? 'N/A' }}</td>
                                    <td>{{ ucfirst($primary?->name ?? ($booking->customer_name ?? 'N/A')) }}</td>
                                    <td>{{ $payment?->plan_type == 'emi_plan' ? 'EMI Plan' : 'Full Payment Amount' }}</td>
                                    <td>{{ number_format($payment?->booking_amount ?? 0, 2) }}</td>
                                    <td>{{ ucfirst($payment?->payment_mode ?? 'N/A') }}</td>
                                    <td>{{ optional($payment?->created_at)->format('d-M-Y') ?? 'N/A' }}</td>
                                    <td>{{ $payment?->receipt_number ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('admin.edit-payment-details.index', ['selected_booking' => $booking->id]) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        No plot payment records found.
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
            if ($('#plotPaymentTable tbody tr td').attr('colspan') === undefined) {
                $('#plotPaymentTable').DataTable();
            }

            $('#customerSelect, #plotSelect').change(function() {
                let selected = $(this).val();
                if (!selected) {
                    return;
                }
                $('#customerSelect').val(selected);
                $('#plotSelect').val(selected);
                $('#bookingSelectionForm').submit();
            });

            $('#loadBookingBtn').click(function() {
                let selected = $('#customerSelect').val();
                if (!selected) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Please select a booking',
                    });
                    return;
                }
                $('#bookingSelectionForm').submit();
            });

            $('.delete-btn').click(function() {
                let form = $(this).closest('form');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This payment record will be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete it',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
