@push('scripts')
    <script>
        $(document).ready(function() {
            let selectedIds = new Set();
            let dataTable = null;

            function visibleCheckboxes() {
                return $('.payment_checkbox:visible');
            }

            function setLoading(isLoading) {
                const button = $('#updateEmiDateBtn');
                button.prop('disabled', isLoading);
                button.find('.btn-label').toggleClass('d-none', isLoading);
                button.find('.btn-loader').toggleClass('d-none', !isLoading);
            }

            function syncSelectionUi() {
                const ids = Array.from(selectedIds);
                const selectedCount = ids.length;

                $('#payment_ids').val(ids.join(','));
                $('#selected_count, #modal_selected_count').text(selectedCount);
                $('.selected-count').text(`(${selectedCount})`);
                $('#bulk_update_btn').toggleClass('d-none', selectedCount === 0);

                $('.payment_checkbox').each(function() {
                    $(this).prop('checked', selectedIds.has($(this).val()));
                });

                const visible = visibleCheckboxes();
                const visibleChecked = visible.filter(':checked');
                $('#select_all').prop(
                    'checked',
                    visible.length > 0 && visible.length === visibleChecked.length
                );
            }

            $('#select_all').on('change', function() {
                const checked = $(this).is(':checked');

                visibleCheckboxes().each(function() {
                    if (checked) {
                        selectedIds.add($(this).val());
                    } else {
                        selectedIds.delete($(this).val());
                    }
                });

                syncSelectionUi();
            });

            $(document).on('change', '.payment_checkbox', function() {
                if ($(this).is(':checked')) {
                    selectedIds.add($(this).val());
                } else {
                    selectedIds.delete($(this).val());
                }

                syncSelectionUi();
            });

            $('#bulkDateModal').on('show.bs.modal', function(event) {
                syncSelectionUi();

                if (selectedIds.size === 0) {
                    event.preventDefault();
                    toastr.warning('Please select at least one EMI record.');
                }
            });

            $('#updateEmiDateForm').on('submit', function(event) {
                syncSelectionUi();

                if (selectedIds.size === 0) {
                    event.preventDefault();
                    toastr.warning('Please select at least one EMI record.');
                    return;
                }

                setLoading(true);
            });

            if ($('#emiDateTable tbody tr td').attr('colspan') === undefined) {
                dataTable = $('#emiDateTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                    order: [],
                    columnDefs: [
                        { orderable: false, targets: 0 }
                    ],
                    drawCallback: function() {
                        syncSelectionUi();
                    }
                });
            }

            syncSelectionUi();
        });
    </script>
@endpush
