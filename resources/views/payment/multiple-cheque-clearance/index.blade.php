@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Multiple Cheque Clearance</h3>
                <small class="text-muted">Manage cheque payment clearance status</small>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <button type="button" id="bulk_action_btn" class="btn btn-success d-none" data-bs-toggle="modal"
                    data-bs-target="#statusModal">
                    <i class="fas fa-edit me-1"></i>Update Status
                </button>
            </div>

            {{-- Listing --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="select_all" class="form-check-input">
                            </th>
                            <th>Receipt No</th>
                            <th>Project</th>
                            <th>Block</th>
                            <th>Plot</th>
                            <th>Booking ID</th>
                            <th>Customer Name</th>
                            <th>Amount</th>
                            <th>Bank Name</th>
                            <th>Cheque No</th>
                            <th>Cheque Date</th>
                            <th>Payment Mode</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                {{-- Checkbox --}}
                                <td>
                                    <input type="checkbox" class="form-check-input payment_checkbox"
                                        value="{{ $payment->id }}">
                                </td>

                                {{-- Receipt --}}
                                <td>{{ $payment->receipt_number ?? '-' }}</td>
                                <td>{{ $payment->plotSaleDetail?->project?->name ?? '-' }}</td>
                                <td>{{ $payment->plotSaleDetail?->block?->block ?? '-' }}</td>
                                <td>{{ $payment->plotSaleDetail?->plotDetail?->plot_number ?? '-' }}</td>

                                {{-- Booking --}}
                                <td>{{ $payment->customerBooking?->booking_code ?? '-' }}</td>

                                {{-- Customer --}}
                                <td>{{ $payment->customerBooking?->primaryDetail?->name ?? '-' }}</td>

                                {{-- Amount --}}
                                <td class="fw-semibold text-success">₹{{ number_format($payment->booking_amount ?? 0, 2) }}
                                </td>

                                {{-- Bank --}}
                                <td>{{ $payment->bank_name ?? '-' }}</td>

                                {{-- Cheque No --}}
                                <td>{{ $payment->cheque_number ?? '-' }}</td>

                                {{-- Cheque Date --}}
                                <td>{{ $payment->cheque_date ? $payment->cheque_date->format('d-m-Y') : '-' }}</td>

                                {{-- Payment Mode --}}
                                <td>
                                    <span class="badge bg-info">{{ strtoupper($payment->payment_mode) }}</span>
                                </td>

                                {{-- Status --}}
                                <td>
                                    @php
                                        $statusColor = match ($payment->cheque_status) {
                                            'cleared' => 'success',
                                            'cancelled' => 'danger',
                                            'bounced' => 'dark',
                                            default => 'warning',
                                        };
                                    @endphp
                                    <span
                                        class="badge bg-{{ $statusColor }}">{{ ucfirst($payment->cheque_status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No cheque records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('admin.multiple-cheque-clearance.store') }}">
                    @csrf

                    {{-- Header --}}
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Update Cheque Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    {{-- Body --}}
                    <div class="modal-body">
                        <input type="hidden" name="payment_ids" id="payment_ids">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select Status</label>
                            <select name="cheque_status" id="cheque_status" class="form-select" required>
                                <option value="cleared">Cleared</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="bounced">Bounced</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Clearance Date</label>
                            <input type="date" name="cheque_date" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
                        </div>

                        <div class="mb-3 d-none" id="reason_box">
                            <label class="form-label fw-semibold">Reason</label>
                            <textarea name="cheque_reason" class="form-control" rows="4" placeholder="Enter reason here..."></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@include('payment.multiple-cheque-clearance.script')
