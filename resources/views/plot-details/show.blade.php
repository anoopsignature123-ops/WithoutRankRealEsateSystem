@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Plot Details</h3>
                        <p class="text-muted mb-0 small">View detailed information for this plot</p>
                    </div>

                    <a href="{{ route('plot-details.index') }}" 
                       class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        {{-- Details Card --}}
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="row g-4">
                    @php
                        $fields = [
                            'Project Name' => $plotDetail->project->name ?? 'N/A',
                            'Location' => $plotDetail->location ?? 'N/A',
                            'Number Of Plots' => $plotDetail->number_of_plots ?? 'N/A',
                            'Block' => $plotDetail->block->block ?? 'N/A',
                            'Plot Type' => $plotDetail->plotType->plot_type_name ?? 'N/A',
                            'Plot Number' => $plotDetail->plot_number ?? 'N/A',
                            'Plot No (From)' => $plotDetail->plot_no_from ?? 'N/A',
                            'Plot No (To)' => $plotDetail->plot_no_to ?? 'N/A',
                            'Plot Rate' => $plotDetail->plot_rate ?? 'N/A',
                            'PLC Rate' => $plotDetail->plc_rate ?? 'N/A',
                            'Plot Area' => $plotDetail->plot_area ?? 'N/A',
                            'Width (ft)' => $plotDetail->plot_width ?? 'N/A',
                            'Length (ft)' => $plotDetail->plot_length ?? 'N/A',
                            'Status' => ucfirst($plotDetail->status ?? 'N/A'),
                        ];
                    @endphp

                    @foreach($fields as $label => $value)
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">{{ $label }}</label>
                            <div class="p-2 bg-light rounded border">
                                <span class="fw-semibold text-dark">{{ $value }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection