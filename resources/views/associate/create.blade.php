@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4 associate-form-page">
        <div class="associate-form-hero mb-4">
            <div class="associate-form-hero-title">
                <div class="associate-form-hero-icon">
                    <i class="bi bi-person-plus"></i>
                </div>
                <div>
                    <span class="associate-form-kicker">Associate Management</span>
                    <h3 class="fw-bold mb-1">Add Associate</h3>
                    <p class="mb-0">Create associate profile with sponsor, personal, bank and document details.</p>
                </div>
            </div>

            <a href="{{ route('associate.index') }}" class="btn btn-outline-success associate-form-back">
                <i class="bi bi-arrow-left"></i>
                Back to List
            </a>
        </div>

        <div class="associate-form-shell">
            <form method="POST" action="{{ route('associate.store') }}" enctype="multipart/form-data" id="associateForm">
                <div class="associate-form-body">
                    @csrf

                    @include('associate.form')
                </div>
            </form>
        </div>
    </div>
@endsection
