@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Plot Type</h3>
                        <p class="text-muted mb-0 small">Manage all plot types</p>
                    </div>

                    @can('plot-types-modify')
                        <a href="{{ route('plot-types.create') }}"
                            class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Add Plot Type
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="plotTypeTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Plot Type Name</th>
                                <th>Date</th>
                                @if (auth()->user()->can('plot-types-modify'))
                                    <th width="150">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plotTypes as $key => $plotType)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $plotType->plot_type_name }}</td>
                                    <td>{{ $plotType->date ?? 'N/A' }}</td>

                                    @if (auth()->user()->can('plot-types-modify'))
                                        <td>

                                            <a href="{{ route('plot-types.edit', $plotType->id) }}"
                                                class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>



                                            <form method="POST" action="{{ route('plot-types.destroy', $plotType->id) }}"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                                                    title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>

                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No plot types found
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
            if ($('#plotTypeTable tbody tr').length > 0 && $('#plotTypeTable tbody tr td').attr('colspan') ==
                undefined) {
                $('#plotTypeTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    responsive: true,
                    lengthMenu: [5, 10, 25, 50],
                    language: {
                        search: "",
                        searchPlaceholder: "Search plot type..."
                    }
                });
            }

            $('.delete-btn').click(function() {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This plot type will be deleted!",
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
