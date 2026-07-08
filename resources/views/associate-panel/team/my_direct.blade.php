@extends('layouts.app')

@push('title')
    Associate Panel | {{ $pageTitle ?? 'My All Direct Associates' }}
@endpush

@section('content')
    @php
        $totalAssociates = $associates->count();
    @endphp

    <div class="container-fluid mt-4 transaction-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-people"></i>
                    </span>

                    <div>
                        <span class="text-success fw-bold text-uppercase small">
                            Associate Network
                        </span>

                        <h3 class="fw-bold mb-1 text-dark">
                            {{ $pageTitle ?? 'My All Direct Associates' }}
                        </h3>

                        <p class="text-muted mb-0 small">
                            View and filter your left and right direct associates.
                        </p>
                    </div>
                </div>

                <a href="{{ route('associate-panel.my-tree') }}" class="btn btn-outline-success">
                    <i class="bi bi-diagram-3 me-1"></i>
                    My Tree View
                </a>
            </div>
        </div>

        <div class="transaction-card mb-4">
            <div class="transaction-card-body">
                <div class="transaction-section-title">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-section-title-icon">
                            <i class="bi bi-funnel"></i>
                        </span>

                        <div>
                            <h5 class="fw-bold mb-1">Search Criteria</h5>
                            <small class="text-muted">
                                Filter direct associates by associate ID, direction and joining date range.
                            </small>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('associate-panel.my-direct') }}" id="directFilterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Associate ID</label>
                            <input type="text"
                                name="associate_id"
                                id="associate_id"
                                value="{{ request('associate_id') }}"
                                placeholder="Enter associate ID"
                                class="form-control auto-filter-input">
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Direct Team</label>
                            <select name="direction" id="direction" class="form-control auto-filter">
                                <option value="">All Direct</option>
                                <option value="left" {{ request('direction') == 'left' ? 'selected' : '' }}>
                                    Left Direct
                                </option>
                                <option value="right" {{ request('direction') == 'right' ? 'selected' : '' }}>
                                    Right Direct
                                </option>
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

                        <div class="col-lg-2 col-md-6 d-flex gap-2">
                            <a href="{{ route('associate-panel.my-direct') }}"
                                class="btn btn-outline-secondary flex-fill">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="transaction-card">
            <div class="transaction-card-body">
                <div class="transaction-section-title">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-section-title-icon">
                            <i class="bi bi-list-check"></i>
                        </span>

                        <div>
                            <h5 class="fw-bold mb-1">Direct Associate Records</h5>
                            <small class="text-muted">
                                Direct team details with direction, sponsor and contact information.
                            </small>
                        </div>
                    </div>

                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        {{ $totalAssociates }} Records
                    </span>
                </div>

                <div class="transaction-table-wrap">
                    <table class="table table-hover align-middle mb-0 transaction-table w-100" id="directAssociateTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Associate</th>
                                <th>Direction</th>
                                <th>Sponsor</th>
                                <th>Mobile</th>
                                <th>Joining Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($associates as $key => $item)
                                @php $modalId = 'directAssociateModal' . $item->id; @endphp

                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        <strong>{{ $item->associate_name }}</strong>
                                        <small class="text-muted d-block">{{ $item->associate_id }}</small>
                                    </td>

                                    <td>
                                        @if ($item->direction == 'left')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                                                <i class="bi bi-arrow-left-circle me-1"></i>
                                                Left Direct
                                            </span>
                                        @elseif ($item->direction == 'right')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2">
                                                <i class="bi bi-arrow-right-circle me-1"></i>
                                                Right Direct
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-2">
                                                N/A
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <strong>{{ $item->sponsor?->associate_name ?? '-' }}</strong>
                                        <small class="text-muted d-block">{{ $item->sponsor_id ?? '-' }}</small>
                                    </td>

                                    <td>{{ $item->mobile_number ?? '-' }}</td>

                                    <td>{{ $item->created_at?->format('d M Y') ?? '-' }}</td>

                                    <td>
                                        <button type="button"
                                            class="btn btn-outline-success btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#{{ $modalId }}">
                                            <i class="bi bi-eye me-1"></i>
                                            Details
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No direct associates found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @foreach ($associates as $item)
            <div class="modal fade" id="directAssociateModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 customer-receipt-modal">
                        <div class="customer-receipt-head">
                            <div class="customer-receipt-title">
                                <div class="customer-receipt-icon">
                                    <i class="bi bi-person"></i>
                                </div>

                                <div>
                                    <span>Direct Associate</span>
                                    <h5>{{ $item->associate_name }}</h5>
                                    <small>
                                        {{ $item->associate_id }} |
                                        Joined {{ $item->created_at?->format('d M Y') ?? '-' }}
                                    </small>
                                </div>
                            </div>

                            <button type="button"
                                class="btn-close btn-close-white"
                                data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-0">
                            <div class="customer-receipt-summary">
                                <div>
                                    <small>Direct Team</small>
                                    <strong>{{ $item->direct_count ?? 0 }}</strong>
                                </div>

                                <div>
                                    <small>Total Downline</small>
                                    <strong>{{ $item->downline_count ?? 0 }}</strong>
                                </div>

                                <div>
                                    <small>Direction</small>
                                    <strong>{{ ucfirst($item->direction ?? '-') }}</strong>
                                </div>
                            </div>

                            <div class="customer-receipt-body">
                                <div class="customer-receipt-panel">
                                    <div class="customer-receipt-panel-title">
                                        <i class="bi bi-person-vcard"></i>
                                        <span>Associate Information</span>
                                    </div>

                                    <div class="customer-receipt-line">
                                        <span>Associate ID</span>
                                        <strong>{{ $item->associate_id }}</strong>
                                    </div>

                                    <div class="customer-receipt-line">
                                        <span>Sponsor ID</span>
                                        <strong>{{ $item->sponsor_id ?? '-' }}</strong>
                                    </div>

                                    <div class="customer-receipt-line">
                                        <span>Sponsor Name</span>
                                        <strong>{{ $item->sponsor?->associate_name ?? '-' }}</strong>
                                    </div>

                                    <div class="customer-receipt-line">
                                        <span>Direction</span>
                                        <strong>{{ ucfirst($item->direction ?? '-') }}</strong>
                                    </div>

                                    <div class="customer-receipt-line">
                                        <span>Mobile</span>
                                        <strong>{{ $item->mobile_number ?? '-' }}</strong>
                                    </div>

                                    <div class="customer-receipt-line">
                                        <span>Email</span>
                                        <strong>{{ $item->email ?? '-' }}</strong>
                                    </div>

                                    <div class="customer-receipt-line">
                                        <span>City / State</span>
                                        <strong>{{ trim(($item->city ?? '-') . ' / ' . ($item->state ?? '-')) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-0">
                            <button type="button"
                                class="btn btn-outline-secondary rounded-pill px-4"
                                data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let typingTimer;

            $('.auto-filter').on('change', function() {
                $('#directFilterForm').submit();
            });

            $('.auto-filter-input').on('keyup', function() {
                clearTimeout(typingTimer);

                typingTimer = setTimeout(function() {
                    $('#directFilterForm').submit();
                }, 500);
            });

            if ($('#directAssociateTable tbody tr td[colspan]').length === 0) {
                $('#directAssociateTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    responsive: false,
                    scrollX: true,
                    lengthMenu: [10, 25, 50],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search direct associates..."
                    }
                });
            }
        });
    </script>
@endpush