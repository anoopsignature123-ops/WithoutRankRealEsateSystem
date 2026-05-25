@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-0">Booking Details List</h4>
                        <span class="text-muted small">Manage and view your booking records</span>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('associate-panel.due-emi-amount') }}" class="btn btn-warning shadow-sm text-white">
                            <i class="fas fa-exclamation-circle me-2"></i> Due EMI Amount
                        </a>

                        <a href="{{ route('associate-panel.team-business-report') }}" class="btn btn-primary shadow-sm">
                            <i class="fas fa-file-alt me-2"></i> Team Business Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Searching Criteria</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('associate-panel.booking-detail') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Project Name</label>
                            <select name="project_id" id="project_id" class="form-select">
                                <option value="" disabled selected>-- Select Project --</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}"
                                        {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Select Block</label>
                            <select name="block_id" id="block_id" class="form-select">
                                <option value="" disabled selected>-- Select Block --</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Select Plot</label>
                            <select name="plot_id" id="plot_id" class="form-select">
                                <option value="" disabled selected>-- Select Plot --</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Customer ID</label>
                            <input type="text" name="customer_id" id="customer_id" class="form-control"
                                placeholder="Auto-filled" value="{{ request('customer_id') }}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Booking ID</label>
                            <input type="text" name="booking_id" id="booking_id" class="form-control"
                                placeholder="Auto-filled" value="{{ request('booking_id') }}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success me-2">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                            <a href="{{ route('associate-panel.booking-detail') }}" class="btn btn-secondary">
                                <i class="fas fa-sync-alt me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Booking Details List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle" id="bookingTable">
                        <thead class="table-light">
                            <tr>
                                <th>SNo.</th>
                                <th>Booking ID</th>
                                <th>Customer Name</th>
                                <th>Customer ID</th>
                                <th>Project</th>
                                <th>Block</th>
                                <th>Plot No</th>
                                <th>Plan Type</th>
                                <th>Payment Type</th>
                                <th>Payable</th>
                                <th>Paid</th>
                                <th>Date</th>
                                <th>Mode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $key => $payment)
                                @php
                                    $booking = $payment->booking;
                                    $plotSale = $payment->plotSaleDetail;
                                    $plot = $plotSale?->plotDetail;
                                    $block = $plot?->block;
                                    $project = $block?->project;
                                @endphp

                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $booking?->booking_code ?? '-' }}</td>
                                    <td>{{ $booking?->primaryDetail?->name ?? '-' }}</td>
                                    <td>{{ $booking?->customer_code ?? '-' }}</td>
                                    <td>{{ $project?->name ?? '-' }}</td>
                                    <td>{{ $block?->block ?? '-' }}</td>
                                    <td>{{ $plot?->plot_number ?? '-' }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $payment->plan_type ?? '-')) }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $payment->transaction_category == 'booking_fee' ? 'primary' : 'info' }}">
                                            {{ ucfirst(str_replace('_', ' ', $payment->transaction_category ?? '-')) }}
                                        </span>
                                    </td>
                                    <td>₹{{ number_format($plotSale?->plot_cost ?? 0, 2) }}</td>
                                    <td class="fw-bold text-success">₹{{ number_format($payment->booking_amount ?? 0, 2) }}
                                    </td>
                                    <td>{{ $payment->created_at?->format('d-m-Y') ?? '-' }}</td>
                                    <td>{{ ucfirst($payment->payment_mode ?? '-') }}</td>
                                </tr>
                            @empty
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
            if ($('#bookingTable tbody tr').length > 0 && !$('#bookingTable tbody tr').find('td[colspan]').length) {
                $('#bookingTable').DataTable({
                    responsive: true
                });
            }

            function populateSelect(url, targetElement, defaultText, selectedValue = null) {
                $(targetElement).html(`<option value="" disabled selected>${defaultText}</option>`);
                $.get(url, function(data) {
                    data.forEach(item => {
                        let isSelected = (item.id == selectedValue) ? 'selected' : '';
                        let label = item.block || item.plot_number;
                        $(targetElement).append(
                            `<option value="${item.id}" ${isSelected}>${label}</option>`);
                    });
                });
            }

            $('#project_id').change(function() {
                populateSelect('/associate-panel/get-blocks/' + $(this).val(), '#block_id',
                    '-- Select Block --');
            });
            $('#block_id').change(function() {
                populateSelect('/associate-panel/get-plots/' + $(this).val(), '#plot_id',
                    '-- Select Plot --');
            });
            $('#plot_id').change(function() {
                $.get('/associate-panel/get-booking-by-plot/' + $(this).val(), function(data) {
                    $('#customer_id').val(data.customer_id || '');
                    $('#booking_id').val(data.booking_id || '');
                });
            });

            @if (request('project_id'))
                populateSelect('/associate-panel/get-blocks/{{ request('project_id') }}', '#block_id',
                    '-- Select Block --', '{{ request('block_id') }}');
            @endif
            @if (request('block_id'))
                populateSelect('/associate-panel/get-plots/{{ request('block_id') }}', '#plot_id',
                    '-- Select Plot --', '{{ request('plot_id') }}');
            @endif
        });
    </script>
@endpush
