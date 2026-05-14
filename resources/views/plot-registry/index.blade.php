@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <div class="mb-4">
                    <h4 class="fw-bold mb-1">Plot Registry</h4>
                    <small class="text-muted">Select plot and create registry details</small>
                </div>
                <form method="POST" action="{{ route('admin.plot-registry.store') }}" id="registryForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">
                                Site Name <span class="text-danger">*</span>
                            </label>
                            <select id="project_id" name="project_id"
                                class="form-select @error('project_id') is-invalid @enderror">
                                <option value="">Select Site</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                            @error('project_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- Block --}}
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">
                                Block <span class="text-danger">*</span>
                            </label>
                            <select id="block_id" name="block_id"
                                class="form-select @error('block_id') is-invalid @enderror">
                                <option value="">Select Block</option>
                            </select>
                            @error('block_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- Plot --}}
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">
                                Plot No <span class="text-danger">*</span>
                            </label>
                            <select id="plot_id" name="plot_detail_id" class="form-select">
                                <option value="">Select Plot</option>
                            </select>
                            @error('plot_detail_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <input type="hidden" name="customer_booking_id" id="customer_booking_db_id">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold"> Booking ID</label>
                            <input type="text" id="booking_id" class="form-control bg-light" readonly
                                placeholder="Auto filled">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Customer ID</label>
                            <input type="text" id="customer_id" class="form-control bg-light" readonly
                                placeholder="Auto filled">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Customer Name</label>
                            <input type="text" id="customer_name" class="form-control bg-light" readonly
                                placeholder="Auto filled">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Gata Number</label>
                            <input type="text" name="gata_number"
                                class="form-control  @error('gata_number') is-invalid @enderror"
                                placeholder="Enter gata number" value="{{ old('gata_number') }}">
                            @error('gata_number')
                                <div class="invalid-feedback" d-block>{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- Seller --}}
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Seller Name</label>
                            <input type="text" name="seller_name"
                                class="form-control  @error('seller_name') is-invalid @enderror"
                                placeholder="Enter seller name" value="{{ old('seller_name') }}">
                            @error('seller_name')
                                <div class="invalid-feedback" d-block>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        {{-- Registry No --}}
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Registry No</label>
                            <input type="text" name="register_no"
                                class="form-control  @error('register_no') is-invalid @enderror"
                                placeholder="Enter registry no" value="{{ old('register_no') }}">
                            @error('register_no')
                                <div class="invalid-feedback" d-block>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        {{-- Date --}}
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Registry Date</label>
                            <input type="date" name="register_date"
                                class="form-control  @error('register_date') is-invalid @enderror"
                                value="{{ old('register_date') }}">
                            @error('register_date')
                                <div class="invalid-feedback" d-block>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- Payment Table --}}
                    <div id="paymentSection" class="mt-4 d-none">
                        <h5 class="fw-bold mb-3">Payment Details</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-success">
                                    <tr>
                                        <th>Paid Amount</th>
                                        <th>Pay Date</th>
                                        <th>Pay Mode</th>
                                        <th>Cheque No</th>
                                    </tr>
                                </thead>
                                <tbody id="paymentTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fa fa-save me-1"></i>Plot Registry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            clearAll();
            // Project → Blocks
            $('#project_id').change(function() {
                let projectId = $(this).val();
                resetBlocks();
                resetPlots();
                clearBooking();
                if (!projectId) return;
                $.get(
                    "{{ route('admin.plot-registry.blocks', ':id') }}"
                    .replace(':id', projectId),
                    function(res) {
                        $.each(res, function(index, block) {
                            $('#block_id').append(`
                        <option value="${block.id}">
                            ${block.block}
                        </option>
                    `);

                        });
                    }
                );
            });
            // Block → Plots
            $('#block_id').change(function() {
                let blockId = $(this).val();
                resetPlots();
                clearBooking();
                if (!blockId) return;
                $.get(
                    "{{ route('admin.plot-registry.plots', ':id') }}"
                    .replace(':id', blockId),
                    function(res) {
                        $.each(res, function(index, plot) {
                            $('#plot_id').append(`
                        <option value="${plot.id}">
                            ${plot.plot_number}
                        </option>
                    `);

                        });

                    }
                );

            });
            // Plot → Booking details
            $('#plot_id').change(function() {
                let plotId = $(this).val();
                clearBooking();
                if (!plotId) return;
                $.get(
                    "{{ route('admin.plot-registry.booking', ':id') }}"
                    .replace(':id', plotId),
                    function(res) {
                        if (!res.status) return;
                        $('#customer_booking_db_id').val(
                            res.booking_db_id
                        );
                        $('#booking_id').val(
                            res.booking_id
                        );
                        $('#customer_id').val(
                            res.customer_id
                        );

                        $('#customer_name').val(
                            res.customer_name
                        );
                        let html = `
                    <tr>
                        <td>${res.payment.amount ?? ''}</td>
                        <td>${res.payment.date ?? ''}</td>
                        <td>${res.payment.mode ?? ''}</td>
                        <td>${res.payment.cheque_no ?? ''}</td>
                    </tr>
                `;
                        $('#paymentTableBody').html(html);
                        $('#paymentSection')
                            .removeClass('d-none');

                    }
                );

            });

            function resetBlocks() {
                $('#block_id').html(`<option value="">Select Block</option>`);
            }

            function resetPlots() {
                $('#plot_id').html(`<option value="">Select Plot</option>`);
            }

            function clearBooking() {
                $('#customer_booking_db_id').val('');
                $('#booking_id').val('');
                $('#customer_id').val('');
                $('#customer_name').val('');
                $('#paymentTableBody').html('');
                $('#paymentSection').addClass('d-none');
            }

            function clearAll() {
                resetBlocks();
                resetPlots();
                clearBooking();
            }
        });
    </script>
@endpush
