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
                        Total Records: <span id="directRecordCount">{{ $directAssociates->count() }}</span>
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
                                id="directAssociateSearch"
                                value="{{ request('associate_id') }}"
                                class="form-control auto-filter-input"
                                autocomplete="off"
                                placeholder="Enter sponsor associate id">
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold">Direction</label>
                            <select name="direction" id="directAssociateDirection" class="form-control auto-filter">
                                <option value="">All</option>
                                <option value="left" {{ request('direction') == 'left' ? 'selected' : '' }}>Left</option>
                                <option value="right" {{ request('direction') == 'right' ? 'selected' : '' }}>Right</option>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold">From Date</label>
                            <input type="date"
                                name="from_date"
                                id="directAssociateFromDate"
                                value="{{ request('from_date') }}"
                                class="form-control auto-filter">
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold">To Date</label>
                            <input type="date"
                                name="to_date"
                                id="directAssociateToDate"
                                value="{{ request('to_date') }}"
                                class="form-control auto-filter">
                        </div>

                        <div class="col-lg-3 col-md-12">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" id="resetDirectFilter" class="btn btn-light border px-4">
                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                    Reset
                                </button>

                                <a href="{{ route('direct-associate.export', request()->query()) }}"
                                    id="directExportLink"
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

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative">
            <div class="direct-filter-loader d-none" id="directFilterLoader">
                <div class="direct-filter-loader-box">
                    <span class="spinner-border spinner-border-sm text-success" role="status" aria-hidden="true"></span>
                    <span>Filtering records...</span>
                </div>
            </div>

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
                                <tr
                                    data-associate-id="{{ strtolower($item->associate_id ?? '') }}"
                                    data-associate-name="{{ strtolower($item->associate_name ?? '') }}"
                                    data-direction="{{ strtolower($item->direction ?? '') }}"
                                    data-sponsor-id="{{ strtolower($item->sponsor_id ?? '') }}"
                                    data-sponsor-name="{{ strtolower($item->sponsor?->associate_name ?? '') }}"
                                    data-mobile="{{ strtolower($item->mobile_number ?? '') }}"
                                    data-created-date="{{ $item->created_at?->format('Y-m-d') ?? '' }}">
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
                                                Root
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

@push('styles')
    <style>
        .direct-filter-loader {
            align-items: center;
            background: rgba(255, 255, 255, 0.74);
            backdrop-filter: blur(2px);
            display: flex;
            inset: 0;
            justify-content: center;
            position: absolute;
            z-index: 20;
        }

        .direct-filter-loader-box {
            align-items: center;
            background: #ffffff;
            border: 1px solid rgba(25, 135, 84, 0.18);
            border-radius: 999px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
            color: #198754;
            display: inline-flex;
            font-size: 13px;
            font-weight: 800;
            gap: 10px;
            padding: 10px 16px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            const tableElement = $('#directAssociateTable');
            const loader = $('#directFilterLoader');
            const countLabel = $('#directRecordCount');
            const exportLink = $('#directExportLink');
            const exportBaseUrl = "{{ route('direct-associate.export') }}";
            let typingTimer;
            let table = null;

            function filters() {
                return {
                    query: String($('#directAssociateSearch').val() || '').trim().toLowerCase(),
                    direction: String($('#directAssociateDirection').val() || '').trim().toLowerCase(),
                    fromDate: String($('#directAssociateFromDate').val() || '').trim(),
                    toDate: String($('#directAssociateToDate').val() || '').trim(),
                };
            }

            function updateExportLink(currentFilters) {
                const params = new URLSearchParams();

                if (currentFilters.query) {
                    params.set('associate_id', currentFilters.query);
                }

                if (currentFilters.direction) {
                    params.set('direction', currentFilters.direction);
                }

                if (currentFilters.fromDate) {
                    params.set('from_date', currentFilters.fromDate);
                }

                if (currentFilters.toDate) {
                    params.set('to_date', currentFilters.toDate);
                }

                exportLink.attr('href', exportBaseUrl + (params.toString() ? '?' + params.toString() : ''));
            }

            function rowMatches(row, currentFilters) {
                const rowText = [
                    row.dataset.associateId,
                    row.dataset.associateName,
                    row.dataset.sponsorId,
                    row.dataset.sponsorName,
                    row.dataset.mobile,
                ].join(' ');

                if (currentFilters.query && !rowText.includes(currentFilters.query)) {
                    return false;
                }

                if (currentFilters.direction && row.dataset.direction !== currentFilters.direction) {
                    return false;
                }

                if (currentFilters.fromDate && row.dataset.createdDate < currentFilters.fromDate) {
                    return false;
                }

                if (currentFilters.toDate && row.dataset.createdDate > currentFilters.toDate) {
                    return false;
                }

                return true;
            }

            if (tableElement.find('tbody tr td[colspan]').length === 0) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable !== tableElement.get(0)) {
                        return true;
                    }

                    const row = settings.aoData[dataIndex]?.nTr;

                    if (!row) {
                        return true;
                    }

                    return rowMatches(row, filters());
                });

                table = tableElement.DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: false,
                    responsive: true,
                    lengthMenu: [5, 10, 25, 50]
                });
            }

            function applyDirectFilter(showLoader = true) {
                const currentFilters = filters();
                updateExportLink(currentFilters);

                if (showLoader) {
                    loader.removeClass('d-none');
                }

                window.setTimeout(function() {
                    if (table) {
                        table.draw();
                        countLabel.text(table.rows({ filter: 'applied' }).count());
                    } else {
                        countLabel.text(0);
                    }

                    loader.addClass('d-none');
                }, showLoader ? 180 : 0);
            }

            $('#filterForm').on('submit', function(event) {
                event.preventDefault();
                applyDirectFilter();
            });

            $('.auto-filter').on('change', function() {
                applyDirectFilter();
            });

            $('.auto-filter-input').on('input', function() {
                clearTimeout(typingTimer);

                typingTimer = setTimeout(function() {
                    applyDirectFilter();
                }, 250);
            });

            $('#resetDirectFilter').on('click', function() {
                $('#directAssociateSearch').val('');
                $('#directAssociateDirection').val('');
                $('#directAssociateFromDate').val('');
                $('#directAssociateToDate').val('');
                applyDirectFilter();
            });

            applyDirectFilter(false);
        });
    </script>
@endpush
