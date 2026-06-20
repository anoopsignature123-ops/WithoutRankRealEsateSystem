@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 associate-advance-page">
        <div class="associate-advance-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="associate-advance-hero-icon">
                        <i class="bi bi-wallet2"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">New Advance</span>
                        <h3 class="fw-bold mb-1 text-dark">Add Associate Advance</h3>
                        <p class="text-muted mb-0 small">Create a new advance payment entry for an associate.</p>
                    </div>
                </div>

                <a href="{{ route('associate-advances.index') }}" class="btn btn-outline-success associate-advance-back">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please check:</strong> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="associate-advance-form-card">
            <form method="POST" action="{{ route('associate-advances.store') }}" id="associateAdvanceForm">
                @csrf
                @include('payment.associate-advance.form')
            </form>
        </div>
    </div>
@endsection
