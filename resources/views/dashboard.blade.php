@extends('layouts.app')

@section('title','Club Comofra')

@section('content')

@php
  $u = auth()->user();

  $isSiteAdmin    = $u?->hasRole('admin_sitio');
  $isCompanyAdmin = $u?->hasRole('admin_empresa');
  $isAdmin        = $isSiteAdmin || $isCompanyAdmin;

  $isBusiness     = $u?->hasRole('negocio');
  $isEmployee     = $u?->hasRole('empleado');

  $primaryRole = $isAdmin ? 'admin' : ($isBusiness ? 'negocio' : ($isEmployee ? 'empleado' : 'user'));

  $canSeePointsCard = $isEmployee || $isAdmin;

  $businessId = $isBusiness ? $u->id : null;
  $businessManualUrl = $businessId ? url("/redeems/manual/{$businessId}") : null;
@endphp

<x-flash />

<style>
  .mat-sort{display:inline-flex;align-items:center;gap:.25rem;cursor:pointer;text-decoration:none;}
  .mat-sort .sort-icon{font-size:.9rem;opacity:.6;}
  .mat-sort.active{font-weight:600;}

  .user-avatar {
    width: 32px; height: 32px;
    object-fit: cover;
    border-radius: 999px;
    box-shadow: 0 1px 4px rgba(0,0,0,.15);
    border: 1px solid rgba(0,0,0,.08);
  }

  .email-cell{
  max-width: 260px;              /* ajustá a gusto (220–320 suele ir bien) */
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.qr-a4-preview {
  width: 100%;
  max-width: 620px;
  background: #fff;
  border: 1px solid rgba(0,0,0,.08);
  border-radius: 18px;
  padding: 34px 34px 24px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 8px 26px rgba(0,0,0,.08);
}

.qr-a4-preview::before {
  content: "";
  position: absolute;
  top: -90px;
  left: -90px;
  width: 210px;
  height: 210px;
  background: rgba(255, 153, 0, 0.15); /* naranja suave */
  border-radius: 50%;
}

.qr-logo-wrap {
  text-align: center;
  margin-bottom: 14px;
  position: relative;
  z-index: 1;
}

.qr-logo {
  max-height: 72px;
  max-width: 220px;
  object-fit: contain;
}

.qr-business-name {
  font-size: 34px;
  font-weight: 800;
  color: var(--secondary);
  margin: 10px 0 12px;
}

.qr-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  color: var(--brand);
  margin-bottom: 14px;
}

.qr-divider span {
  width: 120px;
  height: 2px;
  background: var(--brand);
  display: inline-block;
  border-radius: 99px;
}

.qr-subtitle {
  color: #555;
  font-size: 16px;
  margin-bottom: 18px;
}

.qr-box {
  display: inline-block;
  background: #fff;
  padding: 18px;
  border-radius: 20px;
  box-shadow: 0 12px 32px rgba(0,0,0,.16);
  margin-bottom: 20px;
}

.qr-box img {
  width: 330px;
  height: 330px;
  object-fit: contain;
}

.qr-footer-band {
  background: linear-gradient(135deg, var(--secondary), var(--brand));
  color: #fff;
  margin: 10px -34px 18px;
  padding: 22px 28px;
  display: flex;
  justify-content: center;
  gap: 60px;
}

.qr-footer-band div {
  display: flex;
  flex-direction: column;
  align-items: center;
  font-size: 13px;
  font-weight: 600;
}

.qr-footer-band i {
  font-size: 26px;
  margin-bottom: 6px;
}

.qr-print-note {
  font-size: 13px;
  color: #666;
  margin: 0;
}

</style>

{{-- HEADER --}}
<div class="card mat-card mb-3">
  <div class="mat-header">
    <h3 class="mat-title mb-0">
      <i class="bi bi-speedometer2 me-2"></i>Dashboard
    </h3>

    <div class="ms-auto">
      <span class="badge bg-light text-dark">
        <i class="bi bi-person-circle me-1"></i>{{ $u->name }}
      </span>
    </div>
  </div>
</div>

{{-- ACCESOS RÁPIDOS --}}
<div class="row g-3 mb-4">

  @if($canSeePointsCard)
  <div class="col-md-3">
    <a href="{{ route('points.index') }}" class="text-decoration-none">
      <div class="card mat-card h-100">
        <div class="card-body d-flex justify-content-between">
          <div>
            <div class="fw-bold">Puntos</div>
            <div class="text-muted small">
              {{ $isEmployee ? 'Ver mis puntos' : 'Ver movimientos' }}
            </div>
          </div>
          <i class="bi bi-stars fs-4"></i>
        </div>
      </div>
    </a>
  </div>
  @endif

  @if($isEmployee || $isSiteAdmin)
  <div class="col-md-3">
    <a href="{{ route('redeems.manual.index') }}" class="text-decoration-none">
      <div class="card mat-card h-100">
        <div class="card-body d-flex justify-content-between">
          <div>
            <div class="fw-bold">Consumo manual</div>
          </div>
          <i class="bi bi-shop fs-4"></i>
        </div>
      </div>
    </a>
  </div>
  @endif

  @if($isAdmin)
  <div class="col-md-3">
    <a href="{{ route('abm.users.index') }}" class="text-decoration-none">
      <div class="card mat-card h-100">
        <div class="card-body d-flex justify-content-between">
          <div>
            <div class="fw-bold">Usuarios</div>
          </div>
          <i class="bi bi-people fs-4"></i>
        </div>
      </div>
    </a>
  </div>
  @endif

</div>

@if(isset($cumplesHoy) && $cumplesHoy->isNotEmpty())
<div class="card mat-card mb-3" style="background: linear-gradient(135deg,#fff3cd,#ffeeba);">
  <div class="card-body">

    <h5 class="mb-2">
      🎉 Cumpleaños de hoy
    </h5>

    @foreach($cumplesHoy as $user)
      <div class="d-flex align-items-center gap-2 mb-2">

        {{-- FOTO --}}
        @if($user->imagen)
          <img src="{{ asset('storage/'.$user->imagen) }}"
               class="user-avatar">
        @else
          <i class="bi bi-person-circle fs-4"></i>
        @endif

        {{-- NOMBRE --}}
        <strong>{{ $user->name }}</strong>

        <span class="text-muted small">
          ¡Feliz cumple!
        </span>

      </div>
    @endforeach

  </div>
</div>
@endif

{{-- PANEL POR ROL --}}
@if($primaryRole === 'admin')
<div class="card mat-card mb-4">
  <div class="mat-header">
    <h3 class="mat-title mb-0">
      <i class="bi bi-bar-chart me-2"></i>Panel de administrador
    </h3>
  </div>
  <div class="card-body">

    <ul>
      <li>Resumen de puntos</li>
      <li>Importación</li>
      <li>Usuarios</li>
    </ul>

    <div class="d-flex gap-2">
      <a class="btn btn-primary btn-mat" href="{{ route('points.resumen') }}">Resumen</a>
      <a class="btn btn-outline-primary btn-mat" href="{{ route('points.import.create') }}">Importación</a>
      <a class="btn btn-outline-primary btn-mat" href="{{ route('points.create') }}">Crear</a>
    </div>

  </div>
</div>
@endif

{{-- OFERTAS --}}
@if($ofertasDestacadas->count())
<div class="card mat-card mb-4">
  <div class="mat-header">
    <h3 class="mat-title mb-0">
      <i class="bi bi-megaphone me-2"></i>Ofertas destacadas
    </h3>
  </div>
  <div class="card-body">
    <div class="row g-3">
      @foreach($ofertasDestacadas as $oferta)
        <div class="col-md-4">
          <div class="card mat-card h-100">
            @if($oferta->imagenes->first())
              <img src="{{ asset('storage/'.$oferta->imagenes->first()->ruta) }}"
                   class="card-img-top"
                   style="height:180px;object-fit:cover;">
            @endif

            <div class="card-body">
              <h6>{{ $oferta->titulo }}</h6>
              <div class="text-success fw-bold">
                ${{ number_format($oferta->precio,2,',','.') }}
              </div>

              <button class="btn btn-outline-primary btn-sm w-100"
                      onclick="verOferta({{ $oferta->id }})">
                Ver
              </button>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endif

@if($isBusiness && $businessManualUrl)
<div class="card mat-card mb-4">
  <div class="mat-header">
    <h3 class="mat-title mb-0">
      <i class="bi bi-qr-code me-2"></i>Mi QR de cobro
    </h3>
  </div>

  <div class="card-body text-center">

    <div id="qrPrintable" class="qr-a4-preview mx-auto">

<div class="d-flex align-items-center mb-3">
    @if($activeCompany && !empty($activeCompany->logo))
        <img src="{{ asset('storage/' . $activeCompany->logo) }}"
             alt="Logo empresa"
             style="height: 60px; width: auto; object-fit: contain;"
             class="me-3">
    @endif

    <div>
        <h4 class="mb-0">{{ $activeCompany->name ?? 'Dashboard' }}</h4>
        <small class="text-muted">Panel principal</small>
    </div>
</div>

      <h1 class="qr-business-name">{{ $u->name }}</h1>

      <div class="qr-divider">
        <span></span>
        <i class="bi bi-stars"></i>
        <span></span>
      </div>

      <p class="qr-subtitle">
        Escaneá este QR para registrar consumos
      </p>

      <div class="qr-box">
        <img
          src="https://api.qrserver.com/v1/create-qr-code/?size=420x420&data={{ urlencode($businessManualUrl) }}"
          alt="QR de cobro"
          id="qrCobro"
        >
      </div>



      <p class="qr-print-note">
        Imprimí y exhibí este QR en tu local para que puedan escanearlo fácilmente.
      </p>

    </div>

    <button onclick="imprimirQR()" class="btn btn-primary btn-mat mt-3">
      <i class="bi bi-printer me-1"></i> Imprimir cartel A4
    </button>

  </div>
</div>
@endif


{{-- NEGOCIOS TABLA --}}
@if(!$isBusiness && $negocios->isNotEmpty())
<div class="card mat-card">
  <div class="mat-header">
    <h3 class="mat-title mb-0">
      <i class="bi bi-shop me-2"></i>Comercios Adheridos
    </h3>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">

        <thead>
          <tr>
            <th>Imagen</th>
            <th>Nombre</th>
            <th>Dirección</th>
            <th></th>
          </tr>
        </thead>

        <tbody>
          @foreach($negocios as $negocio)

          @php
            $maps = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($negocio->direccion_completa);
          @endphp

          <tr>
            <td>
              @if($negocio->imagen)
                <img src="{{ asset('storage/'.$negocio->imagen) }}" class="user-avatar">
              @endif
            </td>
            <td>{{ $negocio->name }}</td>
            <td>{{ $negocio->direccion_completa }}</td>
            <td class="text-end">
              <a href="{{ $maps }}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-geo-alt"></i>
              </a>
            </td>
          </tr>

          @endforeach
        </tbody>

      </table>
    </div>
  </div>
</div>
@endif

<script>
function verOferta(id){
  window.location.href = '/ofertas/' + id;
}

function imprimirQR() {
    const contenido = document.getElementById('qrPrintable').outerHTML;

    const ventana = window.open('', '_blank');

    ventana.document.write(`
        <html>
            <head>
                <title>Imprimir QR - {{ $u->name }}</title>

                <style>
                    @page {
                        size: A4 portrait;
                        margin: 0;
                    }

                    body {
                        margin: 0;
                        padding: 0;
                        font-family: Arial, sans-serif;
                        background: #fff;
                    }

                    .qr-a4-preview {
                        width: 210mm;
                        height: 297mm;
                        box-sizing: border-box;
                        background: #fff;
                        padding: 28mm 22mm 18mm;
                        position: relative;
                        overflow: hidden;
                        text-align: center;
                    }

.qr-a4-preview::before {
  content: "";
  position: absolute;
  top: -90px;
  left: -90px;
  width: 210px;
  height: 210px;
  background: rgba(255, 153, 0, 0.15); /* naranja suave */
  border-radius: 50%;
}

                    .qr-logo-wrap {
                        text-align: center;
                        margin-bottom: 12mm;
                        position: relative;
                        z-index: 1;
                    }

                    .qr-logo {
                        max-height: 26mm;
                        max-width: 80mm;
                        object-fit: contain;
                    }

.qr-business-name {
  font-size: 34px;
  font-weight: 800;
  color: var(--secondary);
  margin: 10px 0 12px;
}

.qr-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  color: var(--brand);
  margin-bottom: 14px;
}

.qr-divider span {
  width: 120px;
  height: 2px;
  background: var(--brand);
  display: inline-block;
  border-radius: 99px;
}
                    .qr-divider i {
                        font-size: 18pt;
                    }

                    .qr-subtitle {
                        color: #555;
                        font-size: 14pt;
                        margin-bottom: 10mm;
                    }

                    .qr-box {
                        display: inline-block;
                        background: #fff;
                        padding: 8mm;
                        border-radius: 8mm;
                        box-shadow: 0 6mm 18mm rgba(0,0,0,.16);
                        margin-bottom: 13mm;
                    }

                    .qr-box img {
                        width: 118mm;
                        height: 118mm;
                        object-fit: contain;
                    }

.qr-footer-band {
  background: linear-gradient(135deg, var(--secondary), var(--brand));
  color: #fff;
  margin: 10px -34px 18px;
  padding: 22px 28px;
  display: flex;
  justify-content: center;
  gap: 60px;
}

                    .qr-footer-band div {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        font-size: 11pt;
                        font-weight: 600;
                    }

                    .qr-footer-band i {
                        font-size: 23pt;
                        margin-bottom: 3mm;
                    }

                    .qr-print-note {
                        font-size: 11pt;
                        color: #666;
                        margin: 0;
                    }

                    button {
                        display: none;
                    }
                </style>

                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
            </head>

            <body>
                ${contenido}

                <script>
                    window.onload = function() {
                        window.print();
                    }
                <\/script>
            </body>
        </html>
    `);

    ventana.document.close();
}
</script>

@endsection
