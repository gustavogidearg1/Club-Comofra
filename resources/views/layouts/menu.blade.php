@php
  $u = auth()->user();

  $isSiteAdmin    = $u?->hasRole('admin_sitio') ?? false;
  $isCompanyAdmin = $u?->hasRole('admin_empresa') ?? false;
  $isBusiness     = $u?->hasRole('negocio') ?? false;
  $isEmployee     = $u?->hasRole('empleado') ?? false;

  // Prioridad
  $showAdminSite    = $isSiteAdmin;
  $showAdminCompany = !$isSiteAdmin && $isCompanyAdmin;

  $showBusiness = !$showAdminSite && !$showAdminCompany && $isBusiness;
  $showEmployee = !$showAdminSite && !$showAdminCompany && $isEmployee;
@endphp

{{-- TOPBAR --}}
<header class="pc-topbar">
  <div class="pc-topbar-inner container-fluid px-3">
    <a class="pc-brand d-flex align-items-center text-decoration-none" href="{{ route('dashboard') }}">
      <img src="{{ asset('logos/ImgLogoCircular-SF.png') }}" alt="PuntosCom" class="pc-brand-logo">
      <span class="pc-brand-name d-none d-sm-inline">PuntosCom</span>
    </a>

    <div class="ms-auto d-flex align-items-center gap-2">
      {{-- Chip de puntos (solo empleado y si lo pasás por variable) --}}
      @auth
        @if($isEmployee)
          @isset($menuSaldoPuntos)
            <a href="{{ route('points.index') }}"
               class="badge rounded-pill bg-warning text-dark text-decoration-none px-3 py-2 d-none d-md-inline-flex align-items-center gap-2">
              <i class="bi bi-star-fill"></i>
              <span>{{ $menuSaldoPuntos }}</span>
            </a>
          @endisset
        @endif
      @endauth

      <button class="btn pc-icon-btn ripple" id="pcToggle" type="button" aria-label="Abrir menú">
        <i class="bi bi-list"></i>
      </button>
    </div>
  </div>
</header>

{{-- SIDEBAR DERECHO --}}
<aside class="pc-sidebar">
  <div class="pc-sidebar-card mat-card">

    {{-- Header --}}
    <div class="pc-sidebar-header">
      <div class="d-flex align-items-center gap-2">
        <div class="pc-avatar">
          <i class="bi bi-person-circle"></i>
        </div>
        <div class="pc-user">
          <div class="pc-user-name">{{ $u->name ?? 'Invitado' }}</div>
          <div class="pc-user-sub text-muted">{{ $u->email ?? '' }}</div>
        </div>
      </div>

      <button class="btn pc-icon-btn ripple" id="pcToggleClose" type="button" aria-label="Cerrar menú"
              onclick="document.getElementById('pcToggle')?.click()">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    {{-- Navegación --}}
    <nav class="pc-nav">
      @guest
        <a class="pc-nav-link" href="{{ route('login') }}">
          <i class="bi bi-box-arrow-in-right"></i><span>Acceso</span>
        </a>
      @else

        <a class="pc-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
          <i class="bi bi-speedometer2"></i><span>Panel</span>
        </a>

        {{-- ===========================
             ADMIN SITIO (prioridad máxima)
             =========================== --}}
        @if($showAdminSite)
          <div class="pc-nav-section">Administración del sitio</div>

          <a class="pc-nav-link {{ request()->routeIs('abm.companies.*') ? 'active' : '' }}"
             href="{{ route('abm.companies.index') }}">
            <i class="bi bi-buildings"></i><span>Empresas</span>
          </a>

          <a class="pc-nav-link {{ request()->routeIs('abm.users.*') ? 'active' : '' }}"
             href="{{ route('abm.users.index') }}">
            <i class="bi bi-people"></i><span>Usuarios</span>
          </a>

          <a class="pc-nav-link {{ request()->routeIs('points.*') ? 'active' : '' }}"
             href="{{ route('points.index') }}">
            <i class="bi bi-trophy"></i><span>Gestión de puntos</span>
          </a>

          <a class="pc-nav-link {{ request()->routeIs('points.resumen') || request()->routeIs('points.summary') ? 'active' : '' }}"
             href="{{ route('points.resumen') }}">
            <i class="bi bi-bar-chart"></i><span>Resumen de puntos</span>
          </a>

          <a class="pc-nav-link {{ request()->routeIs('points.import.*') ? 'active' : '' }}"
             href="{{ route('points.import.create') }}">
            <i class="bi bi-upload"></i><span>Importación</span>
          </a>

          {{-- opcional test --}}
          <a class="pc-nav-link {{ request()->routeIs('redeems.*') ? 'active' : '' }}"
             href="{{ route('redeems.create') }}">
            <i class="bi bi-qr-code-scan"></i><span>Consumir puntos (test)</span>
          </a>
        @endif

        {{-- ===========================
             ADMIN EMPRESA (si NO es admin_sitio)
             =========================== --}}
        @if($showAdminCompany)
          <div class="pc-nav-section">Administrador de empresa</div>

          <a class="pc-nav-link {{ request()->routeIs('abm.users.*') ? 'active' : '' }}"
             href="{{ route('abm.users.index') }}">
            <i class="bi bi-people"></i><span>Usuarios</span>
          </a>

          <a class="pc-nav-link {{ request()->routeIs('abm.paises.*') ? 'active' : '' }}"
             href="{{ route('abm.paises.index') }}">
            <i class="bi bi-globe"></i><span>Países</span>
          </a>

          <a class="pc-nav-link {{ request()->routeIs('abm.provincias.*') ? 'active' : '' }}"
             href="{{ route('abm.provincias.index') }}">
            <i class="bi bi-map"></i><span>Provincias</span>
          </a>

          <a class="pc-nav-link {{ request()->routeIs('abm.localidades.*') ? 'active' : '' }}"
             href="{{ route('abm.localidades.index') }}">
            <i class="bi bi-geo-alt"></i><span>Localidades</span>
          </a>

          <a class="pc-nav-link {{ request()->routeIs('points.*') ? 'active' : '' }}"
             href="{{ route('points.index') }}">
            <i class="bi bi-trophy"></i><span>Gestión de puntos</span>
          </a>

          <a class="pc-nav-link {{ request()->routeIs('points.resumen') || request()->routeIs('points.summary') ? 'active' : '' }}"
             href="{{ route('points.resumen') }}">
            <i class="bi bi-bar-chart"></i><span>Resumen de puntos</span>
          </a>

          <a class="pc-nav-link {{ request()->routeIs('points.import.*') ? 'active' : '' }}"
             href="{{ route('points.import.create') }}">
            <i class="bi bi-upload"></i><span>Importación</span>
          </a>
        @endif

        {{-- ===========================
             NEGOCIO (solo si NO es admin)
             =========================== --}}
        @if($showBusiness)
          <div class="pc-nav-section">Negocio</div>

          <a class="pc-nav-link {{ request()->routeIs('redeems.*') ? 'active' : '' }}"
             href="{{ route('redeems.create') }}">
            <i class="bi bi-qr-code-scan"></i><span>Consumir puntos</span>
          </a>
        @endif

        {{-- ===========================
             EMPLEADO (solo si NO es admin)
             =========================== --}}
        @if($showEmployee)
          <div class="pc-nav-section">Mi cuenta</div>

          <a class="pc-nav-link {{ request()->routeIs('me.index') ? 'active' : '' }}"
             href="{{ route('me.index') }}">
            <i class="bi bi-person-badge"></i><span>Mi cuenta</span>
          </a>

          <a class="pc-nav-link {{ request()->routeIs('points.*') ? 'active' : '' }}"
             href="{{ route('points.index') }}">
            <i class="bi bi-stars"></i><span>Mis puntos</span>
          </a>
        @endif

        <div class="pc-nav-divider"></div>

        @if (Route::has('profile.edit'))
          <a class="pc-nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}"
             href="{{ route('profile.edit') }}">
            <i class="bi bi-gear"></i><span>Perfil</span>
          </a>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="px-2 pt-2">
          @csrf
          <button class="btn btn-outline-secondary w-100 btn-mat" type="submit">
            <i class="bi bi-box-arrow-left me-1"></i> Cerrar sesión
          </button>
        </form>

      @endguest
    </nav>

  </div>
</aside>
