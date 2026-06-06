@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
   
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">
                            <i class="bi bi-people-fill me-2 text-success"></i> User Management
                        </h3>
                        <p class="text-muted mb-0 small">Manage, view, and update system user accounts.</p>
                    </div>
                    @can('users-modify')
                    <a href="{{ route('users.create') }}" class="btn btn-success px-4 shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add New User
                    </a>
                @endcan
                </div>
            </div>
        </div>
        {{-- TABLE SECTION --}}
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="usersTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Profile</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Password</th>
                                <th>Created By</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $key => $user)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <img src="{{ $user->profile_image ? asset('storage/' . $user->profile_image) : asset('assets/images/avatar.png') }}"
                                            class="rounded-circle border"
                                            style="width: 40px; height: 40px; object-fit: cover;">
                                    </td>
                                    <td class="fw-bold text-dark">{{ ucfirst($user->name) }}</td>
                                    <td class="text-muted">{{ $user->email }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-3">
                                            {{ ucfirst($user->roles->first()?->name) ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-muted fw-bold">{{ $user->plain_text }}</td>
                                    <td>
                                        <span class="text-muted">{{ $user->creator?->name ?? 'System' }}</span>
                                    </td>
                                    <td>
                                        @if ($user->status == 'active')
                                            <span class="badge bg-success-subtle text-success px-3">Active</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger px-3">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @can('users-modify')
                                            <div class="btn-group">
                                                <a href="{{ route('users.edit', $user->id) }}"
                                                    class="btn btn-sm btn-light border-0 text-primary">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                    class="delete-form">
                                                    @csrf @method('DELETE')
                                                    <button type="button"
                                                        class="btn btn-sm btn-light border-0 text-danger delete-btn">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">No users found.</td>
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

        if ($('#usersTable tbody tr td[colspan]').length === 0) {
            $('#usersTable').DataTable({
                pageLength: 10,
                ordering: true,
                columnDefs: [
                    {
                        orderable: false,
                        targets: [1, 8]
                    }
                ],
                language: {
                    searchPlaceholder: "Search users...",
                    emptyTable: "No users found"
                },
                retrieve: true
            });
        }

        $(document).on('click', '.delete-btn', function() {
            let form = $(this).closest('form');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

    });
</script>
@endpush
