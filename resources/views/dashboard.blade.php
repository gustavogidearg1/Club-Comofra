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

  // vista principal (prioridad)
  $primaryRole = $isAdmin ? 'admin' : ($isBusiness ? 'negocio' : ($isEmployee ? 'empleado' : 'user'));

  // permisos para ver "Puntos"
  $canSeePointsCard = $isEmployee || $isAdmin;

  // QR fijo del negocio (empleado escanea y entra con negocio precargado)
  $businessId = $isBusiness ? $u->id : null;
  $businessManualUrl = $businessId ? url("/redeems/manual/{$businessId}") : null;
@endphp

<x-flash />


<div class="card mat-card mb-3">
  <div class="mat-header">
    <h3 class="mat-title mb-0">
      <i class="bi bi-speedometer2 me-2"></i>
      Dashboard
    </h3>



    {{-- Badges usuario/roles (responsive) --}}
    <div class="ms-auto d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
      <span class="badge bg-light text-dark">
        <i class="bi bi-person-circle me-1"></i>{{ $u->name }}
      </span>


    </div>
  </div>


</div>

{{-- =========================
     ACCESOS RÁPIDOS
========================= --}}
<div class="row g-3 mb-4">

  {{-- PUNTOS: solo si Empleado o Admin --}}
  @if($canSeePointsCard)
    <div class="col-12 col-md-6 col-lg-3">
      <a href="{{ route('points.index') }}" class="text-decoration-none">
        <div class="card mat-card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-bold">Puntos</div>
                <div class="text-muted small">
                  {{ $isEmployee ? 'Ver mis puntos' : 'Ver movimientos' }}
                </div>
              </div>
              <i class="bi bi-stars" style="font-size:1.6rem;"></i>
            </div>
          </div>
        </div>
      </a>
    </div>
  @endif

  {{-- Consumir (Negocio) --}}
  @if($isBusiness || $isSiteAdmin)
    <div class="col-12 col-md-6 col-lg-3">
      <a href="{{ route('redeems.create') }}" class="text-decoration-none">
        <div class="card mat-card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-bold">Consumir (Negocio)</div>
                <div class="text-muted small">Generar consumo y QR</div>
              </div>
              <i class="bi bi-qr-code-scan" style="font-size:1.6rem;"></i>
            </div>
          </div>
        </div>
      </a>
    </div>
  @endif

  {{-- Consumo manual (Empleado) --}}
  @if($isEmployee || $isSiteAdmin)
    <div class="col-12 col-md-6 col-lg-3">
      <a href="{{ route('redeems.manual.index') }}" class="text-decoration-none">
        <div class="card mat-card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-bold">Consumo manual</div>
                <div class="text-muted small">Empleado → Negocio</div>
              </div>
              <i class="bi bi-shop" style="font-size:1.6rem;"></i>
            </div>
          </div>
        </div>
      </a>
    </div>
  @endif

  {{-- Usuarios (Admin) --}}
  @if($isAdmin)
    <div class="col-12 col-md-6 col-lg-3">
      <a href="{{ route('abm.users.index') }}" class="text-decoration-none">
        <div class="card mat-card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-bold">Usuarios</div>
                <div class="text-muted small">ABM de usuarios</div>
              </div>
              <i class="bi bi-people" style="font-size:1.6rem;"></i>
            </div>
          </div>
        </div>
      </a>
    </div>
  @endif

</div>

{{-- =========================
     SECCIÓN PRINCIPAL SEGÚN ROL
========================= --}}
@if($primaryRole === 'admin')
  <div class="row g-3">
    <div class="col-12 col-lg-8">
      <div class="card mat-card">
        <div class="mat-header">
          <h3 class="mat-title mb-0"><i class="bi bi-bar-chart me-2"></i>Panel de administrador</h3>
        </div>
        <div class="card-body">
          <ul class="mb-0">
            <li>Accedé a <strong>Resumen de puntos</strong> para ver totales y ranking.</li>
            <li>Usá <strong>Importación</strong> para cargar lotes masivos.</li>
            <li>En <strong>Usuarios</strong> gestionás roles, alta/baja y datos.</li>
          </ul>

<div class="d-flex flex-wrap gap-2 mt-3">
  <a class="btn btn-primary btn-mat" href="{{ route('points.resumen') }}">
    <i class="bi bi-bar-chart"></i> Resumen
  </a>

  <a class="btn btn-outline-primary btn-mat" href="{{ route('points.import.create') }}">
    <i class="bi bi-upload"></i> Importación
  </a>

  <a class="btn btn-outline-primary btn-mat" href="{{ route('points.create') }}">
    <i class="bi bi-plus-lg"></i> Crear movimiento
  </a>


</div>

        </div>
      </div>
    </div>


  </div>

@elseif($primaryRole === 'negocio')
  <div class="row g-3">
    <div class="col-12 col-lg-8">
      <div class="card mat-card">
        <div class="mat-header">
          <h3 class="mat-title mb-0"><i class="bi bi-shop me-2"></i>Panel de negocio</h3>
        </div>
        <div class="card-body">
          <div class="mb-2">Acciones típicas:</div>
          <ul class="mb-3">
            <li>Generar consumo por QR (cuando el empleado está presente).</li>
            <li>Si el empleado escanea un QR preimpreso, igual queda trazabilidad en movimientos.</li>
          </ul>

          <a class="btn btn-success btn-mat" href="{{ route('redeems.create') }}">
            <i class="bi bi-qr-code-scan"></i> Crear consumo
          </a>
        </div>
      </div>
    </div>

    {{-- TIP + QR FIJO solo si es Negocio --}}    @if($isBusiness && $businessManualUrl)
      <div class="col-12 col-lg-4">
        <div class="card mat-card h-100">
          <div class="mat-header">
            <h3 class="mat-title mb-0"><i class="bi bi-info-circle me-2"></i>QR del negocio</h3>
          </div>
          <div class="card-body">
            <div class="text-muted mb-2">
              Imprimí este QR y dejalo en caja: el empleado lo escanea y entra directo al consumo manual con tu negocio precargado.
            </div>

            <div class="d-flex justify-content-center p-2 bg-white rounded-4 border">
              {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)->margin(1)->generate($businessManualUrl) !!}
            </div>

            <div class="mt-2">
              <div class="small text-muted">Link:</div>
              <div class="input-group">
                <input id="bizQrUrl" class="form-control form-control-sm" value="{{ $businessManualUrl }}" readonly>
                <button class="btn btn-outline-secondary btn-sm" type="button"
                        onclick="navigator.clipboard?.writeText(document.getElementById('bizQrUrl').value)">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
            </div>

            <div class="d-grid mt-2">
              <button type="button" class="btn btn-outline-primary btn-mat" onclick="window.print()">
                <i class="bi bi-printer"></i> Imprimir
              </button>
            </div>
          </div>
        </div>
      </div>
    @endif
  </div>

@elseif($primaryRole === 'empleado')
  <div class="row g-3">
    <div class="col-12 col-lg-8">
      <div class="card mat-card">
        <div class="mat-header">
          <h3 class="mat-title mb-0"><i class="bi bi-wallet2 me-2"></i>Mi cuenta</h3>
        </div>
        <div class="card-body">
          <div class="mb-3">
            Entrá a <strong>Mis puntos</strong> para ver tu saldo y movimientos.
          </div>

          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-primary btn-mat" href="{{ route('points.index') }}">
              <i class="bi bi-stars"></i> Mis puntos
            </a>
            <a class="btn btn-outline-primary btn-mat" href="{{ route('redeems.manual.index') }}">
              <i class="bi bi-shop"></i> Consumo manual
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card mat-card h-100">
        <div class="mat-header">
          <h3 class="mat-title mb-0"><i class="bi bi-shield-check me-2"></i>Seguridad</h3>
        </div>
        <div class="card-body">
          <div class="text-muted">
            Los consumos quedan registrados con fecha y negocio. Si algo no coincide, avisás y se puede anular.
          </div>
        </div>
      </div>
    </div>
  </div>

@else
  <div class="card mat-card">
    <div class="mat-header">
      <h3 class="mat-title mb-0"><i class="bi bi-person me-2"></i>Bienvenido</h3>
    </div>
    <div class="card-body">
      Tu usuario no tiene un rol operativo asignado todavía. Contactá a un administrador para que te asigne permisos.
    </div>
  </div>
@endif


{{-- =========================
     OFERTAS
========================= --}}
@if($isEmployee || $isAdmin || $isBusiness)

  {{-- DESTACADAS --}}
@if(isset($ofertasDestacadas) && $ofertasDestacadas->count())
    <div class="card mat-card mb-4">
      <div class="mat-header">
        <h3 class="mat-title mb-0">
          <i class="bi bi-megaphone me-2"></i>Ofertas destacadas
        </h3>
      </div>

      <div class="card-body">
        <div id="carouselOfertas" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner">

            @foreach($ofertasDestacadas as $i => $oferta)
              <div class="carousel-item {{ $i == 0 ? 'active' : '' }}">

                <div class="row align-items-center">
                  <div class="col-md-5">
                    @php
                      $img = $oferta->imagenes->first();
                    @endphp

                    @if($img)
                      <img src="{{ asset('storage/'.$img->ruta) }}"
                           class="img-fluid rounded"
                           style="cursor:pointer"
                           onclick="verOferta({{ $oferta->id }})">
                    @endif
                  </div>

                  <div class="col-md-7">
                    <h4>{{ $oferta->titulo }}</h4>

                    @if($oferta->precio)
                      <div class="fw-bold fs-5 text-success">
                        $ {{ number_format($oferta->precio,2,',','.') }}
                      </div>
                    @endif

                    <div class="text-muted small">
                      {{ $oferta->company->name ?? '' }}
                    </div>

                    @if($oferta->fecha_hasta)
                      <div class="small text-danger">
                        Válido hasta {{ $oferta->fecha_hasta->format('d/m/Y') }}
                      </div>
                    @endif

                    <button class="btn btn-primary btn-sm mt-2"
                            onclick="verOferta({{ $oferta->id }})">
                      Ver detalle
                    </button>
                  </div>
                </div>

              </div>
            @endforeach

          </div>

          <button class="carousel-control-prev" type="button" data-bs-target="#carouselOfertas" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
          </button>

          <button class="carousel-control-next" type="button" data-bs-target="#carouselOfertas" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
          </button>
        </div>
      </div>
    </div>
  @endif


  {{-- LISTADO GENERAL --}}
  @if(isset($ofertas) && $ofertas->count())
  <div class="row g-3">
    @foreach($ofertas as $oferta)
      <div class="col-md-4">

        <div class="card mat-card h-100">
          @php $img = $oferta->imagenes->first(); @endphp

          @if($img)
            <img src="{{ asset('storage/'.$img->ruta) }}"
                 class="card-img-top"
                 style="height:180px;object-fit:cover;cursor:pointer"
                 onclick="verOferta({{ $oferta->id }})">
          @endif

          <div class="card-body">

            <h6 class="fw-bold">{{ $oferta->titulo }}</h6>

            @if($oferta->destacada)
              <span class="badge bg-warning text-dark">Destacada</span>
            @endif

            @if($oferta->precio)
              <div class="text-success fw-bold">
                $ {{ number_format($oferta->precio,2,',','.') }}
              </div>
            @endif

            <div class="small text-muted">
              {{ $oferta->company->name ?? '' }}
            </div>

            <button class="btn btn-outline-primary btn-sm mt-2 w-100"
                    onclick="verOferta({{ $oferta->id }})">
              Ver más
            </button>

          </div>
        </div>

      </div>
    @endforeach
  </div>
@endif
@endif


<div class="modal fade" id="modalOferta" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 id="modalTitulo"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <img id="modalImagen" class="img-fluid mb-3 rounded">

        <div id="modalDescripcion"></div>

        <div class="mt-2">
          <strong id="modalPrecio"></strong>
        </div>

        <div class="small text-muted mt-2" id="modalEmpresa"></div>
        <div class="small text-danger" id="modalFecha"></div>

      </div>

    </div>
  </div>
</div>


    <div class="ms-auto d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">

      <div class="d-flex flex-wrap gap-1">
        @if($isSiteAdmin) <span class="badge bg-dark">Admin Sitio</span> @endif
        @if($isCompanyAdmin) <span class="badge bg-primary">Admin Empresa</span> @endif
        @if($isBusiness) <span class="badge bg-success">Negocio</span> @endif
        @if($isEmployee) <span class="badge bg-warning text-dark">Empleado</span> @endif
      </div>
    </div>

    <script>
function verOferta(id){

    fetch('/ofertas/'+id)
        .then(r => r.text())
        .then(html => {

            // alternativa simple: cargar JSON si querés optimizar
            // por ahora te dejo simple si después lo querés mejorar lo hacemos

            window.location.href = '/ofertas/'+id;

        });
}
</script>

@endsection
