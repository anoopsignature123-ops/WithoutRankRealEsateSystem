@extends('layouts.app')
@push('title')
    Promotion Business
@endpush
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/report.css') }}">
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="transaction-hero mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                {{-- Left --}}
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-graph-up-arrow text-success"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Promotion Center</span>
                        <h3 class="fw-bold text-dark mb-1">Promotion Business</h3>
                        <p class="text-muted small mb-0">Review associate business and promotion status.</p>
                    </div>
                </div>
                {{-- Right --}}
                <div class="d-flex align-items-end gap-2 flex-wrap">
                    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
                        <div style="min-width:320px;">
                            <label class="form-label small fw-semibold mb-1">Associate</label>
                            <select name="associate_id" class="form-select">
                                <option value="">All Associates</option>
                                @foreach ($associateList as $associate)
                                    <option value="{{ $associate->id }}"
                                        {{ request('associate_id') == $associate->id ? 'selected' : '' }}>
                                        {{ $associate->associate_id }} - {{ $associate->associate_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-success"><i class="bi bi-search"></i></button>
                        <a href="{{ route('promoted.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </form>
                    <a href="{{ route('promoted.history') }}" class="btn btn-outline-success">
                        <i class="bi bi-clock-history me-1"></i>History
                    </a>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Total Associates</small>
                        <h4 class="fw-bold mb-0">{{ $summary['total_associates'] ?? $reports->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border border-success-subtle shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Ready For Promotion</small>
                        <h4 class="fw-bold text-success mb-0">{{ $summary['eligible'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border border-primary-subtle shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Total Business</small>
                        <h4 class="fw-bold text-primary mb-0">
                            &#8377;{{ number_format($summary['total_business'] ?? 0, 2) }}
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border border-warning-subtle shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold">Promotion History</small>
                        <h4 class="fw-bold text-warning mb-0">{{ $summary['history_count'] ?? $histories->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="bi bi-table text-success me-2"></i>Promotion Projection</h5>
                        <small class="text-muted">
                            Rank upgrade is calculated from paid amount only where booking status is booked.
                        </small>
                    </div>
                    <span class="badge bg-success-subtle text-success border rounded-pill px-3 py-2">
                        {{ $reports->count() }} Records
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle nowrap w-100" id="promotionTable">
                        <thead class="table-success">
                            <tr>
                                <th>#</th>
                                <th>Associate</th>
                                <th>Current Rank</th>
                                <th class="text-end">Self Business</th>
                                <th class="text-end">Team Business</th>
                                <th class="text-end">Total Business</th>
                                <th>Next Projection</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reports as $key => $row)
                                @php
                                    $progress = min(100, (float) ($row['progress_percent'] ?? 0));
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $row['associate']->associate_name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $row['associate']->associate_id ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                                            {{ $row['current_rank']?->designation ?? 'Not Assigned' }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold">
                                        ₹{{ number_format($row['self_business'] ?? 0, 2) }}
                                    </td>
                                    <td class="text-end fw-semibold">
                                        ₹{{ number_format($row['team_business'] ?? 0, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        ₹{{ number_format($row['total_business'] ?? 0, 2) }}
                                    </td>
                                    <td style="min-width:220px;">
                                        @if ($row['next_rank'])
                                            <div class="fw-semibold small mb-1">
                                                {{ $row['next_rank']->designation }}
                                            </div>

                                            <div class="progress mb-1" style="height:6px;">
                                                <div class="progress-bar bg-success" style="width: {{ $progress }}%;">
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between gap-2">
                                                <span
                                                    class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">
                                                    {{ number_format($progress, 1) }}%
                                                </span>

                                                <span
                                                    class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                                    Gap ₹{{ number_format($row['remaining_target'] ?? 0, 0) }}
                                                </span>
                                            </div>

                                            <small class="text-muted d-block mt-1">
                                                Target ₹{{ number_format($row['next_target_from'] ?? 0, 0) }}
                                            </small>
                                        @else
                                            <span class="badge bg-success rounded-pill px-3 py-2">
                                                Highest Rank
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($row['can_promote'])
                                            <span class="badge bg-success rounded-pill px-3 py-2">
                                                <i class="bi bi-check-circle-fill me-1"></i>
                                                Eligible
                                            </span>

                                            <div class="small text-success mt-1">
                                                {{ $row['eligible_rank']?->designation }}
                                            </div>
                                        @else
                                            <span
                                                class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2">
                                                <i class="bi bi-graph-up-arrow me-1"></i>
                                                Growing
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($row['can_promote'])
                                            <form action="{{ route('promoted.check', $row['associate']->id) }}"
                                                method="POST" class="promotion-action-form">
                                                @csrf

                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                                    Promote
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-3"
                                                disabled>
                                                Waiting
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                                        <span class="text-muted">No promotion records found.</span>
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
        $(function() {
            $('#promotionTable').DataTable({
                pageLength: 15,
                ordering: true,
                responsive: false,
                scrollX: true,
                language: {
                    emptyTable: 'No promotion records found.'
                }
            });
            $('.promotion-action-form').on('submit', function(event) {
                const form = this;

                if (form.dataset.confirmed === '1') {
                    return true;
                }
                event.preventDefault();
                Swal.fire({
                    title: 'Check promotion?',
                    text: 'The system will recalculate business and upgrade rank if eligible.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, check',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#198754'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = '1';
                        form.submit();
                    }
                });
            });
            @if (session('promotion_alert'))
                Swal.fire({
                    icon: @json(session('promotion_alert')['type'] ?? 'info'),
                    title: @json(session('promotion_alert')['title'] ?? 'Promotion Check'),
                    text: @json(session('promotion_alert')['message'] ?? ''),
                    confirmButtonColor: '#198754'
                });
            @endif
        });
    </script>
@endpush
