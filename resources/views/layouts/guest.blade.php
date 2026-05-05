<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        document.documentElement.setAttribute('data-theme','dark');document.documentElement.style.colorScheme='dark';
    </script>
    <title>{{ config('app.name', 'FitGo') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
    <link href="{{ asset('css/fitgo-theme.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .fitgo-auth-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 28px 18px calc(28px + env(safe-area-inset-bottom));
            background: var(--dark);
            font-family: var(--ff-body), ui-sans-serif, system-ui, sans-serif;
        }
        .fitgo-auth-brand {
            font-family: var(--ff-head), sans-serif;
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--heading);
            letter-spacing: -0.5px;
            margin-bottom: 22px;
            text-decoration: none;
        }
        .fitgo-auth-brand span { color: var(--g); }
        .fitgo-auth-card {
            width: 100%;
            max-width: 440px;
            background: var(--dark3);
            border: 1px solid var(--border);
            border-radius: var(--r2);
            padding: 28px 30px;
            box-shadow: var(--shadow-soft);
        }
        .fitgo-auth-shell input[type="text"],
        .fitgo-auth-shell input[type="email"],
        .fitgo-auth-shell input[type="password"],
        .fitgo-auth-shell textarea {
            background: var(--dark4) !important;
            border-color: var(--border) !important;
            color: var(--heading) !important;
        }
        .fitgo-auth-shell label {
            color: var(--muted);
        }
        .fitgo-auth-shell .text-gray-600, .fitgo-auth-shell .dark\:text-gray-400 {
            color: var(--muted) !important;
        }
        .fitgo-auth-shell button[type="submit"],
        .fitgo-auth-shell button.inline-flex.items-center {
            background: linear-gradient(135deg, var(--g), var(--g2)) !important;
            color: #fff !important;
            border: none !important;
            border-radius: var(--r3) !important;
            font-family: var(--ff-head), sans-serif !important;
            font-weight: 700 !important;
            text-transform: none !important;
            letter-spacing: 0 !important;
            padding: 11px 22px !important;
            box-shadow: 0 4px 20px rgba(34,197,94,.25);
        }
        .fitgo-auth-shell button[type="submit"]:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="fitgo-auth-page">
        <a href="{{ url('/') }}" class="fitgo-auth-brand">Fit<span>Go</span></a>
        <div class="fitgo-auth-card fitgo-auth-shell">
            {{ $slot }}
        </div>
    </div>
    <script src="{{ asset('js/fitgo-theme.js') }}" defer></script>
</body>
</html>
