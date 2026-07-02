@php
    $plotSale = $plotSale ?? null;
@endphp

<div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">

    <div class="card-header py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center"
                style="width:48px;height:48px;">
                <i class="bi bi-house-check fs-4"></i>
            </div>

            <div>
                <h5 class="fw-bold mb-0">Plot Sale Details</h5>
                <small class="">
                    Select property, block, available plot and calculate booking amount.
                </small>
            </div>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="alert alert-success bg-success-subtle border-success-subtle text-success rounded-4 mb-4">
            <div class="d-flex align-items-start gap-3">
                <i class="bi bi-info-circle fs-4"></i>
                <div>
                    <h6 class="fw-bold mb-1">Plot Booking Information</h6>
                    <small>
                        Select property and block, then choose an available plot to auto-fill rate, area, PLC and final
                        amount.
                    </small>
                </div>
            </div>
        </div>
        <div class="row g-3">
            <input type="hidden" name="edit_booking_code" id="editBookingCode"
                value="{{ old('edit_booking_code', $plotSale?->booking_code ?? '') }}">
            <input type="hidden" name="edit_plot_sale_detail_id" id="editPlotSaleDetailId"
                value="{{ old('edit_plot_sale_detail_id', '') }}">

            @php
                $bookingGroups = ($plotSales ?? collect())->groupBy(fn($sale) => $sale->booking_code ?? 'UNASSIGNED');
            @endphp

            @if ($bookingGroups->isNotEmpty())
                {{-- Booking groups moved to separate bottom card for cleaner top form --}}
            @endif

            <div class="modal fade" id="plotEditModal" tabindex="-1" aria-labelledby="plotEditModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="plotEditModalLabel">Edit Booked Plot</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Booking Code</label>
                                    <input type="text" id="modalBookingCode" class="form-control bg-light" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Plot Number</label>
                                    <input type="text" id="modalPlotNumber" class="form-control bg-light" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Plot Rate</label>
                                    <input type="text" id="modalPlotRate" class="form-control bg-light" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Plot Area</label>
                                    <input type="text" id="modalPlotArea" class="form-control bg-light" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Plot Cost</label>
                                    <input type="text" id="modalPlotCost" class="form-control bg-light" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">PLC Amount</label>
                                    <input type="text" id="modalPlcAmount" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Dev. Charge</label>
                                    <input type="text" id="modalTotalDevelopmentCharge" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Dev. Rate</label>
                                    <input type="text" id="modalDevelopmentRate" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Other Charges</label>
                                    <input type="text" id="modalOtherCharges" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Coupon Discount</label>
                                    <input type="text" id="modalCouponDiscount" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Final Payable</label>
                                    <input type="text" id="modalFinalPayable" class="form-control" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Total Plot Cost</label>
                                    <input type="text" id="modalTotalPlotCost" class="form-control" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Booking Date</label>
                                    <input type="date" id="modalBookingDate" class="form-control">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Remark</label>
                                    <textarea id="modalRemark" rows="3" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success" id="applyPlotEdit">Apply Changes</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="viewBookingGroupModal" tabindex="-1"
                aria-labelledby="viewBookingGroupModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewBookingGroupModalLabel">Booking Group Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <span class="badge bg-success rounded-pill" id="viewGroupBookingCode"></span>
                                <small class="text-muted d-block mt-2" id="viewGroupBookingDate"></small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted small text-uppercase">
                                            <th>Plot</th>
                                            <th>Project / Block</th>
                                            <th class="text-end">Area</th>
                                            <th class="text-end">Plot Cost</th>
                                            <th class="text-end">PLC</th>
                                            <th class="text-end">Final Payable</th>
                                            <th class="text-end">Total Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody id="viewGroupBookingTableBody"></tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <div class="text-end">
                                    <div class="text-muted small">Group Total</div>
                                    <div class="fw-bold fs-5" id="viewGroupBookingTotal"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Property Name --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Property Name <span class="text-danger">*</span>
                </label>

                <select name="project_id" id="projectId"
                    class="form-select @error('project_id') is-invalid @enderror">
                    <option value="">Select Property</option>

                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}"
                            {{ old('project_id', $plotSale?->project_id) == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>

                @error('project_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Block --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Block <span class="text-danger">*</span>
                </label>

                <select name="block_id" id="blockId" class="form-select @error('block_id') is-invalid @enderror">
                    <option value="">Select Block</option>
                </select>

                @error('block_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Show Plot Button --}}
            <div class="col-md-12">
                <div class="d-flex justify-content-end">
                    <button type="button" id="showPlots"
                        class="btn btn-success rounded-pill px-4 {{ old('block_id', $plotSale?->block_id) ? '' : 'd-none' }}">
                        <i class="bi bi-grid-3x3-gap me-1"></i>
                        Show Available Plots
                    </button>
                </div>
            </div>

            {{-- Dynamic Plot Cards --}}
            <div class="col-md-12">
                <div id="plotListSection"></div>
            </div>

            {{-- Hidden Plot ID --}}
            <input type="hidden" name="plot_detail_id" id="plotId"
                value="{{ old('plot_detail_id', $plotSale?->plot_detail_id) }}">
            <div id="selectedPlotHiddenFields"></div>

            <div class="col-md-12 mt-2">
                <div class="card border-0 bg-light rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 text-success">
                            <i class="bi bi-pin-map me-1"></i>
                            Selected Plot Information
                        </h6>

                        <div class="row g-3">

                            {{-- Plot Number --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Plot Number</label>
                                <input type="text" id="plotNumber" class="form-control bg-white"
                                    name="plot_number" readonly
                                    value="{{ old('plot_number', $plotSale?->plotDetail?->plot_number) }}"
                                    placeholder="Auto selected plot number">
                            </div>

                            {{-- Plot Rate --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Plot Rate</label>
                                <input type="text" name="plot_rate" id="plotRate" class="form-control bg-white"
                                    readonly value="{{ old('plot_rate', $plotSale?->plot_rate) }}"
                                    placeholder="Auto filled plot rate">
                            </div>

                            {{-- Plot Area --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Plot Area (Sq.Ft)</label>
                                <input type="text" name="plot_area" id="plotArea" class="form-control bg-white"
                                    readonly value="{{ old('plot_area', $plotSale?->plot_area) }}"
                                    placeholder="Auto filled plot area">
                            </div>

                            {{-- Plot Cost --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Plot Cost</label>
                                <input type="text" name="plot_cost" id="plotCost" class="form-control bg-white"
                                    readonly value="{{ old('plot_cost', $plotSale?->plot_cost) }}"
                                    placeholder="Auto calculated plot cost">
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mt-2">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 text-success">
                            <i class="bi bi-calculator me-1"></i>
                            Charges & Final Calculation
                        </h6>

                        <div class="row g-3">

                            {{-- Development Charge --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Total Development Charge</label>
                                <input type="text" id="totalDevelopmentCharge" name="total_development_charge"
                                    class="form-control @error('total_development_charge') is-invalid @enderror"
                                    value="{{ old('total_development_charge', $plotSale?->total_development_charge) }}"
                                    placeholder="Enter development charge">

                                @error('total_development_charge')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Development Rate --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Development Rate (Per Sq.Ft)</label>
                                <input type="text" id="developmentRate" name="development_rate"
                                    class="form-control @error('development_rate') is-invalid @enderror"
                                    value="{{ old('development_rate', $plotSale?->development_rate) }}"
                                    placeholder="Enter development rate">

                                @error('development_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- PLC Amount --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">PLC Amount</label>
                                <input type="text" name="plc_amount" id="plcAmount" class="form-control bg-light"
                                    readonly value="{{ old('plc_amount', $plotSale?->plc_amount) }}"
                                    placeholder="Auto calculated PLC amount">
                            </div>

                            {{-- Other Charges --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Other Charges</label>
                                <input type="text" id="otherCharges" name="other_charges"
                                    class="form-control @error('other_charges') is-invalid @enderror"
                                    value="{{ old('other_charges', $plotSale?->other_charges) }}"
                                    placeholder="Enter other charges">

                                @error('other_charges')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Final Payable --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Final Payable</label>
                                <input type="text" name="final_payable" id="finalPayable"
                                    class="form-control bg-light fw-bold text-success" readonly
                                    value="{{ old('final_payable', $plotSale?->final_payable) }}"
                                    placeholder="Auto calculated final amount">
                            </div>

                            {{-- Coupon Discount --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Coupon Discount</label>
                                <input type="text" id="couponDiscount" name="coupon_discount"
                                    class="form-control @error('coupon_discount') is-invalid @enderror"
                                    value="{{ old('coupon_discount', $plotSale?->coupon_discount) }}"
                                    placeholder="Enter coupon discount">

                                @error('coupon_discount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Total Plot Cost --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Total Plot Cost</label>
                                <input type="text" name="total_plot_cost" id="totalPlotCost"
                                    class="form-control bg-light fw-bold text-success" readonly
                                    value="{{ old('total_plot_cost', $plotSale?->total_plot_cost) }}"
                                    placeholder="Auto calculated total amount">
                            </div>

                            {{-- Booking Date --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Booking Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="booking_date"
                                    class="form-control @error('booking_date') is-invalid @enderror"
                                    value="{{ old('booking_date', $plotSale?->booking_date ? \Carbon\Carbon::parse($plotSale->booking_date)->format('Y-m-d') : '') }}">

                                @error('booking_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Remark --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Remark</label>
                                <textarea id="remark" name="remark" rows="3" class="form-control @error('remark') is-invalid @enderror"
                                    placeholder="Enter booking remark">{{ old('remark', $plotSale?->remark) }}</textarea>

                                @error('remark')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <div class="d-flex justify-content-end mt-2 mb-4 m-2">
        <a href="{{ route('customer-booking.edit', [$customer->id, 'step' => 3]) }}"
            class="btn btn-outline-secondary px-4">
            Previous
        </a>

        <button type="submit" class="btn btn-success px-4 ms-2">
            Save & Next
        </button>
    </div>
</div>
@if ($bookingGroups->isNotEmpty())
    <div class="card border shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center"
                        style="width:44px;height:44px;">
                        <i class="bi bi-collection"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Plot Bookings History</h6>
                        <small class="text-muted">Each group keeps a separate booking code for this customer.</small>
                    </div>
                </div>

            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-success">
                        <tr>
                            <th>Booking Code</th>
                            <th>Property</th>
                            <th>Plots</th>
                            <th>Status</th>
                            <th class="text-end"> Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookingGroups as $bookingCode => $group)
                            @php
                                $firstSale = $group->first();
                                $hasPayment = $group->flatMap->payments
                                    ->where('transaction_category', 'booking_fee')
                                    ->isNotEmpty();
                                $plotNumbers = $group->map(fn($sale) => $sale->plotDetail?->plot_number)->filter();
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $bookingCode }}</div>
                                    <small class="text-muted">
                                        {{ $firstSale?->booking_date ? \Carbon\Carbon::parse($firstSale->booking_date)->format('d M Y') : 'Date pending' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $firstSale?->project?->name ?? '-' }}</div>
                                    <small class="text-muted">Block {{ $firstSale?->block?->block ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($plotNumbers as $plotNumber)
                                            <span
                                                class="badge bg-success-subtle text-success border rounded-pill px-2">
                                                {{ $plotNumber }}
                                            </span>
                                        @endforeach
                                    </div>
                                    <small class="text-muted">{{ $group->count() }} plot(s)</small>
                                </td>

                                <td>
                                    @php
                                        $statuses = $group->pluck('status')->unique()->values();
                                        $groupStatus = $statuses->count() === 1 ? $statuses->first() : 'mixed';
                                    @endphp

                                    @if ($groupStatus === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif ($groupStatus === 'cancelled')
                                        <span class="badge bg-danger">Cancelled</span>
                                    @elseif ($groupStatus === 'transferred')
                                        <span class="badge bg-warning text-dark">Transferred</span>
                                    @elseif ($groupStatus === 'changed')
                                        <span class="badge bg-info text-dark">Changed</span>
                                    @else
                                        <span class="badge bg-secondary">Mixed</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-success ">&#8377;
                                    {{ number_format($group->sum('total_plot_cost'), 2) }}</td>


                            </tr>

                            @foreach ($group as $sale)
                                <tr>
                                    <td></td>
                                    <td>
                                        <div class="fw-semibold">{{ $sale->plotDetail?->plot_number ?? '-' }}</div>
                                        <small class="text-muted">
                                            {{ $sale->project?->name ?? '-' }} / Block
                                            {{ $sale->block?->block ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div>{{ number_format((float) $sale->plot_area, 2) }} Sq.Ft.</div>
                                        <small class="text-muted">
                                            Rate &#8377; {{ number_format((float) $sale->plot_rate, 2) }}
                                            | PLC &#8377; {{ number_format((float) $sale->plc_amount, 2) }}
                                        </small>
                                    </td>
                                    <td>
                                        @if ($sale->status === 'active')
                                            <span class="badge bg-success-subtle text-success border">Active</span>
                                        @elseif ($sale->status === 'cancelled')
                                            <span class="badge bg-danger-subtle text-danger border">Cancelled</span>
                                        @elseif ($sale->status === 'transferred')
                                            <span
                                                class="badge bg-warning-subtle text-warning border">Transferred</span>
                                        @elseif ($sale->status === 'changed')
                                            <span class="badge bg-info-subtle text-info border">Changed</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">&#8377;
                                        {{ number_format($sale->total_plot_cost ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
