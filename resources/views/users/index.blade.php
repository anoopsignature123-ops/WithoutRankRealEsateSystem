@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 transaction-page staff-management-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-people-fill"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Access Control</span>
                        <h3 class="fw-bold mb-1 text-dark">User / Staff Management</h3>
                        <p class="text-muted mb-0 small">Manage staff accounts, roles, passwords and account status.</p>
                    </div>
                </div>

                @can('users-modify')
                    <a href="{{ route('users.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>
                        Add New User
                    </a>
                @endcan
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="role-stat-card">
                    <span class="role-stat-icon"><i class="bi bi-person-lines-fill"></i></span>
                    <div>
                        <small>Total Staff</small>
                        <strong>{{ $users->count() }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="role-stat-card">
                    <span class="role-stat-icon"><i class="bi bi-check-circle"></i></span>
                    <div>
                        <small>Active Staff</small>
                        <strong>{{ $users->where('status', 'active')->count() }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="role-stat-card">
                    <span class="role-stat-icon"><i class="bi bi-slash-circle"></i></span>
                    <div>
                        <small>Inactive Staff</small>
                        <strong>{{ $users->where('status', 'inactive')->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="transaction-card transaction-history-card staff-table-card mb-4">
            <div class="transaction-history-head">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-section-title-icon">
                        <i class="bi bi-table"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-1">Staff Records</h5>
                        <small class="text-muted">View and update staff access details.</small>
                    </div>
                </div>

                <span class="transaction-count">{{ $users->count() }} Records</span>
            </div>

            <div class="transaction-table-wrap">
                <table class="table transaction-table align-middle mb-0" id="usersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Staff</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Password</th>
                            <th>Created By</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $key => $user)
                            @php
                                $roleName = $user->roles->first()?->name;
                            @endphp
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ ! empty($user->profile_image) ? getFileUrl($user->profile_image) : asset('assets/images/avatar.png') }}"
                                            class="staff-avatar" alt="{{ $user->name }}"
                                            onerror="this.src='{{ asset('assets/images/avatar.png') }}'">
                                        <div>
                                            <div class="fw-bold text-dark">{{ ucfirst($user->name) }}</div>
                                            <small class="text-muted">Staff ID: {{ $user->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $roleName ? ucfirst($roleName) : 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <code class="staff-password-code">{{ $user->plain_text ?: '-' }}</code>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $user->creator?->name ?? 'System' }}</span>
                                </td>
                                <td>
                                    @if ($user->status == 'active')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @can('users-modify')
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-pencil-square me-1"></i>
                                                Edit
                                            </a>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-btn">
                                                    <i class="bi bi-trash me-1"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-muted small">No access</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-person-x fs-1 d-block mb-2 text-muted"></i>
                                    No users found.
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
            const hasRecords = {{ $users->count() > 0 ? 'true' : 'false' }};

            if (hasRecords) {
                $('#usersTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    responsive: true,
                    lengthMenu: [5, 10, 25, 50],
                    columnDefs: [{
                        orderable: false,
                        targets: [7]
                    }],
                    language: {
                        search: "",
                        searchPlaceholder: "Search staff..."
                    }
                });
            }

            $(document).on('click', '.delete-btn', function() {
                const form = $(this).closest('form');

                Swal.fire({
                    title: 'Delete User?',
                    text: 'This staff account will be removed.',
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
