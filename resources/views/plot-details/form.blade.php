<div class="row g-4">
    {{-- Project --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">Project Name <span class="text-danger">*</span></label>
        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                style="border: 1px solid #ced4da;">
                <i class="bi bi-layers"></i>
            </span>
            <select name="project_id" id="project_id"
                class="form-select rounded-start-0 @error('project_id') is-invalid @enderror"
                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                <option value="">Select Project</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}"
                        {{ old('project_id', $plotDetail->project_id ?? '') == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @error('project_id')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Location --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">Location</label>
        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                style="border: 1px solid #ced4da;">
                <i class="bi bi-map"></i>
            </span>
            <input type="text" name="location" id="location" readonly class="form-control rounded-start-0"
                placeholder="Project location" value="{{ old('location', $plotDetail->location ?? '') }}"
                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
        </div>
    </div>

    {{-- Number Of Plots --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">Number Of Plots <span
                class="text-danger">*</span></label>
        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                style="border: 1px solid #ced4da;">
                <i class="bi bi-hash"></i>
            </span>
            <input type="number" name="number_of_plots"
                class="form-control rounded-start-0 @error('number_of_plots') is-invalid @enderror"
                placeholder="Enter number of plots"
                value="{{ old('number_of_plots', $plotDetail->number_of_plots ?? '') }}"
                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
        </div>
        @error('number_of_plots')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Block --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">Block <span class="text-danger">*</span></label>
        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                style="border: 1px solid #ced4da;">
                <i class="bi bi-grid-3x3-gap"></i>
            </span>
            <select name="block_id" id="block_id"
                class="form-select rounded-start-0 @error('block_id') is-invalid @enderror"
                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                <option value="">Select Block</option>
            </select>
        </div>
        @error('block_id')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Plot Type --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">Plot Type <span class="text-danger">*</span></label>
        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                style="border: 1px solid #ced4da;">
                <i class="bi bi-tag"></i>
            </span>
            <select name="plot_type_id" id="plot_type_id"
                class="form-select rounded-start-0 @error('plot_type_id') is-invalid @enderror"
                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                <option value="">Select Plot Type</option>
                @foreach ($plotTypes as $type)
                    <option value="{{ $type->id }}"
                        {{ old('plot_type_id', $plotDetail->plot_type_id ?? '') == $type->id ? 'selected' : '' }}>
                        {{ $type->plot_type_name }}
                    </option>
                @endforeach
            </select>
        </div>
        @error('plot_type_id')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Plot Number --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">Plot Number <span class="text-danger">*</span></label>
        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                style="border: 1px solid #ced4da;">
                <i class="bi bi-pin-map"></i>
            </span>
            <input type="text" name="plot_number" id="plot_number"
                class="form-control rounded-start-0 @error('plot_number') is-invalid @enderror"
                placeholder="Enter plot number" value="{{ old('plot_number', $plotDetail->plot_number ?? '') }}"
                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
        </div>
        @error('plot_number')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Plot Range Dynamic Section --}}
    <div id="plotRangeSection" class="col-md-12 p-0" style="display:none;">
        <div class="row g-4 px-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary mb-2">Plot No (From)</label>
                <div class="d-flex align-items-stretch">
                    <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                        style="border: 1px solid #ced4da;">
                        <i class="bi bi-arrow-right-short"></i>
                    </span>
                    <input type="text" name="plot_no_from" class="form-control rounded-start-0"
                        placeholder="Enter starting plot number"
                        value="{{ old('plot_no_from', $plotDetail->plot_no_from ?? '') }}"
                        style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary mb-2">Plot No (To)</label>
                <div class="d-flex align-items-stretch">
                    <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                        style="border: 1px solid #ced4da;">
                        <i class="bi bi-arrow-left-short"></i>
                    </span>
                    <input type="text" name="plot_no_to" class="form-control rounded-start-0"
                        placeholder="Enter ending plot number"
                        value="{{ old('plot_no_to', $plotDetail->plot_no_to ?? '') }}"
                        style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                </div>
            </div>
        </div>
    </div>

    {{-- Plot Rate --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">Plot Rate <span class="text-danger">*</span></label>
        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                style="border: 1px solid #ced4da;">
                <i class="bi bi-currency-rupee"></i>
            </span>
            <input type="number" name="plot_rate" id="plot_rate" class="form-control rounded-start-0"
                placeholder="Enter plot rate" value="{{ old('plot_rate', $plotDetail->plot_rate ?? '') }}"
                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
        </div>
    </div>

    {{-- PLC --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">PLC Rate (%)</label>
        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                style="border: 1px solid #ced4da;">
                <i class="bi bi-percent"></i>
            </span>
            <input type="text" name="plc_rate" id="plc_rate" readonly class="form-control rounded-start-0"
                placeholder="Auto calculated" value="{{ old('plc_rate', $plotDetail->plc_rate ?? '') }}"
                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
        </div>
    </div>

    {{-- Plot Area --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">Plot Area <span class="text-danger">*</span></label>
        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                style="border: 1px solid #ced4da;">
                <i class="bi bi-textarea-resize"></i>
            </span>
            <input type="text" name="plot_area" id="plot_area" class="form-control rounded-start-0"
                placeholder="Example: 1200 sq.ft" value="{{ old('plot_area', $plotDetail->plot_area ?? '') }}"
                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
        </div>
    </div>

    {{-- Status --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">Status <span class="text-danger">*</span></label>
        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                style="border: 1px solid #ced4da;">
                <i class="bi bi-info-circle"></i>
            </span>
            <select name="status" class="form-select rounded-start-0">
                <option value="">Select Status</option>
                <option value="available"
                    {{ old('status', $plotDetail->status ?? '') == 'available' ? 'selected' : '' }}>Available
                </option>
                {{-- <option value="booked" {{ old('status', $plotDetail->status ?? '') == 'booked' ? 'selected' : '' }}>
                    Booked</option>
                <option value="hold" {{ old('status', $plotDetail->status ?? '') == 'hold' ? 'selected' : '' }}>
                    Hold</option>
                <option value="registry"
                    {{ old('status', $plotDetail->status ?? '') == 'registry' ? 'selected' : '' }}>Registry</option> --}}
            </select>
        </div>
    </div>

    {{-- Width + Length Dynamic Section --}}
    <div id="plotDimensionSection" class="col-md-12 p-0" style="display:none;">
        <div class="row g-4 px-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary mb-2">Width (ft)</label>
                <div class="d-flex align-items-stretch">
                    <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                        style="border: 1px solid #ced4da;">
                        <i class="bi bi-arrow-left-right"></i>
                    </span>
                    <input type="text" name="plot_width" class="form-control rounded-start-0"
                        placeholder="Enter width" value="{{ old('plot_width', $plotDetail->plot_width ?? '') }}"
                        style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary mb-2">Length (ft)</label>
                <div class="d-flex align-items-stretch">
                    <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                        style="border: 1px solid #ced4da;">
                        <i class="bi bi-arrow-up-down"></i>
                    </span>
                    <input type="text" name="plot_length" class="form-control rounded-start-0"
                        placeholder="Enter length" value="{{ old('plot_length', $plotDetail->plot_length ?? '') }}"
                        style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Save Button Alignment --}}
<div class="text-end mt-4">
    <button type="submit" class="btn btn-success px-4 fw-semibold shadow-sm py-2">
        <i class="bi bi-check-circle me-1"></i> Save Plot Details
    </button>
</div>

@push('scripts')
    <script>
        $(function() {
            // Project Data Load
            function loadProjectData(projectId, selectedBlock = '') {
                if (projectId == '') {
                    $('#location').val('');
                    $('#block_id').html('<option value="">Select Block</option>');
                    return;
                }
                $.get('/get-project-data/' + projectId, function(response) {
                    $('#location').val(response.location);
                    let html = '<option value="">Select Block</option>';
                    $.each(response.blocks, function(i, block) {
                        let selected = selectedBlock == block.id ? 'selected' : '';
                        html += `<option value="${block.id}" ${selected}>${block.block}</option>`;
                    });
                    $('#block_id').html(html);
                });
            }

            $('#project_id').change(function() {
                loadProjectData($(this).val());
            });

            // Edit Mode Auto-Load
            let projectId = $('#project_id').val();
            if (projectId) {
                loadProjectData(projectId, "{{ old('block_id', $plotDetail->block_id ?? '') }}");
            }

            // PLC Calculation
            function calculatePlc() {
                let rate = parseFloat($('#plot_rate').val()) || 0;
                let plc = (rate * 5) / 100;
                $('#plc_rate').val(plc.toFixed(2));
            }
            $('#plot_rate').on('keyup change', calculatePlc);
            calculatePlc();

            // Plot Type Toggle Logic
            function togglePlotRange() {
                let text = $('#plot_type_id option:selected').text().trim().toLowerCase();
                if (text == 'normal') {
                    $('#plotRangeSection').slideDown();
                    // $('#plot_number').prop('readonly', false);
                } else {
                    $('#plotRangeSection').slideUp();
                    // $('#plot_number').prop('readonly', false);
                }
            }
            $('#plot_type_id').change(togglePlotRange);
            togglePlotRange();

            // Plot Dimension Toggle Logic
            function toggleDimension() {
                let area = $('#plot_area').val().trim();
                if (area != '') {
                    $('#plotDimensionSection').slideDown();
                } else {
                    $('#plotDimensionSection').slideUp();
                }
            }
            $('#plot_area').on('keyup change', toggleDimension);
            toggleDimension();
        });
    </script>
@endpush
