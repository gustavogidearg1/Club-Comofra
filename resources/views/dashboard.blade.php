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

{{-- NEGOCIOS TABLA --}}
@if($negocios->isNotEmpty())
<div class="card mat-card">
  <div class="mat-header">
    <h3 class="mat-title mb-0">
      <i class="bi bi-shop me-2"></i>Comercios
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
</script>

@endsection
