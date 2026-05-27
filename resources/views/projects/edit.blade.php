@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Edit Project</h3>
                        <p class="text-muted mb-0 small">Update project information</p>
                    </div>

                    <a href="{{ route('projects.index') }}" 
                       class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('projects.update', $project->id) }}">
                    @csrf
                    @method('PUT')
                    
                    @include('projects.form')
                </form>
            </div>
        </div>
    </div>
@endsection