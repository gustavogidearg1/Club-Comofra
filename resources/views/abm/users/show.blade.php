@extends('layouts.app')

@section('title', 'Usuario - '.$user->name)

@section('content')

@if($user->hasRole('negocio'))
  @php
    $qrUrl = route('redeems.manual.create', ['business' => $user->id]);
  @endphp

  <div class="card mat-card mt-3">
    <div class="mat-header">
<h3 class="mat-title mb-0">
  <i class="bi bi-qr-code me-2"></i> QR del Negocio:
  <span class="ms-1"><i class="bi bi-person-badge"></i> {{ $user->name }}</span>
</h3>

      <div class="ms-auto d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-mat" onclick="window.print()">
          <i class="bi bi-printer"></i> Imprimir
        </button>
      </div>
    </div>

<div class="card-body">
  <div class="row g-3 justify-content-center">
    <div class="col-12 col-lg-8">
      <div class="p-3 p-md-4 bg-white border rounded-4 d-flex flex-column align-items-center">

        {{-- QR más grande --}}
        <div class="qr-wrap">
          {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(460)->margin(1)->generate($qrUrl) !!}
        </div>

        <div class="mt-3 text-center">
          <div class="form-text text-muted">
            El empleado escanea este QR y se abre la pantalla con este negocio precargado.
          </div>

          <div class="alert alert-info mt-3 mb-0">
            Recomendación: imprimilo y pegalo en la caja del comercio.
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<style>
  /* Hace que el SVG del QR se adapte bien y se vea grande */
  .qr-wrap svg{
    width: 100% !important;
    height: auto !important;
    max-width: 520px;     /* controla "lo grande" */
  }

  /* Ajuste especial para impresión (opcional pero útil) */
  @media print{
    .qr-wrap svg{
      max-width: 650px;
    }
  }
</style>


  </div>

  <script>
  function copyText(id){
    const el = document.getElementById(id);
    if(!el) return;
    el.select();
    el.setSelectionRange(0, 99999);
    navigator.clipboard?.writeText(el.value);
  }
  </script>
@endif


<div class="card mat-card mb-3">
  <div class="mat-header">

    <div class="ms-auto d-flex gap-2">
      <a href="{{ route('abm.users.index') }}" class="btn btn-outline-secondary btn-sm">Atrás</a>
      <a href="{{ route('abm.users.edit', $user) }}" class="btn btn-primary btn-mat btn-sm">
        <i class="bi bi-pencil"></i> Editar
      </a>
    </div>
  </div>

  <div class="card-body">
    <div class="row g-3 align-items-start">
      <div class="col-md-3">
        @if($user->imagen)
          <img src="{{ Storage::url($user->imagen) }}" class="img-fluid rounded-3 shadow-sm" alt="User image">
        @else
          <div class="border rounded-3 p-4 text-center text-muted">
            <i class="bi bi-image" style="font-size:2rem;"></i>
            <div class="small mt-2">No imagen</div>
          </div>
        @endif
      </div>

      <div class="col-md-9">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted small">Email</div>
            <div class="fw-semibold">{{ $user->email }}</div>
          </div>

          <div class="col-md-6">
            <div class="text-muted small">Empresa</div>
            <div class="fw-semibold">{{ $user->company?->name ?? '—' }}</div>
          </div>

          <div class="col-md-4">
            <div class="text-muted small">CUIL</div>
            <div class="fw-semibold">{{ $user->cuil ?? '—' }}</div>
          </div>

          <div class="col-md-8">
            <div class="text-muted small">Direccion</div>
            <div class="fw-semibold">{{ $user->direccion ?? '—' }}</div>
          </div>
<div class="col-md-4">
  <div class="text-muted small">Teléfono</div>
  <div class="fw-semibold">{{ $user->telefono ?? '—' }}</div>
</div>


          <div class="col-md-4">
            <div class="text-muted small">Fecha de nacimiento</div>
            <div class="fw-semibold">{{ $user->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</div>
          </div>

          <div class="col-md-4">
            <div class="text-muted small">Pais</div>
            <div class="fw-semibold">{{ $user->pais?->nombre ?? '—' }}</div>
          </div>

          <div class="col-md-4">
            <div class="text-muted small">Provincia</div>
            <div class="fw-semibold">{{ $user->provincia?->nombre ?? '—' }}</div>
          </div>

          <div class="col-md-4">
            <div class="text-muted small">Localidad</div>
            <div class="fw-semibold">
              {{ $user->localidad?->nombre ?? '—' }}
              @if($user->localidad?->cp)
                <span class="text-muted">({{ $user->localidad->cp }})</span>
              @endif
            </div>
          </div>

          <div class="col-md-4">
            <div class="text-muted small">Activo</div>
            @if($user->activo)
              <span class="badge text-bg-success">Si</span>
            @else
              <span class="badge text-bg-secondary">No</span>
            @endif
          </div>

          <div class="col-md-8">
            <div class="text-muted small">Roles</div>
            <div>
              @forelse($user->roles as $role)
                <span class="badge text-bg-secondary me-1">{{ $role->name }}</span>
              @empty
                <span class="text-muted">—</span>
              @endforelse
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
