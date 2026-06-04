{{-- @extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">


        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">Customer Booking</h3>
                        <p class="text-muted mb-0 small">Add new customer booking details</p>
                    </div>

                    <a href="{{ route('customer-booking.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>

                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                @include('customer-booking.partials.stepper', ['step' => $step])
                <form method="POST" enctype="multipart/form-data"
                    action="{{ isset($customer) ? route('customer-booking.update', $customer->id) : route('customer-booking.store') }}">
                    @csrf
                    @if (isset($customer))
                        @method('PUT')
                    @endif
                    <input type="hidden" name="step" value="{{ $step }}">
                    @if ($step == 1)
                        @include('customer-booking.partials.step-1')
                    @endif
                    @if ($step == 2)
                        @include('customer-booking.partials.step-2')
                    @endif
                    @if ($step == 3)
                        @include('customer-booking.partials.step-3')
                    @endif
                    @if ($step == 4)
                        @include('customer-booking.partials.step-4')
                    @endif
                    @if ($step == 5)
                        @include('customer-booking.partials.step-5')
                    @endif
                </form>
            </div>
        </div>
    </div>
@endsection --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        {{-- Page Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-4 d-flex align-items-center justify-content-center"
                            style="width:58px;height:58px;">
                            <i class="bi bi-person-plus fs-3"></i>
                        </div>

                        <div>
                            <h3 class="fw-bold mb-1 text-dark">
                                Customer Booking
                            </h3>

                            <p class="text-muted mb-0 small">
                                Add and manage customer booking details step by step.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('customer-booking.index') }}"
                        class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back
                    </a>

                </div>

            </div>
        </div>

        {{-- Booking Form Card --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

            <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">
                            Booking Form
                        </h5>

                        <small class="text-muted">
                            Complete all required steps to finish customer booking.
                        </small>
                    </div>

                    <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                        Step {{ $step }} of 5
                    </span>
                </div>
            </div>

            <div class="card-body p-4">

                @include('customer-booking.partials.stepper', ['step' => $step])

                <form method="POST"
                    enctype="multipart/form-data"
                    action="{{ isset($customer) ? route('customer-booking.update', $customer->id) : route('customer-booking.store') }}">

                    @csrf

                    @if (isset($customer))
                        @method('PUT')
                    @endif

                    <input type="hidden" name="step" value="{{ $step }}">

                    @if ($step == 1)
                        @include('customer-booking.partials.step-1')
                    @endif

                    @if ($step == 2)
                        @include('customer-booking.partials.step-2')
                    @endif

                    @if ($step == 3)
                        @include('customer-booking.partials.step-3')
                    @endif

                    @if ($step == 4)
                        @include('customer-booking.partials.step-4')
                    @endif

                    @if ($step == 5)
                        @include('customer-booking.partials.step-5')
                    @endif

                </form>

            </div>
        </div>

    </div>
@endsection

@push('styles')
    <style>
        .card {
            transition: box-shadow .2s ease;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
        }

        .btn {
            font-weight: 600;
        }
    </style>
@endpush