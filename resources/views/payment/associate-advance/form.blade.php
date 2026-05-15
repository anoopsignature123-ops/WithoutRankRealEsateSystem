<div class="row">
    {{-- Associate --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">Associate</label>
        <select name="associate_id" class="form-select">
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

    {{-- Amount --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">Advance Amount</label>
        <input type="number" step="0.01" name="advance_amount" class="form-control"
            placeholder="Enter advance amount"
            value="{{ old('advance_amount', $advance->advance_amount ?? '') }}">
        @error('advance_amount')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Date --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">Advance Date</label>
        <input type="date" name="advance_date" class="form-control"
            value="{{ old('advance_date', isset($advance) ? $advance->advance_date?->format('Y-m-d') : date('Y-m-d')) }}">
        @error('advance_date')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Remarks --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">Remarks</label>
        <textarea name="remarks" rows="1" class="form-control"
            placeholder="Enter remarks...">{{ old('remarks', $advance->remarks ?? '') }}</textarea>
        @error('remarks')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="text-end mt-3">
    <button type="submit" class="btn btn-success px-4">
        <i class="bi bi-check-circle me-1"></i>
        {{ isset($advance) ? 'Update Advance' : 'Save Advance' }}
    </button>
</div>