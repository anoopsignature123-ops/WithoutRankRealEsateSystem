@if ($step == 4)
    @php
        $existingPlotSalesForJs = ($activePlotSales ?? collect())
            ->mapWithKeys(function ($sale) {
                return [
                    $sale->plot_detail_id => [
                        'id' => $sale->plot_detail_id,
                        'saleId' => $sale->id,
                        'bookingCode' => $sale->booking_code,
                        'projectId' => $sale->project_id,
                        'projectName' => $sale->project?->name,
                        'blockId' => $sale->block_id,
                        'blockName' => $sale->block?->block,
                        'number' => $sale->plotDetail?->plot_number,
                        'rate' => (float) $sale->plot_rate,
                        'area' => (float) $sale->plot_area,
                        'plc' => (float) $sale->plc_amount,
                        'plotCost' => (float) $sale->plot_cost,
                        'totalDevelopmentCharge' => (float) $sale->total_development_charge,
                        'developmentRate' => (float) $sale->development_rate,
                        'otherCharges' => (float) $sale->other_charges,
                        'couponDiscount' => (float) $sale->coupon_discount,
                        'finalPayable' => (float) $sale->final_payable,
                        'totalPlotCost' => (float) $sale->total_plot_cost,
                        'bookingDate' => $sale->booking_date
                            ? (is_string($sale->booking_date)
                                ? \Carbon\Carbon::parse($sale->booking_date)->format('Y-m-d')
                                : $sale->booking_date->format('Y-m-d'))
                            : '',
                        'remark' => $sale->remark ?? '',
                    ],
                ];
            })
            ->toArray();

        $bookingGroupsForJs = ($plotSales ?? collect())
            ->groupBy('booking_code')
            ->map(function ($group) {
                return $group
                    ->mapWithKeys(function ($sale) {
                        return [
                            $sale->plot_detail_id => [
                                'id' => $sale->plot_detail_id,
                                'saleId' => $sale->id,
                                'bookingCode' => $sale->booking_code,
                                'projectId' => $sale->project_id,
                                'projectName' => $sale->project?->name,
                                'blockId' => $sale->block_id,
                                'blockName' => $sale->block?->block,
                                'number' => $sale->plotDetail?->plot_number,
                                'rate' => (float) $sale->plot_rate,
                                'area' => (float) $sale->plot_area,
                                'plc' => (float) $sale->plc_amount,
                                'plotCost' => (float) $sale->plot_cost,
                                'totalDevelopmentCharge' => (float) $sale->total_development_charge,
                                'developmentRate' => (float) $sale->development_rate,
                                'otherCharges' => (float) $sale->other_charges,
                                'couponDiscount' => (float) $sale->coupon_discount,
                                'finalPayable' => (float) $sale->final_payable,
                                'totalPlotCost' => (float) $sale->total_plot_cost,
                                'bookingDate' => $sale->booking_date
                                    ? (is_string($sale->booking_date)
                                        ? \Carbon\Carbon::parse($sale->booking_date)->format('Y-m-d')
                                        : $sale->booking_date->format('Y-m-d'))
                                    : '',
                                'remark' => $sale->remark ?? '',
                            ],
                        ];
                    })
                    ->toArray();
            })
            ->toArray();
    @endphp

    <form method="POST" action="{{ route('customer-booking.update', $customer->id) }}">
        @csrf
        @method('PUT')

        <input type="hidden" name="step" value="4">

        @include('customer-booking.partials.plot-sale-form')



    </form>

    @push('scripts')
        <script>
            $(document).ready(function() {
                let selectedProjectId = "{{ old('project_id', $plotSale?->project_id) }}";
                let selectedBlockId = "{{ old('block_id', $plotSale?->block_id) }}";
                let customerId = "{{ $customer->id ?? '' }}";
                let selectedPlots = {};
                let currentEditPlotDetailId = null;
                let existingPlotSales = @json($existingPlotSalesForJs);
                let batchPlotSales = @json($bookingGroupsForJs);

                if (Object.keys(existingPlotSales).length) {
                    selectedPlots = Object.assign({}, existingPlotSales);
                    let batch = Object.values(selectedPlots)[0];
                    if (batch) {
                        $('#editBookingCode').val(batch.bookingCode || '');
                        $('#projectId').val(batch.projectId || '');
                        $('#blockId').val(batch.blockId || '');
                        $('#showPlots').removeClass('d-none');
                    }
                }

                function populateBatchSelection(bookingCode) {
                    selectedPlots = {};
                    currentEditPlotDetailId = null;

                    if (bookingCode && batchPlotSales[bookingCode]) {
                        selectedPlots = Object.assign({}, batchPlotSales[bookingCode]);
                    }

                    $('#editBookingCode').val(bookingCode || '');
                    $('#editPlotSaleDetailId').val('');
                    renderSelectedPlots();
                }

                function calculateFinalAmount() {
                    let plotCost = 0;
                    let plcAmount = 0;
                    Object.values(selectedPlots).forEach(function(plot) {
                        plotCost += parseFloat(plot.plotCost) || 0;
                        plcAmount += parseFloat(plot.plc) || 0;
                    });
                    let developmentCharge = parseFloat($('#totalDevelopmentCharge').val()) || 0;
                    let otherCharges = parseFloat($('#otherCharges').val()) || 0;
                    let couponDiscount = parseFloat($('#couponDiscount').val()) || 0;

                    let finalAmount = plotCost + plcAmount + developmentCharge + otherCharges - couponDiscount;

                    if (finalAmount < 0) {
                        finalAmount = 0;
                    }

                    $('#finalPayable').val(finalAmount.toFixed(2));
                    $('#totalPlotCost').val(finalAmount.toFixed(2));
                }

                function renderSelectedPlots() {
                    let plots = Object.values(selectedPlots);
                    let hiddenHtml = '';
                    let numbers = [];
                    let rateTotal = 0;
                    let areaTotal = 0;
                    let costTotal = 0;
                    let plcTotal = 0;

                    plots.forEach(function(plot) {
                        numbers.push(plot.number);
                        rateTotal += parseFloat(plot.rate) || 0;
                        areaTotal += parseFloat(plot.area) || 0;
                        costTotal += parseFloat(plot.plotCost) || 0;
                        plcTotal += parseFloat(plot.plc) || 0;
                        hiddenHtml += `
                            <input type="hidden" name="plot_detail_ids[]" value="${plot.id}">
                            <input type="hidden" name="plot_details[${plot.id}][sale_id]" value="${plot.saleId || ''}">
                            <input type="hidden" name="plot_details[${plot.id}][plot_number]" value="${plot.number}">
                            <input type="hidden" name="plot_details[${plot.id}][plot_rate]" value="${plot.rate}">
                            <input type="hidden" name="plot_details[${plot.id}][plot_area]" value="${plot.area}">
                            <input type="hidden" name="plot_details[${plot.id}][plot_cost]" value="${plot.plotCost}">
                            <input type="hidden" name="plot_details[${plot.id}][plc_amount]" value="${plot.plc}">
                            <input type="hidden" name="plot_details[${plot.id}][booking_code]" value="${plot.bookingCode || ''}">
                            <input type="hidden" name="plot_details[${plot.id}][booking_date]" value="${plot.bookingDate}">
                            <input type="hidden" name="plot_details[${plot.id}][remark]" value="${plot.remark}">
                        `;

                        if (plot.totalDevelopmentCharge !== undefined) {
                            hiddenHtml +=
                                `<input type="hidden" name="plot_details[${plot.id}][total_development_charge]" value="${plot.totalDevelopmentCharge}">`;
                        }
                        if (plot.developmentRate !== undefined) {
                            hiddenHtml +=
                                `<input type="hidden" name="plot_details[${plot.id}][development_rate]" value="${plot.developmentRate}">`;
                        }
                        if (plot.otherCharges !== undefined) {
                            hiddenHtml +=
                                `<input type="hidden" name="plot_details[${plot.id}][other_charges]" value="${plot.otherCharges}">`;
                        }
                        if (plot.couponDiscount !== undefined) {
                            hiddenHtml +=
                                `<input type="hidden" name="plot_details[${plot.id}][coupon_discount]" value="${plot.couponDiscount}">`;
                        }
                        if (plot.finalPayable !== undefined) {
                            hiddenHtml +=
                                `<input type="hidden" name="plot_details[${plot.id}][final_payable]" value="${plot.finalPayable}">`;
                        }
                        if (plot.totalPlotCost !== undefined) {
                            hiddenHtml +=
                                `<input type="hidden" name="plot_details[${plot.id}][total_plot_cost]" value="${plot.totalPlotCost}">`;
                        }
                    });

                    $('#selectedPlotHiddenFields').html(hiddenHtml);

                    if (plots.length) {
                        $('#plotId').val(plots[0].id || '');
                        $('#plotNumber').val(numbers.join(', '));
                        $('#plotRate').val((rateTotal / plots.length).toFixed(2));
                        $('#plotArea').val(areaTotal.toFixed(2));
                        $('#plotCost').val(costTotal.toFixed(2));
                        $('#plcAmount').val(plcTotal.toFixed(2));
                    } else {
                        $('#plotId').val('');
                        $('#plotNumber').val('');
                        $('#plotRate').val('');
                        $('#plotArea').val('');
                        $('#plotCost').val('');
                        $('#plcAmount').val('');
                    }

                    $('.plot-card').each(function() {
                        let id = String($(this).data('id'));
                        $(this).toggleClass('border-success shadow', !!selectedPlots[id]);
                        $(this).find('.plot-select-badge').toggleClass('d-none', !selectedPlots[id]);
                    });

                    calculateFinalAmount();
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
                    selectedPlots = {};
                    renderSelectedPlots();
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
                    selectedPlots = {};
                    renderSelectedPlots();
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
                                                Click any plot card to select it for this booking.
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

                            let selectedClass = selectedPlots[String(plot.id)] ?
                                'border-success shadow' : '';

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
                                                    <span class="badge bg-primary rounded-pill mb-2 plot-select-badge ${selectedPlots[String(plot.id)] ? '' : 'd-none'}">
                                                        Selected
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
                        renderSelectedPlots();
                    });
                });

                $(document).on('click', '.plot-card', function() {
                    let id = $(this).data('id');
                    let number = $(this).data('number');
                    let rate = parseFloat($(this).data('rate')) || 0;
                    let area = parseFloat($(this).data('area')) || 0;
                    let plc = parseFloat($(this).data('plc')) || 0;
                    let plotCost = rate * area;

                    let key = String(id);
                    if (selectedPlots[key]) {
                        Swal.fire({
                            icon: 'question',
                            title: `Remove Plot ${number}?`,
                            text: 'This plot will be removed from the current booking selection.',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, remove',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#198754',
                            cancelButtonColor: '#6c757d',
                        }).then(function(result) {
                            if (!result.isConfirmed) {
                                return;
                            }

                            delete selectedPlots[key];
                            renderSelectedPlots();
                        });
                    } else {
                        let existingSelection = Object.values(selectedPlots)[0];
                        let confirmText = existingSelection
                            ? `Plot ${existingSelection.number} is already selected. It will be replaced with Plot ${number}.`
                            : `Plot ${number} will be selected for this booking.`;

                        Swal.fire({
                            icon: 'question',
                            title: `Select Plot ${number}?`,
                            text: confirmText,
                            showCancelButton: true,
                            confirmButtonText: 'Yes, select',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#198754',
                            cancelButtonColor: '#6c757d',
                        }).then(function(result) {
                            if (!result.isConfirmed) {
                                return;
                            }

                            selectedPlots = {};
                            selectedPlots[key] = {
                                saleId: existingPlotSales[key]?.saleId || null,
                                id,
                                number,
                                rate: rate.toFixed(2),
                                area: area.toFixed(2),
                                plc: plc.toFixed(2),
                                plotCost: plotCost.toFixed(2),
                                bookingCode: $('#editBookingCode').val() || '',
                                bookingDate: $('input[name="booking_date"]')?.val() || '',
                                remark: $('textarea[name="remark"]')?.val() || '',
                            };

                            renderSelectedPlots();
                        });
                    }
                });

                $(document).on('click', '.plot-add-more-btn', function() {
                    let bookingCode = $(this).data('booking-code');
                    populateBatchSelection(bookingCode);

                    let batch = Object.values(selectedPlots)[0];
                    if (batch) {
                        $('#projectId').val(batch.projectId || '');
                        loadBlocks(batch.projectId || '', batch.blockId || '');
                    }

                    Swal.fire({
                        icon: 'info',
                        title: 'Booking Selected',
                        text: 'Existing plot booking loaded. Update plot details as needed.'
                    });
                });

                $(document).on('click', '.plot-view-group-btn', function() {
                    let bookingCode = $(this).data('booking-code');
                    let group = batchPlotSales[bookingCode] || {};
                    let rows = '';
                    let total = 0;
                    let bookingDate = '';

                    Object.values(group).forEach(function(plot) {
                        rows += `
                            <tr>
                                <td>
                                    <strong>${plot.number}</strong>
                                    <div class="text-muted small">Plot ${plot.number}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">${plot.projectName || '—'}</div>
                                    <small class="text-muted">Block ${plot.blockName || '—'}</small>
                                </td>
                                <td class="text-end">${parseFloat(plot.area).toFixed(2)} Sq.Ft</td>
                                <td class="text-end">₹${parseFloat(plot.plotCost).toFixed(2)}</td>
                                <td class="text-end">₹${parseFloat(plot.plc).toFixed(2)}</td>
                                <td class="text-end">₹${parseFloat(plot.finalPayable).toFixed(2)}</td>
                                <td class="text-end">₹${parseFloat(plot.totalPlotCost).toFixed(2)}</td>
                            </tr>
                        `;
                        total += parseFloat(plot.totalPlotCost) || 0;
                        if (!bookingDate && plot.bookingDate) {
                            bookingDate = plot.bookingDate;
                        }
                    });

                    $('#viewGroupBookingCode').text(bookingCode);
                    $('#viewGroupBookingDate').text(bookingDate ? `Booking date: ${bookingDate}` :
                        'Booking date not set');
                    $('#viewGroupBookingTableBody').html(rows || `
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No plot details found for this booking group.</td>
                        </tr>
                    `);
                    $('#viewGroupBookingTotal').text(`₹${total.toFixed(2)}`);
                    let viewModal = $('#viewBookingGroupModal');

                    viewModal.appendTo('body');
                    viewModal.modal({
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });

                    viewModal.modal('show');
                });

                $(document).on('click', '.plot-edit-btn', function() {
                    let saleId = $(this).data('sale-id');
                    let bookingCode = $(this).data('booking-code');
                    let plotId = $(this).data('plot-id');

                    populateBatchSelection(bookingCode);
                    currentEditPlotDetailId = String(plotId);
                    $('#editPlotSaleDetailId').val(saleId);

                    let batch = Object.values(selectedPlots)[0];
                    if (batch) {
                        $('#projectId').val(batch.projectId || '');
                        loadBlocks(batch.projectId || '', batch.blockId || '');
                    }

                    $('#modalBookingCode').val(bookingCode);
                    $('#modalPlotNumber').val($(this).data('plot-number'));
                    $('#modalPlotRate').val($(this).data('plot-rate'));
                    $('#modalPlotArea').val($(this).data('plot-area'));
                    $('#modalPlotCost').val($(this).data('plot-cost'));
                    $('#modalPlcAmount').val($(this).data('plc') ?? 0);
                    $('#modalTotalDevelopmentCharge').val($(this).data('total-development-charge') ?? 0);
                    $('#modalDevelopmentRate').val($(this).data('development-rate') ?? 0);
                    $('#modalOtherCharges').val($(this).data('other-charges') ?? 0);
                    $('#modalCouponDiscount').val($(this).data('coupon-discount') ?? 0);
                    $('#modalFinalPayable').val($(this).data('final-payable') ?? 0);
                    $('#modalTotalPlotCost').val($(this).data('total-plot-cost') ?? 0);
                    $('#modalBookingDate').val($(this).data('booking-date') ?? '');
                    $('#modalRemark').val($(this).data('remark') ?? '');
                    let editModal = $('#plotEditModal');

                    editModal.appendTo('body');
                    editModal.modal({
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });

                    editModal.modal('show');
                });

                function calculateModalTotals() {
                    let plotCost = parseFloat($('#modalPlotCost').val()) || 0;
                    let plcAmount = parseFloat($('#modalPlcAmount').val()) || 0;
                    let developmentCharge = parseFloat($('#modalTotalDevelopmentCharge').val()) || 0;
                    let otherCharges = parseFloat($('#modalOtherCharges').val()) || 0;
                    let couponDiscount = parseFloat($('#modalCouponDiscount').val()) || 0;

                    let finalPayable = Math.max(0, plotCost + plcAmount + developmentCharge + otherCharges);
                    let totalPlotCost = Math.max(0, finalPayable - couponDiscount);

                    $('#modalFinalPayable').val(finalPayable.toFixed(2));
                    $('#modalTotalPlotCost').val(totalPlotCost.toFixed(2));
                }

                $('#modalPlcAmount, #modalTotalDevelopmentCharge, #modalOtherCharges, #modalCouponDiscount').on(
                    'keyup change', calculateModalTotals);

                $('#applyPlotEdit').click(function() {
                    if (!currentEditPlotDetailId) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Plot missing',
                            text: 'Unable to update the selected plot.'
                        });
                        return;
                    }

                    let plot = selectedPlots[currentEditPlotDetailId];
                    if (!plot) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Plot missing',
                            text: 'Please select the plot before editing.'
                        });
                        return;
                    }

                    plot.plc = parseFloat($('#modalPlcAmount').val()) || 0;
                    plot.totalDevelopmentCharge = parseFloat($('#modalTotalDevelopmentCharge').val()) || 0;
                    plot.developmentRate = parseFloat($('#modalDevelopmentRate').val()) || 0;
                    plot.otherCharges = parseFloat($('#modalOtherCharges').val()) || 0;
                    plot.couponDiscount = parseFloat($('#modalCouponDiscount').val()) || 0;
                    plot.finalPayable = parseFloat($('#modalFinalPayable').val()) || 0;
                    plot.totalPlotCost = parseFloat($('#modalTotalPlotCost').val()) || 0;
                    plot.bookingDate = $('#modalBookingDate').val() || '';
                    plot.remark = $('#modalRemark').val() || '';

                    selectedPlots[currentEditPlotDetailId] = plot;
                    renderSelectedPlots();
                    $('#plotEditModal').modal('hide');
                });

                $('#otherCharges, #couponDiscount').on('keyup change', function() {
                    calculateFinalAmount();
                });

                $('form').on('submit', function(event) {
                    if (Object.keys(selectedPlots).length === 0) {
                        event.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Plot Required',
                            text: 'Please select at least one plot for booking.'
                        });
                        return;
                    }

                    if (Object.keys(selectedPlots).length > 1) {
                        event.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Only One Plot Allowed',
                            text: 'Please select only one plot for this booking.'
                        });
                    }
                });

                renderSelectedPlots();
                calculateFinalAmount();
            });
        </script>
    @endpush
@endif
