@extends('layouts.app')

@push('title')
    Associate Tree
@endpush

@php
    /*
    |--------------------------------------------------------------------------
    | Convert Associate Tree Into JavaScript-Friendly Data
    |--------------------------------------------------------------------------
    */

    $buildTreeData = function ($associate, $isRoot = false) use (&$buildTreeData) {
        if (!$associate) {
            return null;
        }

        $treeChildren = $associate->tree_children ?? [
            'left' => collect(),
            'right' => collect(),
        ];

        $leftChild = collect($treeChildren['left'] ?? [])->first();
        $rightChild = collect($treeChildren['right'] ?? [])->first();

        $treeStats = $associate->tree_stats ?? [];
        $children = [];

        /*
        |--------------------------------------------------------------------------
        | Left Child
        |--------------------------------------------------------------------------
        */
        if ($leftChild) {
            $leftTreeData = $buildTreeData($leftChild, false);

            if ($leftTreeData) {
                $leftTreeData['side'] = 'left';
                $children[] = $leftTreeData;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Right Child
        |--------------------------------------------------------------------------
        */
        if ($rightChild) {
            $rightTreeData = $buildTreeData($rightChild, false);

            if ($rightTreeData) {
                $rightTreeData['side'] = 'right';
                $children[] = $rightTreeData;
            }
        }

        $associateName = trim((string) ($associate->associate_name ?? 'Associate'));

        return [
            'id' => (int) $associate->id,
            'associate_id' => $associate->associate_id ?? '-',
            'name' => $associateName,
            'initial' => strtoupper(mb_substr($associateName ?: 'A', 0, 1)),
            'side' => $isRoot ? 'root' : strtolower(trim((string) ($associate->direction ?? 'left'))),
            'sponsor_id' => $associate->sponsor_id ?? '-',
            'under_place_id' => $associate->under_place_id ?? '-',
            'mobile' => $associate->mobile_number ?? '-',
            'joining_date' => $associate->created_at?->format('d M Y') ?? '-',
            'direct_count' => (int) ($treeStats['direct_count'] ?? 0),
            'downline_count' => (int) ($treeStats['downline_count'] ?? 0),
            'left_team_business' => (float) ($treeStats['left_team_business'] ?? 0),
            'right_team_business' => (float) ($treeStats['right_team_business'] ?? 0),
            'left_team_area' => (float) ($treeStats['left_team_area'] ?? 0),
            'right_team_area' => (float) ($treeStats['right_team_area'] ?? 0),
            'self_business' => (float) ($treeStats['self_business'] ?? 0),
            'team_business' => (float) ($treeStats['team_business'] ?? 0),
            'total_business' => (float) ($treeStats['total_business'] ?? 0),
            'self_area' => (float) ($treeStats['plot_area'] ?? 0),
            'team_area' => (float) ($treeStats['team_area'] ?? 0),
            'total_area' => (float) ($treeStats['total_area'] ?? 0),
            'children' => $children,
        ];
    };

    $treeData = $rootAssociate ? $buildTreeData($rootAssociate, true) : null;
    $rootStats = $rootAssociate?->tree_stats ?? [];
@endphp

@section('content')
    <div class="container-fluid mt-4 associate-tree-page">

        {{-- =====================================================
            PAGE HEADER
        ====================================================== --}}
        <div class="tree-page-header mb-4">
            <div class="tree-page-title">
                <div class="tree-title-icon">
                    <i class="bi bi-diagram-3"></i>
                </div>

                <div>
                    <span class="tree-kicker">Associate Network</span>
                    <h3 class="fw-bold mb-1">Associate Tree</h3>
                    <p class="mb-0">View associate hierarchy with compact left and right placement.</p>
                </div>
            </div>

            {{-- =================================================
                REAL-TIME FILTER
            ================================================== --}}
            <form action="javascript:void(0)" class="tree-search-form" id="associateTreeFilterForm" autocomplete="off">
                <div class="tree-filter-field tree-filter-search">
                    <label for="associateTreeSearch">Associate ID / Name</label>
                    <input type="text" id="associateTreeSearch" class="form-control"
                        placeholder="Search associate ID or name" autocomplete="off">
                </div>

                <div class="tree-filter-field tree-filter-direction">
                    <label for="associateTreeDirection">Direction</label>
                    <select id="associateTreeDirection" class="form-select">
                        <option value="">All Directions</option>
                        <option value="left">Left</option>
                        <option value="right">Right</option>
                    </select>
                </div>

                <div class="tree-filter-actions">
                    <button type="button" class="btn btn-success" id="applyTreeFilter">
                        <i class="bi bi-search"></i>
                        Show
                    </button>

                    <button type="button" class="btn btn-outline-secondary" id="resetTreeFilter">
                        <i class="bi bi-arrow-clockwise"></i>
                        Reset
                    </button>

                    @if ($rootAssociate)
                        <button type="button" id="downloadTree" class="btn btn-dark">
                            <i class="bi bi-download"></i>

                            <span id="downloadButtonText">
                                Download Tree
                            </span>

                            <span id="downloadSpinner" class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                        </button>
                    @endif
                </div>
            </form>
        </div>

        {{-- =====================================================
            TREE CARD
        ====================================================== --}}
        <div class="tree-main-card">
            <div class="tree-card-head">
                <div>
                    <h5 class="fw-bold mb-1" id="treeChartTitle">Network Chart</h5>
                    <small class="text-muted fs-7" id="treeFilterStatus">
                        Drag to move the chart and hover over a node for details.
                    </small>
                </div>

                @if ($rootAssociate)
                    <div class="tree-summary-pills">
                        <span>
                            <i class="bi bi-person-plus me-1"></i>
                            <strong id="visibleTreeCount">{{ $rootStats['downline_count'] ?? 0 }}</strong>
                            Visible Associates
                        </span>
                        <span>
                            <i class="bi bi-people me-1"></i>
                            {{ $rootStats['downline_count'] ?? 0 }}
                            Total Downline
                        </span>
                    </div>
                @endif
            </div>

            @if ($rootAssociate)
                <div class="compact-tree-scroll" id="treeScrollArea">
                    <div class="compact-tree-scroll-inner">
                        <div class="compact-tree-export" id="treeExportContainer">
                            <div class="tree-download-heading" id="treeDownloadHeading">
                                <div>
                                    <h4>Associate Network Tree</h4>
                                    <p id="treeDownloadFilterText">
                                        Root: {{ $rootAssociate->associate_name ?? '-' }}
                                        ({{ $rootAssociate->associate_id ?? '-' }})
                                    </p>
                                </div>
                                <span>Generated: {{ now()->format('d M Y, h:i A') }}</span>
                            </div>

                            <svg id="associateTreeSvg" xmlns="http://www.w3.org/2000/svg"></svg>
                        </div>
                    </div>
                </div>

                <div id="treeNodeTooltip" class="compact-tree-tooltip"></div>
            @else
                <div class="tree-empty-box">
                    <div class="tree-empty-icon">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">No Associate Tree Found</h5>
                    <p class="text-muted mb-0">No root associate was found.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@include('treeScript')
