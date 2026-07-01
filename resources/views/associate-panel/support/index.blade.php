@extends('layouts.app')

@section('content')
    @php
        $totalTickets = $enquiries->count();
        $openTickets = $enquiries->where('status', '!=', 'Resolved')->count();
        $resolvedTickets = $enquiries->where('status', 'Resolved')->count();
    @endphp

    <div class="container-fluid mt-4 transaction-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-headset"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Support Center</span>
                        <h3 class="fw-bold mb-1 text-dark">Support</h3>
                        <p class="text-muted mb-0 small">Raise support tickets and track admin replies from one place.</p>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger border-0 shadow-sm">
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-secondary">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Total Tickets</small>
                        <h4 class="fw-bold mb-0">{{ $totalTickets }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-warning">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Open Tickets</small>
                        <h4 class="fw-bold text-warning mb-0">{{ $openTickets }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-success">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Resolved Tickets</small>
                        <h4 class="fw-bold text-success mb-0">{{ $resolvedTickets }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-4">
                <div class="transaction-card h-100">
                    <div class="transaction-card-body">
                        <div class="transaction-section-title">
                            <div class="d-flex align-items-center gap-3">
                                <span class="transaction-section-title-icon"><i class="bi bi-pencil-square"></i></span>
                                <div>
                                    <h5 class="fw-bold mb-1">Create Ticket</h5>
                                    <small class="text-muted">Share the issue clearly so admin can respond faster.</small>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('associate-panel.support.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                                <input type="text" name="query"
                                    class="form-control @error('query') is-invalid @enderror"
                                    value="{{ old('query') }}" placeholder="Enter support subject" required>
                                @error('query')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                                <textarea name="description" rows="8" class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Explain your issue with booking ID, payment receipt, or page name if applicable." required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-send me-1"></i> Submit Ticket
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="transaction-card">
                    <div class="transaction-card-body">
                        <div class="transaction-section-title">
                            <div class="d-flex align-items-center gap-3">
                                <span class="transaction-section-title-icon"><i class="bi bi-clock-history"></i></span>
                                <div>
                                    <h5 class="fw-bold mb-1">Ticket History</h5>
                                    <small class="text-muted">View your submitted tickets and admin replies.</small>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                {{ $totalTickets }} Records
                            </span>
                        </div>

                        <div class="transaction-table-wrap">
                            <table class="table table-hover align-middle mb-0 transaction-table w-100" id="supportTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Admin Reply</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($enquiries as $item)
                                        @php
                                            $modalId = 'supportTicketModal' . $item->id;
                                            $statusClass = match ($item->status) {
                                                'Resolved' => 'bg-success-subtle text-success border border-success-subtle',
                                                'In-Progress' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                                default => 'bg-warning-subtle text-warning border border-warning-subtle',
                                            };
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td style="min-width: 250px;">
                                                <strong>{{ ucfirst($item->query) }}</strong>
                                                <small class="text-muted d-block">
                                                    {{ \Illuminate\Support\Str::limit($item->description, 80) }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge {{ $statusClass }}">{{ $item->status }}</span>
                                            </td>
                                            <td style="min-width: 260px;">
                                                @if ($item->reply)
                                                    <span class="text-success fw-semibold">
                                                        <i class="bi bi-check-circle me-1"></i> Reply received
                                                    </span>
                                                    <small class="text-muted d-block">{{ \Illuminate\Support\Str::limit($item->reply, 70) }}</small>
                                                @else
                                                    <span class="text-muted">No reply yet</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $item->created_at?->format('d M Y') }}</strong>
                                                <small class="text-muted d-block">{{ $item->created_at?->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#{{ $modalId }}">
                                                    <i class="bi bi-eye me-1"></i> Details
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">
                                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                No support tickets found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach ($enquiries as $item)
            @php
                $statusClass = match ($item->status) {
                    'Resolved' => 'bg-success-subtle text-success border border-success-subtle',
                    'In-Progress' => 'bg-primary-subtle text-primary border border-primary-subtle',
                    default => 'bg-warning-subtle text-warning border border-warning-subtle',
                };
            @endphp
            <div class="modal fade" id="supportTicketModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 customer-receipt-modal">
                        <div class="customer-receipt-head">
                            <div class="customer-receipt-title">
                                <div class="customer-receipt-icon"><i class="bi bi-headset"></i></div>
                                <div>
                                    <span>Support Ticket</span>
                                    <h5>{{ ucfirst($item->query) }}</h5>
                                    <small>Created on {{ $item->created_at?->format('d M Y h:i A') }}</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-0">
                            <div class="customer-receipt-summary">
                                <div>
                                    <small>Ticket ID</small>
                                    <strong>#{{ $item->id }}</strong>
                                </div>
                                <div>
                                    <small>Status</small>
                                    <strong>{{ $item->status }}</strong>
                                </div>
                                <div>
                                    <small>Created</small>
                                    <strong>{{ $item->created_at?->format('d M Y') }}</strong>
                                </div>
                                <div>
                                    <small>Reply</small>
                                    <strong>{{ $item->reply ? 'Received' : 'Pending' }}</strong>
                                </div>
                            </div>

                            <div class="customer-receipt-body">
                                <div class="customer-receipt-panel">
                                    <div class="customer-receipt-panel-title">
                                        <i class="bi bi-chat-left-text"></i>
                                        <span>Your Message</span>
                                    </div>
                                    <div class="mb-3">
                                        <span class="badge {{ $statusClass }}">{{ $item->status }}</span>
                                    </div>
                                    <p class="mb-0">{{ $item->description }}</p>
                                </div>

                                <div class="customer-receipt-panel mt-4">
                                    <div class="customer-receipt-panel-title">
                                        <i class="bi bi-reply"></i>
                                        <span>Admin Reply</span>
                                    </div>
                                    @if ($item->reply)
                                        <p class="mb-0">{{ $item->reply }}</p>
                                    @else
                                        <div class="text-muted">Admin reply is not available yet.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
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
            if ($('#supportTable tbody tr td[colspan]').length === 0) {
                $('#supportTable').DataTable({
                    order: [
                        [4, 'desc']
                    ],
                    pageLength: 10,
                    responsive: false,
                    scrollX: true,
                    lengthMenu: [10, 25, 50],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search support tickets..."
                    }
                });
            }
        });
    </script>
@endpush
