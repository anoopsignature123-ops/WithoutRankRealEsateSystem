@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        {{-- Header Card --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Edit Associate Advance</h3>
                        <p class="text-muted mb-0 small">Update existing advance payment details</p>
                    </div>
                    <a href="{{ route('associate-advances.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('associate-advances.update', $advance->id) }}">
                    @csrf
                    @method('PUT')
                    @include('payment.associate-advance.form')
                </form>
            </div>
        </div>
    </div>
@endsection