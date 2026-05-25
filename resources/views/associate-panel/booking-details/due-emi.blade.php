@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        {{-- Header Section --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold mb-1">Due EMI Amount List</h4>
                        <span class="text-muted small">View all pending EMI payments with installment progress</span>
                    </div>
                    <a href="{{ route('associate-panel.booking-detail') }}" class="btn btn-outline-secondary shadow-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        {{-- Data Table Section --}}
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle" id="dueEmiTable">
                        <thead class="table-dark">
                            <tr>
                                <th>SNo.</th>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Associate</th>
                                <th>Project</th>
                                <th>Block</th>
                                <th>Plot</th>
                                <th>Plot Amount</th>
                                <th>Booking Amount</th>
                                <th>Total Due</th>
                                <th>Total EMI</th>
                                <th>EMI Amount</th>
                                <th>Paid</th>
                                <th>Rem.</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>View EMI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dueEmi as $key => $emi)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td class="fw-bold text-primary">{{ $emi->booking_code }}</td>
                                    <td>{{ $emi->customer_name }}</td>
                                    <td>{{ $emi->associate_name }}</td>
                                    <td>{{ $emi->project_name }}</td>
                                    <td>{{ $emi->block_name }}</td>
                                    <td>{{ $emi->plot_no }}</td>
                                    <td class="fw-bold">₹{{ number_format($emi->plot_amount, 2) }}</td>
                                    <td class="text-success fw-bold">₹{{ number_format($emi->booking_amount, 2) }}</td>
                                    <td class="text-danger fw-bold">₹{{ number_format($emi->due_amount, 2) }}</td>
                                    <td><span class="badge bg-secondary">{{ $emi->total_installments }}</span></td>
                                    <td class="text-info fw-bold">₹{{ number_format($emi->emi_amount, 2) }}</td>
                                    <td><span class="badge bg-success">{{ $emi->paid_installments }}</span></td>
                                    <td><span class="badge bg-warning text-dark">{{ $emi->remaining_installments }}</span>
                                    </td>
                                    <td style="min-width: 150px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress w-100" style="height: 8px;">
                                                <div class="progress-bar bg-success"
                                                    style="width: {{ $emi->progress_percent }}%"></div>
                                            </div>
                                            <small class="fw-bold">{{ $emi->emi_progress }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($emi->status == 'Pending')
                                            <span class="badge bg-danger">Pending</span>
                                        @else
                                            <span class="badge bg-success">Completed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="collapse"
                                            data-bs-target="#emiHistory{{ $key }}">
                                            <i class="fas fa-eye me-1"></i> View
                                        </button>
                                    </td>
                                </tr>
                                {{-- Collapsible Row --}}
                                <tr class="collapse" id="emiHistory{{ $key }}">
                                    <td colspan="17">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>EMI No.</th>
                                                        <th>EMI Amount</th>
                                                        <th>Status</th>
                                                        <th>Paid Date</th>
                                                        <th>Receipt No.</th>
                                                        <th>Payment Mode</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($emi->emi_history as $history)
                                                        <tr>
                                                            <td>EMI {{ $history['month'] }}</td>
                                                            <td class="fw-bold text-primary">
                                                                ₹{{ number_format($history['emi_amount'], 2) }}</td>
                                                            <td>
                                                                @if ($history['status'] == 'Paid')
                                                                    <span class="badge bg-success">Paid</span>
                                                                @else
                                                                    <span class="badge bg-danger">Pending</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $history['paid_date'] }}</td>
                                                            <td>{{ $history['receipt_number'] }}</td>
                                                            <td>{{ ucfirst($history['payment_mode']) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="text-center text-muted py-4">No Due EMI records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .table thead th {
            white-space: nowrap;
        }

        .progress {
            background-color: #e9ecef;
            border-radius: 10px;
        }

        .progress-bar {
            border-radius: 10px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            const hasData = $('#dueEmiTable tbody tr').length > 0 && !$('#dueEmiTable tbody tr').find('td[colspan]')
                .length;

            if (hasData) {
                $('#dueEmiTable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    language: {
                        searchPlaceholder: "Search EMI Records..."
                    }
                });
            }
        });
    </script>
@endpush
