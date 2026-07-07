@extends('layouts.app')

@push('title')
    Associate Management
@endpush
@section('content')
    <div class="container-fluid py-4 associate-management-page">

        <div class="associate-management-hero mb-4">
            <div class="associate-management-title">
                <div class="associate-management-icon">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div>
                    <span class="associate-management-kicker">Associate Management</span>
                    <h3 class="fw-bold mb-1">Associate Records</h3>
                    <p class="mb-0">Manage associate records, ranks, sponsor details and login information.</p>
                </div>
            </div>

            <div class="associate-management-hero-actions">
                <div class="associate-management-count">
                    <span>Total Records</span>
                    <strong>{{ $associates->count() }}</strong>
                </div>
                <a href="{{ route('associate.create') }}" class="btn btn-success">
                    <i class="bi bi-person-plus"></i>
                    Add Associate
                </a>
            </div>
        </div>

        <div class="associate-management-filter mb-4">
            <div class="associate-management-section-head">
                <div class="associate-management-section-icon">
                    <i class="bi bi-funnel"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Find Associate</h5>
                    <p class="text-muted mb-0">Filter by joining date, name or level.</p>
                </div>
            </div>

            <form method="GET" class="mt-3">
                <div class="row g-3 align-items-end">
                    <div class="col-xl-4 col-lg-4 col-md-6">
                        <label class="form-label">Joining Date</label>
                        <input type="date" name="joining_date" value="{{ request('joining_date') }}"
                            class="form-control">
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6">
                        <label class="form-label">Associate Name</label>
                        <input type="text" name="associate_name" value="{{ request('associate_name') }}"
                            class="form-control" placeholder="Enter associate name">
                    </div>

                    {{-- <div class="col-xl-3 col-lg-4 col-md-6">
                        <label class="form-label">Level</label>
                        <select name="rank_id" class="form-select">
                            <option value="">Select Level</option>

                            @foreach ($ranks as $rank)
                                <option value="{{ $rank->id }}" {{ request('rank_id') == $rank->id ? 'selected' : '' }}>
                                    {{ $rank?->designation . ' (' . number_format($rank->commission, 2) . ')' }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}

                    <div class="col-xl-3 col-lg-12">
                        <div class="associate-management-filter-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-search"></i>
                                Search
                            </button>
                            <a href="{{ route('associate.index') }}" class="btn btn-light">
                                <i class="fa-solid fa-arrow-rotate-left"></i>
                                Reset
                            </a>
                            <a href="{{ route('associate.export', request()->query()) }}" class="btn btn-outline-success">
                                <i class="bi bi-download"></i>
                                Export
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="associate-management-table-card">
            <div class="associate-management-table-head">
                <div class="associate-management-section-head">
                    <div class="associate-management-section-icon">
                        <i class="bi bi-table"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Associate List</h5>
                        <p class="text-muted mb-0">All associate records are listed below.</p>
                    </div>
                </div>

                <span class="associate-management-record-pill">{{ $associates->count() }} Records</span>
            </div>

            <div class="associate-management-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="associateTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Sponsor ID</th>
                                <th>Associate ID</th>
                                <th>Under Place ID</th>
                                <th>Associate Name</th>
                                <th>Mobile</th>
                                <th>Direction</th>
                                {{-- <th>Percentage / Level</th> --}}
                                <th>Password</th>
                                <th>Joining Date</th>
                                <th class="text-center" width="170">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($associates as $key => $associate)
                                <tr>
                                    <td></td>

                                    <td>
                                        <span class="associate-management-muted">
                                            {{ $associate->sponsor_id ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="associate-management-id">
                                            {{ $associate->associate_id }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $associate->under_place_id ?? 'N/A' }}
                                    </td>

                                    <td>
                                        <div class="associate-management-name">
                                            <span>{{ strtoupper(substr($associate->associate_name ?? 'A', 0, 1)) }}</span>
                                            <div>
                                                <strong>{{ $associate->associate_name }}</strong>
                                                <small>{{ $associate->email ?? 'No email' }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="associate-management-muted">
                                            {{ $associate->mobile_number ?? '-' }}
                                        </span>
                                    </td>

                                    {{-- <td>
                                        <div class="fw-semibold text-dark">
                                            {{ number_format($associate->rank?->commission ?? 0, 2) }}%
                                        </div>

                                        <small class="text-muted">
                                            {{ $associate->rank?->designation ?? '-' }}
                                        </small>
                                    </td> --}}
                                     <td>
                                        <div class="fw-semibold text-dark">
                                            {{ ucfirst($associate->direction ?? '-') }}
                                        </div>
                                    </td>

                                    <td>
                                        <span class="associate-management-password">
                                            {{ $associate->plain_password ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ $associate->created_at?->format('d-m-Y') ?? '-' }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <div class="associate-management-actions">

                                            <a href="{{ route('associate.show', $associate->id) }}"
                                                class="btn btn-sm btn-light" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="{{ route('associate.edit', $associate->id) }}"
                                                class="btn btn-sm btn-light" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <form action="{{ route('associate.destroy', $associate->id) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')

                                                <button type="button"
                                                    class="btn btn-sm btn-light text-danger delete-btn"
                                                    title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        <div class="associate-management-empty">
                                            <div><i class="bi bi-inbox"></i></div>
                                            <h5>No Associates Found</h5>
                                            <p class="mb-0">Try changing filters or add a new associate.</p>
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
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {

            if (!$('#associateTable tbody tr td[colspan]').length) {
                let table = $('#associateTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                    lengthMenu: [5, 10, 25, 50],
                    columnDefs: [{
                        targets: 0,
                        orderable: false,
                        searchable: false
                    }]
                });

                table.on('order.dt search.dt draw.dt', function() {
                    table.column(0, {
                        search: 'applied',
                        order: 'applied'
                    }).nodes().each(function(cell, i) {
                        cell.innerHTML = '#' + (i + 1);
                    });
                }).draw();
            }

            $('.delete-btn').click(function() {
                let form = $(this).closest('form');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Associate will be deleted!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

        });
    </script>
@endpush
