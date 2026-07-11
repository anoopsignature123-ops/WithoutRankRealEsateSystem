<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        .header {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            text-decoration: underline;
        }

        .section-title {
            background: #333;
            color: #fff;
            padding: 5px;
            font-weight: bold;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        td {
            border: 1px solid #999;
            padding: 6px;
        }

        .label {
            font-weight: bold;
            width: 25%;
            background: #f9f9f9;
        }

        .doc-img {
            width: 100%;
            height: 120px;
            border: 1px solid #ccc;
            object-fit: cover;
        }

        .doc-label {
            text-align: center;
            font-weight: bold;
            margin-top: 5px;
        }
    </style>
</head>

<body>

    <div class="header">PROSPECT'S INFORMATIONS</div>

    <div class="section-title">PERSONAL INFORMATIONS</div>
    <table>
        <tr>
            <td class="label">AGENT ID</td>
            <td>{{ $associate->associate_id }}</td>
            <td rowspan="4" style="text-align: center; width: 120px;">
                @if ($associate->photo)
                    <img src="{{ public_path('storage/' . $associate->photo) }}" style="width: 80px; height: 90px;">
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">AGENT NAME</td>
            <td>{{ $associate->associate_name }}</td>
        </tr>
        <tr>
            <td class="label">FATHER'S NAME</td>
            <td>{{ $associate->father_name }}</td>
        </tr>
        <tr>
            <td class="label">DOB / GENDER</td>
            <td>{{ $associate->dob }} / {{ $associate->gender }}</td>
        </tr>
    </table>

    <div class="section-title">COMMUNICATION INFORMATION</div>
    <table>
        <tr>
            <td class="label">MOBILE</td>
            <td>{{ $associate->mobile_number }}</td>
            <td class="label">EMAIL</td>
            <td>{{ $associate->email }}</td>
        </tr>
        <tr>
            <td class="label">ADDRESS</td>
            <td colspan="3">{{ $associate->address }}, {{ $associate->cityName?->city ?? $associate->city }}, {{ $associate->stateName?->state ?? $associate->state }}</td>
        </tr>
    </table>

    <div class="section-title">BANK & NOMINEE DETAILS</div>
    <table>
        <tr>
            <td class="label">PAN NO</td>
            <td>{{ $associate->pancard_number }}</td>
            <td class="label">A/C NO</td>
            <td>{{ $associate->bankDetail->account_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">BANK NAME</td>
            <td>{{ $associate->bankDetail->bank_name ?? 'N/A' }}</td>
            <td class="label">IFSC</td>
            <td>{{ $associate->bankDetail->ifsc_code ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">NOMINEE</td>
            <td>{{ $associate->bankDetail->nominee_name ?? 'N/A' }}
                ({{ $associate->bankDetail->nominee_relation ?? '' }})</td>
            <td class="label">AGE</td>
            <td>{{ $associate->bankDetail->nominee_age ?? '' }}</td>
        </tr>
    </table>

    <div class="section-title">VERIFIED DOCUMENTS</div>
    <table style="border: none;">
        <tr>
            <td style="border: none; text-align: center;">
                @if ($associate->pancard_photo)
                    <img src="{{ public_path('storage/' . $associate->pancard_photo) }}" class="doc-img">
                @endif
                <div class="doc-label">PAN CARD</div>
            </td>
            <td style="border: none; text-align: center;">
                @if ($associate->id_proof_photo)
                    <img src="{{ public_path('storage/' . $associate->id_proof_photo) }}" class="doc-img">
                @endif
                <div class="doc-label">ID PROOF</div>
            </td>
            <td style="border: none; text-align: center;">
                @if ($associate->bankDetail?->bank_passbook)
                    <img src="{{ public_path('storage/' . $associate->bankDetail->bank_passbook) }}" class="doc-img">
                @endif
                <div class="doc-label">PASSBOOK</div>
            </td>
        </tr>
    </table>
</body>

</html>
