@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <!-- Header -->


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
                            View and manage all registered associates along with their profile and account details.
                        </p>
                    </div>
                </div>
                <a href="{{ route('associate-panel.register-create') }}" class="btn btn-success">
                    <i class="bi bi-person-plus me-1"></i>
                    Add New Associate
                </a>
            </div>
        </div>
    </div>
    <!-- Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label class="mb-2">Joining Date</label>
                        <input type="date" name="joining_date" value="{{ request('joining_date') }}"
                            class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="mb-2">Associate Name</label>
                        <input type="text" name="associate_name" value="{{ request('associate_name') }}"
                            class="form-control" placeholder="Enter associate name">
                    </div>
                    <div class="col-md-3">
                        <label class="mb-2">Level</label>
                        <select name="rank_id" class="form-control">
                            <option value="">Select Level</option>
                            @foreach ($ranks as $rank)
                                <option value="{{ $rank->id }}" {{ request('rank_id') == $rank->id ? 'selected' : '' }}>
                                    {{ $rank?->designation . ' (' . number_format($rank->commission, 2) . ')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-light"><i class="bi bi-search me-1"></i> Search</button>
                        <a href="{{ route('associate-panel.associate-detail') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset
                        </a>
                        <a href="{{ route('associate-panel.export-associate', request()->query()) }}"
                            class="btn btn-success">
                            <i class="bi bi-download me-1"></i> Export Excel
                        </a>
                    </div>
                </div>
            </form>
            <!-- Table -->
            <div class="table-responsive">

                <table class="table table-hover align-middle" id="associateTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Sponsor Id</th>
                            <th>Associate ID</th>
                            <th>Under Place Id</th>
                            <th>Associate Name</th>
                            <th>Mobile</th>
                            <th>Percentage / Leval</th>
                            <th>Password</th>
                            <th>Joining Date</th>
                            <th width="160">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        @forelse($associates as $key => $associate)
                            <tr>
                                <td>
                                    {{ $key + 1 }}
                                </td>
                                <td>
                                    {{ $associate->sponsor_id }}
                                </td>
                                <td>
                                    {{ $associate->associate_id }}
                                </td>
                                <td>
                                    {{ $associate->under_place_id ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $associate->associate_name }}
                                </td>
                                <td>
                                    {{ $associate->mobile_number }}
                                </td>
                                <td>
                                    {{ number_format($associate->rank->commission, 2) . ' (' . $associate->rank?->designation . ')' }}
                                </td>
                                <td>
                                    {{ $associate->plain_password }}
                                </td>
                                <td>
                                    {{ $associate->created_at?->format('d-m-Y') }}
                                </td>
                                <td>
                                    <a href="{{ route('associate-panel.associat-download-pdf', $associate->id) }}"
                                        class="btn btn-sm btn-outline-danger" title="Download PDF">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                    <!-- Edit -->
                                    <a href="{{ route('associate-panel.associate-edit', $associate->id) }}"
                                        class="btn btn-sm btn-outline-success" title="Edit Associate">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('associate-panel.associate-delete', $associate->id) }}"
                                        method="POST" class="d-inline delete-form" title="Associate Delete">
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

                                <td colspan="7" class="text-center text-muted py-4">

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
