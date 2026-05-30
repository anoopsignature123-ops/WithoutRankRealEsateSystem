@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Designation / Rank Master</h3>
                        <p class="text-muted mb-0 small">Manage and configure designations and ranks</p>
                    </div>
                    @can('rank-designation-modify')
                        <a href="{{ route('designations.create') }}" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Add Designation
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Listing --}}
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="designationTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Designation Name</th>
                                <th>Rank Number</th>
                                <th>Commission (%)</th>
                                @if(auth()->user()->can('rank-designation-modify'))
                                    <th width="150">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($designationRanks as $key => $designation)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $designation->designation }}</td>
                                    <td>{{ $designation->rank_number }}</td>
                                    <td>{{ number_format($designation->commission, 2) }} %</td>
                                    @if(auth()->user()->can('rank-designation-modify'))
                                        <td>
                                             
                                                <a href="{{ route('designations.edit', $designation->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                            
                                             
                                                <form action="{{ route('designations.destroy', $designation->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn"><i class="bi bi-trash"></i></button>
                                                </form>
                                            
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No designation records found</td>
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
            if ($('#designationTable tbody tr td').attr('colspan') == undefined) {
                $('#designationTable').DataTable({
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
                    text: 'This record will be deleted!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
