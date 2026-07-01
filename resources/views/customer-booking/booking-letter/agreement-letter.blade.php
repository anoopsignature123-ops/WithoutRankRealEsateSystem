{{-- agreement-letter.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Agreement Letter</title>

    <style>
        @page {
            size: A4;
            margin: 45px 60px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            display: block;
        }

        .company-sub {
            font-size: 12px;
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }

        .title-box {
            background-color: #000;
            color: #fff;
            font-weight: bold;
            font-size: 13px;
            padding: 4px 0;
            width: 300px;
            margin: 0 auto;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .content {
            text-align: justify;
        }

        p {
            margin-bottom: 12px;
        }

        .center-text {
            text-align: center;
            font-weight: bold;
            margin: 10px 0;
        }

        .bold-heading {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 10px;
        }

        .legal-points {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .legal-points li {
            margin-bottom: 12px;
            text-align: justify;
        }

        .bottom-container {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .bottom-container td {
            vertical-align: top;
            width: 50%;
        }

        .witness-section {
            line-height: 1.6;
        }

        .witness-title {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .witness-block {
            margin-bottom: 15px;
        }

        .signature-section {
            text-align: right;
            padding-right: 10px;
        }

        .sig-block {
            margin-bottom: 50px;
            line-height: 1.4;
        }

        .sig-party {
            font-weight: bold;
        }

        .plot-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 14px 0;
            font-size: 11px;
        }

        .plot-table th,
        .plot-table td {
            border: 1px solid #555;
            padding: 4px 6px;
            text-align: left;
        }

        .plot-table th {
            background: #f1f1f1;
            font-weight: bold;
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

        $customerName = strtoupper($primary?->name ?? '-');
        $relationTitle = strtoupper($primary?->title ?? '');
        $relationName = strtoupper($primary?->relation_name ?? '-');
        $address = $primary?->permanent_address ?? $primary?->address ?? '-';

        $projectName = $project?->name ?? 'Sani Infra Height';
        $blockName = $block?->block ?? '-';
        $plotNumber = $plotSales->pluck('plotDetail.plot_number')->filter()->implode(', ') ?: ($plotDetail?->plot_number ?? '-');
        $plotArea = number_format($plotSales->sum(fn ($sale) => (float) ($sale->plot_area ?? 0)), 2);
        $isMultiplePlot = $plotSales->count() > 1;
    @endphp

    <div class="header">
        <span class="company-name">Sani Infra Height</span>
        <span class="company-sub">Allotment / Agreement Department</span>
        <div class="title-box">Agreement Letter</div>
    </div>

    <div class="content">

        <p>
            This Agreement is executed between M/s <strong>Sani Infra Height</strong>, through its director,
            Sri ........................................................, son of Sri
            ........................................................, resident of
            ........................................................; hereinafter referred to as 'First Party';
        </p>

        <div class="center-text">AND</div>

        <p>
            Sri/Smt. <strong>{{ $customerName }}</strong>,
            {{ $relationTitle ? $relationTitle . ' ' : '' }} Sri
            <strong>{{ $relationName }}</strong>,
            resident of <strong>{{ $address }}</strong>;
            hereinafter referred to as 'Second Party'.
        </p>

        <p>
            WHEREAS, Sri ........................................................, son of Sri
            ........................................................, resident of
            ........................................................ has been authorized to execute this agreement by
            the Resolution, passed in the meeting of its directors held at its registered office on .......... .
        </p>

        <p>
            WHEREAS, the First Party is owner-in-possession of certain land situated at
            <strong>{{ $projectName }}</strong>, Block <strong>{{ $blockName }}</strong>,
            Plot No{{ $isMultiplePlot ? 's' : '' }}. <strong>{{ $plotNumber }}</strong>, total area
            <strong>{{ $plotArea }}</strong> Sq.Ft.,
            and is developing the said project under the brand name
            <strong>{{ $projectName }}</strong>; hereinafter referred to as the 'Project Land'.
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
                            <td>{{ number_format((float) ($sale?->plot_area ?? 0), 2) }} Sq.Ft.</td>
                            <td>Rs. {{ number_format((float) ($sale?->plot_rate ?? 0), 2) }}</td>
                            <td>Rs. {{ number_format((float) ($sale?->total_plot_cost ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <p>
            WHEREAS, the Second Party has experience of Site Develop &amp; Maintenance, development of infrastructure
            &amp; facilities, outdoor landscaping etc. and has required infrastructure to develop these facilities.
        </p>

        <p>
            WHEREAS, the First Party wants to focus on the activity of Land Procurement and Marketing of the aforesaid
            project. Hence-fore they desire to engage services of any professional for 'site development &amp;
            maintenance, development of infrastructure &amp; facilities, outdoor landscaping etc. and hence offered to
            the Second Party to provide aforesaid services. The Second Party accepted the said offer of the First Party
            and both the parties entered into this 'Agreement' on following 'Terms &amp; Conditions'.
        </p>

        <div class="bold-heading">NOW THIS 'AGREEMENT' WITNESSETH AS:</div>

        <ul class="legal-points">
            <li>1. That the First Party desires to focus on the activity of Land Procurement and Marketing of the
                aforesaid project, hence-fore is engaging to the Second Party to obtain their services.</li>

            <li>2. That the Second Party shall develop all the activities as listed in the annexure. The list of the
                activities/jobs can be amended by the parties with mutual consent, from time to time as per the
                requirement of the project.</li>

            <li>3. That the First Party shall provide the possession of the common lands to the Second Party to develop
                facilities and infrastructure. However, wherever required the Second Party shall be responsible for
                taking the properties on lease from the interested plot/farm owners and utilised them for purposes
                related with the development of facilities/infrastructure and other associated activities e.g.
                horticulture/ farming etc.</li>

            <li>4. That the Second Party shall meet be responsible for their commitment to the 3rd Party, whatsoever and
                the First Party shall not be responsible for them.</li>

            <li>5. That charges/expenses for all the activities assigned to the Second Party by the First Party shall be
                paid by the First Party as per the mutual agreed rate/charges. These charges will vary with the change
                of the job responsibility assigned.</li>

            <li>6. That all the work related with the site development e.g. roads, lighting, landscaping etc. shall also
                be handed over to the Sani Infra Height Estate Management Agency.</li>

            <li>7. That, the terms the 'First Party' and the 'Second Party', unless repugnant to the context hereof,
                shall mean and include their legal heirs, executors, administrators, successors, nominee etc.</li>

            <li>8. That the 'Terms &amp; Conditions' of this 'Agreement' as set out hereinafter are out of free will,
                accord, mutual consent and without coercion.</li>

            <li>9. That, this 'Agreement' may be altered, modified, amended or surrendered only by registered
                'Supplementary Agreement'. The 'Supplementary Agreement' shall be part of this 'Agreement'.</li>

            <li>10. That, the terms used in this 'Agreement' shall be inclusive of each other and also include
                modifications of the Lease. Words of any gender used in this Lease shall be held to include any other
                gender, and words in singular shall be held to include the plural and the plural to include the
                singular, when the sense require.</li>

            <li>11. That, any notice or demand desired or required to be given hereunder shall be in writing and deemed
                given when personally delivered or sent through postage prepaid Registered Post/Speed Post and addressed
                as set forth above.</li>

            <li>12. That, if either party brings an action to enforce the terms hereof or declare rights hereunder the
                prevailing party in any such action, on trial or appeal, shall be entitled to his reasonable attorney's
                fees to be paid by the losing party as fixed by the court.</li>

            <li>13. That, in the event of any dispute or difference arising out of or in connection with the 'Terms
                &amp; Conditions' of this 'Agreement', the claims shall be referred to the arbitration of any arbitrator
                as consented to by the both parties. In the event of non-consent between the parties, on the name of
                Arbitrator, the Competent Civil Court at Lucknow shall be entitled to appoint an arbitrator. The
                jurisdiction of dispute, if any, shall be at Lucknow only.</li>

            <li>14. That the First Party shall keep the original 'Agreement' and the Second Party shall keep the
                'Photocopy/Certified Copy' thereof.</li>
        </ul>

        <p style="margin-top: 15px;">
            The First Party and the Second Party have signed this 'Agreement' in the presence of following witnesses.
        </p>

        <p style="margin-bottom: 2px;">Lucknow</p>
        <p style="margin-top: 0px;"><strong>{{ now()->format('d-M-Y') }}</strong></p>

        <table class="bottom-container">
            <tr>
                <td class="witness-section">
                    <div class="witness-title">Witnesses:</div>

                    <div class="witness-block">
                        1.<br>
                        Name:<br>
                        Father's Name:<br>
                        Address:
                    </div>

                    <div class="witness-block">
                        2.<br>
                        Name:<br>
                        Father's Name:<br>
                        Address:
                    </div>
                </td>

                <td class="signature-section">
                    <div class="sig-block">
                        <div>(Sani Infra Height)</div>
                        <div class="sig-party">First Party</div>
                    </div>

                    <div class="sig-block" style="margin-bottom: 0px;">
                        <div>({{ $customerName }})</div>
                        <div class="sig-party">Second Party</div>
                    </div>
                </td>
            </tr>
        </table>

    </div>
</body>

</html>
