@extends('layouts.app')

@push('title')
    Associate Panel | Associate List
@endpush

@section('content')
    <div class="container-fluid mt-4">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-people-fill"></i>
                    </span>

                    <div>
                        <span class="text-success fw-bold text-uppercase small">
                            Associate Management
                        </span>

                        <h3 class="fw-bold mb-1 text-dark">
                            Associate List
                        </h3>

                        <p class="text-muted mb-0 small">
                            View and manage all registered associates with their placement direction.
                        </p>
                    </div>
                </div>

                <a href="{{ route('associate-panel.register-create') }}" class="btn btn-success">
                    <i class="bi bi-person-plus me-1"></i>
                    Add New Associate
                </a>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="GET" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="mb-2">Joining Date</label>
                            <input type="date"
                                name="joining_date"
                                value="{{ request('joining_date') }}"
                                class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="mb-2">Associate Name</label>
                            <input type="text"
                                name="associate_name"
                                value="{{ request('associate_name') }}"
                                class="form-control"
                                placeholder="Enter associate name">
                        </div>

                        <div class="col-md-3">
                            <label class="mb-2">Direction</label>
                            <select name="direction" class="form-control">
                                <option value="">All Direction</option>
                                <option value="left" {{ request('direction') == 'left' ? 'selected' : '' }}>
                                    Left
                                </option>
                                <option value="right" {{ request('direction') == 'right' ? 'selected' : '' }}>
                                    Right
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-light">
                                <i class="bi bi-search me-1"></i>
                                Search
                            </button>

                            <a href="{{ route('associate-panel.associate-detail') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Reset
                            </a>

                            <a href="{{ route('associate-panel.export-associate', request()->query()) }}"
                                class="btn btn-success">
                                <i class="bi bi-download me-1"></i>
                                Export Excel
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="associateTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Sponsor Id</th>
                                <th>Associate ID</th>
                                <th>Under Place Id</th>
                                <th>Direction</th>
                                <th>Associate Name</th>
                                <th>Mobile</th>
                                <th>Password</th>
                                <th>Joining Date</th>
                                <th width="160">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($associates as $key => $associate)
                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>{{ $associate->sponsor_id }}</td>

                                    <td>{{ $associate->associate_id }}</td>

                                    <td>{{ $associate->under_place_id ?? 'N/A' }}</td>

                                    <td>
                                        @if ($associate->direction == 'left')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                                                Left
                                            </span>
                                        @elseif ($associate->direction == 'right')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2">
                                                Right
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-2">
                                                N/A
                                            </span>
                                        @endif
                                    </td>

                                    <td>{{ $associate->associate_name }}</td>

                                    <td>{{ $associate->mobile_number }}</td>

                                    <td>{{ $associate->plain_password }}</td>

                                    <td>{{ $associate->created_at?->format('d-m-Y') }}</td>

                                    <td>
                                        <a href="{{ route('associate-panel.associat-download-pdf', $associate->id) }}"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Download PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>

                                        <a href="{{ route('associate-panel.associate-edit', $associate->id) }}"
                                            class="btn btn-sm btn-outline-success"
                                            title="Edit Associate">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form action="{{ route('associate-panel.associate-delete', $associate->id) }}"
                                            method="POST"
                                            class="d-inline delete-form"
                                            title="Associate Delete">
                                            @csrf
                                            @method('DELETE')

                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        No associates found
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
            $('#associateTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: false,
                responsive: true,
                lengthMenu: [5, 10, 25, 50]
            });

            $('.delete-btn').click(function() {
                let form = $(this).closest('form');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Associate will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush