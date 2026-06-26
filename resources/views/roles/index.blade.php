@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 transaction-page role-management-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-shield-lock"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Access Control</span>
                        <h3 class="fw-bold mb-1 text-dark">Role Management</h3>
                        <p class="text-muted mb-0 small">Create roles and control module permissions for staff users.</p>
                    </div>
                </div>

                <a href="{{ route('roles.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i>
                    Add New Role
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="role-stat-card">
                    <span class="role-stat-icon"><i class="bi bi-person-lock"></i></span>
                    <div>
                        <small>Total Roles</small>
                        <strong>{{ $roles->count() }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="role-stat-card">
                    <span class="role-stat-icon"><i class="bi bi-check2-square"></i></span>
                    <div>
                        <small>Total Permissions Assigned</small>
                        <strong>{{ $roles->sum(fn ($role) => $role->permissions->count()) }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="role-stat-card">
                    <span class="role-stat-icon"><i class="bi bi-clock-history"></i></span>
                    <div>
                        <small>Latest Role</small>
                        <strong>{{ ucfirst($roles->first()?->name ?? 'N/A') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="transaction-card transaction-history-card role-table-card mb-4">
            <div class="transaction-history-head">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-section-title-icon">
                        <i class="bi bi-list-check"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-1">Role Records</h5>
                        <small class="text-muted">Manage role names and assigned permission counts.</small>
                    </div>
                </div>

                <span class="transaction-count">{{ $roles->count() }} Records</span>
            </div>

            <div class="transaction-table-wrap">
                <table class="table transaction-table align-middle mb-0" id="rolesTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Role Name</th>
                            <th class="text-center">Permissions</th>
                            <th>Created</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $key => $role)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="role-avatar">{{ strtoupper(substr($role->name, 0, 1)) }}</span>
                                        <div>
                                            <div class="fw-bold text-dark">{{ ucfirst($role->name) }}</div>
                                            <small class="text-muted">System access role</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        {{ $role->permissions->count() }} Permissions
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        {{ $role->created_at ? $role->created_at->format('d M, Y') : '-' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-pencil-square me-1"></i>
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('roles.destroy', $role->id) }}"
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn">
                                                <i class="bi bi-trash me-1"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-shield-slash fs-1 d-block mb-2 text-muted"></i>
                                    No roles found. Please add a new role.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const hasRecords = {{ $roles->count() > 0 ? 'true' : 'false' }};

            if (hasRecords) {
                $('#rolesTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    responsive: true,
                    lengthMenu: [5, 10, 25, 50],
                    columnDefs: [{
                        orderable: false,
                        targets: [4]
                    }],
                    language: {
                        search: "",
                        searchPlaceholder: "Search roles..."
                    }
                });
            }

            $('.delete-btn').click(function() {
                const form = $(this).closest('form');

                Swal.fire({
                    title: 'Delete Role?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete',
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
