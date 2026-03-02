<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','Club Comofra')</title>

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
  <meta name="apple-mobile-web-app-title" content="Club Comofra">
  <meta name="apple-touch-fullscreen" content="yes">

  {{-- Icons --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  {{-- Vite --}}
  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('styles')

  {{-- ✅ Tip iPhone --}}
  <style>
    .ios-a2hs-tip{
      position: fixed;
      left: 14px;
      right: 14px;
      bottom: 14px;
      z-index: 2000;
      pointer-events: none;
      opacity: 0;
      transform: translateY(10px);
      transition: opacity .25s ease, transform .25s ease;
    }
    .ios-a2hs-tip.is-show{ opacity: 1; transform: translateY(0); }
    .ios-a2hs-card{
      pointer-events: auto;
      background: rgba(255,255,255,.96);
      border: 1px solid rgba(15,23,42,.10);
      border-radius: 16px;
      box-shadow: 0 14px 40px rgba(2,6,23,.18);
      padding: 12px 12px 10px 12px;
      backdrop-filter: blur(8px);
    }
    .ios-a2hs-row{ display:flex; gap: 10px; align-items:flex-start; }
    .ios-a2hs-icon{
      width: 36px; height: 36px;
      border-radius: 12px;
      display:flex; align-items:center; justify-content:center;
      background: #FFF2E6;
      border: 1px solid rgba(255,153,0,.35);
      font-weight: 800;
      color:#FF9900;
      flex: 0 0 auto;
    }
    .ios-a2hs-title{
      font-weight: 800; margin: 0;
      font-size: .98rem; color:#0f172a; line-height: 1.2;
    }
    .ios-a2hs-text{ margin: 2px 0 0 0; color:#334155; font-size: .92rem; }
    .ios-a2hs-pill{
      display:inline-flex; align-items:center; gap: 6px;
      padding: 6px 10px;
      border-radius: 999px;
      background: #f1f5f9;
      border: 1px solid rgba(15,23,42,.08);
      font-size: .85rem;
      color:#0f172a;
      white-space: nowrap;
      margin-top: 8px;
    }
    .ios-a2hs-close{
      margin-left:auto;
      border: 0;
      background: transparent;
      width: 34px; height: 34px;
      border-radius: 10px;
      display:flex; align-items:center; justify-content:center;
      color:#0f172a;
      opacity: .75;
      cursor: pointer;
    }
    .ios-a2hs-close:hover{ opacity: 1; background:#f1f5f9; }

    .ios-a2hs-alert{
      margin-top: 10px;
      padding: 10px 12px;
      border-radius: 12px;
      background: #fff7ed;
      border: 1px solid rgba(234,88,12,.25);
      color: #7c2d12;
      font-size: .9rem;
    }
    .ios-a2hs-alert strong{ font-weight: 800; }

    /* ===== Chip puntos (topbar) ===== */
.points-chip{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-height: 34px;
  padding: .35rem .7rem;
  border-radius: 999px;

  font-weight: 800;
  font-size: .92rem;
  letter-spacing: .2px;
  text-decoration: none;

  background: #585858;
  color:white;
  border: 1px solid rgba(255,153,0,.35);
  box-shadow: 0 2px 10px rgba(2,6,23,.06);

  transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
}

.points-chip:hover{
  background: rgba(255,153,0,.20);
  box-shadow: 0 6px 18px rgba(2,6,23,.10);
  transform: translateY(-1px);
}

.points-chip:active{
  transform: translateY(0);
  box-shadow: 0 2px 10px rgba(2,6,23,.06);
}

.points-chip:focus{
  outline: none;
}

.points-chip:focus-visible{
  outline: 3px solid rgba(255,153,0,.35);
  outline-offset: 2px;
}
  </style>
</head>

<body>
@php
  $activeCompany = session('active_company');
  $u = auth()->user();

  // Roles (Spatie)
  $isSiteAdmin    = $u?->hasRole('admin_sitio') ?? false;
  $isCompanyAdmin = $u?->hasRole('admin_empresa') ?? false;
  $isEmployee     = $u?->hasRole('empleado') ?? false;

  // ===== Saldo puntos (cualquier usuario que sea EMPLEADO, incluso si es admin) =====
  $menuSaldoPuntos = null;

  if ($isEmployee && $u) {
    $q = \App\Models\PointMovement::query()
      ->where('employee_user_id', $u->id);

    // si tenés anulaciones, no las cuentes
    if (\Illuminate\Support\Facades\Schema::hasColumn('point_movements', 'voided_at')) {
      $q->whereNull('voided_at');
    }

    $menuSaldoPuntos = (int) $q->sum('points');
  }

  // Link del chip:
  // - empleado "puro" => points.index normal (vista empleado)
  // - admin + empleado => points.index filtrado a sí mismo
  $pointsChipUrl = route('points.index');
  if ($isEmployee && ($isSiteAdmin || $isCompanyAdmin)) {
    $pointsChipUrl = route('points.index', ['employee_id' => $u->id]);
  }
@endphp

<div class="app-shell" id="appShell">

  {{-- ===== Topbar ===== --}}
  <header class="topbar">
    <div class="topbar-left">
      <a href="{{ route('dashboard') }}" class="topbar-home">
        @if($activeCompany && !empty($activeCompany->logo))
          <img class="company-logo"
               src="{{ asset('storage/'.$activeCompany->logo) }}"
               alt="Company Logo">
        @else
          <div class="company-logo-fallback">
            <img
              src="{{ asset('logos/ImgLogoCircular-SF.png') }}"
              alt="Logo {{ config('app.name') }}"
              width="36"
              height="36"
              style="border-radius:12px; object-fit:cover; box-shadow: 0 6px 16px rgba(0,0,0,.12);"
            />
          </div>
        @endif

        <div class="topbar-title">
          <div class="app-name">Club Comofra</div>
          @if($activeCompany)
            <div class="company-name">{{ $activeCompany->name ?? '' }}</div>
          @endif
        </div>
      </a>
    </div>

    <div class="topbar-actions d-flex align-items-center gap-2">

{{-- Puntos (empleado, incluso si es admin) --}}
@if(!is_null($menuSaldoPuntos))
  <a href="{{ $pointsChipUrl }}"
     class="points-chip"
     title="Mis puntos">
    {{ number_format((int)$menuSaldoPuntos, 0, ',', '.') }}
  </a>
@endif

  {{-- Botón menú mobile (offcanvas) --}}
  <button class="btn btn-light btn-sm d-lg-none"
          type="button"
          data-bs-toggle="offcanvas"
          data-bs-target="#rightSidebarOffcanvas"
          aria-controls="rightSidebarOffcanvas">
    <i class="bi bi-list"></i>
  </button>

  {{-- Botón contraer menú (solo desktop) --}}
  <button class="btn btn-light btn-sm d-none d-lg-inline-flex"
          type="button"
          id="sidebarToggleBtn"
          title="Toggle sidebar">
    <i class="bi bi-layout-sidebar-inset-reverse"></i>
  </button>
</div>

  </header>

  {{-- ===== Content ===== --}}
  <main class="app-content">
    <div class="container-fluid py-3">
      @yield('content')
    </div>
  </main>

  {{-- ===== Right Sidebar Desktop ===== --}}
  <aside class="right-sidebar d-none d-lg-flex" id="rightSidebar">
    @include('layouts.partials.right_sidebar')
  </aside>

  {{-- ===== Right Sidebar Mobile/Tablet (Offcanvas) ===== --}}
  <div class="offcanvas offcanvas-end sidebar-offcanvas d-lg-none"
       tabindex="-1"
       id="rightSidebarOffcanvas"
       aria-labelledby="rightSidebarOffcanvasLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="rightSidebarOffcanvasLabel">Menu</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
      @include('layouts.partials.right_sidebar', ['isOffcanvas' => true])
    </div>
  </div>

</div> {{-- /app-shell --}}

{{-- ✅ Tip iPhone (HTML) - debe estar antes del script --}}
<div id="ios-a2hs-tip" class="ios-a2hs-tip d-none" aria-live="polite">
  <div class="ios-a2hs-card">
    <div class="ios-a2hs-row">
      <div class="ios-a2hs-icon" id="ios-a2hs-icon">⤴︎</div>

      <div style="min-width:0;">
        <p class="ios-a2hs-title" id="ios-a2hs-title">Agregar Club Comofra a tu inicio</p>
        <p class="ios-a2hs-text" id="ios-a2hs-text">
          Tocá <strong>Compartir</strong> y elegí <strong>“Agregar a pantalla de inicio”</strong>.
        </p>

        <div class="d-flex flex-wrap gap-2" id="ios-a2hs-pills">
          <span class="ios-a2hs-pill">⤴︎ Compartir</span>
          <span class="ios-a2hs-pill">＋ Agregar a inicio</span>
        </div>

        <div class="ios-a2hs-alert d-none" id="ios-a2hs-inapp">
          Estás en un navegador interno. Para instalar, tocá <strong>Compartir</strong> y elegí <strong>“Abrir en Safari”</strong>.
        </div>
      </div>

      <button type="button" class="ios-a2hs-close" id="ios-a2hs-close" aria-label="Cerrar">✕</button>
    </div>
  </div>
</div>

<script>
  (function() {
    const shell = document.getElementById('appShell');
    const btn = document.getElementById('sidebarToggleBtn');
    const KEY = 'clubcomofra_sidebar_collapsed';

    function isDesktop() {
      return window.matchMedia('(min-width: 992px)').matches;
    }

    function getLinksForTooltip(){
      const desktopSidebar = document.getElementById('rightSidebar');
      if (!desktopSidebar) return [];
      return Array.from(desktopSidebar.querySelectorAll('[data-bs-toggle="tooltip"]'));
    }

    function initTooltipsIfNeeded(){
      if (!isDesktop()) return;

      const collapsed = shell.classList.contains('sidebar-collapsed');
      const links = getLinksForTooltip();

      if (!collapsed) {
        links.forEach(el => {
          const instance = bootstrap.Tooltip.getInstance(el);
          if (instance) instance.dispose();
        });
        return;
      }

      links.forEach(el => {
        if (!bootstrap.Tooltip.getInstance(el)) {
          new bootstrap.Tooltip(el, { trigger: 'hover focus' });
        }
      });
    }

    const initial = localStorage.getItem(KEY) === '1';
    if (initial) shell.classList.add('sidebar-collapsed');

    if (window.bootstrap) initTooltipsIfNeeded();

    if (btn) {
      btn.addEventListener('click', () => {
        shell.classList.toggle('sidebar-collapsed');
        localStorage.setItem(KEY, shell.classList.contains('sidebar-collapsed') ? '1' : '0');
        if (window.bootstrap) initTooltipsIfNeeded();
      });
    }

    window.addEventListener('resize', () => {
      if (window.bootstrap) initTooltipsIfNeeded();
    });
  })();

  // ===== Service Worker =====
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('{{ asset('sw.js') }}');
    });
  }

  // =========================
  // ✅ Tip iPhone "Agregar a pantalla de inicio"
  // =========================
  document.addEventListener('DOMContentLoaded', () => {
    const tip   = document.getElementById('ios-a2hs-tip');
    const close = document.getElementById('ios-a2hs-close');

    const title = document.getElementById('ios-a2hs-title');
    const text  = document.getElementById('ios-a2hs-text');
    const pills = document.getElementById('ios-a2hs-pills');
    const inapp = document.getElementById('ios-a2hs-inapp');
    const icon  = document.getElementById('ios-a2hs-icon');

    if (!tip) return;

    const isIOS = () => /iphone|ipad|ipod/i.test(navigator.userAgent);

    const isStandalone = () =>
      window.matchMedia('(display-mode: standalone)').matches ||
      (window.navigator.standalone === true);

    const isSafari = () => {
      const ua = navigator.userAgent;
      const isWebkit = /WebKit/i.test(ua);
      const isCriOS = /CriOS/i.test(ua);
      const isFxiOS = /FxiOS/i.test(ua);
      return isWebkit && !isCriOS && !isFxiOS;
    };

    const isInAppBrowser = () => {
      const ua = navigator.userAgent.toLowerCase();
      return ua.includes('instagram') ||
             ua.includes('fbav') ||
             ua.includes('fban') ||
             ua.includes('whatsapp') ||
             ua.includes('wv') ||
             ua.includes('line') ||
             ua.includes('twitter') ||
             ua.includes('snapchat');
    };

    // Mostrar 1 vez cada 24hs
    const KEY = 'clubcomofra_ios_a2hs_last_seen_v1';
    const now = Date.now();
    const last = parseInt(localStorage.getItem(KEY) || '0', 10);
    const COOLDOWN_MS = 24 * 60 * 60 * 1000;
    const canShow = (now - last) > COOLDOWN_MS;

    if (!isIOS() || isStandalone() || !canShow) return;

    localStorage.setItem(KEY, String(now));

    const inApp = isInAppBrowser();
    const safari = isSafari();

    if (inApp) {
      icon.textContent = '🧭';
      title.textContent = 'Abrí en Safari para instalar';
      text.innerHTML = 'Estás dentro de una app. Para instalar Club Comofra, primero abrí el sitio en <strong>Safari</strong>.';
      pills.classList.add('d-none');
      inapp.classList.remove('d-none');
    } else if (safari) {
      icon.textContent = '⤴︎';
      title.textContent = 'Agregar Club Comofra a tu inicio';
      text.innerHTML = 'Tocá <strong>Compartir</strong> y elegí <strong>“Agregar a pantalla de inicio”</strong>.';
      pills.classList.remove('d-none');
      inapp.classList.add('d-none');
    } else {
      icon.textContent = '🧭';
      title.textContent = 'Para instalar, usá Safari';
      text.innerHTML = 'En iPhone, la instalación funciona mejor desde <strong>Safari</strong>. Abrí el sitio en Safari y luego agregalo al inicio.';
      pills.classList.add('d-none');
      inapp.classList.remove('d-none');
      inapp.innerHTML = 'Tip: tocá <strong>Compartir</strong> y buscá <strong>“Abrir en Safari”</strong>.';
    }

    tip.classList.remove('d-none');
    requestAnimationFrame(() => tip.classList.add('is-show'));

    const HIDE_MS = 5000;
    const hide = () => {
      tip.classList.remove('is-show');
      setTimeout(() => tip.classList.add('d-none'), 250);
    };

    const t = setTimeout(hide, HIDE_MS);

    close?.addEventListener('click', () => {
      clearTimeout(t);
      hide();
    });
  });
</script>

@stack('scripts')
</body>
</html>
