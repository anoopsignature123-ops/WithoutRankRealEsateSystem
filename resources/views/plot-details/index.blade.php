@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Plot Details</h3>
                        <p class="text-muted mb-0 small">Manage plot details and inventory</p>
                    </div>

                    @can('plot-details-modify')
                        <a href="{{ route('plot-details.create') }}"
                            class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Add Plot
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Filter Card --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('plot-details.index') }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Project</label>
                            <select name="project_id" id="filter_project_id" class="form-control form-select select2">
                                <option value="">All Projects</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}"
                                        {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Plot Number</label>
                            <select name="plot_number" id="filter_plot_number" class="form-control form-select select2">
                                <option value="">Select Plot No</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Filter</button>
                            <a href="{{ route('plot-details.index') }}" class="btn btn-secondary"><i
                                    class="bi bi-arrow-clockwise me-1"></i>Reset</a>
                            <a href="{{ route('plot-details.export', request()->all()) }}" class="btn btn-success"><i
                                    class="bi bi-download me-1"></i>Export</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="plotDetailsTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Project</th>
                                <th>Location</th>
                                <th>Block</th>
                                <th>Plot Type</th>
                                <th>Plot No.</th>
                                <th>Area</th>
                                <th>Rate</th>
                                <th>Status</th>
                                @if (auth()->user()->can('plot-details-modify'))
                                    <th>Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plotDetails as $key => $plot)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $plot->project?->name }}</td>
                                    <td>{{ $plot->location }}</td>
                                    <td>{{ $plot->block?->block }}</td>
                                    <td>{{ $plot->plotType?->plot_type_name }}</td>
                                    <td>{{ $plot->plot_number }}</td>
                                    <td>{{ number_format($plot->plot_area, 2) }}</td>
                                    <td>{{ number_format($plot->plot_rate, 2) }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $plot->status == 'available' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($plot->status) }}
                                        </span>
                                    </td>
                                    @if (auth()->user()->can('plot-details-edit'))
                                        <td>
                                            <a href="{{ route('plot-details.show', $plot->id) }}"
                                                class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>

                                            <a href="{{ route('plot-details.edit', $plot->id) }}"
                                                class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>


                                            <form method="POST" action="{{ route('plot-details.destroy', $plot->id) }}"
                                                class="d-inline delete-form">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-btn"><i
                                                        class="bi bi-trash"></i></button>
                                            </form>

                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No data found</td>
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
            if ($.fn.select2) {
                $('.select2').select2({});
            }
            if ($.fn.DataTable) {
                $('#plotDetailsTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    responsive: true
                });
            }
            $('.delete-btn').click(function() {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Delete record?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });

            });

            function loadPlotNumbers(projectId, selectedPlot = '') {
                if (!projectId) {
                    $('#filter_plot_number').html('<option value="">Select Plot No</option>');
                    return;
                }
                $.ajax({
                    url: '/get-project-plots/' + projectId,
                    type: 'GET',
                    success: function(response) {
                        $('#filter_plot_number').html(
                            '<option value="">Select Plot No</option>'
                        );
                        $.each(response, function(index, plot) {
                            let selected = selectedPlot == plot.plot_number ? 'selected' : '';
                            $('#filter_plot_number').append(`
                        <option value="${plot.plot_number}" ${selected}>${plot.plot_number}</option>`);
                        });
                        $('#filter_plot_number').trigger('change');
                    }
                });
            }
            $('#filter_project_id').change(function() {
                let projectId = $(this).val();
                loadPlotNumbers(projectId);
            });
            let selectedProject = $('#filter_project_id').val();
            let selectedPlot = "{{ request('plot_number') }}";
            if (selectedProject) {
                loadPlotNumbers(selectedProject, selectedPlot);
            }
        });
    </script>
@endpush
