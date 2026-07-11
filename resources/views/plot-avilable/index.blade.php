@extends('layouts.app')

@push('title')
    Plot Availability
@endpush
@section('content')
    @php
        $statusCounts = $plots->countBy('current_status');
        $legendItems = [
            ['label' => 'Available', 'key' => 'Available', 'class' => 'available'],
            ['label' => 'Booked', 'key' => 'Booked Plot', 'class' => 'booked'],
            ['label' => 'Hold', 'key' => 'Hold Plot', 'class' => 'hold'],
            ['label' => 'Alloted', 'key' => 'Alloted Plot', 'class' => 'alloted'],
            ['label' => 'Registry', 'key' => 'Registry Plot', 'class' => 'registry'],
        ];
    @endphp

    <div class="container-fluid mt-4 transaction-page plot-availability-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">                                                                   
                    <span class="transaction-icon">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Inventory Map</span>
                        <h3 class="fw-bold mb-1 text-dark">Plot Availability Status</h3>
                        <p class="text-muted mb-0 small">Track project, block and plot availability in real time.</p>
                    </div>
                </div>

                <span class="transaction-count">{{ $plots->count() }} Plots</span>
            </div>
        </div>

        <div class="plot-status-grid mb-4">
            @foreach ($legendItems as $item)
                <div class="plot-status-card plot-status-{{ $item['class'] }}">
                    <span class="plot-status-dot"></span>
                    <div>
                        <small>{{ $item['label'] }}</small>
                        <strong>{{ $statusCounts[$item['key']] ?? 0 }}</strong>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="transaction-card mb-4">
            <div class="transaction-card-body">
                <div class="transaction-section-title">
                    <div class="d-flex align-items-center gap-3">
                        <span class="transaction-section-title-icon">
                            <i class="bi bi-funnel"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-1">Filter Plots</h5>
                            <small class="text-muted">Search plots by project, block or plot number.</small>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('plot-availability.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Project</label>
                            <select name="project_id" class="form-select">
                                <option value="">All Projects</option>
                                @foreach ($projects as $p)
                                    <option value="{{ $p->id }}"
                                        {{ request('project_id') == $p->id ? 'selected' : '' }}> {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Block</label>
                            <select name="block_id" class="form-select">
                                <option value="">All Blocks</option>
                                @foreach ($blocks as $b)
                                    <option value="{{ $b->id }}"
                                        {{ request('block_id') == $b->id ? 'selected' : '' }}> {{ $b->block }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Plot Number</label>
                            <input type="text" name="plot_number" value="{{ request('plot_number') }}"
                                placeholder="Example: B-12" class="form-control">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success flex-fill"><i class="bi bi-search me-1"></i>
                                    Search
                                </button>
                                <a href="{{ route('plot-availability.index') }}" class="btn btn-outline-secondary flex-fill">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="transaction-card mb-4">
            <div class="transaction-history-head">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-section-title-icon"><i class="bi bi-map"></i></span>
                    <div>
                        <h5 class="fw-bold mb-1">Plot Inventory</h5>
                        <small class="text-muted">Hover any plot to view quick details.</small>
                    </div>
                </div>
                <div class="plot-legend">
                    @foreach ($legendItems as $item)
                        <span><i class="plot-status-dot plot-status-{{ $item['class'] }}"></i>{{ $item['label'] }}</span>
                    @endforeach
                </div>
            </div>

            <div class="transaction-card-body pt-0">
                <div class="plot-grid">
                    @forelse ($plots as $plot)
                        @php
                            $map = [
                                'Available' => 'available',
                                'Booked Plot' => 'booked',
                                'Hold Plot' => 'hold',
                                'Alloted Plot' => 'alloted',
                                'Registry Plot' => 'registry',
                            ];
                            $style = $map[$plot->current_status] ?? 'unknown';
                            $popContent =
                                'Project: ' .
                                ($plot->project?->name ?? '-') .
                                '<br>Block: ' .
                                ($plot->block?->block ?? '-') .
                                '<br>Area: ' .
                                ($plot->plot_area ?? 0) .
                                ' Sqft' .
                                '<br>Rate: Rs. ' .
                                number_format((float) ($plot->plot_rate ?? 0), 2);
                        @endphp

                        <button type="button" class="plot-box plot-box-{{ $style }}" data-bs-toggle="popover"
                            data-bs-trigger="hover focus" data-bs-placement="top" data-bs-html="true"
                            data-bs-custom-class="plot-popover" title="Plot #{{ $plot->plot_number }}"
                            data-bs-content="{{ $popContent }}">
                            <span class="plot-number">#{{ $plot->plot_number }}</span>
                            <span class="plot-area"> {{ $plot->plot_area ?? 0 }} Sqft </span>
                            <span class="plot-status-label">{{ str_replace(' Plot', '', $plot->current_status) }}</span>
                        </button>
                    @empty
                        <div class="plot-empty-state"><i class="bi bi-map fs-1 d-block mb-2 text-muted"></i>
                            No plots found for selected filters.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(el) {
                new bootstrap.Popover(el, {
                    container: 'body'
                });
            });
        });
    </script>
@endpush
