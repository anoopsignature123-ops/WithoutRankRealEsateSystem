<!DOCTYPE html>
<html>

<head>
    <title>
        Agreement Letter
    </title>
    <link rel="stylesheet" href="{{ asset('assets/css/booking-letter.css') }}">

</head>

<body>

    <div class="letter-box agreement-letter">

        <div class="header">

            <div class="company-name">
                COMPANY NAME
            </div>

            <div class="letter-title">
                Agreement Letter
            </div>

        </div>


        <p class="agreement-text">

            This agreement is made between
            <strong>COMPANY NAME</strong>
            and

            <strong>
                {{ $booking->primaryDetail?->name }}
            </strong>

            for plot booking under Booking ID

            <strong>
                {{ $booking->booking_code }}
            </strong>.

            The customer agrees to all company rules,
            payment terms, registration terms,
            possession terms and legal conditions.

        </p>


        <p class="agreement-text">

            Plot Details:

            <br><br>

            Project:
            <strong>
                {{ $booking->plotSaleDetail?->plotDetail?->block?->project?->name }}
            </strong>

            <br>

            Block:
            <strong>
                {{ $booking->plotSaleDetail?->plotDetail?->block?->block }}
            </strong>

            <br>

            Plot Number:
            <strong>
                {{ $booking->plotSaleDetail?->plotDetail?->plot_number }}
            </strong>

            <br>

            Plan Type:
            <strong>
                {{ $booking->payment?->plan_type }}
            </strong>

        </p>


        <div class="signature">

            <div class="signature-box">

                <div class="line">
                    Customer Signature
                </div>

            </div>


            <div class="signature-box">

                <div class="line">
                    Company Signature
                </div>

            </div>

        </div>

    </div>

</body>

</html>
