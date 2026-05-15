@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>
                        <h4 class="fw-bold mb-1">
                            Multiple Cheque Clearance
                        </h4>

                        <small class="text-muted">
                            Manage cheque payment clearance status
                        </small>
                    </div>

                    <button type="button" id="bulk_action_btn" class="btn btn-success d-none" data-bs-toggle="modal"
                        data-bs-target="#statusModal">

                        <i class="fas fa-edit me-1"></i>
                        Update Status

                    </button>

                </div>


                <div class="table-responsive">

                    <table class="table table-hover table-bordered align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th width="50">

                                    <input type="checkbox" id="select_all" class="form-check-input">

                                </th>

                                <th>Receipt</th>
                                <th>Booking</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Bank</th>
                                <th>Cheque No</th>
                                <th>Cheque Date</th>
                                <th>Mode</th>
                                <th>Status</th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($payments as $payment)
                                <tr>

                                    <td>

                                        <input type="checkbox" class="form-check-input payment_checkbox"
                                            value="{{ $payment->id }}">

                                    </td>


                                    <td>
                                        {{ $payment->receipt_number }}
                                    </td>


                                    <td>
                                        {{ $payment->customerBooking?->booking_code }}
                                    </td>


                                    <td>
                                        {{ $payment->customerBooking?->primaryDetail?->name }}
                                    </td>


                                    <td class="fw-semibold text-success">
                                        ₹{{ number_format($payment->booking_amount, 2) }}
                                    </td>


                                    <td>
                                        {{ $payment->bank_name ?: '-' }}
                                    </td>


                                    <td>
                                        {{ $payment->cheque_number ?: '-' }}
                                    </td>


                                    <td>
                                        {{ $payment->cheque_date ?: '-' }}
                                    </td>


                                    <td>
                                        <span class="badge bg-info">
                                            {{ strtoupper($payment->payment_mode) }}
                                        </span>
                                    </td>


                                    <td>

                                        @php
                                            $statusColor = match ($payment->cheque_status) {
                                                'cleared' => 'success',
                                                'cancelled' => 'danger',
                                                'bounced' => 'dark',
                                                default => 'warning',
                                            };
                                        @endphp

                                        <span class="badge bg-{{ $statusColor }}">

                                            {{ ucfirst($payment->cheque_status) }}

                                        </span>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="10" class="text-center py-4 text-muted">

                                        No cheque payments found

                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>



    <div class="modal fade" id="statusModal" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content border-0 shadow">

                <form method="POST" action="{{ route('admin.multiple-cheque-clearance.store') }}">

                    @csrf


                    <div class="modal-header">

                        <h5 class="modal-title fw-bold">

                            Update Cheque Status

                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>

                    </div>



                    <div class="modal-body">

                        <input type="hidden" name="payment_ids" id="payment_ids">


                        <div class="mb-3">

                            <label class="form-label fw-semibold">

                                Select Status

                            </label>

                            <select name="cheque_status" id="cheque_status" class="form-select">

                                <option value="cleared">
                                    Cleared
                                </option>

                                <option value="cancelled">
                                    Cancelled
                                </option>

                                <option value="bounced">
                                    Bounced
                                </option>

                                <option value="pending">
                                    Pending
                                </option>

                            </select>

                        </div>



                        <div class="mb-3 d-none" id="reason_box">

                            <label class="form-label fw-semibold">

                                Reason

                            </label>

                            <textarea name="cheque_reason" class="form-control" rows="4" placeholder="Enter reason..."></textarea>

                        </div>

                    </div>



                    <div class="modal-footer border-0">

                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">

                            Close

                        </button>


                        <button type="submit" class="btn btn-success">

                            Save Changes

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
@endsection

@include('payment.multiple-cheque-clearance.script')
