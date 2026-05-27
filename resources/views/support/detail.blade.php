@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        {{-- Page Header --}}
        <div class="card border-0 shadow-sm mb-4 rounded-4">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-1">Support Ticket Details</h4>
                <small class="text-muted">Viewing detailed information and managing support request #{{ $support->id }}</small>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="fw-bold mb-1">Ticket Details</h4>
                                <small class="text-muted">Support request information</small>
                            </div>
                            @if ($support->status == 'Pending')
                                <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">Pending</span>
                            @elseif($support->status == 'Resolved')
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Resolved</span>
                            @else
                                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">In-Progress</span>
                            @endif
                        </div>

                        <div class="border rounded-4 p-3 bg-light mb-3">
                            <small class="text-muted d-block mb-1">Associate Name</small>
                            <div class="fw-bold">{{ $support->associate->associate_name ?? '-' }}</div>
                        </div>

                        <div class="border rounded-4 p-3 bg-light mb-3">
                            <small class="text-muted d-block mb-1">Query</small>
                            <div class="fw-bold">{{ $support->query }}</div>
                        </div>

                        <div class="border rounded-4 p-3 bg-light mb-3">
                            <small class="text-muted d-block mb-2">Description</small>
                            <div class="text-dark">{{ $support->description }}</div>
                        </div>

                        <div class="border rounded-4 p-3 bg-light">
                            <small class="text-muted d-block mb-1">Created Date</small>
                            <div class="fw-semibold">{{ $support->created_at->format('d M Y h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="fw-bold mb-1">Reply Support Ticket</h4>
                                <small class="text-muted">Send response and update ticket status</small>
                            </div>
                        </div>

                        @if ($support->status == 'Resolved')
                            <div class="alert alert-success rounded-4 border-0">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                    <div>
                                        <div class="fw-bold">Ticket Already Resolved</div>
                                        <small>This support ticket has been closed. You can only view the reply.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="border rounded-4 p-4 bg-light">
                                <label class="fw-semibold mb-2">Admin Reply</label>
                                <div class="text-dark">{{ $support->reply ?? 'No reply available' }}</div>
                            </div>
                        @else
                            <form action="{{ route('support.reply', $support->id) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Reply Message</label>
                                    <textarea name="reply" rows="7" class="form-control rounded-4 @error('reply') is-invalid @enderror"
                                        placeholder="Write your reply here...">{{ old('reply', $support->reply) }}</textarea>
                                    @error('reply')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Update Status</label>
                                    <select name="status" class="form-select rounded-4">
                                        <option value="">Select Status</option>
                                        <option value="In-Progress"
                                            {{ $support->status == 'In-Progress' ? 'selected' : '' }}>In-Progress</option>
                                        <option value="Resolved" {{ $support->status == 'Resolved' ? 'selected' : '' }}>
                                            Resolved</option>
                                    </select>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('support.index') }}" class="btn btn-outline-secondary px-4">
                                        <i class="bi bi-arrow-left me-1"></i>
                                        Back
                                    </a>
                                    <button type="submit" class="btn btn-success rounded-pill px-4">
                                        <i class="bi bi-send me-1"></i>
                                        Submit Reply
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection