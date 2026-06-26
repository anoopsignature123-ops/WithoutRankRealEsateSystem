@extends('layouts.app')

@section('content')
    @php
        $photoUrl = $associate->photo ? getFileUrl($associate->photo) : null;
        $documentItems = [
            ['label' => 'Photo', 'path' => $associate->photo, 'icon' => 'bi-person-square'],
            ['label' => 'ID Proof', 'path' => $associate->id_proof_photo, 'icon' => 'bi-card-heading'],
            ['label' => 'PAN Card', 'path' => $associate->pancard_photo, 'icon' => 'bi-credit-card-2-front'],
            ['label' => 'Passbook', 'path' => $associate->bankDetail?->bank_passbook, 'icon' => 'bi-bank'],
        ];
    @endphp

    <div class="container-fluid py-4 associate-view-page">
        <div class="associate-view-hero mb-4">
            <div class="associate-view-profile">
                <div class="associate-view-avatar">
                    @if ($photoUrl)
                        <img src="{{ $photoUrl }}" alt="{{ $associate->associate_name }}">
                    @else
                        {{ strtoupper(substr($associate->associate_name ?? 'A', 0, 1)) }}
                    @endif
                </div>

                <div>
                    <span class="associate-view-kicker">Associate Profile</span>
                    <h3 class="fw-bold mb-1">{{ $associate->associate_name }}</h3>
                    <div class="associate-view-meta">
                        <span>{{ $associate->associate_id }}</span>
                        <span>{{ $associate->rank?->designation ?? 'No Rank' }}</span>
                        <span>{{ $associate->created_at?->format('d M Y') ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="associate-view-actions">
                <a href="{{ route('associate.edit', $associate->id) }}" class="btn btn-success">
                    <i class="bi bi-pencil-square"></i>
                    Edit Associate
                </a>
                <a href="{{ route('associate.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i>
                    Back to List
                </a>
            </div>
        </div>

        <div class="associate-view-stats mb-4">
            <div>
                <span>Sponsor ID</span>
                <strong>{{ $associate->sponsor_id ?? '-' }}</strong>
            </div>
            <div>
                <span>Under Place ID</span>
                <strong>{{ $associate->under_place_id ?? '-' }}</strong>
            </div>
            <div>
                <span>Commission</span>
                <strong>{{ number_format($associate->rank?->commission ?? 0, 2) }}%</strong>
            </div>
            <div>
                <span>Login Password</span>
                <strong>{{ $associate->plain_password ?? '-' }}</strong>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="associate-view-card">
                    <div class="associate-view-card-head">
                        <div class="associate-view-card-icon">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Basic Information</h5>
                            <p class="text-muted mb-0">Personal, sponsor and contact details.</p>
                        </div>
                    </div>

                    <div class="associate-view-grid">
                        <div class="associate-view-field">
                            <span>Associate ID</span>
                            <strong>{{ $associate->associate_id }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>Sponsor</span>
                            <strong>{{ $associate->sponsor?->associate_name ?? '-' }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>Under Place</span>
                            <strong>{{ $associate->underPlace?->associate_name ?? '-' }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>Gender</span>
                            <strong>{{ $associate->gender ? ucfirst($associate->gender) : '-' }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>Father / Husband</span>
                            <strong>{{ $associate->father_name ?? '-' }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>Date of Birth</span>
                            <strong>{{ $associate->dob ? \Carbon\Carbon::parse($associate->dob)->format('d M Y') : '-' }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>Mobile</span>
                            <strong>{{ $associate->mobile_number ?? '-' }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>Email</span>
                            <strong>{{ $associate->email ?? '-' }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>PAN</span>
                            <strong>{{ $associate->pancard_number ?? '-' }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>Aadhaar</span>
                            <strong>{{ $associate->aadhar_number ?? '-' }}</strong>
                        </div>
                        <div class="associate-view-field associate-view-field-wide">
                            <span>Address</span>
                            <strong>{{ $associate->address ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="associate-view-card h-100">
                    <div class="associate-view-card-head">
                        <div class="associate-view-card-icon">
                            <i class="bi bi-person-heart"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Nominee Details</h5>
                            <p class="text-muted mb-0">Nominee and joining information.</p>
                        </div>
                    </div>

                    <div class="associate-view-stack">
                        <div class="associate-view-field">
                            <span>Name</span>
                            <strong>{{ $associate->bankDetail?->nominee_name ?? '-' }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>Relation</span>
                            <strong>{{ $associate->bankDetail?->nominee_relation ?? '-' }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>Age</span>
                            <strong>{{ $associate->bankDetail?->nominee_age ?? '-' }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>Joining Date</span>
                            <strong>{{ $associate->bankDetail?->joining_date?->format('d M Y') ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="associate-view-card h-100">
                    <div class="associate-view-card-head">
                        <div class="associate-view-card-icon">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Bank Details</h5>
                            <p class="text-muted mb-0">Account and IFSC information.</p>
                        </div>
                    </div>

                    <div class="associate-view-grid associate-view-grid-two">
                        <div class="associate-view-field">
                            <span>Bank Name</span>
                            <strong>{{ $associate->bankDetail?->bank_name ?? '-' }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>Account Holder</span>
                            <strong>{{ $associate->bankDetail?->account_holder_name ?? '-' }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>Account Number</span>
                            <strong>{{ $associate->bankDetail?->account_number ?? '-' }}</strong>
                        </div>
                        <div class="associate-view-field">
                            <span>IFSC Code</span>
                            <strong>{{ $associate->bankDetail?->ifsc_code ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="associate-view-card h-100">
                    <div class="associate-view-card-head">
                        <div class="associate-view-card-icon">
                            <i class="bi bi-folder2-open"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Documents</h5>
                            <p class="text-muted mb-0">Uploaded associate documents.</p>
                        </div>
                    </div>

                    <div class="associate-view-documents">
                        @foreach ($documentItems as $document)
                            <div class="associate-view-document">
                                <div class="associate-view-document-icon">
                                    <i class="bi {{ $document['icon'] }}"></i>
                                </div>
                                <div>
                                    <strong>{{ $document['label'] }}</strong>
                                    @if ($document['path'])
                                        <a href="{{ getFileUrl($document['path']) }}" target="_blank">
                                            View File
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @else
                                        <span>Not Uploaded</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
