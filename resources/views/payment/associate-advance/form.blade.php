@php
    $isEdit = isset($advance);
@endphp

<div class="associate-advance-form-head">
    <div class="d-flex align-items-center gap-3">
        <span class="associate-advance-form-icon">
            <i class="bi bi-cash-coin"></i>
        </span>
        <div>
            <h5 class="fw-bold mb-1">{{ $isEdit ? 'Advance Details' : 'New Advance Details' }}</h5>
            <small class="text-muted">Select associate, amount and date carefully before saving.</small>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Associate</label>
        <select name="associate_id" class="form-select associate-advance-select @error('associate_id') is-invalid @enderror">
            <option value="">Select Associate</option>
            @foreach ($associates as $associate)
                <option value="{{ $associate->id }}"
                    {{ old('associate_id', $advance->associate_id ?? '') == $associate->id ? 'selected' : '' }}>
                    {{ $associate->associate_id ?? '-' }} - {{ $associate->associate_name }}
                </option>
            @endforeach
        </select>
        @error('associate_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Advance Amount</label>
        <div class="input-group associate-advance-input-group">
            <span class="input-group-text">&#8377;</span>
            <input type="text" inputmode="decimal" name="advance_amount" id="advanceAmount"
                class="form-control @error('advance_amount') is-invalid @enderror"
                placeholder="Enter advance amount"
                value="{{ old('advance_amount', $advance->advance_amount ?? '') }}">
        </div>
        @error('advance_amount')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Advance Date</label>
        <input type="date" name="advance_date" class="form-control @error('advance_date') is-invalid @enderror"
            value="{{ old('advance_date', isset($advance) ? $advance->advance_date?->format('Y-m-d') : date('Y-m-d')) }}">
        @error('advance_date')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Remarks</label>
        <textarea name="remarks" rows="2" class="form-control @error('remarks') is-invalid @enderror"
            placeholder="Enter remarks...">{{ old('remarks', $advance->remarks ?? '') }}</textarea>
        @error('remarks')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="associate-advance-form-actions">
    <a href="{{ route('associate-advances.index') }}" class="btn btn-light border px-4">Cancel</a>
    <button type="submit" class="btn btn-success px-4" id="associateAdvanceSubmit">
        <span class="btn-label">
            <i class="bi bi-check-circle me-1"></i>
            {{ $isEdit ? 'Update Advance' : 'Save Advance' }}
        </span>
        <span class="btn-loader d-none">
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Saving...
        </span>
    </button>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            function sanitizeAmount(value) {
                value = String(value || '').replace(/[^\d.]/g, '');
                const firstDot = value.indexOf('.');

                if (firstDot !== -1) {
                    value = value.substring(0, firstDot + 1) + value.substring(firstDot + 1).replace(/\./g, '');
                }

                return value;
            }

            $('.associate-advance-select').select2({
                width: '100%'
            });

            $('#advanceAmount').on('input change blur', function() {
                const cleaned = sanitizeAmount($(this).val());
                if ($(this).val() !== cleaned) {
                    $(this).val(cleaned);
                }
            });

            $('#associateAdvanceForm').on('submit', function() {
                $('#advanceAmount').val(sanitizeAmount($('#advanceAmount').val()));
                const button = $('#associateAdvanceSubmit');
                button.prop('disabled', true);
                button.find('.btn-label').addClass('d-none');
                button.find('.btn-loader').removeClass('d-none');
            });
        });
    </script>
@endpush
