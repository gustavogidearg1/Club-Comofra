@extends('layouts.app')

@section('title', 'Usuario - '.$user->name)

@section('content')

@if($user->hasRole('negocio'))
@php
    $qrUrl = route('redeems.manual.create', ['business' => $user->id]);
@endphp

<style>
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
    background: rgba(255, 153, 0, 0.15);
    border-radius: 50%;
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

.qr-box svg {
    width: 330px;
    height: 330px;
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

@media print {
    .no-print {
        display: none !important;
    }

    body {
        background: #fff;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

<div class="card mat-card mt-3">
    <div class="mat-header">
        <h3 class="mat-title mb-0">
            <i class="bi bi-qr-code me-2"></i>
            QR del Negocio
        </h3>

        <div class="ms-auto no-print">
            <button type="button"
                    class="btn btn-primary btn-mat"
                    onclick="imprimirQR()"
                <i class="bi bi-printer me-1"></i>
                Imprimir cartel A4
            </button>
        </div>
    </div>

    <div class="card-body text-center">

        <div id="qrPrintable" class="qr-a4-preview mx-auto">

            @if($user->company && $user->company->logo)
                <div class="mb-3">
                    <img src="{{ asset('storage/'.$user->company->logo) }}"
                         alt="Logo"
                         style="max-height:70px;">
                </div>
            @endif

            <h1 class="qr-business-name">
                {{ $user->name }}
            </h1>

            <div class="qr-divider">
                <span></span>
                <i class="bi bi-stars"></i>
                <span></span>
            </div>

            <p class="qr-subtitle">
                Escaneá este QR para registrar consumos
            </p>

            <div class="qr-box">
                {!! QrCode::size(420)->margin(1)->generate($qrUrl) !!}
            </div>



            <p class="qr-print-note">
                Imprimí y exhibí este QR en tu local para que puedan escanearlo fácilmente.
            </p>

        </div>

    </div>
</div>
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

<script>
function imprimirQR() {

    const contenido = document.getElementById('qrPrintable').outerHTML;

    const ventana = window.open('', '_blank');

    ventana.document.write(`
        <html>
        <head>
            <title>QR - {{ $user->name }}</title>

            <style>
                @page{
                    size:A4 portrait;
                    margin:0;
                }

                body{
                    margin:0;
                    padding:0;
                    background:#fff;
                    font-family:Arial,sans-serif;

                    display:flex;
                    justify-content:center;
                    align-items:center;

                    width:100%;
                    min-height:100vh;
                }

                .qr-a4-preview{
                    width:210mm;
                    min-height:297mm;

                    box-sizing:border-box;

                    background:#fff;
                    padding:28mm 22mm 18mm;

                    position:relative;
                    overflow:hidden;

                    text-align:center;
                }

                .qr-a4-preview::before{
                    content:"";
                    position:absolute;
                    top:-90px;
                    left:-90px;
                    width:210px;
                    height:210px;
                    background:rgba(255,153,0,.15);
                    border-radius:50%;
                }

                .qr-business-name{
                    font-size:34px;
                    font-weight:800;
                    color:#222;
                    margin:10px 0 12px;
                }

                .qr-divider{
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    gap:14px;
                    margin-bottom:14px;
                }

                .qr-divider span{
                    width:120px;
                    height:2px;
                    background:#ff9900;
                    border-radius:99px;
                }

                .qr-subtitle{
                    color:#555;
                    font-size:16px;
                    margin-bottom:18px;
                }

                .qr-box{
                    display:inline-block;
                    background:#fff;
                    padding:18px;
                    border-radius:20px;
                    box-shadow:0 12px 32px rgba(0,0,0,.16);
                    margin-bottom:20px;
                }

                .qr-box svg{
                    width:330px;
                    height:330px;
                }

                .qr-print-note{
                    font-size:13px;
                    color:#666;
                    margin-top:20px;
                }

                img{
                    max-height:70px;
                }
            </style>

            <link rel="stylesheet"
                  href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        </head>

        <body>

            ${contenido}

            <script>
                window.onload = function(){
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
