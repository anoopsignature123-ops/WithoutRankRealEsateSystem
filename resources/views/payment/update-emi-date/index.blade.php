@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Update EMI Date</h3>
                        <p class="text-muted mb-0 small"> Single and Multiple EMI Date Update </p>
                    </div>
                </div>
            </div>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">


                    <button type="button" id="bulk_update_btn" class="btn btn-success d-none" data-bs-toggle="modal"
                        data-bs-target="#bulkDateModal">
                        <i class="fas fa-calendar-alt me-1"></i>
                        Update EMI Date
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="50"><input type="checkbox" id="select_all" class="form-check-input"></th>
                                <th>Agent ID</th>
                                <th>Customer ID</th>
                                <th>Customer Name</th>
                                <th>Booking ID</th>
                                <th>Plot No</th>
                                <th>Installment Amount</th>
                                <th>Duration</th>
                                <th>Old EMI Date</th>
                                <th>Current EMI Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td><input type="checkbox" class="form-check-input payment_checkbox"
                                            value="{{ $payment->id }}"></td>
                                    <td>{{ $payment->customerBooking?->associate_code }}</td>
                                    <td>{{ $payment->customerBooking?->customer_code }}</td>
                                    <td>{{ $payment->customerBooking?->primaryDetail?->name }}</td>
                                    <td>{{ $payment->customerBooking?->booking_code }}</td>
                                    <td>{{ $payment->plotSaleDetail?->plotDetail?->plot_number }}</td>
                                    <td class="fw-semibold text-success">
                                        ₹{{ number_format($payment->booking_amount, 2) }}
                                    </td>
                                    <td>{{ $payment->emi_months }} Months</td>
                                    <td>
                                        {{ $payment->created_at ? $payment->created_at->format('d-m-Y') : '-' }}
                                    </td>

                                    <td>
                                        {{ $payment->emi_date ? $payment->emi_date->format('d-m-Y') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">No EMI Records Found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bulkDateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('update-emi-date.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Update EMI Date</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="payment_ids" id="payment_ids">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select New EMI Date</label>
                            <input type="date" name="emi_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@include('payment.update-emi-date.script')
