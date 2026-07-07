@extends('layouts.app')

@push('title')
    {{ $pageTitle }}
@endpush

@section('content')
    <div class="container-fluid py-4">

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-4 bg-light d-flex align-items-center justify-content-center me-3"
                            style="width:60px;height:60px;">
                            <i class="bi bi-person-lines-fill fs-2 text-success"></i>
                        </div>

                        <div>
                            <h3 class="fw-bold mb-1 text-dark" id="pageTitle">
                                {{ $pageTitle }}
                            </h3>

                            <p class="text-muted mb-0">
                                View direct associates by left and right placement.
                            </p>
                        </div>
                    </div>

                    <div class="badge bg-light text-dark border rounded-pill px-3 py-2">
                        Total Records: {{ $directAssociates->count() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form method="GET" id="filterForm">
                    <div class="row g-3 align-items-end">

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Sponsor / Associate ID</label>
                            <input type="text"
                                name="associate_id"
                                value="{{ request('associate_id') }}"
                                class="form-control auto-filter-input"
                                placeholder="Enter sponsor associate id">
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold">Direction</label>
                            <select name="direction" class="form-control auto-filter">
                                <option value="">All</option>
                                <option value="left" {{ request('direction') == 'left' ? 'selected' : '' }}>Left</option>
                                <option value="right" {{ request('direction') == 'right' ? 'selected' : '' }}>Right</option>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold">From Date</label>
                            <input type="date"
                                name="from_date"
                                value="{{ request('from_date') }}"
                                class="form-control auto-filter">
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold">To Date</label>
                            <input type="date"
                                name="to_date"
                                value="{{ request('to_date') }}"
                                class="form-control auto-filter">
                        </div>

                        <div class="col-lg-3 col-md-12">
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('direct-associate') }}" class="btn btn-light border px-4">
                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                    Reset
                                </a>

                                <a href="{{ route('direct-associate.export', request()->query()) }}"
                                    class="btn btn-outline-success px-4">
                                    <i class="bi bi-download me-1"></i>
                                    Export
                                </a>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="directAssociateTable">
                        <thead class="table-light">
                            <tr>
                                <th>SR No.</th>
                                <th>Associate ID</th>
                                <th>Associate Name</th>
                                <th>Direction</th>
                                <th>Sponsor ID</th>
                                <th>Sponsor Name</th>
                                <th>Mobile No</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($directAssociates as $key => $item)
                                <tr>
                                    <td>#{{ $key + 1 }}</td>

                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                            {{ $item->associate_id }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="fw-bold text-dark">
                                            {{ $item->associate_name }}
                                        </div>
                                    </td>

                                    <td>
                                        @if ($item->direction == 'left')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                                                Left
                                            </span>
                                        @elseif ($item->direction == 'right')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2">
                                                Right
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-2">
                                                -
                                            </span>
                                        @endif
                                    </td>

                                    <td>{{ $item->sponsor_id ?? '-' }}</td>
                                    <td>{{ $item->sponsor?->associate_name ?? '-' }}</td>
                                    <td>{{ $item->mobile_number ?? '-' }}</td>

                                    <td>
                                        <span class="text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ $item->created_at?->format('d-m-Y') ?? '-' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                                        No data found
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
            let typingTimer;

            $('.auto-filter').on('change', function() {
                $('#filterForm').submit();
            });

            $('.auto-filter-input').on('keyup', function() {
                clearTimeout(typingTimer);

                typingTimer = setTimeout(function() {
                    $('#filterForm').submit();
                }, 500);
            });

            if ($('#directAssociateTable tbody tr td').attr('colspan') == undefined) {
                $('#directAssociateTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: false,
                    responsive: true,
                    lengthMenu: [5, 10, 25, 50]
                });
            }
        });
    </script>
@endpush