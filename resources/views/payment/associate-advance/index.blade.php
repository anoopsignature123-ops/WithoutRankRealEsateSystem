@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        {{-- Header Card --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Associate Advances</h3>
                        <p class="text-muted mb-0 small">Manage and track associate advance payments</p>
                    </div>
                    @can('associate-advance-modify')
                        <a href="{{ route('associate-advances.create') }}"
                            class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Add Advance
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Listing --}}
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="advanceTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Associate ID</th>
                                <th>Associate Name</th>
                                <th>Advance Amount</th>
                                <th>Advance Date</th>
                                <th>Remarks</th>
                                @if (auth()->user()->can('associate-advance-modify'))
                                    <th width="120">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($advances as $key => $advance)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td><span
                                            class="badge bg-light text-dark border">{{ $advance->associate?->associate_id ?? '-' }}</span>
                                    </td>
                                    <td class="fw-medium">{{ $advance->associate?->associate_name ?? '-' }}</td>
                                    <td class="fw-bold text-success">₹{{ number_format($advance->advance_amount, 2) }}</td>
                                    <td class="text-muted">{{ $advance->advance_date?->format('d-m-Y') }}</td>
                                    <td>{{ $advance->remarks ?? '-' }}</td>
                                    @if (auth()->user()->can('associate-advance-modify'))
                                        <td>

                                            <a href="{{ route('associate-advances.edit', $advance->id) }}"
                                                class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>


                                            <form method="POST"
                                                action="{{ route('associate-advances.destroy', $advance->id) }}"
                                                class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-btn"><i
                                                        class="bi bi-trash"></i></button>
                                            </form>

                                        </td>
                                    @endif
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
            // DataTable initialization
            if ($('#advanceTable tbody tr td').attr('colspan') == undefined) {
                $('#advanceTable').DataTable({
                    pageLength: 10,
                    responsive: true
                });
            }

            // Delete confirmation
            $(document).on('click', '.delete-btn', function() {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This advance record will be permanently deleted!",
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
