@extends('layouts.app')

@push('title')
    Associate Panel | My Tree
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/tree.css') }}">
@endpush

@section('content')
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
                        <p class="text-muted mb-0 small">
                            View your team hierarchy with left and right placement.
                        </p>
                    </div>
                </div>

                <form method="GET"
                    action="{{ route('associate-panel.my-tree') }}"
                    class="tree-search-form"
                    id="treeFilterForm">

                    <input type="text"
                        name="associate_id"
                        id="associate_id"
                        value="{{ request('associate_id') }}"
                        class="form-control"
                        placeholder="Search downline associate ID">

                    <select name="direction" id="direction" class="form-select">
                        <option value="">All Direction</option>
                        <option value="left" {{ ($direction ?? request('direction')) === 'left' ? 'selected' : '' }}>
                            Left
                        </option>
                        <option value="right" {{ ($direction ?? request('direction')) === 'right' ? 'selected' : '' }}>
                            Right
                        </option>
                    </select>

                    <a href="{{ route('associate-panel.my-tree') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </form>
            </div>
        </div>

        <div class="tree-main-card">
            <div class="tree-card-head">
                <div>
                    <h5 class="fw-bold mb-1">Team Hierarchy</h5>
                    <small class="text-muted">
                        Scroll horizontally and vertically to explore the complete tree.
                    </small>
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
                            'isRoot' => true,
                        ])
                    </div>
                </div>
            @else
                <div class="tree-empty-box">
                    <div class="tree-empty-icon">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <h5 class="fw-bold mb-1">No Associate Found</h5>
                    <p class="text-muted mb-0">
                        Search with your own associate ID or a valid downline associate ID.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let typingTimer;

            $('#direction').on('change', function() {
                $('#treeFilterForm').submit();
            });

            $('#associate_id').on('keyup', function() {
                clearTimeout(typingTimer);

                typingTimer = setTimeout(function() {
                    $('#treeFilterForm').submit();
                }, 500);
            });
        });
    </script>
@endpush