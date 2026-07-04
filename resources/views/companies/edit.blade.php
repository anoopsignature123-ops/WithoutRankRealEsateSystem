@extends('layouts.app')

@push('title')
    Edit Company
@endpush

@section('content')
    <div class="container-fluid mt-4">
        <div class="transaction-hero mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        <i class="bi bi-pencil-square text-success"></i>
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Company Setup</span>
                        <h3 class="fw-bold text-dark mb-1">Edit Company</h3>
                        <p class="text-muted small mb-0">Update company profile and contact information.</p>
                    </div>
                </div>

                <a href="{{ route('company.index') }}" class="btn btn-outline-success rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('company.update', $company->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @include('companies.form')
                </form>
            </div>
        </div>
    </div>
@endsection
