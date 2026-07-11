@extends('layouts.app')

@push('title')
    Associate Panel | My Tree
@endpush

@php
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

        if ($leftChild) {
            $leftTreeData = $buildTreeData($leftChild);
            if ($leftTreeData) {
                $leftTreeData['side'] = 'left';
                $children[] = $leftTreeData;
            }
        }

        if ($rightChild) {
            $rightTreeData = $buildTreeData($rightChild);
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
    <div class="container-fluid mt-4 transaction-page associate-tree-page">
        <div class="transaction-hero tree-page-header mb-4">
            <div class="tree-page-title">
                <div class="tree-title-icon">
                    <i class="bi bi-diagram-3"></i>
                </div>

                <div>
                    <span class="tree-kicker">Associate Network</span>
                    <h3 class="fw-bold mb-1">My Tree View</h3>
                    <p class="mb-0">View your team hierarchy with compact left and right placement.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('associate-panel.my-tree') }}" class="tree-search-form"
                id="associateTreeFilterForm">
                <div class="tree-filter-field tree-filter-search">
                    <label for="associateTreeSearch">Associate ID</label>
                    <input type="text" id="associateTreeSearch" name="associate_id"
                        value="{{ request('associate_id') }}" class="form-control"
                        placeholder="Search downline associate ID" autocomplete="off">
                </div>

                <div class="tree-filter-field tree-filter-direction">
                    <label for="associateTreeDirection">Direction</label>
                    <select id="associateTreeDirection" name="direction" class="form-select">
                        <option value="">All Directions</option>
                        <option value="left" {{ ($direction ?? request('direction')) === 'left' ? 'selected' : '' }}>Left</option>
                        <option value="right" {{ ($direction ?? request('direction')) === 'right' ? 'selected' : '' }}>Right</option>
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

        <div class="tree-main-card">
            <div class="tree-card-head">
                <div>
                    <h5 class="fw-bold mb-1" id="treeChartTitle">Associate Team</h5>
                    <small class="text-muted" id="treeFilterStatus">Root stays centered. Drag to move the chart and hover over a node for details.</small>
                </div>

                @if ($rootAssociate)
                    <div class="tree-summary-pills">
                        <span><i class="bi bi-eye me-1"></i><strong id="visibleTreeCount">0</strong> Showing</span>
                        <span><i class="bi bi-person-plus me-1"></i>{{ $rootStats['direct_count'] ?? 0 }} Direct</span>
                        <span><i class="bi bi-people me-1"></i>{{ $rootStats['downline_count'] ?? 0 }} Downline</span>
                    </div>
                @endif
            </div>

            @if ($rootAssociate)
                <div class="compact-tree-scroll" id="treeScrollArea">
                    <div class="tree-filter-loader d-none" id="treeFilterLoader">
                        <div class="tree-filter-loader-box">
                            <span class="spinner-border spinner-border-sm text-success" role="status" aria-hidden="true"></span>
                            <span>Filtering tree...</span>
                        </div>
                    </div>

                    <div class="compact-tree-scroll-inner">
                        <div class="compact-tree-export" id="treeExportContainer">
                            <div class="tree-download-heading" id="treeDownloadHeading">
                                <div>
                                    <h4>
                                        Associate Network Tree
                                    </h4>

                                    <p id="treeDownloadFilterText">
                                        Root:
                                        {{ $rootAssociate->associate_name ?? '-' }}
                                        ({{ $rootAssociate->associate_id ?? '-' }})
                                    </p>
                                </div>

                                <span>
                                    Generated:
                                    {{ now()->format('d M Y, h:i A') }}
                                </span>
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
                    <h5 class="fw-bold mb-1">No Associate Found</h5>
                    <p class="text-muted mb-0">Search with your own associate ID or a valid downline associate ID.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@include('treeScript')