@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        {{-- Page Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <div class="d-flex align-items-center">
                        <div class="rounded-4 bg-light d-flex align-items-center justify-content-center me-3"
                            style="width:60px;height:60px;">
                            <i class="bi bi-person-badge fs-2 text-secondary"></i>
                        </div>

                        <div>
                            <h3 class="fw-bold mb-1 text-dark">
                                Associate Management
                            </h3>

                            <p class="text-muted mb-0">
                                Manage associate records, ranks, sponsor details and login information.
                            </p>
                        </div>
                    </div>

                    <div class="badge bg-light text-dark border rounded-pill px-3 py-2">
                        Total Records: {{ $associates->count() }}
                    </div>

                </div>

            </div>
        </div>

        {{-- Filter --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">

                <form method="GET">
                    <div class="row g-3 align-items-end">

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">
                                Joining Date
                            </label>

                            <input type="date" name="joining_date" value="{{ request('joining_date') }}"
                                class="form-control">
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">
                                Associate Name
                            </label>

                            <input type="text" name="associate_name" value="{{ request('associate_name') }}"
                                class="form-control" placeholder="Enter associate name">
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">
                                Level
                            </label>

                            <select name="rank_id" class="form-select">
                                <option value="">Select Level</option>

                                @foreach ($ranks as $rank)
                                    <option value="{{ $rank->id }}"
                                        {{ request('rank_id') == $rank->id ? 'selected' : '' }}>
                                        {{ $rank?->designation . ' (' . number_format($rank->commission, 2) . ')' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="d-flex gap-2 flex-wrap">

                                <button type="submit" class="btn btn-success px-4">
                                    <i class="bi bi-search me-1"></i>
                                    Search
                                </button>

                                <a href="{{ route('associate.index') }}" class="btn btn-light border px-4">
                                    <i class="fa-solid fa-arrow-rotate-left"></i> Reset
                                </a>

                                <a href="{{ route('associate.export', request()->query()) }}"
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
        {{-- Table --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0" id="associateTable">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Sponsor ID</th>
                                <th>Associate ID</th>
                                <th>Under Place ID</th>
                                <th>Associate Name</th>
                                <th>Mobile</th>
                                <th>Percentage / Level</th>
                                <th>Password</th>
                                <th>Joining Date</th>
                                <th class="text-center" width="170">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($associates as $key => $associate)
                                <tr>
                                    <td>

                                    </td>

                                    <td>
                                        <span class="text-dark">
                                            {{ $associate->sponsor_id ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                            {{ $associate->associate_id }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $associate->under_place_id ?? 'N/A' }}
                                    </td>

                                    <td>
                                        <div class="fw-bold text-dark">
                                            {{ $associate->associate_name }}
                                        </div>
                                    </td>

                                    <td>
                                        <span class="text-muted">
                                            {{ $associate->mobile_number ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ number_format($associate->rank?->commission ?? 0, 2) }}%
                                        </div>

                                        <small class="text-muted">
                                            {{ $associate->rank?->designation ?? '-' }}
                                        </small>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
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
                                        <div class="d-inline-flex gap-2">

                                            <a href="{{ route('associate.show', $associate->id) }}"
                                                class="btn btn-sm btn-light border" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="{{ route('associate.edit', $associate->id) }}"
                                                class="btn btn-sm btn-light border" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <form action="{{ route('associate.destroy', $associate->id) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')

                                                <button type="button"
                                                    class="btn btn-sm btn-light border text-danger delete-btn"
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
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
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

            if ($('#associateTable tbody tr td').attr('colspan') == undefined) {
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
