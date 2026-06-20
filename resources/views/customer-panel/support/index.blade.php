@extends('layouts.app')

@section('content')
    <div class="container-fluid customer-panel-page customer-support-page">
        <div class="customer-profile-hero mb-4">
            <div class="customer-profile-main">
                <div class="customer-avatar profile-avatar">
                    <i class="bi bi-headset"></i>
                </div>
                <div>
                    <span class="customer-dashboard-kicker">Support Center</span>
                    <h3 class="mb-1">Customer Support</h3>
                    <p class="mb-0">Raise support tickets and track admin replies.</p>
                </div>
            </div>

            <div class="customer-profile-meta">
                <span class="badge bg-white text-success border rounded-pill px-3 py-2">
                    Total Tickets: {{ $enquiries->count() }}
                </span>
                <small>We will reply from admin panel</small>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="customer-stat-card success">
                    <div class="customer-stat-icon"><i class="bi bi-ticket-detailed"></i></div>
                    <div>
                        <small>Total Tickets</small>
                        <h4>{{ $enquiries->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="customer-stat-card warning">
                    <div class="customer-stat-icon"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <small>Open Tickets</small>
                        <h4>{{ $enquiries->where('status', '!=', 'Resolved')->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="customer-stat-card success">
                    <div class="customer-stat-icon"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <small>Closed Tickets</small>
                        <h4>{{ $enquiries->where('status', 'Resolved')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-xl-4">
                <div class="customer-section-card h-100">
                    <div class="customer-section-header d-block">
                        <h5 class="mb-1">
                            <i class="bi bi-pencil-square text-success me-2"></i>
                            Create Ticket
                        </h5>
                        <p class="mb-0">Submit your issue or query with clear details.</p>
                    </div>

                    <div class="customer-section-body">
                        <form action="{{ route('customer-panel.support.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Subject</label>
                                <input type="text" name="query"
                                    class="form-control form-control-lg rounded-4 @error('query') is-invalid @enderror"
                                    value="{{ old('query') }}" placeholder="Example: Payment receipt issue" required>
                                @error('query')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" rows="8" class="form-control rounded-4 @error('description') is-invalid @enderror"
                                    placeholder="Write your issue in detail..." required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-success rounded-4 fw-semibold w-100 shadow-sm">
                                <i class="bi bi-send me-2"></i>
                                Submit Ticket
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="customer-section-card">
                    <div class="customer-section-header">
                        <div>
                            <h5 class="mb-1">
                                <i class="bi bi-clock-history text-success me-2"></i>
                                Ticket History
                            </h5>
                            <p class="mb-0">View your submitted tickets and admin replies.</p>
                        </div>
                    </div>

                    <div class="customer-section-body">
                        @if ($enquiries->count())
                            <div class="table-responsive">
                                <table class="table align-middle table-hover customer-table w-100 support-history-table" id="customerSupportTable">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Admin Reply</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($enquiries as $item)
                                            <tr>
                                                <td style="min-width: 230px;">
                                                    <div class="fw-bold text-dark">{{ ucfirst($item->query) }}</div>
                                                    <small class="text-muted">{{ Str::limit($item->description, 70) }}</small>
                                                </td>
                                                <td style="min-width: 140px;">
                                                    @if ($item->status == 'Pending')
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill">Pending</span>
                                                    @elseif($item->status == 'Resolved')
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">Resolved</span>
                                                    @else
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">In-Progress</span>
                                                    @endif
                                                </td>
                                                <td style="min-width: 280px;">
                                                    @if ($item->reply)
                                                        <div class="support-reply-box">
                                                            <div>
                                                                <i class="bi bi-reply-fill me-1"></i>
                                                                Admin Reply
                                                            </div>
                                                            <small>{{ $item->reply }}</small>
                                                        </div>
                                                    @else
                                                        <span class="text-muted small">No reply yet</span>
                                                    @endif
                                                </td>
                                                <td style="min-width: 130px;">
                                                    <div class="fw-semibold">{{ $item->created_at->format('d M Y') }}</div>
                                                    <small class="text-muted">{{ $item->created_at->format('h:i A') }}</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="customer-empty-state">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <h5 class="mt-3">No Support Tickets Found</h5>
                                <p class="text-muted mb-0">Create your first ticket from the form on this page.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($enquiries->count())
        <script>
            $(document).ready(function() {
                $('#customerSupportTable').DataTable({
                    order: [
                        [3, 'desc']
                    ],
                    pageLength: 5,
                    lengthMenu: [5, 10, 25],
                    scrollX: true,
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search ticket..."
                    }
                });
            });
        </script>
    @endif
@endpush
