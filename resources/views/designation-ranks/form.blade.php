<div class="row g-4">
    {{-- Designation --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">Designation <span class="text-danger">*</span></label>
        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                style="border: 1px solid #ced4da;">
                <i class="bi bi-briefcase"></i>
            </span>
            <input type="text" name="designation" placeholder="Enter designation"
                class="form-control rounded-start-0 @error('designation') is-invalid @enderror"
                value="{{ old('designation', $designationRank->designation ?? '') }}"
                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
        </div>
        @error('designation')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Rank Number --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">Rank Number <span class="text-danger">*</span></label>
        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                style="border: 1px solid #ced4da;">
                <i class="bi bi-hash"></i>
            </span>
            <input type="number" name="rank_number" placeholder="Enter rank number"
                class="form-control rounded-start-0 @error('rank_number') is-invalid @enderror"
                value="{{ old('rank_number', $designationRank->rank_number ?? '') }}"
                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
        </div>
        @error('rank_number')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Priority --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">Priority <span class="text-danger">*</span></label>
        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                style="border: 1px solid #ced4da;">
                <i class="bi bi-sort-numeric-down"></i>
            </span>
            <input type="number" name="priority" placeholder="Enter priority order"
                class="form-control rounded-start-0 @error('priority') is-invalid @enderror"
                value="{{ old('priority', $designationRank->priority ?? $designationRank->rank_number ?? '') }}"
                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
        </div>
        <small class="text-muted d-block mt-1">Lower priority comes first in rank and promotion order.</small>
        @error('priority')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Commission --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">Commission (%) <span
                class="text-danger">*</span></label>
        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0 rounded-start rounded-0"
                style="border: 1px solid #ced4da;">
                <i class="bi bi-percent"></i>
            </span>
            <input type="number" step="0.01" name="commission" placeholder="Enter commission"
                class="form-control rounded-start-0 @error('commission') is-invalid @enderror"
                value="{{ old('commission', $designationRank->commission ?? '') }}"
                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
        </div>
        @error('commission')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Target From --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">
            Target From <span class="text-danger">*</span>
        </label>

        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0" style="border: 1px solid #ced4da;">
                <i class="bi bi-graph-up-arrow"></i>
            </span>

            <input type="number" name="target_from" placeholder="Enter target from"
                class="form-control rounded-start-0 @error('target_from') is-invalid @enderror"
                value="{{ old('target_from', $designationRank->target_from ?? '') }}">
        </div>

        @error('target_from')
            <div class="invalid-feedback d-block mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{-- Target To --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold text-secondary mb-2">
            Target To <span class="text-danger">*</span>
        </label>

        <div class="d-flex align-items-stretch">
            <span class="input-group-text bg-light text-muted px-3 border-end-0" style="border: 1px solid #ced4da;">
                <i class="bi bi-bullseye"></i>
            </span>

            <input type="number" name="target_to" placeholder="Enter target to"
                class="form-control rounded-start-0 @error('target_to') is-invalid @enderror"
                value="{{ old('target_to', $designationRank->target_to ?? '') }}">
        </div>

        @error('target_to')
            <div class="invalid-feedback d-block mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>

{{-- Form Action Button --}}
<div class="text-end mt-4">
    <button type="submit" class="btn btn-success px-4 fw-semibold shadow-sm py-2">
        <i class="bi bi-check-circle me-1"></i> Save Designation & Rank
    </button>
</div>
