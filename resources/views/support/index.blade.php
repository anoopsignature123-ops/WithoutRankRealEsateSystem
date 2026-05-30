@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        {{-- Top Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted d-block">Total Tickets</small>
                            <h4 class="fw-bold mb-0">{{ $supports->count() }}</h4>
                        </div>
                        <div class="bg-dark bg-opacity-10 text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="bi bi-ticket-detailed"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted d-block">Open Tickets</small>
                            <h4 class="fw-bold text-warning mb-0">{{ $supports->where('status', '!=', 'Resolved')->count() }}</h4>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted d-block">Closed Tickets</small>
                            <h4 class="fw-bold text-success mb-0">{{ $supports->where('status', 'Resolved')->count() }}</h4>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Support Table --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 px-4">
                <h5 class="fw-bold mb-1">Support Tickets</h5>
                <small class="text-muted">Manage all associate support requests</small>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table id="supportTable" class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Associate</th>
                                <th>Query</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supports as $support)
                                <tr>
                                    <td class="fw-semibold">#{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fw-semibold" style="width: 34px; height: 34px; font-size: 12px;">
                                                {{ strtoupper(substr($support->associate->associate_name ?? 'A', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold small">{{ $support->associate->associate_name ?? '-' }}</div>
                                                <small class="text-muted">{{ $support->associate->associate_id ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold small">{{ $support->query }}</div>
                                        <small class="text-muted">{{ Str::limit($support->description, 45) }}</small>
                                    </td>
                                    <td>
                                        @if ($support->status == 'Pending')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill">Pending</span>
                                        @elseif($support->status == 'Resolved')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">Resolved</span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">In-Progress</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small fw-semibold">{{ $support->created_at->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $support->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td class="text-center">
                                        
                                        <a href="{{ route('support.detail', $support->id) }}" class="btn btn-success btn-sm rounded-pill px-3" title="Show Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No support tickets found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#supportTable').DataTable({
                pageLength: 10,
                responsive: true,
                ordering: true,
                language: { search: "_INPUT_", searchPlaceholder: "Search tickets..." }
            });
        });
    </script>
@endpush