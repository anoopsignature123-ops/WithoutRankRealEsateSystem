@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/tree.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4">

    <div class="team-tree-header mb-4">
        <div class="team-tree-overlay"></div>

        <div class="team-tree-header-content">
            <div class="team-tree-profile">
                <div class="team-tree-avatar">
                    {{ strtoupper(substr(auth()->user()->associate_name ?? 'A', 0, 1)) }}
                </div>

                <div>
                    <span class="team-tree-badge">ACTIVE ASSOCIATE</span>
                    <h4 class="fw-bold mb-1">
                        {{ auth()->user()->associate_name ?? 'Associate' }}
                    </h4>
                    <p class="mb-0">View your complete team hierarchy and downline structure.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('associate-panel.my-tree') }}" class="team-tree-search">
                <input type="text"
                       name="associate_id"
                       value="{{ request('associate_id') }}"
                       class="form-control"
                       placeholder="Enter Associate ID">

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-search"></i>
                    Search
                </button>

                <a href="{{ route('associate-panel.my-tree') }}" class="btn btn-light">
                    <i class="bi bi-arrow-clockwise"></i>
                    Reset
                </a>
            </form>
        </div>
    </div>

    <div class="team-tree-card">
        @if ($rootAssociate)
            <div class="tree-container">
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
                <p class="text-muted mb-0">Please search with a valid associate ID.</p>
            </div>
        @endif
    </div>

</div>
@endsection