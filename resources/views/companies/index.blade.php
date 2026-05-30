@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Company Profile</h3>
                <small class="text-muted">Manage company details and activation state</small>
            </div>
            @can('company-profile-modify')
                <a href="{{ route('company.create') }}" class="btn btn-success shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Add Company
                </a>
            @endcan
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-hover" id="companyTable">
                        <thead class="table-light">
                            <tr>
                                <th width="60">#</th>
                                <th width="80">Logo</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th width="200">Status</th>
                                @if (auth()->user()->can('company-profile-modify'))
                                    <th width="120">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($companies as $key => $company)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <img src="{{ getFileUrl($company->logo) }}" width="45" height="45"
                                            class="rounded-circle border object-fit-cover shadow-sm">
                                    </td>
                                    <td class="fw-semibold">{{ $company->name }}</td>
                                    <td>{{ $company->email }}</td>
                                    <td>{{ $company->contact_no }}</td>
                                    <td>
                                        {{-- Status Toggle Switch Wrapper --}}
                                        <div class="form-check form-switch d-flex align-items-center custom-switch-wrapper">
                                            <input class="form-check-input status-toggle-switch" type="checkbox"
                                                data-id="{{ $company->id }}" id="statusSwitch{{ $company->id }}"
                                                {{ $company->status === 'active' || $company->status == 1 ? 'checked disabled' : '' }}
                                                style="cursor: pointer; width: 2.6em; height: 1.3em;">

                                            <label
                                                class="form-check-label ms-2 fw-bold text-capitalize status-label {{ $company->status === 'active' || $company->status == 1 ? 'text-success' : 'text-secondary' }}"
                                                for="statusSwitch{{ $company->id }}">
                                                {{ $company->status === 'active' || $company->status == 1 ? 'Active' : 'Inactive' }}
                                            </label>
                                        </div>
                                    </td>
                                    @if (auth()->user()->can('company-profile-modify'))
                                        <td>
                                           
                                                <a href="{{ route('company.edit', $company->id) }}"
                                                    class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                               </a>                                    
                                                <form method="POST" action="{{ route('company.destroy', $company->id) }}"
                                                    class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                                                        title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                             
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No company found</td>
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
            // DataTables safely initialized with DrawCallback fallback
            if ($('#companyTable tbody tr td').attr('colspan') == undefined) {
                $('#companyTable').DataTable({
                    "drawCallback": function(settings) {
                        // Page switch hone par bhi styles update rahengi
                        $('.status-toggle-switch:checked').addClass('bg-success border-success').prop(
                            'disabled', true);
                    }
                });
            }

            // Status Switch AJAX Handler
            $(document).on('change', '.status-toggle-switch', function() {
                let companyId = $(this).data('id');
                let currentSwitch = $(this);

                // Agar pehle se active wale par click ho toh change na ho
                if (!currentSwitch.is(':checked')) {
                    currentSwitch.prop('checked', true);
                    return;
                }

                Swal.fire({
                    title: 'Activate this Company?',
                    text: "Activating this will deactivate all other companies automatically.",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Activate'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('company') }}/" + companyId + "/toggle-status",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    // 1. Sabhi switches ko uncheck aur normal state me layen
                                    $('.status-toggle-switch').prop('checked', false)
                                        .prop('disabled', false)
                                        .removeClass('bg-success border-success');

                                    // 2. Sabhi labels ko 'Inactive' gray text set karein
                                    $('.status-label').text('Inactive')
                                        .removeClass('text-success')
                                        .addClass('text-secondary');

                                    // 3. Sirf clicked switch ko pure 'Active' Green layout pr shift karein
                                    currentSwitch.prop('checked', true)
                                        .prop('disabled', true)
                                        .addClass('bg-success border-success');

                                    currentSwitch.closest('.custom-switch-wrapper')
                                        .find('.status-label')
                                        .text('Active')
                                        .removeClass('text-secondary')
                                        .addClass('text-success');

                                    Swal.fire('Updated!', response.message, 'success');
                                }
                            },
                            error: function() {
                                currentSwitch.prop('checked', false).removeClass(
                                    'bg-success border-success');
                                Swal.fire('Error', 'Something went wrong!', 'error');
                            }
                        });
                    } else {
                        currentSwitch.prop('checked', false).removeClass(
                            'bg-success border-success');
                    }
                });
            });

            // Delete Form Handler
            $(document).on('click', '.delete-btn', function() {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This company will be permanently deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>

    {{-- CSS Fallback to enforce proper Bootstrap custom color design --}}
    <style>
        .form-check-input.status-toggle-switch {
            border: 2px solid #ced4da !important;
        }

        .form-check-input.status-toggle-switch: those styles .form-check-input.status-toggle-switch:checked {
            background-color: #198754 !important;
            border-color: #198754 !important;
            opacity: 1 !important;
        }

        .form-check-input.status-toggle-switch:disabled {
            opacity: 1 !important;
            /* Opacity 1 rakhne se full green dikhega */
            cursor: not-allowed;
        }
    </style>
@endpush
