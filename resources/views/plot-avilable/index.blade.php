@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h3 class="fw-bold" style="color: #198754;">Plot Availability Status</h3>
                <p class="text-muted">Real-time inventory tracking map</p>
            </div>
            {{-- Status Legend --}}
            <div class="d-flex gap-2 bg-white p-2 rounded-pill shadow-sm border px-3">
                <small class="d-flex align-items-center gap-1"><span class="dot-status bg-available"></span> Available</small>
                <small class="d-flex align-items-center gap-1"><span class="dot-status bg-booked"></span> Booked</small>
                <small class="d-flex align-items-center gap-1"><span class="dot-status bg-hold"></span> Hold</small>
                <small class="d-flex align-items-center gap-1"><span class="dot-status bg-alloted"></span> Alloted</small>
                <small class="d-flex align-items-center gap-1"><span class="dot-status bg-registry"></span> Registry</small>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('plot-availability.index') }}" class="row g-3">
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label fw-bold">Project</label>
                        <select name="project_id" class="form-select">
                            <option value="">Select Project</option>
                            @foreach ($projects as $p)
                                <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label fw-bold">Block</label>
                        <select name="block_id" class="form-select">
                            <option value="">Select Block</option>
                            @foreach ($blocks as $b)
                                <option value="{{ $b->id }}" {{ request('block_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->block }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label fw-bold">Plot Number</label>
                        <input type="text" name="plot_number" value="{{ request('plot_number') }}"
                            placeholder="e.g. B-12" class="form-control">
                    </div>
                    <div class="col-md-12 col-lg-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-success w-100">Search</button>
                        <a href="{{ route('plot-availability.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Grid View --}}
        <div class="row g-3">
            @forelse ($plots as $plot)
                @php
                    // Mapping premium colors
                    $map = [
                        'Available' => 'status-available',
                        'Booked Plot' => 'status-booked',
                        'Hold Plot' => 'status-hold',
                        'Alloted Plot' => 'status-alloted',
                        'Registry Plot' => 'status-registry',
                    ];
                    $style = $map[$plot->current_status] ?? 'border-secondary text-secondary bg-light';
                    $popContent = "Project: {$plot->project?->name} <br> Area: {$plot->plot_area} Sqft <br> Rate: ₹{$plot->plot_rate}";
                @endphp
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="plot-box {{ $style }} d-flex flex-column align-items-center justify-content-center p-2 shadow-sm"
                        data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true"
                        title="Plot #{{ $plot->plot_number }}" data-bs-content="{{ $popContent }}">
                        <span class="fw-bolder h5 mb-1">#{{ $plot->plot_number }}</span>
                        <small class="small font-monospace">{{ $plot->plot_area }} Sqft</small>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="alert alert-light">No plots found.</div>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function(el) {
                return new bootstrap.Popover(el);
            });
        });
    </script>

    <style>
        .dot-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .bg-available {
            background-color: #10b981;
        }

        .bg-booked {
            background-color: #f59e0b;
        }

        .bg-hold {
            background-color: #ef4444;
        }

        .bg-alloted {
            background-color: #3b82f6;
        }

        .bg-registry {
            background-color: #1f2937;
        }

        /* Premium Status Classes */
        .status-available {
            border: 1px solid #10b981;
            color: #065f46;
            background: #ecfdf5;
        }

        .status-available:hover {
            background: #10b981;
            color: white;
        }

        .status-booked {
            border: 1px solid #f59e0b;
            color: #92400e;
            background: #fffbeb;
        }

        .status-booked:hover {
            background: #f59e0b;
            color: white;
        }

        .status-hold {
            border: 1px solid #ef4444;
            color: #991b1b;
            background: #fef2f2;
        }

        .status-hold:hover {
            background: #ef4444;
            color: white;
        }

        .status-alloted {
            border: 1px solid #3b82f6;
            color: #1e40af;
            background: #eff6ff;
        }

        .status-alloted:hover {
            background: #3b82f6;
            color: white;
        }

        .status-registry {
            border: 1px solid #1f2937;
            color: #111827;
            background: #f3f4f6;
        }

        .status-registry:hover {
            background: #1f2937;
            color: white;
        }

        .plot-box {
            height: 90px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .plot-box:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            z-index: 5;
        }

        .plot-box .h5 {
            font-size: 1.1rem;
        }

        .card {
            border-radius: 15px;
        }

        .form-select:focus,
        .form-control:focus {
            border-color: #198754 !important;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
    </style>
@endsection
