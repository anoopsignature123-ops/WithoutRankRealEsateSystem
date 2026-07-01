<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Allotment Letter</title>

    <style>
        @page {
            size: A4;
            margin: 240px 65px 30px 65px;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .content {
            text-align: justify;
        }

        p {
            margin: 0 0 16px 0;
            text-indent: 0;
        }

        .center-heading {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 16px 0;
        }

        .details-block {
            margin-top: 25px;
            line-height: 1.5;
        }

        .details-block div {
            font-weight: bold;
        }

        .plot-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 16px 0;
            font-size: 12px;
        }

        .plot-table th,
        .plot-table td {
            border: 1px solid #555;
            padding: 5px 6px;
            text-align: left;
        }

        .plot-table th {
            background: #f1f1f1;
            font-weight: bold;
        }

        .signature-table,
        .witness-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table {
            margin-top: 55px;
        }

        .witness-table {
            margin-top: 45px;
        }

        .signature-table td,
        .witness-table td {
            width: 50%;
            vertical-align: top;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .party-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .signatory-info {
            font-size: 14px;
            line-height: 1.3;
        }

        .company-bottom {
            font-weight: bold;
            margin-top: 2px;
        }

        .witness-title {
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>

<body>

    @php
        $primary = $booking->primaryDetail;
        $plotSale = $booking->plotSaleDetail;
        $plotSales = $booking->relationLoaded('plotSaleDetails') && $booking->plotSaleDetails->isNotEmpty()
            ? $booking->plotSaleDetails
            : collect([$plotSale])->filter();
        $plotDetail = $plotSale?->plotDetail;
        $project = $plotSale?->project;
        $block = $plotSale?->block;
        $payment = $booking->payments?->first() ?? $booking->payment;

        $customerName = strtoupper($primary?->name ?? '-');
        $customerAddress = $primary?->permanent_address ?? $primary?->address ?? '-';

        $projectName = strtoupper($project?->name ?? 'SANI INFRA HEIGHT');
        $blockName = $block?->block ?? '-';
        $plotNumber = $plotSales->pluck('plotDetail.plot_number')->filter()->implode(', ') ?: ($plotDetail?->plot_number ?? '-');
        $plotArea = number_format($plotSales->sum(fn ($sale) => (float) ($sale->plot_area ?? 0)), 2);
        $isMultiplePlot = $plotSales->count() > 1;

        $bookingAmountValue = $booking->payments?->sum(fn ($item) => (float) ($item->booking_amount ?? $item->paid_amount ?? 0)) ?? 0;
        $bookingAmount = $bookingAmountValue > 0 ? number_format($bookingAmountValue, 2) . '/-' : '0.00/-';
        $paymentMode = $payment?->payment_mode ? ucwords(str_replace('_', ' ', $payment->payment_mode)) : '-';
        $paymentDate = $payment?->created_at ? $payment->created_at->format('d/m/Y') : '-';

        $letterDate = $booking->created_at ?? now();
    @endphp

    <div class="content">

        <p>
            This PAL is made on the <strong>{{ $letterDate->format('dS') }}</strong> day of
            <strong>{{ $letterDate->format('F, Y') }}</strong>
        </p>

        <div class="center-heading">BETWEEN</div>

        <p>
            <strong>SANI INFRA HEIGHT</strong>, a Partnership firm within the meaning of Sani Infra Height through its
            Authorised Signatory (Here in after called First Party) which expression shall unless it be repugnant to the
            context or meaning there of shall mean and include legal representative, executors and administrators;
        </p>

        <div class="center-heading">AND</div>

        <p>
            <strong>{{ $customerName }}</strong>, resident of
            <strong>{{ $customerAddress }}</strong> (Here in after called Second Party) which expression shall unless it
            be repugnant to the context or meaning there of shall mean and include his/her legal representative,
            executors and administrators and assignee;
        </p>

        <p>
            This PAL between the above said both the parties is as per the Terms &amp; Conditions (Annexure-A) &amp;
            Payment Schedule (Annexure-B) of the attached Booking Form for the said Property Unit
            (Project - <strong>{{ $projectName }}</strong>,
            Block - <strong>{{ $blockName }}</strong>,
            Plot No{{ $isMultiplePlot ? 's' : '' }} - <strong>{{ $plotNumber }}</strong>,
            Total Area <strong>{{ $plotArea }} Sq.ft.</strong>) as per the attached map of the project
            <strong>{{ $projectName }}</strong> of the First Party (SANI INFRA HEIGHT).
        </p>

        @if ($isMultiplePlot)
            <table class="plot-table">
                <thead>
                    <tr>
                        <th>Plot No.</th>
                        <th>Block</th>
                        <th>Area</th>
                        <th>Rate</th>
                        <th>Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($plotSales as $sale)
                        <tr>
                            <td>{{ $sale?->plotDetail?->plot_number ?? '-' }}</td>
                            <td>{{ $sale?->block?->block ?? '-' }}</td>
                            <td>{{ number_format((float) ($sale?->plot_area ?? 0), 2) }} Sq.ft.</td>
                            <td>Rs. {{ number_format((float) ($sale?->plot_rate ?? 0), 2) }}</td>
                            <td>Rs. {{ number_format((float) ($sale?->total_plot_cost ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <p>
            An amount of Rs. <strong>{{ $bookingAmount }}</strong>
            - By <strong>{{ $paymentMode }}</strong> Dated-
            <strong>{{ $paymentDate }}</strong> received as booking amount.
        </p>

        <p>
            Whereas this plot allotment letter (PAL) has been executed on this
            <strong>{{ $letterDate->format('dS') }}</strong> day of
            <strong>{{ $letterDate->format('F, Y') }}</strong> between both the parties will fully and without any
            pressure in the presence of the witness. This PAL is being prepared and signed in the duplicate with a copy
            of the same available with both the parties.
        </p>

        <div class="details-block">
            <div>Place : Lucknow</div>
            <div>Dated: {{ $letterDate->format('dS F Y') }}</div>
        </div>

        <table class="signature-table">
            <tr>
                <td class="text-left">
                    <div class="party-title">FIRST PARTY</div>
                    <div class="signatory-info">
                        <div>(Authorised Signatory)</div>
                        <div class="company-bottom">SANI INFRA HEIGHT</div>
                    </div>
                </td>

                <td class="text-right">
                    <div class="party-title">SECOND PARTY</div>
                    <div class="signatory-info">
                        <div>{{ $customerName }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="witness-table">
            <tr>
                <td class="text-left">
                    <div class="witness-title">(Witness 1)</div>
                </td>

                <td class="text-right">
                    <div class="witness-title">(Witness 2)</div>
                </td>
            </tr>
        </table>

    </div>

</body>

</html>
