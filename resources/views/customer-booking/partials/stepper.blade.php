@php
    $totalSteps = 5;
    $progress = ($step / $totalSteps) * 100;

    $steps = [
        1 => ['title' => 'Basic Details', 'icon' => 'bi-person'],
        2 => ['title' => 'Applicant', 'icon' => 'bi-file-earmark-text'],
        3 => ['title' => 'Documents', 'icon' => 'bi-folder2-open'],
        4 => ['title' => 'Plot Details', 'icon' => 'bi-grid'],
        5 => ['title' => 'Payment', 'icon' => 'bi-credit-card'],
    ];
@endphp

<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">

    <div class="card-body p-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

            <div>
                <h5 class="fw-bold mb-1">
                    Customer Booking Process
                </h5>

                <small class="text-muted">
                    Complete all steps to finish booking.
                </small>
            </div>

            <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                Step {{ $step }} of {{ $totalSteps }}
            </span>

        </div>

        <div class="progress rounded-pill mb-4 bg-light" style="height: 8px;">
            <div class="progress-bar bg-success rounded-pill"
                role="progressbar"
                style="width: {{ $progress }}%;"
                aria-valuenow="{{ $progress }}"
                aria-valuemin="0"
                aria-valuemax="100">
            </div>
        </div>

        <div class="row g-3">
            @foreach ($steps as $number => $item)
                @php
                    $isActive = $step == $number;
                    $isCompleted = $step > $number;
                    $canClick = isset($customer);
                @endphp

                <div class="col-xl col-md-4 col-sm-6">

                    <a href="{{ $canClick ? route('customer-booking.edit', [$customer->id, 'step' => $number]) : 'javascript:void(0)' }}"
                        class="text-decoration-none booking-step"
                        data-can-click="{{ $canClick ? 'yes' : 'no' }}">

                        <div class="booking-step-card border rounded-4 p-3 h-100
                            {{ $isActive ? 'active-step' : '' }}
                            {{ $isCompleted ? 'completed-step' : '' }}">

                            <div class="d-flex align-items-center gap-3">

                                <div class="step-icon rounded-circle d-flex align-items-center justify-content-center">
                                    @if ($isCompleted)
                                        <i class="bi bi-check-lg"></i>
                                    @else
                                        <i class="bi {{ $item['icon'] }}"></i>
                                    @endif
                                </div>

                                <div class="flex-grow-1">
                                    <small class="text-muted fw-semibold d-block">
                                        Step {{ $number }}
                                    </small>

                                    <div class="fw-bold step-title">
                                        {{ $item['title'] }}
                                    </div>
                                </div>

                            </div>

                        </div>

                    </a>

                </div>
            @endforeach
        </div>

    </div>
</div>

@push('styles')
    <style>
        .booking-step-card {
            background: #ffffff;
            border-color: #e5e7eb !important;
            transition: all .2s ease;
        }

        .booking-step-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, .06);
        }

        .step-icon {
            width: 42px;
            height: 42px;
            background: #f8fafc;
            color: #64748b;
            font-size: 18px;
            flex-shrink: 0;
        }

        .step-title {
            color: #111827;
            font-size: 14px;
        }

        .booking-step-card.completed-step {
            background: #f8fafc;
            border-color: #198754 !important;
        }

        .booking-step-card.completed-step .step-icon {
            background: #198754;
            color: #ffffff;
        }

        .booking-step-card.active-step {
            background: #ffffff;
            border-color: #198754 !important;
            box-shadow: 0 8px 20px rgba(25, 135, 84, .12);
        }

        .booking-step-card.active-step .step-icon {
            background: #198754;
            color: #ffffff;
        }

        .booking-step-card.active-step .step-title {
            color: #198754;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.booking-step').click(function(e) {
                let canClick = $(this).data('can-click');

                if (canClick === 'no') {
                    e.preventDefault();

                    Swal.fire({
                        icon: 'info',
                        title: 'Complete Current Step',
                        text: 'Please save your current details before moving to the next step.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#198754'
                    });
                }
            });
        });
    </script>
@endpush