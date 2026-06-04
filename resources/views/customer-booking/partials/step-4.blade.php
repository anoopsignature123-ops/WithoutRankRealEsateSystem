@if ($step == 4)
    <form method="POST" action="{{ route('customer-booking.update', $customer->id) }}">
        @csrf
        @method('PUT')

        <input type="hidden" name="step" value="4">

        @include('customer-booking.partials.plot-sale-form')

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('customer-booking.edit', [$customer->id, 'step' => 3]) }}"
                class="btn btn-outline-secondary px-4">
                Previous
            </a>

            <button type="submit" class="btn btn-success px-4 ms-2">
                Save & Next
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            $(document).ready(function() {
                let selectedProjectId = "{{ old('project_id', $plotSale?->project_id) }}";
                let selectedBlockId = "{{ old('block_id', $plotSale?->block_id) }}";
                let customerId = "{{ $customer->id ?? '' }}";

                function calculateFinalAmount() {
                    let plotCost = parseFloat($('#plotCost').val()) || 0;
                    let plcAmount = parseFloat($('#plcAmount').val()) || 0;
                    let otherCharges = parseFloat($('#otherCharges').val()) || 0;
                    let couponDiscount = parseFloat($('#couponDiscount').val()) || 0;

                    let finalAmount = plotCost + plcAmount + otherCharges - couponDiscount;

                    if (finalAmount < 0) {
                        finalAmount = 0;
                    }

                    $('#finalPayable').val(finalAmount.toFixed(2));
                    $('#totalPlotCost').val(finalAmount.toFixed(2));
                }

                function loadBlocks(projectId, selectedBlock = '') {
                    if (!projectId) {
                        $('#blockId').html('<option value="">Select Block</option>');
                        $('#showPlots').addClass('d-none');
                        return;
                    }

                    $('#blockId').html('<option value="">Loading...</option>');

                    $.get('/get-blocks/' + projectId, function(blocks) {
                        let html = '<option value="">Select Block</option>';

                        $.each(blocks, function(i, block) {
                            let selected = selectedBlock == block.id ? 'selected' : '';

                            html += `
                                <option value="${block.id}" ${selected}>
                                    ${block.block}
                                </option>
                            `;
                        });

                        $('#blockId').html(html);

                        if (selectedBlock) {
                            $('#showPlots').removeClass('d-none');
                        }
                    });
                }

                if (selectedProjectId) {
                    loadBlocks(selectedProjectId, selectedBlockId);
                }

                $('#projectId').change(function() {
                    let projectId = $(this).val();

                    $('#plotListSection').html('');
                    $('#showPlots').addClass('d-none');
                    $('#plotId').val('');
                    $('#plotNumber').val('');
                    $('#plotRate').val('');
                    $('#plotArea').val('');
                    $('#plotCost').val('');
                    $('#plcAmount').val('');
                    calculateFinalAmount();

                    loadBlocks(projectId);
                });

                $('#blockId').change(function() {
                    let blockId = $(this).val();

                    $('#plotListSection').html('');
                    $('#plotId').val('');
                    $('#plotNumber').val('');
                    $('#plotRate').val('');
                    $('#plotArea').val('');
                    $('#plotCost').val('');
                    $('#plcAmount').val('');
                    calculateFinalAmount();

                    if (blockId) {
                        $('#showPlots').removeClass('d-none');
                    } else {
                        $('#showPlots').addClass('d-none');
                    }
                });

                $('#showPlots').click(function() {
                    let blockId = $('#blockId').val();

                    if (!blockId) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Block Required',
                            text: 'Please select block first.'
                        });
                        return;
                    }

                    $('#plotListSection').html(`
                        <div class="text-center py-4">
                            <div class="spinner-border text-success" role="status"></div>
                            <div class="fw-semibold text-muted mt-2">
                                Loading available plots...
                            </div>
                        </div>
                    `);

                    let plotsUrl = '/get-plots/' + blockId;

                    if (customerId) {
                        plotsUrl += '/' + customerId;
                    }

                    $.get(plotsUrl, function(plots) {
                        if (plots.length === 0) {
                            let html = `
                                <div class="alert alert-warning alert-dismissible fade show rounded-4 mt-3" role="alert">
                                    <div class="d-flex gap-3">
                                        <i class="bi bi-exclamation-triangle fs-4"></i>
                                        <div>
                                            <strong>No Plots Available!</strong>
                                            <p class="mb-0">
                                                All plots in this block have been booked. Please select a different block or project.
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `;

                            $('#plotListSection').html(html);
                            return;
                        }

                        let html = `
                            <div class="card border-0 bg-light rounded-4 mt-3">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                        <div>
                                            <h6 class="fw-bold mb-1 text-success">
                                                <i class="bi bi-grid-3x3-gap me-1"></i>
                                                Available Plots
                                            </h6>
                                            <small class="text-muted">
                                                Click any plot card to select for booking.
                                            </small>
                                        </div>

                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                            ${plots.length} Available
                                        </span>
                                    </div>

                                    <div class="row g-3">
                        `;

                        $.each(plots, function(i, plot) {
                            let plotType = 'N/A';

                            if (plot.plot_type) {
                                plotType = plot.plot_type.plot_type_name;
                            }

                            let selectedClass = '';

                            if ($('#plotId').val() == plot.id) {
                                selectedClass = 'border-success shadow';
                            }

                            html += `
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="card plot-card h-100 border ${selectedClass} rounded-4"
                                        style="cursor:pointer;"
                                        data-id="${plot.id}"
                                        data-number="${plot.plot_number}"
                                        data-rate="${plot.plot_rate}"
                                        data-area="${plot.plot_area}"
                                        data-plc="${plot.plc_rate}">

                                        <div class="card-body p-3">

                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <span class="badge bg-success rounded-pill mb-2">
                                                        Available
                                                    </span>

                                                    <h5 class="fw-bold text-dark mb-0">
                                                        Plot ${plot.plot_number}
                                                    </h5>
                                                </div>

                                                <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width:40px;height:40px;">
                                                    <i class="bi bi-geo-alt-fill"></i>
                                                </div>
                                            </div>

                                            <div class="border-top pt-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <small class="text-muted">Rate</small>
                                                    <strong>₹${parseFloat(plot.plot_rate).toFixed(2)}</strong>
                                                </div>

                                                <div class="d-flex justify-content-between mb-2">
                                                    <small class="text-muted">Area</small>
                                                    <strong>${parseFloat(plot.plot_area).toFixed(2)} Sq.Ft</strong>
                                                </div>

                                                <div class="d-flex justify-content-between mb-2">
                                                    <small class="text-muted">PLC</small>
                                                    <strong>₹${parseFloat(plot.plc_rate || 0).toFixed(2)}</strong>
                                                </div>

                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">Type</small>
                                                    <strong>${plotType}</strong>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        html += `
                                    </div>
                                </div>
                            </div>
                        `;

                        $('#plotListSection').html(html);
                    });
                });

                $(document).on('click', '.plot-card', function() {
                    $('.plot-card').removeClass('border-success shadow');
                    $(this).addClass('border-success shadow');

                    let id = $(this).data('id');
                    let number = $(this).data('number');
                    let rate = parseFloat($(this).data('rate')) || 0;
                    let area = parseFloat($(this).data('area')) || 0;
                    let plc = parseFloat($(this).data('plc')) || 0;
                    let plotCost = rate * area;

                    $('#plotId').val(id);
                    $('#plotNumber').val(number);
                    $('#plotRate').val(rate.toFixed(2));
                    $('#plotArea').val(area.toFixed(2));
                    $('#plcAmount').val(plc.toFixed(2));
                    $('#plotCost').val(plotCost.toFixed(2));

                    calculateFinalAmount();

                    Swal.fire({
                        icon: 'success',
                        title: 'Plot Selected',
                        text: 'Plot ' + number + ' selected successfully.',
                        timer: 1200,
                        showConfirmButton: false
                    });
                });

                $('#otherCharges, #couponDiscount').on('keyup change', function() {
                    calculateFinalAmount();
                });

                calculateFinalAmount();
            });
        </script>
    @endpush
@endif