@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Development Master</h3>
                        <p class="text-muted mb-0 small">Manage development amounts</p>
                    </div>
                    @can('plc-development-rate-modify')
                        <a href="{{ route('developments.create') }}" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Add Development
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
                    <table class="table table-hover align-middle" id="developmentTable">
                        <thead class="table-light">
                            <tr>
                                <th width="80">#</th>
                                <th>Amount</th>
                                @if(auth()->user()->can('plc-development-rate-modify'))
                                    <th width="150">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($developments as $key => $development)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>₹ {{ number_format($development->amount, 2, '.', '') }}</td>
                                    @if(auth()->user()->can('plc-development-rate-modify'))
                                        <td>
                                            
                                                <a href="{{ route('developments.edit', $development->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                            
                                            
                                                <form action="{{ route('developments.destroy', $development->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn"><i class="bi bi-trash"></i></button>
                                                </form>
                                             
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No development records found</td>
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
            if ($('#developmentTable tbody tr td').attr('colspan') == undefined) {

                $('#developmentTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                    ordering: true,
                    searching: true
                });

            }


            // Delete
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
