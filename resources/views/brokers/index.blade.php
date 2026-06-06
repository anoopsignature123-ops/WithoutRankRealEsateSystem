@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">
                            <i class="bi bi-people-fill me-2 text-success"></i> Broker Management
                        </h3>
                        <p class="text-muted mb-0 small">Manage, view, and update broker accounts.</p>
                    </div>
                    @can('brokers-modify')
                        <a href="{{ route('brokers.create') }}" class="btn btn-success px-4 shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Add New Broker
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="brokersTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Broker Name</th>
                                <th>Mobile</th>
                                <th>PAN</th>
                                <th>Aadhaar</th>
                                <th>Bank Name</th>
                                <th>Account No.</th>
                                <th>IFSC</th>
                                <th>Holder</th>
                                <th>Created At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($brokers as $key => $broker)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td class="fw-semibold">{{ $broker->name }}</td>
                                    <td>{{ $broker->mobile_number ?? 'N/A' }}</td>
                                    <td>{{ $broker->pancard_number ?? 'N/A' }}</td>
                                    <td class="text-muted">{{ $broker->aadhar_number }}</td>
                                    <td>{{ $broker->bankDetail->bank_name ?? 'N/A' }}</td>
                                    <td>{{ $broker->bankDetail->account_number ?? 'N/A' }}</td>
                                    <td class="text-uppercase">{{ $broker->bankDetail->ifsc_code ?? 'N/A' }}</td>
                                    <td>{{ $broker->bankDetail->account_holder_name ?? 'N/A' }}</td>
                                    <td>{{ $broker->created_at?->format('d M Y') ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('brokers.show', $broker->id) }}"
                                                class="btn btn-sm btn-light border text-info"><i class="bi bi-eye"></i></a>
                                            @can('brokers-modify')
                                                <a href="{{ route('brokers.edit', $broker->id) }}"
                                                    class="btn btn-sm btn-light border text-primary"><i
                                                        class="bi bi-pencil-square"></i></a>
                                                <form action="{{ route('brokers.destroy', $broker->id) }}" method="POST"
                                                    class="delete-form d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="button"
                                                        class="btn btn-sm btn-light border text-danger delete-btn"><i
                                                            class="bi bi-trash"></i></button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if ($brokers->isEmpty())
                            <tfoot>
                                <tr>
                                    <td colspan="11" class="text-center py-2  accordion">No brokers found.</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        $(document).ready(function() {
            if ($('#brokersTable tbody tr').length > 0) {
                $('#brokersTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    columnDefs: [{
                        orderable: false,
                        targets: [10] 
                    }],
                    language: {
                        searchPlaceholder: "Search brokers..."
                    }
                });
            }

            $(document).on('click', '.delete-btn', function() {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this action!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete It!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
