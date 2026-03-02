<!doctype html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
 <meta name="apple-mobile-web-app-title" content="Club Comofra">

{{-- Favicon + iOS icon (versionado anti-cache) --}}
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico?v=20260220') }}">
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico?v=20260220') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/icon-180.png?v=20260220') }}">

  {{-- ===== PWA / Manifest ===== --}}
  <link rel="manifest" href="{{ asset('manifest.json') }}">
 <meta name="theme-color" content="#FF9900">

  <meta name="mobile-web-app-capable" content="yes">

  {{-- iOS (Safari) --}}
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Club-Comofra">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/icon-180.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">

  {{-- Bootstrap CSS + Icons (CDN primero) --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  {{-- Tu CSS/JS (Vite después) --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-light">
  {{ $slot }}

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  {{-- ===== Service Worker ===== --}}
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function () {
        navigator.serviceWorker.register('{{ asset('sw.js') }}');
      });
    }
  </script>
</body>
</html>
