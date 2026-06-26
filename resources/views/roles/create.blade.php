@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 transaction-page role-management-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-shield-plus"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Access Control</span>
                        <h3 class="fw-bold mb-1 text-dark">Add New Role</h3>
                        <p class="text-muted mb-0 small">Define role name and assign module permissions.</p>
                    </div>
                </div>

                <a href="{{ route('roles.index') }}" class="btn btn-outline-success">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Roles
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('roles.store') }}" id="roleForm">
            @csrf

            <div class="transaction-card mb-4">
                <div class="transaction-card-body">
                    <div class="transaction-section-title">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon">
                                <i class="bi bi-person-badge"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold mb-1">Role Details</h5>
                                <small class="text-muted">This name will be used while assigning roles to users.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label fw-semibold">Role Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Example: Manager" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="transaction-card mb-4">
                <div class="transaction-card-body">
                    <div class="transaction-section-title">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon">
                                <i class="bi bi-ui-checks-grid"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold mb-1">Module Permissions</h5>
                                <small class="text-muted">Choose list/modify permissions for each module.</small>
                            </div>
                        </div>
                    </div>

                    <div class="role-permission-stack">
                        @foreach ($modules as $module)
                            <div class="role-module-card">
                                <div class="role-module-head">
                                    <div>
                                        <h6 class="mb-1">{{ $module->name }}</h6>
                                        <small>{{ $module->children->count() > 0 ? $module->children->count() . ' modules' : 'Single module' }}</small>
                                    </div>

                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                        <label class="form-check-label small fw-bold text-muted"
                                            for="all-{{ $module->id }}">Select All</label>
                                        <input type="checkbox" class="form-check-input select-all m-0"
                                            id="all-{{ $module->id }}">
                                    </div>
                                </div>

                                <div class="role-permission-grid">
                                    @php
                                        $items = $module->children->count() > 0 ? $module->children : collect([$module]);
                                    @endphp

                                    @foreach ($items as $item)
                                        <div class="role-permission-item">
                                            <div class="role-permission-title">{{ $item->name }}</div>

                                            @php
                                                $allowedActions = app(App\Services\RoleService::class)->getActions($item->slug);
                                            @endphp

                                            <div class="role-check-row">
                                                @foreach ($allowedActions as $action)
                                                    @php $permissionName = $item->slug . '-' . $action; @endphp
                                                    <label class="role-check">
                                                        <input type="checkbox" class="form-check-input permission-checkbox"
                                                            name="permissions[]" value="{{ $permissionName }}"
                                                            {{ in_array($permissionName, old('permissions', [])) ? 'checked' : '' }}>
                                                        <span>{{ ucfirst($action) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="transaction-action-bar mb-4">
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-success" id="roleSubmitBtn">
                    <span class="btn-label">
                        <i class="bi bi-check-circle me-1"></i>
                        Save Role
                    </span>
                    <span class="btn-loader d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.select-all').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const moduleCard = this.closest('.role-module-card');
                const checkboxes = moduleCard.querySelectorAll('.permission-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        });

        document.getElementById('roleForm')?.addEventListener('submit', function() {
            const button = document.getElementById('roleSubmitBtn');
            button.disabled = true;
            button.querySelector('.btn-label')?.classList.add('d-none');
            button.querySelector('.btn-loader')?.classList.remove('d-none');
        });
    </script>
@endpush
