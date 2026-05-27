@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-headset text-success me-2"></i>
                    Support Center
                </h3>
                <p class="text-muted mb-0">
                    Raise support tickets and track admin replies
                </p>
            </div>

            {{-- Stats Section --}}
            <div class="d-flex gap-3 flex-wrap">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body px-4 py-3">
                        <small class="text-muted d-block mb-1">Total Tickets</small>
                        <h5 class="fw-bold mb-0">{{ $enquiries->count() }}</h5>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body px-4 py-3">
                        <small class="text-muted d-block mb-1">Open Tickets</small>
                        <h5 class="fw-bold text-warning mb-0">
                            {{ $enquiries->where('status', '!=', 'Resolved')->count() }}
                        </h5>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body px-4 py-3">
                        <small class="text-muted d-block mb-1">Closed Tickets</small>
                        <h5 class="fw-bold text-success mb-0">
                            {{ $enquiries->where('status', 'Resolved')->count() }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Create Ticket Form (Left Side) --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-1">
                            <i class="bi bi-pencil-square text-success me-2"></i>
                            Create Ticket
                        </h5>
                        <p class="text-muted small mb-0">Submit your issue or query</p>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('associate-panel.support.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Subject</label>
                                <input type="text" name="query"
                                    class="form-control form-control-lg rounded-4 @error('query') is-invalid @enderror"
                                    placeholder="Enter your issue subject" required>
                                @error('query')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" rows="7" class="form-control rounded-4 @error('description') is-invalid @enderror"
                                    placeholder="Explain your issue properly..." required></textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg rounded-4 fw-semibold shadow-sm">
                                    <i class="bi bi-send me-2"></i> Submit Ticket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Ticket History Table (Right Side) --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-1">
                            <i class="bi bi-clock-history text-success me-2"></i>
                            Ticket History
                        </h5>
                        <p class="text-muted small mb-0">View all support conversations</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table align-middle table-hover" id="supportTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Admin Reply</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($enquiries as $item)
                                        <tr>
                                            <td style="min-width: 230px;">
                                                <div class="fw-bold text-dark">{{ ucfirst($item->query) }}</div>
                                                <small class="text-muted">
                                                    {{ Str::limit($item->description, 70) }}
                                                </small>
                                            </td>
                                            <td style="min-width: 130px;">
                                                @if ($item->status == 'Pending')
                                                    <span
                                                        class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill">Pending</span>
                                                @elseif($item->status == 'Resolved')
                                                    <span
                                                        class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">Resolved</span>
                                                @else
                                                    <span
                                                        class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">In-Progress</span>
                                                @endif
                                            </td>
                                            <td style="min-width: 260px;">
                                                @if ($item->reply)
                                                    <div class="bg-light rounded-4 p-3 border">
                                                        <div class="fw-semibold text-success mb-1">
                                                            <i class="bi bi-reply-fill me-1"></i> Admin Reply
                                                        </div>
                                                        <small class="text-dark">{{ $item->reply }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">No reply yet</span>
                                                @endif
                                            </td>
                                            <td style="min-width: 120px;">
                                                <div class="fw-semibold">{{ $item->created_at->format('d M Y') }}</div>
                                                <small class="text-muted">{{ $item->created_at->format('h:i A') }}</small>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                    No support tickets found
                                                </div>
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
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#supportTable').DataTable({
                order: [
                    [3, 'desc']
                ],
                pageLength: 5,
                lengthMenu: [5, 10, 25],
            });
        });
    </script>
@endpush
