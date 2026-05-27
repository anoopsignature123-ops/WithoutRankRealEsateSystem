@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Project Management</h3>
                        <p class="text-muted mb-0 small">Manage all projects</p>
                    </div>

                    @can('project-create')
                        <a href="{{ route('projects.create') }}" 
                           class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Add Project
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="projectTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Site Name</th>
                                <th>Site Location</th>
                                <th>Date</th>
                                @if (auth()->user()->can('project-edit') || auth()->user()->can('project-delete'))
                                    <th width="150">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $key => $project)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td class="fw-semibold">{{ $project->name }}</td>
                                    <td>{{ $project->location }}</td>
                                    <td>{{ $project->date }}</td>

                                    @if (auth()->user()->can('project-edit') || auth()->user()->can('project-delete'))
                                        <td>
                                            @can('project-edit')
                                                <a href="{{ route('projects.edit', $project->id) }}" 
                                                   class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan

                                            @can('project-delete')
                                                <form method="POST" action="{{ route('projects.destroy', $project->id) }}" 
                                                      class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No project found
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
            if ($('#projectTable tbody tr td').attr('colspan') == undefined) {
                $('#projectTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    responsive: true
                });
            }

            $('.delete-btn').click(function() {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This project will be deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush