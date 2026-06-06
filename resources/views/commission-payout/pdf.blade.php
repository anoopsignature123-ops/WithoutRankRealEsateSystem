<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Commission Ledger Report</title>

    <style>
        @page {
            margin: 18px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #111827;
        }

        .header-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .header-table td {
            border: none;
            padding: 0;
        }

        .title {
            
            font-size: 18px;
            font-weight: bold;
            color: #198754;
            margin-bottom: 3px;
        }

        .subtitle {
            font-size: 10px;
            color: #6b7280;
        }

        .date {
            text-align: right;
            font-size: 10px;
            color: #374151;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .summary-table td {
            border: 1px solid #d1d5db;
            padding: 7px;
            text-align: center;
        }

        .summary-label {
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 3px;
        }

        .summary-value {
            font-size: 12px;
            font-weight: bold;
            color: #111827;
        }

        .summary-green {
            color: #198754;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .report-table th {
            background: #198754;
            color: #ffffff;
            border: 1px solid #157347;
            padding: 5px 4px;
            font-size: 8px;
            text-align: left;
        }

        .report-table td {
            border: 1px solid #d1d5db;
            padding: 5px 4px;
            vertical-align: top;
            font-size: 8px;
            word-wrap: break-word;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .small {
            font-size: 7px;
            color: #6b7280;
        }

        .status-paid {
            color: #198754;
            font-weight: bold;
        }

        .status-pending {
            color: #d97706;
            font-weight: bold;
        }

        .footer-total td {
            background: #f3f4f6;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <table class="header-table">
        <tr>
            <td>
                <div class="title">Commission Ledger Report</div>
                <div class="subtitle">Generated self and team commission records</div>
            </td>
            <td class="date">
                Generated Date<br>
                <strong>{{ now()->format('d M Y h:i A') }}</strong>
            </td>
        </tr>
    </table>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">Total Records</div>
                <div class="summary-value">{{ $commissions->count() }}</div>
            </td>
            <td>
                <div class="summary-label">Total Business</div>
                <div class="summary-value">Rs. {{ number_format($commissions->sum('payment_amount'), 2) }}</div>
            </td>
            <td>
                <div class="summary-label">Total Commission</div>
                <div class="summary-value summary-green">Rs.
                    {{ number_format($commissions->sum('commission_amount'), 2) }}</div>
            </td>
            <td>
                <div class="summary-label">Pending Commission</div>
                <div class="summary-value">Rs.
                    {{ number_format($commissions->where('status', 'pending')->sum('commission_amount'), 2) }}</div>
            </td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width:3%;">#</th>
                <th style="width:9%;">Generated</th>
                <th style="width:11%;">Associate</th>
                <th style="width:11%;">Source</th>
                <th style="width:10%;">Customer</th>
                <th style="width:8%;">Booking</th>
                <th style="width:12%;">Plot Details</th>
                <th style="width:6%;">Type</th>
                <th style="width:8%;" class="text-right">Business</th>
                <th style="width:5%;" class="text-right">%</th>
                <th style="width:9%;" class="text-right">Commission</th>
                <th style="width:8%;">Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse($commissions as $key => $row)
                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>

                    <td>
                        {{ $row->generated_date ? \Carbon\Carbon::parse($row->generated_date)->format('d M Y') : '-' }}
                        <br>
                        <span class="small">
                            {{ $row->generation?->from_date ? \Carbon\Carbon::parse($row->generation->from_date)->format('d M') : '-' }}
                            -
                            {{ $row->generation?->to_date ? \Carbon\Carbon::parse($row->generation->to_date)->format('d M Y') : '-' }}
                        </span>
                    </td>

                    <td>
                        {{ $row->associate?->associate_name ?? '-' }}
                        <br>
                        <span class="small">{{ $row->associate?->associate_id ?? '-' }}</span>
                    </td>

                    <td>
                        {{ $row->sourceAssociate?->associate_name ?? '-' }}
                        <br>
                        <span class="small">{{ $row->sourceAssociate?->associate_id ?? '-' }}</span>
                    </td>

                    <td>{{ $row->customerBooking?->primaryDetail?->name ?? '-' }}</td>

                    <td>{{ $row->customerBooking?->booking_code ?? '-' }}</td>

                    <td>
                        Plot: {{ $row->plotSaleDetail?->plotDetail?->plot_number ?? '-' }}
                        <br>
                        Project: {{ $row->plotSaleDetail?->project?->name ?? '-' }}
                        <br>
                        Block: {{ $row->plotSaleDetail?->block?->block ?? '-' }}
                        <br>
                        Area: {{ $row->plotSaleDetail?->plotDetail?->plot_area ?? '-' }} Sqft
                    </td>

                    <td>{{ ucfirst($row->commission_type ?? '-') }}</td>

                    <td class="text-right">
                        {{ number_format((float) $row->payment_amount, 2) }}
                    </td>

                    <td class="text-right">
                        {{ number_format((float) $row->commission_percent, 2) }}%
                    </td>

                    <td class="text-right">
                        {{ number_format((float) $row->commission_amount, 2) }}
                    </td>

                    <td>
                        @if ($row->status == 'paid')
                            <span class="status-paid">Paid</span>
                        @else
                            <span class="status-pending">Pending</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">No records found</td>
                </tr>
            @endforelse
        </tbody>

        @if ($commissions->count() > 0)
            <tfoot>
                <tr class="footer-total">
                    <td colspan="8" class="text-right">Grand Total</td>
                    <td class="text-right">
                        {{ number_format($commissions->sum('payment_amount'), 2) }}
                    </td>
                    <td></td>
                    <td class="text-right">
                        {{ number_format($commissions->sum('commission_amount'), 2) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>

</html>
