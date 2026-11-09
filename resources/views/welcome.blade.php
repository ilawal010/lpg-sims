<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Intisar Oil & Gas | LPG-SIMS</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f8f6;
            color: #173c2b;
            display: flex;
            flex-direction: column;
        }

        .header {
            padding: 24px 6%;
            background: #ffffff;
            border-bottom: 1px solid #e5eee8;
        }

        .brand {
            font-size: 19px;
            font-weight: 750;
            letter-spacing: -0.4px;
            color: #12633d;
        }

        .main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 35px 20px;
        }

        .welcome-card {
            width: 100%;
            max-width: 460px;
            padding: 50px 38px;
            background: #ffffff;
            border: 1px solid #e2ebe5;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 12px 35px rgba(12, 68, 38, 0.05);
        }

        .symbol {
            width: 68px;
            height: 68px;
            margin: 0 auto 25px;
            background: #e9f5ed;
            color: #137343;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .symbol svg {
            width: 34px;
            height: 34px;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 29px;
            font-weight: 750;
            letter-spacing: -0.8px;
        }

        .subtitle {
            font-size: 15px;
            color: #66766d;
            line-height: 1.7;
            margin: 0 0 32px;
        }

        .login-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 15px 20px;
            background: #137343;
            color: white;
            border-radius: 9px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            transition: background 0.2s ease;
        }

        .login-button:hover {
            background: #0d5933;
        }

        .note {
            margin-top: 23px;
            font-size: 12px;
            color: #829087;
        }

        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #85938a;
        }

        @media (max-width: 480px) {
            .welcome-card {
                padding: 38px 24px;
            }

            h1 {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>

    <header class="header">
        <div class="brand">Intisar Oil &amp; Gas</div>
    </header>

    <main class="main">
        <section class="welcome-card">

            <div class="symbol">
                <svg viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.7"
                     stroke-linecap="round"
                     stroke-linejoin="round"
                     aria-hidden="true">
                    <path d="M12 2C9 6 6 9.5 6 14a6 6 0 0 0 12 0c0-4.5-3-8-6-12Z"/>
                    <path d="M10 16a2 2 0 0 0 4 0"/>
                </svg>
            </div>

            <h1>Intisar Oil &amp; Gas</h1>

            <p class="subtitle">
                LPG Sales, Inventory &amp; Management System
            </p>

            @auth
                <a href="{{ route('dashboard') }}"
                   class="login-button">
                    Go to Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="login-button">
                    Staff Login
                </a>
            @endauth

            <div class="note">
                Authorized personnel only
            </div>

        </section>
    </main>

    <footer class="footer">
        &copy; {{ date('Y') }} Intisar Oil &amp; Gas
    </footer>

</body>
</html>
