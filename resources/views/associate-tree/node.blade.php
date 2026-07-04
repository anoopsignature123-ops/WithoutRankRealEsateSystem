@php
    $treeStats = $associate->tree_stats ?? [];
    $formatTreeAmount = fn ($amount) => 'Rs. '.number_format((float) $amount, 2);
    $formatTreeArea = fn ($area) => number_format((float) $area, 2).' Sqft';
@endphp

<div class="org-level">
    <div class="node-wrapper">
        <div class="associate-card">
            <div class="associate-top"></div>

            <div class="associate-avatar">
                {{ strtoupper(substr($associate->associate_name ?? 'A', 0, 1)) }}
            </div>

            <div class="associate-content">
                <div class="associate-id">{{ $associate->associate_id }}</div>
                <div class="associate-name">{{ $associate->associate_name }}</div>
                <div class="associate-rank">{{ $associate->rank?->designation ?? 'Associate' }}</div>

                <div class="associate-stats">
                    <div>
                        <strong>{{ $treeStats['direct_count'] ?? ($associate->direct_count ?? 0) }}</strong>
                        <span>Direct</span>
                    </div>
                    <div>
                        <strong>{{ $treeStats['downline_count'] ?? ($associate->downline_count ?? 0) }}</strong>
                        <span>Downline</span>
                    </div>
                </div>

                <div class="associate-extra-stats">
                    <div>
                        <span>Total Business</span>
                        <strong>{{ $formatTreeAmount($treeStats['total_business'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Total Area</span>
                        <strong>{{ $formatTreeArea($treeStats['total_area'] ?? 0) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="associate-tooltip">
            <div class="tooltip-header">
                <div class="tooltip-avatar">
                    {{ strtoupper(substr($associate->associate_name ?? 'A', 0, 1)) }}
                </div>

                <div>
                    <div class="tooltip-title">{{ $associate->associate_name }}</div>
                    <div class="tooltip-subtitle">{{ $associate->associate_id }}</div>
                </div>
            </div>

            <div class="tooltip-body">
                <div class="tooltip-item">
                    <span>Sponsor ID</span>
                    <strong>{{ $associate->sponsor_id ?? '-' }}</strong>
                </div>
                <div class="tooltip-item">
                    <span>Under Place</span>
                    <strong>{{ $associate->under_place_id ?? '-' }}</strong>
                </div>
                <div class="tooltip-item">
                    <span>Direct Team</span>
                    <strong>{{ $treeStats['direct_count'] ?? ($associate->direct_count ?? 0) }}</strong>
                </div>
                <div class="tooltip-item">
                    <span>Total Downline</span>
                    <strong>{{ $treeStats['downline_count'] ?? ($associate->downline_count ?? 0) }}</strong>
                </div>
                <div class="tooltip-item">
                    <span>Self Plot Area</span>
                    <strong>{{ $formatTreeArea($treeStats['plot_area'] ?? 0) }}</strong>
                </div>
                <div class="tooltip-item">
                    <span>Team Plot Area</span>
                    <strong>{{ $formatTreeArea($treeStats['team_area'] ?? 0) }}</strong>
                </div>
                <div class="tooltip-item">
                    <span>Total Area</span>
                    <strong>{{ $formatTreeArea($treeStats['total_area'] ?? 0) }}</strong>
                </div>
                <div class="tooltip-item">
                    <span>Self Business</span>
                    <strong>{{ $formatTreeAmount($treeStats['self_business'] ?? 0) }}</strong>
                </div>
                <div class="tooltip-item">
                    <span>Team Business</span>
                    <strong>{{ $formatTreeAmount($treeStats['team_business'] ?? 0) }}</strong>
                </div>
                <div class="tooltip-item">
                    <span>Total Business</span>
                    <strong>{{ $formatTreeAmount($treeStats['total_business'] ?? 0) }}</strong>
                </div>
                <div class="tooltip-item">
                    <span>Level</span>
                    <strong>{{ $associate->level ?? 0 }}</strong>
                </div>
                <div class="tooltip-item">
                    <span>Mobile</span>
                    <strong>{{ $associate->mobile_number ?? '-' }}</strong>
                </div>
                <div class="tooltip-item">
                    <span>Rank</span>
                    <strong>{{ $associate->rank?->designation ?? '-' }}</strong>
                </div>
                <div class="tooltip-item">
                    <span>Joining</span>
                    <strong>{{ $associate->created_at?->format('d M Y') ?? '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    @if ($associate->children->count())
        <div class="vertical-line"></div>

        <div class="children-wrapper">
            @foreach ($associate->children as $child)
                <div class="child-node">
                    @include('associate-tree.node', [
                        'associate' => $child,
                    ])
                </div>
            @endforeach
        </div>
    @endif
</div>
