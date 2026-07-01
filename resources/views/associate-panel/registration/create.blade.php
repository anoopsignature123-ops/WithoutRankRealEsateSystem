@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 transaction-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon"><i class="bi bi-person-plus"></i></span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Associate Registration</span>
                        <h3 class="fw-bold mb-1 text-dark">Add New Associate</h3>
                        <p class="text-muted mb-0 small">Create a new associate under your network with profile, bank and document details.</p>
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

        <form method="POST" action="{{ route('associate-panel.register-store') }}" enctype="multipart/form-data">
            @csrf
            @include('associate-panel.registration.form')
        </form>
    </div>
@endsection
