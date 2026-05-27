@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Plot Rate Master</h3>
                        <p class="text-muted mb-0 small">Manage and configure plot rates</p>
                    </div>
                    @can('plc-development-rate-create')
                        <a href="{{ route('plot-rates.create') }}" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Add Plot Rate
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Listing --}}
        <div class="card shadow-sm border-0">
            @include('partials.master-header')
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="plotRateTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Project</th>
                                <th>Block</th>
                                <th>Plot Rate</th>
                                @if(auth()->user()->can('plc-development-rate-edit') || auth()->user()->can('plc-development-rate-delete'))
                                    <th width="150">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plotRates as $key => $plotRate)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $plotRate->project?->name }}</td>
                                    <td>{{ $plotRate->block?->block }}</td>
                                    <td>{{ number_format($plotRate->plot_rate, 2, '.', '') }}</td>
                                    @if(auth()->user()->can('plc-development-rate-edit') || auth()->user()->can('plc-development-rate-delete'))
                                        <td>
                                            @can('plc-development-rate-edit')
                                                <a href="{{ route('plot-rates.edit', $plotRate->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                            @endcan
                                            @can('plc-development-rate-delete')
                                                <form action="{{ route('plot-rates.destroy', $plotRate->id) }}" method="POST" class="d-inline delete-form">
                                                    @csrf @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn"><i class="bi bi-trash"></i></button>
                                                </form>
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No plot rates found</td>
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

            // DataTable
            if ($('#plotRateTable tbody tr td').attr('colspan') == undefined) {

                $('#plotRateTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                    ordering: true,
                    searching: true
                });

            }


            // Delete Confirm
            $('.delete-btn').click(function() {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This record will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
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
