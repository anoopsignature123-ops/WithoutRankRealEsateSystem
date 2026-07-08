@php
    $treeStats = $associate->tree_stats ?? [];
    $formatTreeAmount = fn ($amount) => 'Rs. ' . number_format((float) $amount, 2);
    $formatTreeArea = fn ($area) => number_format((float) $area, 2) . ' Sqft';
    $isRoot = $isRoot ?? false;

    $treeChildren = $associate->tree_children ?? ['left' => collect(), 'right' => collect()];
    $leftChildren = collect($treeChildren['left'] ?? []);
    $rightChildren = collect($treeChildren['right'] ?? []);
    $childrenCount = $leftChildren->count() + $rightChildren->count();

    $directionClass = strtolower($associate->direction ?? '');
    $directionText = $isRoot ? 'Root' : ucfirst($associate->direction ?? '-');
@endphp

<div class="org-level {{ $isRoot ? 'root' : '' }}">
    <div class="node-wrapper">
        <div class="associate-card {{ $isRoot ? 'root-card' : $directionClass }}">
            <div class="associate-avatar">
                {{ strtoupper(substr($associate->associate_name ?? 'A', 0, 1)) }}
            </div>

            <div class="associate-content">
                <div class="associate-id">{{ $associate->associate_id }}</div>
                <div class="associate-name">{{ $associate->associate_name }}</div>

                <div class="associate-direction-badge {{ $isRoot ? 'root' : $directionClass }}">
                    {{ $directionText }}
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
                    <span>Direction</span>
                    <strong>{{ $directionText }}</strong>
                </div>

                <div class="tooltip-item">
                    <span>Direct Team</span>
                    <strong>{{ $treeStats['direct_count'] ?? 0 }}</strong>
                </div>

                <div class="tooltip-item">
                    <span>Total Downline</span>
                    <strong>{{ $treeStats['downline_count'] ?? 0 }}</strong>
                </div>

                <div class="tooltip-item">
                    <span>Total Business</span>
                    <strong>{{ $formatTreeAmount($treeStats['total_business'] ?? 0) }}</strong>
                </div>

                <div class="tooltip-item">
                    <span>Total Area</span>
                    <strong>{{ $formatTreeArea($treeStats['total_area'] ?? 0) }}</strong>
                </div>

                <div class="tooltip-item">
                    <span>Mobile</span>
                    <strong>{{ $associate->mobile_number ?? '-' }}</strong>
                </div>

                <div class="tooltip-item">
                    <span>Joining</span>
                    <strong>{{ $associate->created_at?->format('d M Y') ?? '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    @if ($childrenCount > 0)
        <div class="vertical-line"></div>

        <div class="binary-children-wrapper">
            <div class="binary-group left-group {{ $leftChildren->isEmpty() ? 'empty-group' : '' }}">
                <div class="binary-label left-label">Left</div>

                @if ($leftChildren->count())
                    <div class="binary-group-children {{ $leftChildren->count() === 1 ? 'single-child' : '' }}">
                        @foreach ($leftChildren as $child)
                            <div class="child-node left">
                                @include('associate-tree.node', [
                                    'associate' => $child,
                                    'isRoot' => false,
                                ])
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="binary-group right-group {{ $rightChildren->isEmpty() ? 'empty-group' : '' }}">
                <div class="binary-label right-label">Right</div>

                @if ($rightChildren->count())
                    <div class="binary-group-children {{ $rightChildren->count() === 1 ? 'single-child' : '' }}">
                        @foreach ($rightChildren as $child)
                            <div class="child-node right">
                                @include('associate-tree.node', [
                                    'associate' => $child,
                                    'isRoot' => false,
                                ])
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>