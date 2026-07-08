@extends('layouts.app')

@push('title')
    Associate Tree
@endpush

@section('content')
    <div class="container-fluid mt-4 associate-tree-page">
        <div class="tree-page-header mb-4">
            <div class="tree-page-title">
                <div class="tree-title-icon">
                    <i class="bi bi-diagram-3"></i>
                </div>

                <div>
                    <span class="tree-kicker">Associate Network</span>
                    <h3 class="fw-bold mb-1">Associate Tree</h3>
                    <p class="mb-0">View associate hierarchy with left and right placement.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('associate-tree') }}" class="tree-search-form">
                <input type="text" name="associate_id" value="{{ request('associate_id') }}" class="form-control"
                    placeholder="Search associate ID">

                <select name="direction" class="form-select">
                    <option value="">All Directions</option>
                    <option value="left" {{ ($direction ?? request('direction')) === 'left' ? 'selected' : '' }}>
                        Left
                    </option>
                    <option value="right" {{ ($direction ?? request('direction')) === 'right' ? 'selected' : '' }}>
                        Right
                    </option>
                </select>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-search"></i> Show
                </button>

                <a href="{{ route('associate-tree') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </form>
        </div>

        <div class="tree-main-card">
            <div class="tree-card-head">
                <div>
                    <h5 class="fw-bold mb-1">Network Chart</h5>
                    <small class="text-muted">Scroll horizontally or vertically to view complete tree.</small>
                </div>

                @if ($rootAssociate)
                    <div class="tree-summary-pills">
                        <span>{{ $rootAssociate->direct_count ?? 0 }} Direct</span>
                        <span>{{ $rootAssociate->downline_count ?? 0 }} Downline</span>
                    </div>
                @endif
            </div>

            @if ($rootAssociate)
                <div class="tree-scroll-area">
                    <div class="tree-bg-pattern"></div>

                    <div class="org-chart-wrapper">
                        @include('associate-tree.node', [
                            'associate' => $rootAssociate,
                            'isRoot' => true,
                        ])
                    </div>
                </div>
            @else
                <div class="tree-empty-box">
                    <div class="tree-empty-icon">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">No Associate Tree Found</h5>
                    <p class="text-muted mb-0">Please enter a valid associate ID.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
