@php
    $errorCode = $code ?? '404';
    $errorTitle = $title ?? 'Page Not Found!';
    $errorMessage = $message ?? 'The page you are trying to access does not exist or is protected.';
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $errorCode }} | Real Estate CRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/admin.png') }}">
    <style>
        :root {
            --primary: #16a34a;
            --primary-dark: #15803d;
            --bg-dark: #0f172a;
            --text-main: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.7);
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .error-wrap {
            position: relative;
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at 0% 0%, rgba(22, 163, 74, 0.15) 0%, transparent 35%),
                radial-gradient(circle at 100% 100%, rgba(190, 134, 94, 0.1) 0%, transparent 35%),
                linear-gradient(to bottom, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.95)),
                url("{{ asset('assets/images/login_b.jpg') }}") center/cover no-repeat;
        }

        .error-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 600px;
            padding: 40px 20px;
            animation: fadeIn 0.8s ease-out;
        }

        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .error-code {
            font-size: clamp(100px, 20vw, 200px);
            line-height: 1;
            font-weight: 900;
            letter-spacing: -5px;
            background: linear-gradient(to bottom, #fff 30%, rgba(255, 255, 255, 0.1));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
        }

        .error-title {
            margin-top: -10px;
            font-size: clamp(32px, 5vw, 56px);
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -1px;
        }

        .error-message {
            margin: 16px auto 32px;
            color: var(--text-muted);
            font-size: 18px;
            line-height: 1.6;
            font-weight: 400;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 32px;
            border-radius: 12px;
            color: white;
            background: var(--primary);
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(22, 163, 74, 0.3);
        }

        .btn-home:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(22, 163, 74, 0.4);
        }

        .footer {
            position: absolute;
            bottom: 30px;
            left: 0;
            right: 0;
            text-align: center;
            color: var(--text-muted);
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .footer b {
            color: var(--text-main);
        }

        /* Background Decoration */
        .orb {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(22, 163, 74, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 1;
            filter: blur(40px);
        }

        .orb-1 {
            top: -100px;
            right: -100px;
        }

        .orb-2 {
            bottom: -100px;
            left: -100px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .error-title {
                font-size: 32px;
            }
        }
    </style>
</head>

<body>

    <div class="error-wrap">
        <div class="error-content">

            <div class="brand-pill">
                🏢 Real Estate CRM • Secure Zone
            </div>

            <div class="error-code">
                {{ $errorCode }}
            </div>

            <div class="error-title">
                {{ $errorTitle }}
            </div>

            <div class="error-message">
                {{ $errorMessage }}
            </div>

            {{-- <a href="{{ url('/') }}" class="btn-home">
                Return to Dashboard 🏠
            </a> --}}

        </div>

        <div class="footer">
            © {{ date('Y') }} <b>Signature IT Software Designers</b>
            <br>
            Building Digital Townships Since Day One 🚀
        </div>

    </div>

</body>

</html>
