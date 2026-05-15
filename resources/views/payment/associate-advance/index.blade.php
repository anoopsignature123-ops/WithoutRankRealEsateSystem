@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Associate Advances</h3>
                <small class="text-muted">Manage associate advance records</small>
            </div>
            <a href="{{ route('admin.associate-advances.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>Add Advance
            </a>
        </div>

        {{-- Listing --}}
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-hover" id="advanceTable">
                        <thead class="table-success">
                            <tr>
                                <th>#</th>
                                <th>Associate ID</th>
                                <th>Associate Name</th>
                                <th>Advance Amount</th>
                                <th>Advance Date</th>
                                <th>Remarks</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($advances as $key => $advance)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $advance->associate?->associate_id ?? '-' }}</td>
                                    <td>{{ $advance->associate?->associate_name ?? '-' }}</td>
                                    <td class="fw-semibold text-success">₹{{ number_format($advance->advance_amount, 2) }}</td>
                                    <td>{{ $advance->advance_date?->format('d-m-Y') }}</td>
                                    <td>{{ $advance->remarks ?? '-' }}</td>
                                    <td>
                                        {{-- Edit --}}
                                        <a href="{{ route('admin.associate-advances.edit', $advance->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        {{-- Delete --}}
                                        <form method="POST"
                                            action="{{ route('admin.associate-advances.destroy', $advance->id) }}"
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No advance records found</td>
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
            // Initialize DataTable if table has data
            if ($('#advanceTable tbody tr td').attr('colspan') == undefined) {
                $('#advanceTable').DataTable();
            }

            // Delete confirmation
            $('.delete-btn').click(function() {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This advance record will be deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush