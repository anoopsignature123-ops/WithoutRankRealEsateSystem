@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Edit Plot Type</h3>
                        <p class="text-muted mb-0 small">Update plot type information</p>
                    </div>

                    <a href="{{ route('plot-types.index') }}" 
                       class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('plot-types.update', $plotType->id) }}">
                    @csrf
                    @method('PUT')
                    
                    @include('plot-types.form')
                </form>
            </div>
        </div>
    </div>
@endsection