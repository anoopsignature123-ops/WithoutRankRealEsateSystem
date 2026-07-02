@push('scripts')
    <script>
        $(document).ready(function() {
            let allPlotGroups = [];

            function formatAmount(amount) {
                return Number(amount || 0).toFixed(2);
            }

            function refreshSelect($select) {
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.trigger('change.select2');
                }
            }

            function statusBadgeClass(status) {
                status = String(status || '').toLowerCase();

                if (status === 'cleared') return 'emi-status-badge cleared';
                if (status === 'paid') return 'emi-status-badge paid';
                if (status === 'hold') return 'emi-status-badge hold';

                return 'emi-status-badge pending';
            }

            function sanitizeAmount(value) {
                value = String(value || '').replace(/[^\d.]/g, '');
                const firstDot = value.indexOf('.');

                if (firstDot !== -1) {
                    value = value.substring(0, firstDot + 1) + value.substring(firstDot + 1).replace(/\./g, '');
                }

                return value;
            }

            function selectedPlotType() {
                return $('input[name="payment_plot_type"]:checked').val() || 'multiple';
            }

            function setSummaryLoading(isLoading) {
                $('#emi_summary_loader').toggleClass('d-none', !isLoading);
                $('#project_id, #block_id, #plot_id, #booking_amount_input, #payment_mode, #submitEmiPaymentBtn')
                    .prop('disabled', isLoading);
            }

            function setButtonLoading(isLoading) {
                const button = $('#submitEmiPaymentBtn');
                button.prop('disabled', isLoading);
                button.find('.btn-label').toggleClass('d-none', isLoading);
                button.find('.btn-loader').toggleClass('d-none', !isLoading);
            }

            function resetPaymentFields() {
                $('.bank-field, .cheque-field, .dd-field, .transaction-field').addClass('d-none');
            }

            function updateQuickButtons() {
                const hasBooking = Boolean($('#customer_booking_id').val() && $('#plot_sale_detail_id').val());
                const dueAmount = parseFloat($('#max_due_amount').val()) || 0;
                const monthlyEmi = parseFloat($('#monthly_emi_value').val()) || 0;

                $('#fill_monthly_emi').toggleClass('d-none', !(hasBooking && monthlyEmi > 0 && dueAmount > 0));
                $('#fill_due_amount').toggleClass('d-none', !(hasBooking && dueAmount > 0));
            }

            function clearBookingData() {
                $('#customer_booking_id').val('');
                $('#plot_sale_detail_id').val('');
                $('#plot_sale_detail_ids_container').empty();
                $('#booking_id').val('');
                $('#customer_id').val('');
                $('#customer_name').val('');

                $('#total_cost').text('0.00');
                $('#booking_amount').text('0.00');
                $('#total_paid').text('0.00');
                $('#hold_amount').text('0.00');
                $('#due_amount').text('0.00');
                $('#emi_start_date').text('-');
                $('#emi_months').text('0 / 0 Months');
                $('#monthly_emi').text('0.00');

                $('#booking_amount_input').val('').removeAttr('max');
                $('#monthly_emi_value').val('');
                $('#max_due_amount').val('0');
                $('#minimum_emi').html('&#8377;0.00');
                $('#payment_history_count').text('0 Records');
                renderEmiOverview(null);
                $('#form_selected_plots_box').addClass('d-none');
                $('#form_selected_plot_count').text('0 Plots');
                $('#form_selected_plot_mode').text('Verify EMI plot details before entering amount.');
                $('#form_selected_plots').html(`
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">
                            Select booking group to view EMI details.
                        </td>
                    </tr>
                `);

                $('#payment_history').html(`
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">No Payment Found</td>
                    </tr>
                `);

                updateQuickButtons();
            }

            function resetPlotGroups() {
                $('#plot_group_hint').text('Select project and block to load EMI groups.');
            }

            function renderEmiOverview(overview) {
                if (!overview) {
                    $('#emi_view_box').addClass('d-none');
                    $('#emi_view_total_badge').text('0 EMI');
                    $('#emi_view_total, #emi_view_paid, #emi_view_hold, #emi_view_remaining').text('0');
                    $('#emi_view_progress_bar')
                        .css('width', '0%')
                        .attr('aria-valuenow', 0)
                        .text('');
                    $('#emi_view_progress_text').text('0% Paid');
                    return;
                }

                const total = parseInt(overview.total_installments || 0, 10);
                const paid = parseInt(overview.paid_installments || 0, 10);
                const hold = parseInt(overview.hold_installments || 0, 10);
                const remaining = parseInt(overview.remaining_installments || 0, 10);
                const progress = Math.min(100, Math.max(0, parseInt(overview.progress_percent || 0, 10)));

                $('#emi_view_total_badge').text(total + (total === 1 ? ' EMI' : ' EMIs'));
                $('#emi_view_total').text(total);
                $('#emi_view_paid').text(paid);
                $('#emi_view_hold').text(hold);
                $('#emi_view_remaining').text(remaining);
                $('#emi_view_progress_bar')
                    .css('width', progress + '%')
                    .attr('aria-valuenow', progress)
                    .text('');
                $('#emi_view_progress_text').text(progress + '% Paid');
                $('#emi_view_box').removeClass('d-none');
            }

            function renderPlotGroups(plots) {
                resetPlotGroups();
                $('#plot_id').html('<option value="">Select booking group</option>');

                const mode = selectedPlotType();
                const filteredPlots = (Array.isArray(plots) ? plots : []).filter(function(plot) {
                    return mode === 'multiple' ? Boolean(plot.is_multiple) : !Boolean(plot.is_multiple);
                });

                if (filteredPlots.length === 0) {
                    $('#plot_group_hint').text(
                        mode === 'multiple'
                            ? 'No multiple plot EMI booking found for this block.'
                            : 'No single plot EMI booking found for this block.'
                    );
                    refreshSelect($('#plot_id'));
                    return;
                }

                $.each(filteredPlots, function(index, plot) {
                    const bookingText = plot.booking_code ? ` | ${plot.booking_code}` : '';
                    const customerText = plot.customer_name ? ` | ${plot.customer_name}` : '';
                    $('#plot_id').append(
                        `<option value="${plot.id}">${plot.plot_number}${bookingText}${customerText}</option>`
                    );
                });

                $('#plot_group_hint').text(
                    filteredPlots.length + (filteredPlots.length > 1 ? ' EMI groups found.' : ' EMI group found.')
                );
                refreshSelect($('#plot_id'));
            }

            function renderPlotSaleInputs(plotSaleIds) {
                const ids = Array.isArray(plotSaleIds) && plotSaleIds.length ? plotSaleIds : [];
                $('#plot_sale_detail_ids_container').empty();

                $.each(ids, function(index, id) {
                    $('#plot_sale_detail_ids_container').append(
                        `<input type="hidden" name="plot_sale_detail_ids[]" value="${id}">`
                    );
                });
            }

            function renderSelectedPlots(plots, overview = null, isMultiple = false) {
                if (!Array.isArray(plots) || plots.length === 0) {
                    $('#form_selected_plots_box').addClass('d-none');
                    return;
                }

                let html = '';
                $.each(plots, function(index, plot) {
                    const progressData = isMultiple && overview ? overview : plot;
                    const paidInstallments = progressData.paid_installments ?? 0;
                    const holdInstallments = progressData.hold_installments ?? 0;
                    const remainingInstallments = progressData.remaining_installments ?? 0;
                    const totalInstallments = progressData.total_installments ?? 0;
                    const progressPercent = progressData.progress_percent ?? 0;
                    const progressLabel = isMultiple ? 'group EMI paid' : 'EMI paid';

                    html += `<tr>
                        <td>
                            <span class="fw-bold text-dark">${plot.plot_no ?? '-'}</span>
                            <span class="d-block small text-muted">${plot.project ?? '-'} / Block ${plot.block ?? '-'}</span>
                            <span class="d-block small text-muted">Cost: &#8377;${plot.total_cost ?? '0.00'}</span>
                        </td>
                        <td class="text-nowrap">${plot.area ?? '0.00'} Sq.Ft.</td>
                        <td class="text-nowrap">&#8377;${plot.monthly_emi ?? '0.00'}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1 mb-1">
                                <span class="badge bg-success-subtle text-success border">${paidInstallments} Paid</span>
                                <span class="badge bg-warning-subtle text-warning border">${holdInstallments} Hold</span>
                                <span class="badge bg-danger-subtle text-danger border">${remainingInstallments} Left</span>
                            </div>
                            <div class="progress emi-row-progress" role="progressbar"
                                aria-valuenow="${progressPercent}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar bg-success" style="width: ${progressPercent}%"></div>
                            </div>
                            <small class="text-muted">${paidInstallments}/${totalInstallments} ${progressLabel}</small>
                        </td>
                        <td class="text-end text-nowrap fw-semibold">&#8377;${plot.due_amount ?? '0.00'}</td>
                    </tr>`;
                });

                $('#form_selected_plots').html(html);
                $('#form_selected_plot_count').text(plots.length + (plots.length > 1 ? ' Plots' : ' Plot'));
                $('#form_selected_plot_mode').text(
                    plots.length > 1
                        ? 'Multiple EMI group selected. One receipt will cover these plots.'
                        : 'Single EMI plot selected.'
                );
                $('#form_selected_plots_box').removeClass('d-none');
            }

            function validateAmount(showAlert = true) {
                const enteredAmount = parseFloat($('#booking_amount_input').val()) || 0;
                const monthlyEmi = parseFloat($('#monthly_emi_value').val()) || 0;
                const dueAmount = parseFloat($('#max_due_amount').val()) || 0;

                if (enteredAmount <= 0) {
                    if (showAlert) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Amount',
                            text: 'Please enter a valid EMI amount.'
                        });
                    }
                    return false;
                }

                if (dueAmount <= 0) {
                    if (showAlert) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Due Amount',
                            text: 'This booking does not have any pending EMI due.'
                        });
                    }
                    return false;
                }

                if (enteredAmount > dueAmount) {
                    if (showAlert) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Amount',
                            text: 'Amount cannot exceed due amount of Rs. ' + formatAmount(dueAmount) + '.'
                        });
                    }
                    $('#booking_amount_input').val(formatAmount(dueAmount));
                    return false;
                }

                if (enteredAmount < monthlyEmi && enteredAmount < dueAmount) {
                    if (showAlert) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid EMI Amount',
                            text: 'Minimum EMI amount is Rs. ' + formatAmount(monthlyEmi) + '.'
                        });
                    }
                    return false;
                }

                return true;
            }

            $('#payment_mode').on('change', function() {
                resetPaymentFields();
                const mode = $(this).val();

                if (['cheque', 'dd', 'neft_rtgs'].includes(mode)) {
                    $('.bank-field').removeClass('d-none');
                }

                if (mode === 'cheque') {
                    $('.cheque-field').removeClass('d-none');
                }

                if (mode === 'dd') {
                    $('.dd-field').removeClass('d-none');
                }

                if (['neft_rtgs', 'card'].includes(mode)) {
                    $('.transaction-field').removeClass('d-none');
                }
            });

            $('#project_id').on('change', function() {
                const projectId = $(this).val();
                $('#block_id').html('<option value="">Select Block</option>');
                $('#plot_id').html('<option value="">Select booking group</option>');
                allPlotGroups = [];
                resetPlotGroups();
                refreshSelect($('#block_id'));
                refreshSelect($('#plot_id'));
                clearBookingData();

                if (!projectId) return;

                $.get("{{ route('emi-payment.blocks', ':id') }}".replace(':id', projectId), function(res) {
                    if (!res.status) return;

                    $.each(res.data, function(index, block) {
                        $('#block_id').append(`<option value="${block.id}">${block.block}</option>`);
                    });
                    refreshSelect($('#block_id'));
                });
            });

            $('#block_id').on('change', function() {
                const blockId = $(this).val();
                $('#plot_id').html('<option value="">Select booking group</option>');
                allPlotGroups = [];
                resetPlotGroups();
                refreshSelect($('#plot_id'));
                clearBookingData();

                if (!blockId) return;

                $.get("{{ route('emi-payment.plots', ':id') }}".replace(':id', blockId), function(res) {
                    if (!res.status) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Pending EMI',
                            text: res.message,
                            confirmButtonColor: '#198754'
                        });
                        return;
                    }

                    allPlotGroups = Array.isArray(res.data) ? res.data : [];
                    renderPlotGroups(allPlotGroups);
                });
            });

            $('input[name="payment_plot_type"]').on('change', function() {
                clearBookingData();
                renderPlotGroups(allPlotGroups);
                $('#payment_plot_type_help').text(
                    selectedPlotType() === 'multiple'
                        ? 'Multiple mode shows only grouped EMI bookings.'
                        : 'Single mode shows only one-plot EMI bookings.'
                );
            });

            $('#plot_id').on('change', function() {
                const plotId = $(this).val();
                clearBookingData();

                if (!plotId) return;

                setSummaryLoading(true);

                $.get("{{ route('emi-payment.details', ':id') }}".replace(':id', plotId), function(res) {
                    setSummaryLoading(false);

                    if (!res.status) {
                        updateQuickButtons();
                        Swal.fire({
                            icon: 'error',
                            title: 'Booking Not Found',
                            text: res.message || 'EMI booking details not found.'
                        });
                        return;
                    }

                    $('#customer_booking_id').val(res.booking_db_id);
                    $('#plot_sale_detail_id').val(res.plot_sale_id);
                    renderPlotSaleInputs(res.plot_sale_ids || [res.plot_sale_id]);
                    $('#booking_id').val(res.booking_code);
                    $('#customer_id').val(res.customer_code);
                    $('#customer_name').val(res.customer_name);

                    $('#total_cost').text(res.total_cost);
                    $('#booking_amount').text(res.booking_amount);
                    $('#total_paid').text(res.total_paid);
                    $('#hold_amount').text(res.hold_amount || '0.00');
                    $('#due_amount').text(res.due_amount);
                    $('#emi_start_date').text(res.emi_start_date);
                    renderEmiOverview(res.emi_overview);
                    renderSelectedPlots(res.plots || [], res.emi_overview, Boolean(res.is_multiple));
                    const overview = res.emi_overview || {};
                    $('#emi_months').text(
                        (overview.paid_installments ?? res.months_passed) + ' / ' +
                        (overview.total_installments ?? res.emi_months) + ' Months'
                    );
                    $('#monthly_emi').text(res.monthly_emi);

                    $('#booking_amount_input').val(res.monthly_emi).attr('max', res.due_amount);
                    $('#monthly_emi_value').val(res.monthly_emi);
                    $('#max_due_amount').val(res.due_amount);
                    $('#minimum_emi').html('&#8377;' + res.monthly_emi);

                    let html = '';
                    if (res.payment_history && res.payment_history.length > 0) {
                        $.each(res.payment_history, function(index, payment) {
                            html += `<tr>
                                <td>${payment.receipt_no ?? '-'}</td>
                                <td>${payment.plot_no ?? '-'}</td>
                                <td>${payment.date ?? '-'}</td>
                                <td>&#8377;${payment.amount ?? '0'}</td>
                                <td><span class="${statusBadgeClass(payment.payment_status)}">${payment.status ?? '-'}</span></td>
                            </tr>`;
                        });
                        $('#payment_history_count').text(res.payment_history.length + ' Records');
                    } else {
                        html = `<tr>
                            <td colspan="5" class="text-center text-muted py-3">No Payment Found</td>
                        </tr>`;
                        $('#payment_history_count').text('0 Records');
                    }

                    $('#payment_history').html(html);
                    updateQuickButtons();
                }).fail(function() {
                    setSummaryLoading(false);
                    updateQuickButtons();
                    Swal.fire({
                        icon: 'error',
                        title: 'Something went wrong',
                        text: 'Unable to load EMI details.'
                    });
                });
            });

            $('#booking_amount_input').on('input change blur', function() {
                const cleanedAmount = sanitizeAmount($(this).val());
                if ($(this).val() !== cleanedAmount) {
                    $(this).val(cleanedAmount);
                }

                const enteredAmount = parseFloat(cleanedAmount) || 0;
                const dueAmount = parseFloat($('#max_due_amount').val()) || 0;

                if (enteredAmount > dueAmount && dueAmount > 0) {
                    validateAmount(true);
                }
            });

            $('#fill_monthly_emi').on('click', function() {
                const monthlyEmi = parseFloat($('#monthly_emi_value').val()) || 0;
                const dueAmount = parseFloat($('#max_due_amount').val()) || 0;

                if (monthlyEmi <= 0 || dueAmount <= 0) return;

                $('#booking_amount_input').val(formatAmount(Math.min(monthlyEmi, dueAmount))).focus();
            });

            $('#fill_due_amount').on('click', function() {
                const dueAmount = parseFloat($('#max_due_amount').val()) || 0;

                if (dueAmount <= 0) return;

                $('#booking_amount_input').val(formatAmount(dueAmount)).focus();
            });

            $('#emiPaymentForm').on('submit', function(event) {
                $('#booking_amount_input').val(sanitizeAmount($('#booking_amount_input').val()));

                if (!$('#customer_booking_id').val() || !$('#plot_sale_detail_id').val()) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Select Plot',
                        text: 'Please select a valid EMI plot first.'
                    });
                    return;
                }

                if (!validateAmount(true)) {
                    event.preventDefault();
                    return;
                }

                setButtonLoading(true);
            });

            $('#payment_mode').trigger('change');
        });
    </script>
@endpush
