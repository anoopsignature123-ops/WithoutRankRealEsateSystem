@extends('layouts.app')
@section('content')
    <div class="container-fluid mt-4">
        <div class="card border-0 shadow-sm mb-4 rounded-4">
            <div class="card-body p-4">
                <div class="row align-items-center g-3">
                    <div class="col-md-3">
                        <h4 class="fw-bold mb-1">Source Management</h4>
                        <small class="text-muted">Manage all sources</small>
                    </div>
                </div>
            </div>
        </div>
        @can('lead-source-create')
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="POST" id="sourceForm" action="{{ route('source.store') }}">
                    @csrf
                    <div id="methodField"></div>
                    <div class="row align-items-end">
                        <div class="col-md-6 col-sm-12 mb-3 mb-md-0">
                            <label class="form-label text-muted small uppercase fw-bold">Source Name</label>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="Enter source name" required autocomplete="off">
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <button type="submit" id="submitBtn" class="btn btn-primary px-4">Save Source</button>
                            <button type="button" id="cancelBtn" class="btn btn-secondary px-4 d-none">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endcan
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="sourceTable">
                        <thead class="table-light">
                            <tr>
                                <th width="80">Sr.No</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th width="150" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sources as $key => $source)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td><strong>{{ $source->name }}</strong></td>
                                    <td class="text-muted">
                                        {{ $source->created_at ? $source->created_at->format('d-M-Y') : 'N/A' }}
                                    </td>
                                    <td class="text-center">
                                        @can('lead-source-edit')
                                        <button type="button" class="btn btn-sm btn-outline-primary editBtn"
                                            data-id="{{ $source->id }}" data-name="{{ $source->name }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @endcan
                                        
                                        @can('lead-source-delete')
                                        <form action="{{ route('source.destroy', $source->id) }}" method="POST"
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
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
            // Initialize Table
            $('#sourceTable').DataTable({
                pageLength: 10,
                ordering: true,
                responsive: true,
                lengthMenu: [5, 10, 25, 50],
                language: { emptyTable: "No sources found" }
            });

            // Edit Button Click
            $('.editBtn').click(function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                $('#name').val(name).focus();
                $('#sourceForm').attr('action', '/source/update/' + id);
                $('#methodField').html('@method('PUT')');
                $('#submitBtn').text('Update Source').removeClass('btn-primary').addClass('btn-success');
                $('#cancelBtn').removeClass('d-none');
                $('html, body').animate({ scrollTop: 0 }, 'fast');
            });

            // Cancel Button Click
            $('#cancelBtn').click(function() {
                resetForm();
            });

            function resetForm() {
                $('#name').val('');
                $('#sourceForm').attr('action', "{{ route('source.store') }}");
                $('#methodField').html('');
                $('#submitBtn').text('Save Source').removeClass('btn-success').addClass('btn-primary');
                $('#cancelBtn').addClass('d-none');
            }

            // Delete Confirmation
            $(document).on('click', '.delete-btn', function() {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Source will be deleted permanently!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush