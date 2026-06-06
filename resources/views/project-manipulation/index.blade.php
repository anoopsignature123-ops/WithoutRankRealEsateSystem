@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center g-3">
                    <div class="col-lg-7">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 text-success rounded-4 d-flex align-items-center justify-content-center me-3"
                                style="width:58px;height:58px;">
                                <i class="bi bi-sliders fs-2"></i>
                            </div>

                            <div>
                                <h4 class="fw-bold mb-1">Project Manipulation</h4>
                                <p class="text-muted mb-0">
                                    Manage and update project plot statuses.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 text-lg-end">
                        <div class="d-flex justify-content-lg-end flex-wrap gap-2">
                            <a href="{{ route('project.manipulation.export', array_merge(request()->all(), ['type' => 'excel'])) }}"
                                class="btn btn-success px-3">
                                <i class="bi bi-file-earmark-excel-fill me-1"></i>
                                Export Excel
                            </a>

                            <a href="{{ route('project.manipulation.export', array_merge(request()->all(), ['type' => 'pdf'])) }}"
                                class="btn btn-danger px-3">
                                <i class="bi bi-file-earmark-pdf-fill me-1"></i>
                                Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-funnel me-2 text-success"></i>
                    Search Plot Records
                </h5>
                <p class="text-muted small mb-0">
                    Filter plots by project, plot number and status.
                </p>
            </div>

            <div class="card-body p-4">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row g-3">

                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-semibold">Project</label>
                            <select name="project_id" id="project_id" class="form-select">
                                <option value="">Select Project</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}"
                                        {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-semibold">Plot</label>
                            <select name="plot_number" id="plot_number" class="form-select">
                                <option value="">Select Plot</option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Select Status</option>
                                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>
                                    Available
                                </option>
                                <option value="booked" {{ request('status') == 'booked' ? 'selected' : '' }}>
                                    Booked
                                </option>
                                <option value="hold" {{ request('status') == 'hold' ? 'selected' : '' }}>
                                    Hold
                                </option>
                                <option value="registry" {{ request('status') == 'registry' ? 'selected' : '' }}>
                                    Registry
                                </option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label class="form-label d-none d-md-block">&nbsp;</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-search me-1"></i>
                                        Search
                                    </button>
                                </div>

                                <div class="col-6">
                                    <a href="{{ url()->current() }}" class="btn btn-light border w-100">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- Listing --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 p-4">
                <div class="row align-items-center g-3">
                    <div class="col-lg-8">
                        <h5 class="fw-bold mb-1">Project Plot Status List</h5>
                        <p class="text-muted small mb-0">
                            Update plot status from available, hold or registry. Booked plots are locked.
                        </p>
                    </div>

                    <div class="col-lg-4 text-lg-end">
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                            Total Records: {{ $plots->count() }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Project</th>
                                <th>Plot No.</th>
                                <th>Plot Size</th>
                                <th>Update Date</th>
                                <th>Status</th>
                                <th width="210">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($plots as $key => $plot)
                                @php
                                    $statusBadge = match ($plot->status) {
                                        'available' => 'bg-success-subtle text-success border border-success-subtle',
                                        'booked' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        'hold' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                        'registry' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        default => 'bg-light text-dark border',
                                    };
                                @endphp

                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $plot->project?->name ?? '-' }}
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                            {{ $plot->plot_number }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $plot->plot_area }} Sqft
                                    </td>

                                    <td>
                                        <span class="text-muted small">
                                            {{ $plot->updated_at ? $plot->updated_at->format('d-m-Y h:i A') : '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge rounded-pill px-3 py-2 {{ $statusBadge }}">
                                            {{ ucfirst($plot->status) }}
                                        </span>
                                    </td>

                                    <td>
                                        @if ($plot->status == 'booked')
                                            <select class="form-select form-select-sm" disabled>
                                                <option selected>Booked</option>
                                            </select>

                                            <small class="text-muted d-block mt-1">
                                                Booked plot cannot be updated.
                                            </small>
                                        @else
                                            <form method="POST"
                                                action="{{ route('project.manipulation.update.status') }}"
                                                class="status-update-form">
                                                @csrf

                                                <input type="hidden" name="plot_id" value="{{ $plot->id }}">

                                                <select name="status"
                                                    class="form-select form-select-sm status-change-select"
                                                    data-old-status="{{ $plot->status }}"
                                                    data-plot-number="{{ $plot->plot_number }}">
                                                    <option value="available" {{ $plot->status == 'available' ? 'selected' : '' }}>
                                                        Available
                                                    </option>
                                                    <option value="booked" {{ $plot->status == 'booked' ? 'selected' : '' }}>
                                                        Booked
                                                    </option>
                                                    <option value="hold" {{ $plot->status == 'hold' ? 'selected' : '' }}>
                                                        Hold
                                                    </option>
                                                    <option value="registry" {{ $plot->status == 'registry' ? 'selected' : '' }}>
                                                        Registry
                                                    </option>
                                                </select>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No plot records found.
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

            let initialProjectId = $('#project_id').val();
            let selectedPlot = @json(request('plot_number'));

            if (initialProjectId) {
                loadPlots(initialProjectId, selectedPlot);
            }

            $('#project_id').change(function() {
                loadPlots($(this).val(), null);
            });

            function loadPlots(projectId, selectedPlot = null) {
                if (!projectId) {
                    $('#plot_number').html('<option value="">Select Plot</option>');
                    return;
                }

                $.get('/get-project-plots-data/' + projectId, function(response) {
                    $('#plot_number').html('<option value="">Select Plot</option>');

                    $.each(response, function(index, plot) {
                        let selected = selectedPlot == plot.plot_number ? 'selected' : '';

                        $('#plot_number').append(`
                            <option value="${plot.plot_number}" ${selected}>
                                ${plot.plot_number}
                            </option>
                        `);
                    });
                });
            }

            $(document).on('change', '.status-change-select', function() {
                let select = $(this);
                let form = select.closest('form');
                let oldStatus = select.data('old-status');
                let newStatus = select.val();
                let plotNumber = select.data('plot-number');

                if (oldStatus === newStatus) {
                    return;
                }

                Swal.fire({
                    title: 'Change Plot Status?',
                    text: 'Plot ' + plotNumber + ' status will be changed from ' + oldStatus + ' to ' + newStatus + '.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Change',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    } else {
                        select.val(oldStatus);
                    }
                });
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: @json(session('success')),
                    confirmButtonColor: '#198754'
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: @json(session('error')),
                    confirmButtonColor: '#dc3545'
                });
            @endif

        });
    </script>
@endpush