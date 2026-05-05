<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <script>
    document.documentElement.setAttribute('data-theme','dark');document.documentElement.style.colorScheme='dark';
  </script>
  <title>@yield('title', 'FitGo')</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet" />
  <link href="{{ asset('css/fitgo-theme.css') }}" rel="stylesheet" />
  @include('layouts.partials.fitgo-shell-styles')
  @stack('head')
</head>
<body>
@include('layouts.partials.fitgo-sidebar')

<main class="main-content">
  <header class="top-header">
    <div class="page-title">@yield('pageTitle', 'FitGo')</div>
    <div class="header-actions">
      @yield('headerActions')
    </div>
  </header>

  <div class="content-area">
    @yield('content')
  </div>
</main>

<button type="button" class="mob-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
  <i class="bi bi-list"></i> Menu
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/fitgo-theme.js') }}" defer></script>
@stack('scripts')
</body>
</html>
