@extends('layouts.app')
@section('content')
    <div class="container-fluid py-4">

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">
                            <i class="bi bi-shield-lock me-2 text-success"></i> Roles & Permissions
                        </h3>
                        <p class="text-muted mb-0 small">Define system roles and assign specific permissions to each.</p>
                    </div>
                    @can('plc-development-rate-create')
                        <a href="{{ route('roles.create') }}" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Add New Role
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <!-- Table -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle" id="rolesTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Role Name</th>
                                <th class="text-center">Permissions</th>
                                <th class="text-center pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $key => $role)
                                <tr>
                                    <td class="ps-3 fw-bold text-muted">{{ $key + 1 }}</td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ ucfirst($role->name) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">
                                            {{ $role->permissions->count() }} Permissions
                                        </span>
                                    </td>
                                    <td class="text-center pe-3">
                                        <a href="{{ route('roles.edit', $role->id) }}"
                                           class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                        </a>

                                        <form method="POST" action="{{ route('roles.destroy', $role->id) }}"
                                             class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"class="btn btn-sm btn-outline-danger delete-btn" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-shield-slash fs-1 mb-2"></i>
                                            <span>No roles found. Please add a new role.</span>
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

            if ($('#rolesTable tbody tr').length > 0 &&
                $('#rolesTable tbody tr td').attr('colspan') == undefined) {
                $('#rolesTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    responsive: true,
                    lengthMenu: [5, 10, 25, 50],
                    language: {
                        search: "",
                        searchPlaceholder: "Search roles..."
                    }
                });
            }
            $('.delete-btn').click(function() {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Delete Role?',
                    text: "This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
