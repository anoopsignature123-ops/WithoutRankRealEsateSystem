@extends('layouts.app')

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
                    <p class="mb-0">View associate hierarchy, direct team and complete downline structure.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('associate-tree') }}" class="tree-search-form">
                <input type="text" name="associate_id" value="{{ request('associate_id') }}"
                    class="form-control" placeholder="Search associate ID">

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-search"></i>
                    Show
                </button>

                <a href="{{ route('associate-tree') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                    Reset
                </a>
            </form>
        </div>

        <div class="tree-main-card">
            <div class="tree-card-head">
                <div>
                    <h5 class="fw-bold mb-1">Network Chart</h5>
                    <small class="text-muted">Scroll horizontally or vertically to explore the tree.</small>
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
                        ])
                    </div>
                </div>
            @else
                <div class="tree-empty-box">
                    <div class="tree-empty-icon">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">No Associate Tree Found</h5>
                    <p class="text-muted mb-0">Please enter a valid associate ID to view hierarchy.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
