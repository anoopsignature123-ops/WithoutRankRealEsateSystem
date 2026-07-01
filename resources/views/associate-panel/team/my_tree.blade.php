@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/tree.css') }}">
@endpush

@section('content')
    @php
        $associateUser = auth('associate')->user();
        $displayAssociate = $rootAssociate ?: $associateUser;
    @endphp

    <div class="container-fluid mt-4 transaction-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-diagram-3"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Associate Network</span>
                        <h3 class="fw-bold mb-1 text-dark">My Tree View</h3>
                        <p class="text-muted mb-0 small">View your team hierarchy, direct members and complete downline structure.</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('associate-panel.my-tree') }}" class="tree-search-form">
                    <input type="text" name="associate_id" value="{{ request('associate_id') }}" class="form-control"
                        placeholder="Search downline associate ID">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="{{ route('associate-panel.my-tree') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </form>
            </div>
        </div>

        {{-- <div class="row g-3 mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-success">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Viewing Associate</small>
                        <h5 class="fw-bold mb-0">{{ $displayAssociate?->associate_name ?? '-' }}</h5>
                        <small class="text-muted">{{ $displayAssociate?->associate_id ?? '-' }}</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-info">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Direct Team</small>
                        <h4 class="fw-bold text-info mb-0">{{ $rootAssociate?->direct_count ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="transaction-card h-100 border-start border-4 border-warning">
                    <div class="transaction-card-body py-3">
                        <small class="text-muted fw-semibold">Total Downline</small>
                        <h4 class="fw-bold text-warning mb-0">{{ $rootAssociate?->downline_count ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div> --}}

        <div class="tree-main-card">
            <div class="tree-card-head">
                <div>
                    <h5 class="fw-bold mb-1">Team Hierarchy</h5>
                    <small class="text-muted">Scroll horizontally and vertically to explore the complete tree.</small>
                </div>
                <div class="tree-summary-pills">
                    <span><i class="bi bi-mouse me-1"></i> Hover node for details</span>
                    <span><i class="bi bi-arrows-move me-1"></i> Scroll to explore</span>
                </div>
            </div>

            @if ($rootAssociate)
                <div class="tree-scroll-area">
                    <div class="tree-bg-pattern"></div>
                    <div class="org-chart-wrapper">
                        @include('associate-panel.team.node', [
                            'associate' => $rootAssociate,
                        ])
                    </div>
                </div>
            @else
                <div class="tree-empty-box">
                    <div class="tree-empty-icon">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <h5 class="fw-bold mb-1">No Associate Found</h5>
                    <p class="text-muted mb-0">Search with your own associate ID or a valid downline associate ID.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
