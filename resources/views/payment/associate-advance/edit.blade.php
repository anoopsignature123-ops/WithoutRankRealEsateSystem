@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Edit Associate Advance</h3>
                <small class="text-muted">Update advance details</small>
            </div>
            <a href="{{ route('admin.associate-advances.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>Back
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.associate-advances.update', $advance->id) }}">
                    @csrf
                    @method('PUT')
                    @include('payment.associate-advance.form')
                </form>
            </div>
        </div>
    </div>
@endsection