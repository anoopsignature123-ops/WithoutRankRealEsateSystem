@push('scripts')
    <script>
        let oldTotalCostValue = 0;
        let totalPaidValue = 0;

        $(document).ready(function() {

            $('#oldProjectId').on('change', function() {
                let projectId = $(this).val();

                clearAll();

                $('#oldBlockId').html('<option value="">Loading...</option>');
                $('#oldPlotId').html('<option value="">Select Plot</option>');

                if (!projectId) {
                    $('#oldBlockId').html('<option value="">Select Block</option>');
                    return;
                }

                loadBlocks(projectId, '#oldBlockId');
            });

            $('#oldBlockId').on('change', function() {
                let blockId = $(this).val();

                clearOldPlotData();

                $('#oldPlotId').html('<option value="">Loading...</option>');

                if (!blockId) {
                    $('#oldPlotId').html('<option value="">Select Plot</option>');
                    return;
                }

                $.get(`/plot-change/booked-plots/${blockId}`, function(plots) {
                    let options = '<option value="">Select Plot</option>';

                    if (plots.length === 0) {
                        Swal.fire('No Plot Found', 'Is block me koi booked plot nahi mila.',
                            'warning');
                    }

                    $.each(plots, function(index, plot) {
                        options +=
                        `<option value="${plot.id}">${plot.plot_number}</option>`;
                    });

                    $('#oldPlotId').html(options);
                });
            });

            $('#oldPlotId').on('change', function() {
                let plotId = $(this).val();

                clearOldPlotData();

                if (!plotId) return;

                $.get(`/plot-change/booking/${plotId}`, function(res) {
                    if (!res.status) {
                        Swal.fire('Booking Not Found', res.message || 'Booking details not found.',
                            'warning');
                        return;
                    }

                    $('#plotSaleDetailId').val(res.plot_sale_detail_id);

                    $('#bookingCode').val(res.booking_code);
                    $('#customerCode').val(res.customer_code);
                    $('#customerName').val(res.customer_name);

                    $('#oldPlotInfo').val(
                        `${res.old_project_name} / ${res.old_block_name} / ${res.old_plot_number}`
                        );
                    $('#oldTotalCost').val('Rs. ' + res.old_total_plot_cost);
                    $('#totalPaidAmount').val('Rs. ' + res.total_paid_amount);
                    $('#oldDueAmount').val('Rs. ' + res.old_due_amount);

                    oldTotalCostValue = parseAmount(res.old_total_plot_cost);
                    totalPaidValue = parseAmount(res.total_paid_amount);

                    $('#oldPlotDetailsCard').removeClass('d-none');
                    $('#newPlotSection').removeClass('d-none');
                });
            });

            $('#newProjectSelect').on('change', function() {
                let projectId = $(this).val();

                clearNewPlotData();

                $('#newBlockSelect').html('<option value="">Loading...</option>');
                $('#newPlotSelect').html('<option value="">Select Available Plot</option>');

                if (!projectId) {
                    $('#newBlockSelect').html('<option value="">Select Block</option>');
                    return;
                }

                loadBlocks(projectId, '#newBlockSelect');
            });

            $('#newBlockSelect').on('change', function() {
                let blockId = $(this).val();

                clearNewPlotData();

                $('#newPlotSelect').html('<option value="">Loading...</option>');

                if (!blockId) {
                    $('#newPlotSelect').html('<option value="">Select Available Plot</option>');
                    return;
                }

                $.get(`/plot-change/available-plots/${blockId}`, function(plots) {
                    let options = '<option value="">Select Available Plot</option>';

                    if (plots.length === 0) {
                        Swal.fire('No Plot Found', 'Is block me koi available plot nahi mila.',
                            'warning');
                    }

                    $.each(plots, function(index, plot) {
                        options +=
                        `<option value="${plot.id}">${plot.plot_number}</option>`;
                    });

                    $('#newPlotSelect').html(options);
                });
            });

            $('#newPlotSelect').on('change', function() {
                let plotId = $(this).val();

                clearNewPlotData();

                if (!plotId) return;

                $.get(`/plot-change/new-plot/${plotId}`, function(res) {
                    if (!res.status) {
                        Swal.fire('Plot Not Found', res.message || 'Plot details not found.',
                            'warning');
                        return;
                    }

                    $('#newProjectId').val(res.new_project_id);
                    $('#newBlockId').val(res.new_block_id);
                    $('#newPlotDetailId').val(res.new_plot_detail_id);

                    $('#newPlotInfo').val(
                        `${res.new_project_name} / ${res.new_block_name} / ${res.new_plot_number}`
                        );
                    $('#newPlotArea').val(res.new_plot_area);
                    $('#newPlotRate').val('Rs. ' + res.new_plot_rate);
                    $('#newPlotCost').val('Rs. ' + res.new_plot_cost);
                    $('#newPlcAmount').val('Rs. ' + res.new_plc_amount);
                    $('#newTotalCost').val('Rs. ' + res.new_total_plot_cost);

                    let newTotalCost = parseAmount(res.new_total_plot_cost);
                    let newDueAmount = Math.max(0, newTotalCost - totalPaidValue);
                    let differenceAmount = newTotalCost - oldTotalCostValue;

                    $('#newDueAmount').val('Rs. ' + formatAmount(newDueAmount));
                    $('#differenceAmount').val('Rs. ' + formatAmount(differenceAmount));

                    $('#newPlotDetailsCard').removeClass('d-none');
                });
            });

            $('#plotChangeBtn').on('click', function() {
                let plotSaleDetailId = $('#plotSaleDetailId').val();
                let newProjectId = $('#newProjectId').val();
                let newBlockId = $('#newBlockId').val();
                let newPlotDetailId = $('#newPlotDetailId').val();

                if (!plotSaleDetailId) {
                    Swal.fire('Error', 'Please select current booked plot.', 'error');
                    return;
                }

                if (!newPlotDetailId) {
                    Swal.fire('Error', 'Please select new available plot.', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Change Plot?',
                    text: 'Old plot will become available and new plot will be booked.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes Change',
                    confirmButtonColor: '#198754'
                }).then((result) => {
                    if (result.isConfirmed) {
                        setPlotChangeLoading(true);
                        $.ajax({
                            url: "{{ route('plot-change.store') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                plot_sale_detail_id: plotSaleDetailId,
                                new_project_id: newProjectId,
                                new_block_id: newBlockId,
                                new_plot_detail_id: newPlotDetailId,
                                change_date: $('#changeDate').val(),
                                change_reason: $('#changeReason').val(),
                                remark: $('#remark').val()
                            },
                            success: function(res) {
                                Swal.fire('Success', res.message, 'success')
                                    .then(() => location.reload());
                            },
                            error: function(xhr) {
                                setPlotChangeLoading(false);
                                Swal.fire(
                                    'Error',
                                    xhr.responseJSON?.message ||
                                    'Plot change failed.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

        });

        function loadBlocks(projectId, targetSelect) {
            $.get(`/plot-change/blocks/${projectId}`, function(blocks) {
                let options = '<option value="">Select Block</option>';

                $.each(blocks, function(index, block) {
                    options += `<option value="${block.id}">${block.block}</option>`;
                });

                $(targetSelect).html(options);
            });
        }

        function clearAll() {
            $('#oldBlockId').html('<option value="">Select Block</option>');
            $('#oldPlotId').html('<option value="">Select Plot</option>');
            clearOldPlotData();
        }

        function clearOldPlotData() {
            $('#plotSaleDetailId').val('');

            $('#bookingCode').val('');
            $('#customerCode').val('');
            $('#customerName').val('');
            $('#oldPlotInfo').val('');
            $('#oldTotalCost').val('');
            $('#totalPaidAmount').val('');
            $('#oldDueAmount').val('');

            oldTotalCostValue = 0;
            totalPaidValue = 0;

            $('#oldPlotDetailsCard').addClass('d-none');
            $('#newPlotSection').addClass('d-none');

            $('#newProjectSelect').val('');
            $('#newBlockSelect').html('<option value="">Select Block</option>');
            $('#newPlotSelect').html('<option value="">Select Available Plot</option>');

            clearNewPlotData();
        }

        function clearNewPlotData() {
            $('#newProjectId').val('');
            $('#newBlockId').val('');
            $('#newPlotDetailId').val('');

            $('#newPlotInfo').val('');
            $('#newPlotArea').val('');
            $('#newPlotRate').val('');
            $('#newPlotCost').val('');
            $('#newPlcAmount').val('');
            $('#newTotalCost').val('');
            $('#newDueAmount').val('');
            $('#differenceAmount').val('');

            $('#changeReason').val('');
            $('#remark').val('');

            $('#newPlotDetailsCard').addClass('d-none');
        }

        function parseAmount(value) {
            if (!value) return 0;
            return parseFloat(String(value).replace(/Rs\./g, '').replace(/,/g, '')) || 0;
        }

        function formatAmount(value) {
            return Number(value || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function setPlotChangeLoading(isLoading) {
            const button = $('#plotChangeBtn');
            button.prop('disabled', isLoading);
            button.find('.btn-label').toggleClass('d-none', isLoading);
            button.find('.btn-loader').toggleClass('d-none', !isLoading);
        }
    </script>
@endpush
