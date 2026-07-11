@extends('layouts.app')

@push('title')
    Associate Panel | My Profile
@endpush
@section('content')
    @php
        $bank = $associate->bankDetail;
        $initials = collect(explode(' ', trim($associate->associate_name ?? 'Associate')))
            ->filter()
            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
            ->take(2)
            ->implode('');
        $documents = [
            ['label' => 'Profile Photo', 'file' => $associate->photo, 'icon' => 'bi-person-square'],
            ['label' => 'ID Proof', 'file' => $associate->id_proof_photo, 'icon' => 'bi-card-checklist'],
            ['label' => 'PAN Card', 'file' => $associate->pancard_photo, 'icon' => 'bi-credit-card-2-front'],
            ['label' => 'Bank Passbook', 'file' => $bank?->bank_passbook, 'icon' => 'bi-bank'],
        ];
        $uploadedDocuments = collect($documents)->whereNotNull('file')->count();
    @endphp

    <div class="container-fluid mt-4 transaction-page">
        <div class="transaction-hero mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="transaction-icon">
                        @if ($associate->photo)
                            <img src="{{ getFileUrl($associate->photo) }}" alt="Profile"
                                style="width:100%;height:100%;object-fit:cover;border-radius:8px;">
                        @else
                            {{ $initials ?: 'A' }}
                        @endif
                    </span>
                    <div>
                        <span class="text-success fw-bold text-uppercase small">Associate Profile</span>
                        <h3 class="fw-bold mb-1 text-dark">My Profile Details</h3>
                        <p class="text-muted mb-0 small">View your personal, bank, nominee and document details.</p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('associate-panel.edit-profile') }}" class="btn btn-success">
                        <i class="bi bi-pencil-square me-1"></i> Edit Profile
                    </a>
                    <a href="{{ route('associate-panel.change-password') }}" class="btn btn-outline-success">
                        <i class="bi bi-shield-lock me-1"></i> Change Password
                    </a>
                    <a href="{{ route('associate-panel.welcome-letter') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-download me-1"></i> Welcome Letter
                    </a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="transaction-card h-100">
                    <div class="transaction-card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon"><i class="bi bi-person-badge"></i></span>
                            <div>
                                <small class="text-muted fw-semibold">Associate ID</small>
                                <h5 class="fw-bold mb-0">{{ $associate->associate_id ?? '-' }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="transaction-card h-100">
                    <div class="transaction-card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon"><i class="bi bi-diagram-3"></i></span>
                            <div>
                                <small class="text-muted fw-semibold">Sponsor</small>
                                <h5 class="fw-bold mb-0">{{ $associate->sponsor?->associate_name ?? '-' }}</h5>
                                <small class="text-muted">{{ $associate->sponsor_id ?? '-' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="transaction-card h-100">
                    <div class="transaction-card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon">
                                <i class="bi bi-signpost-split"></i>
                            </span>

                            <div>
                                <small class="text-muted fw-semibold">
                                    Direction
                                </small>

                                <h5 class="fw-bold mb-0">
                                    @if ($associate->direction == 'left')
                                        <span
                                            class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                                            <i class="bi bi-arrow-left-circle me-1"></i>
                                            Left
                                        </span>
                                    @elseif($associate->direction == 'right')
                                        <span
                                            class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">
                                            <i class="bi bi-arrow-right-circle me-1"></i>
                                            Right
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                            <i class="bi bi-diagram-3 me-1"></i>
                                            Root
                                        </span>
                                    @endif
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="transaction-card h-100">
                    <div class="transaction-card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="transaction-section-title-icon"><i class="bi bi-folder-check"></i></span>
                            <div>
                                <small class="text-muted fw-semibold">Documents</small>
                                <h5 class="fw-bold mb-0">{{ $uploadedDocuments }} / {{ count($documents) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-6">
                <div class="transaction-card h-100">
                    <div class="transaction-card-body">
                        <div class="transaction-section-title">
                            <div class="d-flex align-items-center gap-3">
                                <span class="transaction-section-title-icon"><i class="bi bi-person-vcard"></i></span>
                                <div>
                                    <h5 class="fw-bold mb-1">Personal Details</h5>
                                    <small class="text-muted">Registered identity and contact information.</small>
                                </div>
                            </div>
                        </div>
                        <div class="customer-receipt-line"><span>Associate
                                Name</span><strong>{{ $associate->associate_name ?? '-' }}</strong></div>
                        <div class="customer-receipt-line">
                            <span>Gender</span><strong>{{ ucfirst($associate->gender ?? '-') }}</strong></div>
                        <div class="customer-receipt-line"><span>Father / Husband
                                Name</span><strong>{{ $associate->father_name ?? '-' }}</strong></div>
                        <div class="customer-receipt-line"><span>Date Of
                                Birth</span><strong>{{ $associate->dob ? \Carbon\Carbon::parse($associate->dob)->format('d M Y') : '-' }}</strong>
                        </div>
                        <div class="customer-receipt-line">
                            <span>Mobile</span><strong>{{ $associate->mobile_number ?? '-' }}</strong></div>
                        <div class="customer-receipt-line">
                            <span>Email</span><strong>{{ $associate->email ?? '-' }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="transaction-card h-100">
                    <div class="transaction-card-body">
                        <div class="transaction-section-title">
                            <div class="d-flex align-items-center gap-3">
                                <span class="transaction-section-title-icon"><i class="bi bi-geo-alt"></i></span>
                                <div>
                                    <h5 class="fw-bold mb-1">Address & KYC</h5>
                                    <small class="text-muted">Address, PAN and Aadhaar details.</small>
                                </div>
                            </div>
                        </div>
                        <div class="customer-receipt-line">
                            <span>Address</span><strong>{{ $associate->address ?? '-' }}</strong></div>
                        <div class="customer-receipt-line"><span>City</span><strong>{{ $associate->cityName?->city ?? $associate->city ?? '-' }}</strong>
                        </div>
                        <div class="customer-receipt-line">
                            <span>State</span><strong>{{ $associate->stateName?->state ?? $associate->state ?? '-' }}</strong></div>
                        <div class="customer-receipt-line"><span>PAN Card
                                No</span><strong>{{ $associate->pancard_number ?? '-' }}</strong></div>
                        <div class="customer-receipt-line"><span>Aadhaar
                                No</span><strong>{{ $associate->aadhar_number ?? '-' }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="transaction-card h-100">
                    <div class="transaction-card-body">
                        <div class="transaction-section-title">
                            <div class="d-flex align-items-center gap-3">
                                <span class="transaction-section-title-icon"><i class="bi bi-bank"></i></span>
                                <div>
                                    <h5 class="fw-bold mb-1">Bank Details</h5>
                                    <small class="text-muted">Payout account information.</small>
                                </div>
                            </div>
                        </div>
                        <div class="customer-receipt-line"><span>Bank
                                Name</span><strong>{{ $bank?->bank_name ?? '-' }}</strong></div>
                        <div class="customer-receipt-line"><span>Account
                                No</span><strong>{{ $bank?->account_number ?? '-' }}</strong></div>
                        <div class="customer-receipt-line"><span>IFSC
                                Code</span><strong>{{ $bank?->ifsc_code ?? '-' }}</strong></div>
                        <div class="customer-receipt-line"><span>Account Holder
                                Name</span><strong>{{ $bank?->account_holder_name ?? '-' }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="transaction-card h-100">
                    <div class="transaction-card-body">
                        <div class="transaction-section-title">
                            <div class="d-flex align-items-center gap-3">
                                <span class="transaction-section-title-icon"><i class="bi bi-person-heart"></i></span>
                                <div>
                                    <h5 class="fw-bold mb-1">Nominee Details</h5>
                                    <small class="text-muted">Nominee information for associate record.</small>
                                </div>
                            </div>
                        </div>
                        <div class="customer-receipt-line"><span>Nominee
                                Name</span><strong>{{ $bank?->nominee_name ?? '-' }}</strong></div>
                        <div class="customer-receipt-line"><span>Nominee
                                Relation</span><strong>{{ $bank?->nominee_relation ?? '-' }}</strong></div>
                        <div class="customer-receipt-line"><span>Nominee
                                Age</span><strong>{{ $bank?->nominee_age ?? '-' }}</strong></div>
                        <div class="customer-receipt-line"><span>Joining
                                Date</span><strong>{{ $associate->created_at?->format('d M Y') ?? '-' }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="transaction-card">
                    <div class="transaction-card-body">
                        <div class="transaction-section-title">
                            <div class="d-flex align-items-center gap-3">
                                <span class="transaction-section-title-icon"><i class="bi bi-folder2-open"></i></span>
                                <div>
                                    <h5 class="fw-bold mb-1">Uploaded Documents</h5>
                                    <small class="text-muted">Preview or open uploaded associate documents.</small>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                {{ $uploadedDocuments }} Uploaded
                            </span>
                        </div>

                        <div class="row g-3">
                            @foreach ($documents as $document)
                                <div class="col-xl-3 col-md-6">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="transaction-section-title-icon">
                                                <i class="bi {{ $document['icon'] }}"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-1">{{ $document['label'] }}</h6>
                                                @if ($document['file'])
                                                    <a href="{{ getFileUrl($document['file']) }}" target="_blank"
                                                        class="btn btn-outline-success btn-sm mt-2">
                                                        <i class="bi bi-eye me-1"></i> View File
                                                    </a>
                                                @else
                                                    <small class="text-muted">Not uploaded</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
