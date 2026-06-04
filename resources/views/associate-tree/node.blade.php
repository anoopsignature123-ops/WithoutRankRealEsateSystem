<div class="org-level">

    <div class="node-wrapper">

        <div class="associate-card">

            <div class="avatar-circle">
                <i class="bi bi-person-fill"></i>
            </div>

            <div class="associate-code">
                {{ $associate->associate_id }}
            </div>

            <div class="associate-name">
                {{ $associate->associate_name }}
            </div>

            <div class="associate-rank">
                {{ $associate->rank?->designation ?? 'No Rank' }}
            </div>

        </div>

        <div class="associate-tooltip">

            <div class="tooltip-header">
                <div class="tooltip-title">
                    {{ $associate->associate_name }}
                </div>
                <div class="tooltip-subtitle">
                    {{ $associate->associate_id }}
                </div>
            </div>

            <div class="tooltip-body">

                <div class="info-row">
                    <span>Sponsor ID</span>
                    <strong>{{ $associate->sponsor_id ?? '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Under Place</span>
                    <strong>{{ $associate->under_place_id ?? '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Direct Associate</span>
                    <strong>{{ $associate->direct_count ?? 0 }}</strong>
                </div>

                <div class="info-row">
                    <span>Downline</span>
                    <strong>{{ $associate->downline_count ?? 0 }}</strong>
                </div>

                <div class="info-row">
                    <span>Level</span>
                    <strong>{{ $associate->level ?? 0 }}</strong>
                </div>

                <div class="info-row">
                    <span>Mobile</span>
                    <strong>{{ $associate->mobile_number ?? '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Rank</span>
                    <strong>{{ $associate->rank?->designation ?? '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Joining</span>
                    <strong>{{ $associate->created_at?->format('d-m-Y') ?? '-' }}</strong>
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