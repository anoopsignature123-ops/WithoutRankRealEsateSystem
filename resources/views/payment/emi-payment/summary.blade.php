<div class="col-lg-4">
    <div class="card border-0 shadow-sm sticky-top" style="top:20px;">
        <div class="card-body p-4">
            {{-- Heading --}}
            <div class="border-bottom pb-3 mb-4">
                <h4 class="fw-bold mb-1 text-dark">EMI Summary</h4>
                <small class="text-muted">Payment & Installment Details</small>
            </div>

            {{-- Total Plot Cost --}}
            <div class="bg-light rounded-3 p-3 mb-3">
                <small class="text-muted d-block mb-1">Total Plot Cost</small>
                <h4 id="total_cost" class="fw-bold text-dark mb-0">₹0.00</h4>
            </div>

            {{-- Booking Amount --}}
            <div class="bg-light rounded-3 p-3 mb-3">
                <small class="text-muted d-block mb-1">Booking Amount</small>
                <h4 id="booking_amount" class="fw-bold text-primary mb-0">₹0.00</h4>
            </div>

            {{-- EMI Start Date --}}
            <div class="bg-light rounded-3 p-3 mb-3">
                <small class="text-muted d-block mb-1">EMI Start Date</small>
                <h5 id="emi_start_date" class="fw-bold text-secondary mb-0">-</h5>
            </div>

            {{-- EMI Progress --}}
            <div class="bg-light rounded-3 p-3 mb-3">
                <small class="text-muted d-block mb-1">EMI Progress</small>
                <h4 id="emi_months" class="fw-bold text-warning mb-0">0 / 0 Months</h4>
            </div>

            {{-- Monthly EMI --}}
            <div class="bg-light rounded-3 p-3 mb-4">
                <small class="text-muted d-block mb-1">Monthly EMI</small>
                <h4 id="monthly_emi" class="fw-bold text-info mb-0">₹0.00</h4>
            </div>

            {{-- Payment History --}}
            <div class="border rounded-3 p-3 mb-4">
                <h6 class="fw-bold mb-3">Payment History</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt</th>
                                <th>Date</th>
                                <th>Paid</th>
                                <th>Mode</th>
                            </tr>
                        </thead>
                        <tbody id="payment_history">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No Payment Found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Bottom Summary --}}
            <div class="row">
                <div class="col-6">
                    <div class="bg-success bg-opacity-10 rounded-3 p-3">
                        <small class="text-muted d-block">Total Paid</small>
                        <h5 id="total_paid" class="fw-bold text-success mb-0">₹0.00</h5>
                    </div>
                </div>

                <div class="col-6">
                    <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                        <small class="text-muted d-block">Due Amount</small>
                        <h5 id="due_amount" class="fw-bold text-danger mb-0">₹0.00</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>