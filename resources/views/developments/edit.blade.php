@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Edit Development</h3>
                        <p class="text-muted mb-0 small">Update development information</p>
                    </div>
                    <a href="{{ route('developments.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('developments.update', $development->id) }}" method="POST">
                    @csrf @method('PUT')
                    @include('developments.form')
                </form>
            </div>
        </div>
    </div>
@endsection