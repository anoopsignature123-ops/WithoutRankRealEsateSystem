@extends('layouts.app')

@push('title')
    Associate Detail Report
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/report.css') }}">
@endpush

@section('content')
    <div class="container-fluid py-4">

        <div class="transaction-hero mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-person-badge text-success"></i>
                    </span>

                    <div>
                        <span class="text-success fw-bold text-uppercase small">
                            Associate Detail Report
                        </span>
                        <h3 class="fw-bold text-dark mb-1">Associate Detail Report</h3>
                        <p class="text-muted small mb-0">
                            Search and export associate profile records.
                        </p>
                    </div>
                </div>

                <a href="{{ route('agent-detail-report.export', request()->all()) }}"
                    class="btn btn-success rounded-pill px-4">
                    <i class="bi bi-file-earmark-excel me-1"></i>
                    Export
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Total Associates</small>
                        <h4 class="fw-bold mb-0">{{ $summary['total_records'] }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="card border border-primary-subtle shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Left Associates</small>
                        <h4 class="fw-bold text-primary mb-0">{{ $summary['left_associates'] }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="card border border-warning-subtle shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Right Associates</small>
                        <h4 class="fw-bold text-warning mb-0">{{ $summary['right_associates'] }}</h4>
                    </div>
                </div>
            </div>

            {{-- <div class="col-xl-3 col-md-6">
                <div class="card border border-success-subtle shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Active Associates</small>
                        <h4 class="fw-bold text-success mb-0">{{ $summary['active_agents'] }}</h4>
                    </div>
                </div>
            </div> --}}
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center"
                        style="width:44px;height:44px;">
                        <i class="bi bi-funnel"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold mb-1">Filter Report</h5>
                        <small class="text-muted">
                            Filter associates by ID, direction, name, mobile and joining date.
                        </small>
                    </div>
                </div>

                <form method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-semibold">Associate</label>
                            <select name="associate_id" class="form-select">
                                <option value="">All Associates</option>
                                @foreach ($associateList as $associate)
                                    <option value="{{ $associate->id }}"
                                        {{ request('associate_id') == $associate->id ? 'selected' : '' }}>
                                        {{ $associate->associate_id }} / {{ $associate->associate_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-2 col-md-6">
                            <label class="form-label fw-semibold">Direction</label>
                            <select name="direction" class="form-select">
                                <option value="">All</option>
                                <option value="left" {{ request('direction') == 'left' ? 'selected' : '' }}>Left</option>
                                <option value="right" {{ request('direction') == 'right' ? 'selected' : '' }}>Right</option>
                            </select>
                        </div>

                        <div class="col-xl-2 col-md-6">
                            <label class="form-label fw-semibold">Name</label>
                            <input type="text" name="name" value="{{ request('name') }}"
                                class="form-control" placeholder="Enter name">
                        </div>

                        <div class="col-xl-2 col-md-6">
                            <label class="form-label fw-semibold">Mobile</label>
                            <input type="text" name="mobile" value="{{ request('mobile') }}"
                                class="form-control" placeholder="Enter mobile">
                        </div>

                        <div class="col-xl-1 col-md-6">
                            <label class="form-label fw-semibold">From</label>
                            <input type="date" name="from_date" value="{{ request('from_date') }}"
                                class="form-control">
                        </div>

                        <div class="col-xl-1 col-md-6">
                            <label class="form-label fw-semibold">To</label>
                            <input type="date" name="to_date" value="{{ request('to_date') }}"
                                class="form-control">
                        </div>

                        <div class="col-xl-1 col-md-6 d-flex gap-2">
                            <button type="submit" class="btn btn-success flex-fill">
                                <i class="bi bi-search"></i>
                            </button>

                            <a href="{{ route('agent-detail-report.index') }}"
                                class="btn btn-outline-secondary px-3">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">
                            <i class="bi bi-table text-success me-2"></i>
                            Associate Records
                        </h5>
                        <small class="text-muted">
                            Showing associate profile and sponsor details.
                        </small>
                    </div>

                    <span class="badge bg-success-subtle text-success border rounded-pill px-3 py-2">
                        {{ $agents->count() }} Records
                    </span>
                </div>

                <div class="table-responsive">
                    <table id="associateReportTable" class="table table-hover align-middle nowrap w-100">
                        <thead class="table-success">
                            <tr>
                                <th>Sr.No</th>
                                <th>Sponsor ID</th>
                                <th>Associate ID</th>
                                <th>Associate Name</th>
                                <th>Mobile Number</th>
                                <th>Direction</th>
                                {{-- <th>Status</th> --}}
                                <th>Joining Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($agents as $key => $agent)
                                @php
                                    $status = strtolower($agent->status ?? 'active');

                                    $statusClass = match ($status) {
                                        'active' => 'bg-success',
                                        'inactive' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                @endphp

                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                            {{ $agent->sponsor_id ?? 'Self' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge bg-success-subtle text-success border rounded-pill px-3 py-2">
                                            {{ $agent->associate_id ?? 'N/A' }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="fw-bold">{{ $agent->associate_name ?? 'N/A' }}</div>
                                        <small class="text-muted">Associate</small>
                                    </td>

                                    <td>
                                        {{ $agent->mobile_number ? '+91 ' . $agent->mobile_number : 'N/A' }}
                                    </td>

                                    <td>
                                        @if ($agent->direction == 'left')
                                            <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-2">
                                                Left
                                            </span>
                                        @elseif ($agent->direction == 'right')
                                            <span class="badge bg-warning-subtle text-warning border rounded-pill px-3 py-2">
                                                Right
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-2">
                                                N/A
                                            </span>
                                        @endif
                                    </td>

                                    {{-- <td>
                                        <span class="badge {{ $statusClass }} rounded-pill px-3 py-2">
                                            {{ ucfirst($agent->status ?? 'Active') }}
                                        </span>
                                    </td> --}}

                                    <td>
                                        {{ $agent->created_at ? $agent->created_at->format('d-M-Y') : 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                                        <span class="text-muted">No associate records found.</span>
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
            $('#associateReportTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                responsive: false,
                scrollX: true,
                language: {
                    emptyTable: 'No associate records found.'
                }
            });
        });
    </script>
@endpush