@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 associate-advance-page">
        <div class="associate-advance-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="associate-advance-hero-icon">
                        <i class="bi bi-wallet2"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Associate Payment</span>
                        <h3 class="fw-bold mb-1 text-dark">Associate Advances</h3>
                        <p class="text-muted mb-0 small">Manage and track advance payments issued to associates.</p>
                    </div>
                </div>

                @can('associate-advance-modify')
                    <a href="{{ route('associate-advances.create') }}" class="btn btn-success associate-advance-primary">
                        <i class="bi bi-plus-circle me-1"></i> Add Advance
                    </a>
                @endcan
            </div>
        </div>

        

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="associate-advance-table-card">
            <div class="associate-advance-table-head">
                <div class="d-flex align-items-center gap-3">
                    <span class="associate-advance-table-icon">
                        <i class="bi bi-cash-stack"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-1">Advance Records</h5>
                        <small class="text-muted">All associate advance entries are listed below.</small>
                    </div>
                </div>

                <span class="associate-advance-count">{{ $advances->count() }} Records</span>
            </div>

            <div class="associate-advance-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 associate-advance-table" id="advanceTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Associate</th>
                                <th>Amount</th>
                                <th>Advance Date</th>
                                <th>Remarks</th>
                                @can('associate-advance-modify')
                                    <th width="150">Action</th>
                                @endcan
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($advances as $key => $advance)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <strong>{{ $advance->associate?->associate_id ?? '-' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $advance->associate?->associate_name ?? '-' }}</small>
                                    </td>
                                    <td class="fw-bold text-success">
                                        &#8377;{{ number_format((float) $advance->advance_amount, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $advance->advance_date?->format('d-M-Y') ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="associate-advance-remark">{{ $advance->remarks ?: 'N/A' }}</span>
                                    </td>
                                    @can('associate-advance-modify')
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('associate-advances.edit', $advance->id) }}"
                                                    class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-pencil-square me-1"></i>  
                                                </a>

                                                <form method="POST" action="{{ route('associate-advances.destroy', $advance->id) }}"
                                                    class="d-inline associate-advance-delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->can('associate-advance-modify') ? 6 : 5 }}"
                                        class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No advance records found.
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
            if ($('#advanceTable tbody tr td').attr('colspan') === undefined) {
                $('#advanceTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                });
            }

            $(document).on('click', '.delete-btn', function() {
                const button = $(this);
                const form = button.closest('form');

                Swal.fire({
                    title: 'Delete advance record?',
                    text: 'This advance record will be permanently deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'
                        );
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
