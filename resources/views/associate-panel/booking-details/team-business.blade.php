@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        {{-- Header Section --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-0">Team Business Report</h4>
                        <span class="text-muted small">Team booking and business details</span>
                    </div>
                    <a href="{{ route('associate-panel.booking-detail') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        {{-- Table Section --}}
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle" id="reportTable">
                        <thead class="table-dark">
                            <tr>
                                <th>SNo.</th>
                                <th>Booking ID</th>
                                <th>Customer Name</th>
                                <th>Associate Name</th>
                                <th>Project Name</th>
                                <th>Plot No</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $key => $report)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $report->booking_code }}</td>
                                    <td>{{ $report->customer_name }}</td>
                                    <td>{{ $report->agent_name }}</td>
                                    <td>{{ $report->project_name }}</td>
                                    <td>{{ $report->plot_no }}</td>
                                    <td class="fw-bold text-success">₹{{ number_format($report->amount, 2) }}</td>
                                    <td>{{ $report->date }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-danger">No Records Found</td>
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
            // DataTable init only if data exists
            const hasData = $('#reportTable tbody tr').length > 0 &&
                !$('#reportTable tbody tr').find('td[colspan]').length;

            if (hasData) {
                $('#reportTable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    language: {
                        search: "Filter records:",
                    }
                });
            }
        });
    </script>
@endpush
