@extends('layouts.app')
@section('content')
    <div class="container-fluid mt-4">

        {{-- Header Section --}}
        <div class="card border-0 shadow-sm mb-4 rounded-4">
            <div class="card-body p-4">
                <div class="row align-items-center g-3">
                    <div class="col-md-6">
                        <h4 class="fw-bold mb-1" id="formTitle">Enquiry Management</h4>
                        <small class="text-muted">Manage and track all customer enquiries</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Section (Permission Check) --}}
        @can('new-enquiry-create')
        <div class="card shadow-sm border-0 mb-4 rounded-4">
            <div class="card-body p-4">
                @include('enquiry.form')
            </div>
        </div>
        @endcan

        {{-- Table Section --}}
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="enquiryTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Mobile</th>
                                <th>Associate</th>
                                <th>Source</th>
                                <th>Enquiry Type</th>
                                <th>Budget</th>
                                <th>Followup Date</th>
                                <th>Created Date</th>
                                <th width="120" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($enquiries as $key => $enquiry)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td><strong>{{ $enquiry->customer_name }}</strong></td>
                                    <td>{{ $enquiry->mobile_number }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $enquiry->associate?->associate_name ?? 'N/A' }}</span></td>
                                    <td><span class="badge bg-light text-dark border">{{ $enquiry->source?->name ?? 'N/A' }}</span></td>
                                    <td><span class="badge bg-info text-dark border fw-bold">{{ $enquiry->enquiryType?->name ?? 'N/A' }}</span></td>
                                    <td>{{ $enquiry->budget ?? 'N/A' }}</td>
                                    <td>{{ $enquiry->followup_date ? \Carbon\Carbon::parse($enquiry->followup_date)->format('d-M-Y') : 'N/A' }}</td>
                                    <td>{{ $enquiry->created_at ? $enquiry->created_at->format('d-M-Y') : 'N/A' }}</td>
                                    <td class="text-center">
                                        @can('new-enquiry-edit')
                                        <button type="button" class="btn btn-sm btn-outline-primary editBtn rounded-pill px-3" data-id="{{ $enquiry->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @endcan
                                        
                                        @can('new-enquiry-delete')
                                        <form action="{{ route('enquiry.destroy', $enquiry->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn rounded-pill px-3">
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
            $('#enquiryTable').DataTable({
                pageLength: 10,
                ordering: true,
                responsive: true
            });

            $('.editBtn').click(function() {
                let id = $(this).data('id');
                $.get('/enquiry/edit/' + id, function(data) {
                    $('#formTitle').text('Edit Enquiry');
                    $('#customer_name').val(data.customer_name);
                    $('#mobile_number').val(data.mobile_number);
                    $('#email').val(data.email);
                    $('#dob').val(data.dob ? data.dob.substring(0, 10) : '');
                    $('#associate_id').val(data.associate_id);
                    $('#source_id').val(data.source_id);
                    $('#enquiry_types_id').val(data.enquiry_types_id);
                    $('#state').val(data.state);
                    $('#city').val(data.city);
                    $('#plot_size').val(data.plot_size);
                    $('#budget').val(data.budget);
                    $('#location').val(data.location);
                    $('#followup_date').val(data.followup_date ? data.followup_date.substring(0, 10) : '');
                    
                    $('#enquiryForm').attr('action', '/enquiry/update/' + id);
                    $('#methodField').html('@method('PUT')');
                    $('#submitBtn').text('Update Enquiry').removeClass('btn-primary').addClass('btn-success');
                    $('#cancelBtn').removeClass('d-none');
                    $('html, body').animate({ scrollTop: 0 }, 'fast');
                });
            });

            $('#cancelBtn').click(function() {
                resetForm();
            });

            function resetForm() {
                $('#formTitle').text('Add New Enquiry');
                $('#enquiryForm')[0].reset();
                $('#enquiryForm').attr('action', "{{ route('enquiry.store') }}");
                $('#methodField').html('');
                $('#submitBtn').text('Save Enquiry').removeClass('btn-success').addClass('btn-primary');
                $('#cancelBtn').addClass('d-none');
            }

            $(document).on('click', '.delete-btn', function() {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Enquiry will be deleted permanently!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
@endpush