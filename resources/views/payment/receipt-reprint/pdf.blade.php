<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>
        Payment Receipt
    </title>

    <style>
        body {
            font-family: DejaVu Sans;
            color: #222;
            font-size: 13px;
            margin: 0;
            padding: 25px;
        }

        .receipt-box {
            border: 2px solid #222;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
        }

        .company-tag {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .receipt-title {
            background: #f3f3f3;
            text-align: center;
            padding: 8px;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        .top-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .top-table td {
            padding: 6px 0;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #ccc;
            padding: 10px;
        }

        .detail-table th {
            background: #f8f8f8;
            text-align: left;
        }

        .amount-box {
            margin-top: 20px;
            text-align: right;
            font-size: 18px;
            font-weight: bold;
        }

        .footer {
            margin-top: 60px;
            width: 100%;
        }

        .signature {
            text-align: right;
            margin-top: 50px;
        }

        .signature-line {
            border-top: 1px solid #000;
            display: inline-block;
            width: 180px;
            padding-top: 5px;
            text-align: center;
        }

        .thank-you {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-style: italic;
        }
    </style>

</head>

<body>

    <div class="receipt-box">

        {{-- Header --}}
        <div class="header">

            <div class="company-name">
                SANI INFRA HEIGHT
            </div>

            <div class="company-tag">
                Plot Booking & Property Services
            </div>

        </div>



        {{-- Title --}}
        <div class="receipt-title">

            PAYMENT RECEIPT

        </div>



        {{-- Basic Info --}}
        <table class="top-table">

            <tr>

                <td>
                    <strong>
                        Receipt No:
                    </strong>

                    {{ $payment->receipt_number }}
                </td>

                <td align="right">

                    <strong>
                        Date:
                    </strong>

                    {{ $payment->created_at->format('d-M-Y') }}

                </td>

            </tr>

        </table>



        {{-- Customer Details --}}
        <table class="detail-table">

            <tr>

                <th width="30%">
                    Customer Name
                </th>

                <td>
                    {{ $payment->customerBooking->primaryDetail?->name }}
                </td>

            </tr>

            <tr>

                <th>
                    Customer ID
                </th>

                <td>
                    {{ $payment->customerBooking->customer_code }}
                </td>

            </tr>

            <tr>

                <th>
                    Payment Mode
                </th>

                <td>
                    {{ ucfirst($payment->payment_mode) }}
                </td>

            </tr>

            <tr>

                <th>
                    Booking ID
                </th>

                <td>
                    {{ $payment->customerBooking->booking_code }}
                </td>

            </tr>

        </table>



        {{-- Amount --}}
        <div class="amount-box">

            Amount Paid:
            ₹ {{ number_format($payment->booking_amount, 2) }}

        </div>



        {{-- Signature --}}
        <div class="signature">

            <div class="signature-line">

                Authorized Signature

            </div>

        </div>



        {{-- Footer --}}
        <div class="thank-you">

            Thank you for your payment.

        </div>

    </div>

</body>

</html>
