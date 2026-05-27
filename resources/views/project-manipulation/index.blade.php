@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Project Manipulation</h3>
                        <p class="text-muted mb-0 small">Manage and update project plot statuses</p>
                    </div>

                    <div class="d-flex gap-2 mb-3 justify-content-end">
                        <a href="{{ route('project.manipulation.export', array_merge(request()->all(), ['type' => 'excel'])) }}" class="btn btn-success shadow-sm px-3 rounded-pill">
                            <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
                        </a>
                        <a href="{{ route('project.manipulation.export', array_merge(request()->all(), ['type' => 'pdf'])) }}" class="btn btn-danger shadow-sm px-3 rounded-pill">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card shadow-sm border-0 mb-4 rounded-4">
            <div class="card-body p-4">
                <form method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="fw-semibold mb-1">Project</label>
                            <select name="project_id" id="project_id" class="form-select">
                                <option value="">Select Project</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-semibold mb-1">Plot</label>
                            <select name="plot_number" id="plot_number" class="form-select">
                                <option value="">Select Plot</option>
                             </select>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-semibold mb-1">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Select Status</option>
                                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="booked" {{ request('status') == 'booked' ? 'selected' : '' }}>Booked</option>
                                <option value="hold" {{ request('status') == 'hold' ? 'selected' : '' }}>Hold</option>
                                <option value="registry" {{ request('status') == 'registry' ? 'selected' : '' }}>Registry</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-success px-4 fw-semibold">
                                <i class="bi bi-search me-1"></i> Search Plot
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        {{-- Listing --}}
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Project</th>
                                <th>Plot No.</th>
                                <th>Plot Size</th>
                                <th>Update Date</th>
                                <th>Status</th>
                                <th width="150">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($plots as $key => $plot)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $plot->project?->name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $plot->plot_number }}</span></td>
                                    <td>{{ $plot->plot_area }} Sqft</td>
                                    <td class="text-muted small">{{ $plot->updated_at?->format('d-m-Y h:i A') }}</td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'available' => 'text-success',
                                                'booked' => 'text-danger',
                                                'hold' => 'text-warning',
                                                'registry' => 'text-primary'
                                            ];
                                        @endphp
                                        <span class="fw-bold {{ $statusColors[$plot->status] ?? 'text-secondary' }}">
                                            {{ ucfirst($plot->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('project.manipulation.update.status') }}">
                                            @csrf
                                            <input type="hidden" name="plot_id" value="{{ $plot->id }}">
                                            <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                                <option value="available" {{ $plot->status == 'available' ? 'selected' : '' }}>Available</option>
                                                <option value="booked" {{ $plot->status == 'booked' ? 'selected' : '' }}>Booked</option>
                                                <option value="hold" {{ $plot->status == 'hold' ? 'selected' : '' }}>Hold</option>
                                                <option value="registry" {{ $plot->status == 'registry' ? 'selected' : '' }}>Registry</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
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
            if(initialProjectId) {
                loadPlots(initialProjectId);
            }

            $('#project_id').change(function() {
                loadPlots($(this).val());
            });

            function loadPlots(projectId) {
                $.get('/get-project-plots-data/' + projectId, function(response) {
                    $('#plot_number').html('<option value="">Select Plot</option>');
                    $.each(response, function(index, plot) {
                        $('#plot_number').append(`<option value="${plot.plot_number}">${plot.plot_number}</option>`);
                    });
                });
            }
        });
    </script>
@endpush