<div class="col-lg-4">
    <div class="card border-0 shadow-sm sticky-top" style="top:20px;">
        <div class="card-body p-4">
            <div class="border-bottom pb-3 mb-4">
                <h4 class="fw-bold mb-1 text-dark">Paid Amount Details</h4>
                <small class="text-muted">Payment & Transaction Details</small>
            </div>

            <div class="bg-light rounded-3 p-3 mb-3">
                <small class="text-muted d-block mb-1">Total Cost</small>
                <h4 class="fw-bold text-dark mb-0">₹<span id="total_cost">0.00</span></h4>
            </div>

            <div class="bg-light rounded-3 p-3 mb-3">
                <small class="text-muted d-block mb-1">Total Paid</small>
                <h4 class="fw-bold text-success mb-0">₹<span id="total_paid">0.00</span></h4>
            </div>

            <div class="bg-light rounded-3 p-3 mb-4">
                <small class="text-muted d-block mb-1">Due Amount</small>
                <h4 class="fw-bold text-danger mb-0">₹<span id="due_amount">0.00</span></h4>
            </div>

            <div class="border rounded-3 p-3">
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
        </div>
    </div>
</div>