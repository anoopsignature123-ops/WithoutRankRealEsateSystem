@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 transaction-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon"><i class="bi bi-person-gear"></i></span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Associate Registration</span>
                        <h3 class="fw-bold mb-1 text-dark">Edit Associate</h3>
                        <p class="text-muted mb-0 small">Update associate profile, bank and document information carefully.</p>
                    </div>
                </div>
                <a href="{{ route('associate-panel.associate-detail') }}" class="btn btn-outline-success">
                    <i class="bi bi-list-check me-1"></i> Associate List
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('associate-panel.associate-update', $associate->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('associate-panel.registration.form')
        </form>
    </div>
@endsection
