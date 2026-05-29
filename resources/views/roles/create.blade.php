@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        {{-- HEADER --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">
                            <i class="bi bi-shield-plus me-2 text-success"></i> Add New Role
                        </h3>
                        <p class="text-muted mb-0 small">Define system roles and configure specific module permissions.</p>
                    </div>
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('roles.store') }}">
            @csrf

            {{-- ROLE NAME --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <label class="fw-bold text-dark mb-2">Role Name</label>
                    <input type="text" name="name" class="form-control form-control-lg"
                        placeholder="e.g. Administrator" required>
                </div>
            </div>

            {{-- PERMISSIONS GRID --}}
            @foreach ($modules as $module)
                <div class="card shadow-sm mb-4 border-0 rounded-4">
                    <div class="card-header bg-white border-bottom p-3 d-flex align-items-center">
                        <h6 class="mb-0 fw-bold text-success text-uppercase flex-grow-1">
                            <i class="bi bi-shield-check me-2"></i>{{ $module->name }}
                        </h6>
                        <div class="form-check form-switch m-0 d-flex align-items-center">
                            <label class="form-check-label small fw-bold text-muted me-2"
                                for="all-{{ $module->id }}">Select All</label>
                            <input type="checkbox" class="form-check-input select-all m-0" id="all-{{ $module->id }}">
                        </div>
                    </div>

                    <div class="card-body bg-light p-4">
                        {{-- Row add ki hai aur align-items-stretch ensure kiya hai --}}
                        <div class="row g-3 align-items-stretch">
                            @php
                                $items = $module->children->count() > 0 ? $module->children : [$module];
                            @endphp

                            @foreach ($items as $item)
                                <div class="col-sm-6 col-md-4 col-lg-3">
                                    <div class="card border-0 shadow-sm h-100 p-3 rounded-3">
                                        <h6 class="fw-bold small mb-3 text-dark border-bottom pb-2">{{ $item->name }}</h6>

                                        @php
                                            $allowedActions = app(App\Services\RoleService::class)->getActions(
                                                $item->slug,
                                            );
                                        @endphp

                                        @foreach ($allowedActions as $action)
                                            @php $permissionName = $item->slug . '-' . $action; @endphp
                                            <div class="form-check mb-1">
                                                <input type="checkbox" class="form-check-input permission-checkbox"
                                                    name="permissions[]" value="{{ $permissionName }}"
                                                    {{ isset($rolePermissions) && in_array($permissionName, $rolePermissions) ? 'checked' : '' }}>
                                                <label
                                                    class="form-check-label small text-muted">{{ ucfirst($action) }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-end py-4">
                <button type="submit" class="btn btn-success btn-lg px-5 rounded-pill shadow-sm">
                    <i class="bi bi-check-circle me-2"></i> Save Role
                </button>
            </div>
        </form>
    </div>
@endsection



@push('scripts')
    <script>
        document.querySelectorAll('.select-all').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                let cardBody = this.closest('.card').querySelector('.card-body');
                let checkboxes = cardBody.querySelectorAll('.permission-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        });
    </script>
@endpush
