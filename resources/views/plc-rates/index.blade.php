@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">PLC Rate Master</h3>
                        <p class="text-muted mb-0 small">Manage and configure PLC rates</p>
                    </div>
                    @can('plc-development-rate-modify')
                        <a href="{{ route('plc-rates.create') }}" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Add PLC Rate
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
                    <table class="table table-hover align-middle" id="plcRateTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Plot Type</th>
                                <th>PLC Rate (%)</th>
                                @if (auth()->user()->can('plc-development-rate-modify'))
                                    <th width="120">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plcRates as $key => $plcRate)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ ucfirst($plcRate->plotType?->plot_type_name) }}</td>
                                    <td>{{ number_format($plcRate->rate, 2, '.', '') }}</td>
                                    @if (auth()->user()->can('plc-development-rate-modify'))
                                        <td>

                                            <a href="{{ route('plc-rates.edit', $plcRate->id) }}"
                                                class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>


                                            <form action="{{ route('plc-rates.destroy', $plcRate->id) }}" method="POST"
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
                                    <td colspan="4" class="text-center text-muted py-4">No data found</td>
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
            if ($('#plcRateTable tbody tr td[colspan]').length == 0) {
                $('#plcRateTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                    ordering: true,
                    searching: true
                });
            }
            $('.delete-btn').click(function() {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This record will be deleted.',
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
