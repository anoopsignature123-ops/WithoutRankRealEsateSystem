<!DOCTYPE html>
<html>

<head>
    <title>
        Allotement Letter
    </title>

    <link rel="stylesheet" href="{{ asset('assets/css/booking-letter.css') }}">

</head>

<body>

    <div class="letter-box allotement-letter">

        <div class="header">

            <div class="company-name">
                COMPANY NAME
            </div>

            <div class="letter-title">
                Allotement Letter
            </div>

        </div>


        <div class="date-box">

            Date:
            {{ now()->format('d-m-Y') }}

        </div>


        <p>
            Dear
            <strong>
                {{ $booking->primaryDetail?->name }}
            </strong>,
        </p>

        <p>
            We are pleased to confirm the allotment of your plot with following details:
        </p>


        <table class="detail-table">

            <tr>
                <td class="label">Booking ID</td>
                <td>{{ $booking->booking_code }}</td>
            </tr>

            <tr>
                <td class="label">Project</td>
                <td>{{ $booking->plotSaleDetail?->plotDetail?->block?->project?->name }}</td>
            </tr>

            <tr>
                <td class="label">Block</td>
                <td>{{ $booking->plotSaleDetail?->plotDetail?->block?->block }}</td>
            </tr>

            <tr>
                <td class="label">Plot Number</td>
                <td>{{ $booking->plotSaleDetail?->plotDetail?->plot_number }}</td>
            </tr>

            <tr>
                <td class="label">Plot Area</td>
                <td>{{ $booking->plotSaleDetail?->plot_area }}</td>
            </tr>

            <tr>
                <td class="label">Total Cost</td>
                <td>₹{{ $booking->plotSaleDetail?->total_plot_cost }}</td>
            </tr>

        </table>


        <p style="margin-top:30px;">
            This allotement is issued as per company policy and agreed payment terms.
        </p>


        <div class="signature">

            <div class="signature-box">

                <div class="line">
                    Customer Signature
                </div>

            </div>


            <div class="signature-box">

                <div class="line">
                    Authorized Signature
                </div>

            </div>

        </div>

    </div>

</body>

</html>
