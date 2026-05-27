@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="fw-bold mb-1 text-dark">Allotment & Agreement Letter</h3>
                        <p class="text-muted mb-0 small">Manage allotment and agreement documents</p>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" id="filterForm" class="d-flex justify-content-md-end gap-2">
                            <div class="flex-grow-1" style="max-width: 300px;">
                                <select name="booking_id" id="bookingFilter" class="form-select rounded-pill"
                                    onchange="this.form.submit()">
                                    <option value="">-- Select Booking --</option>
                                    @foreach ($bookingList as $item)
                                        <option value="{{ $item->id }}"
                                            {{ request('booking_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->booking_code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {{-- Listing --}}
        <div class="table-responsive">
            <table class="table align-middle table-hover text-center" id="letterTable">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Name</th>
                        <th>Project</th>
                        <th>Block</th>
                        <th>Plot No</th>
                        <th>Plot Rate</th>
                        <th>Plot Area</th>
                        <th>Plan Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $row)
                        <tr>
                            <td>{{ $row->booking_code }}</td>
                            <td>{{ $row->primaryDetail?->name ?? '-' }}</td>
                            <td>{{ $row->plotSaleDetail?->plotDetail?->block?->project?->name ?? '-' }}</td>
                            <td>{{ $row->plotSaleDetail?->plotDetail?->block?->block ?? '-' }}</td>
                            <td>{{ $row->plotSaleDetail?->plotDetail?->plot_number ?? '-' }}</td>
                            <td>₹{{ $row->plotSaleDetail?->plot_rate ?? 0 }}</td>
                            <td>{{ $row->plotSaleDetail?->plot_area ?? '-' }}</td>
                            <td>{{ $row->payment?->plan_type == 'emi_plan' ? 'EMI Plan' : 'Full Payment' }}</td>
                            <td>
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('booking-letter.allotement.pdf', $row->id) }}" target="_blank"
                                        class="btn btn-sm btn-outline-success px-3 py-1.5 fw-medium d-inline-flex align-items-center gap-1.5 shadow-sm"
                                        style="border-radius: 6px; transition: all 0.2s ease;" title="Allotment Letter">
                                        <i class="bi bi-file-earmark-pdf-fill fs-6"></i>
                                        <span>Allotment Letter</span>
                                    </a>

                                    <a href="{{ route('booking-letter.agreement.pdf', $row->id) }}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary px-3 py-1.5 fw-medium d-inline-flex align-items-center gap-1.5 shadow-sm"
                                        style="border-radius: 6px; transition: all 0.2s ease;" title="Agreement Letter">
                                        <i class="bi bi-file-earmark-text-fill fs-6"></i>
                                        <span>Agreement Letter</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No records found</td>
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
            $('#letterTable').DataTable();
            $('#bookingFilter').on(
                'change',
                function() {
                    $('#filterForm').submit();
                }
            );
        });
    </script>
@endpush
