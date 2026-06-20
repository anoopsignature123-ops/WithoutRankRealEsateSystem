@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 update-emi-date-page">
        <div class="update-emi-date-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="update-emi-date-hero-icon">
                        <i class="bi bi-calendar2-week"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">EMI Schedule</span>
                        <h3 class="fw-bold mb-1 text-dark">Update EMI Date</h3>
                        <p class="text-muted mb-0 small">Select one or multiple EMI records and update their next EMI date.</p>
                    </div>
                </div>

                <button type="button" id="bulk_update_btn" class="btn btn-success update-emi-date-primary d-none"
                    data-bs-toggle="modal" data-bs-target="#bulkDateModal">
                    <i class="bi bi-calendar-check me-1"></i>
                    Update Selected
                    <span class="selected-count ms-1">(0)</span>
                </button>
            </div>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please check:</strong> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="update-emi-date-table-card">
            <div class="update-emi-date-table-head">
                <div class="d-flex align-items-center gap-3">
                    <span class="update-emi-date-table-icon">
                        <i class="bi bi-list-check"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-1">EMI Date Records</h5>
                        <small class="text-muted">Only latest EMI plan records are shown here.</small>
                    </div>
                </div>

                <span class="update-emi-date-count">{{ $payments->count() }} Records</span>
            </div>

            <div class="update-emi-date-toolbar">
                <label class="update-emi-date-select-all">
                    <input type="checkbox" id="select_all" class="form-check-input">
                    <span>Select all visible records</span>
                </label>

                <span class="update-emi-date-selection">
                    <strong id="selected_count">0</strong> selected
                </span>
            </div>

            <div class="update-emi-date-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 update-emi-date-table" id="emiDateTable">
                        <thead>
                            <tr>
                                <th width="54" class="text-center">Pick</th>
                                <th>Agent</th>
                                <th>Customer</th>
                                <th>Booking / Plot</th>
                                <th>Monthly EMI</th>
                                <th>Remaining</th>
                                <th>Last EMI</th>
                                <th>Current EMI Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($payments as $payment)
                                @php
                                    $booking = $payment->customerBooking;
                                    $plotSale = $payment->plotSaleDetail;
                                    $monthlyEmi = (float) ($payment->after_booking_payable_amount ?? ($payment->paid_amount ?? 0));
                                @endphp

                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input payment_checkbox"
                                            value="{{ $payment->id }}">
                                    </td>

                                    <td>
                                        <strong>{{ $booking?->associate_code ?? '-' }}</strong>
                                        <br>
                                        <small class="text-muted">Associate ID</small>
                                    </td>

                                    <td>
                                        <strong>{{ $booking?->customer_code ?? '-' }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $booking?->primaryDetail?->name ?? ($booking?->customer_name ?? '-') }}
                                        </small>
                                    </td>

                                    <td>
                                        <strong>{{ $plotSale?->booking_code ?? ($booking?->booking_code ?? '-') }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $plotSale?->project?->name ?? '-' }} /
                                            {{ $plotSale?->block?->block ?? '-' }} /
                                            Plot {{ $plotSale?->plotDetail?->plot_number ?? '-' }}
                                        </small>
                                    </td>

                                    <td class="fw-bold text-success">
                                        &#8377;{{ number_format($monthlyEmi, 2) }}
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $payment->emi_months ?? 0 }} Months
                                        </span>
                                    </td>

                                    <td>
                                        {{ $payment->created_at ? $payment->created_at->format('d-M-Y') : '-' }}
                                    </td>

                                    <td>
                                        @if ($payment->emi_date)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                {{ \Carbon\Carbon::parse($payment->emi_date)->format('d-M-Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                                        No EMI records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade update-emi-date-modal" id="bulkDateModal" tabindex="-1"
        aria-labelledby="bulkDateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('update-emi-date.store') }}" id="updateEmiDateForm">
                    @csrf

                    <div class="modal-header">
                        <div class="d-flex align-items-center gap-3">
                            <span class="update-emi-date-modal-icon">
                                <i class="bi bi-calendar-check"></i>
                            </span>
                            <div>
                                <span class="text-success fw-bold text-uppercase small">Bulk Update</span>
                                <h5 class="modal-title fw-bold mb-0" id="bulkDateModalLabel">Update EMI Date</h5>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="payment_ids" id="payment_ids">

                        <div class="update-emi-date-modal-summary mb-3">
                            <i class="bi bi-info-circle"></i>
                            <span>
                                EMI date will be updated for
                                <strong id="modal_selected_count">0</strong>
                                selected record(s).
                            </span>
                        </div>

                        <label class="form-label fw-semibold">Select New EMI Date</label>
                        <input type="date" name="emi_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success px-4" id="updateEmiDateBtn">
                            <span class="btn-label">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </span>
                            <span class="btn-loader d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@include('payment.update-emi-date.script')
