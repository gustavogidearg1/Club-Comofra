@extends('layouts.app')
@section('title','Comprobante de consumo')

@section('content')

@php
  $empleado = $redemption->employee->name ?? '—';
  $puntos   = number_format((float)($redemption->points ?? 0), 2, ',', '.');
  $negocio  = $redemption->business->name ?? '—';
  $fecha    = optional($redemption->confirmed_at)->format('Y-m-d H:i') ?? '—';
  $nota     = $redemption->note ?? '—';

  $estado = (($redemption->status ?? '') === 'voided' || optional($redemption->movement)->voided_at)
    ? 'ANULADO'
    : 'CONFIRMADO';

  // Texto a compartir
  $shareText =
"🧾 Comprobante de consumo
Empleado: {$empleado}
Puntos consumidos: {$puntos}
Negocio: {$negocio}
Fecha: {$fecha}
Nota: {$nota}
Estado: {$estado}";

  // Link WhatsApp
  $waLink = 'https://wa.me/?text=' . rawurlencode($shareText);
@endphp

<div class="container py-3">
  <div class="card mat-card">
    <div class="mat-header">
      <h3 class="mat-title mb-0">
        <i class="bi bi-receipt me-2"></i> Comprobante de consumo
      </h3>

      <div class="ms-auto d-flex gap-2">
        {{-- Compartir por WhatsApp --}}
        <a href="{{ $waLink }}"
           target="_blank" rel="noopener"
           class="btn btn-success btn-mat">
          <i class="bi bi-whatsapp me-1"></i> Compartir
        </a>

        <a href="{{ route('points.index') }}" class="btn btn-outline-secondary btn-mat">
          <i class="bi bi-arrow-left"></i> Volver
        </a>
      </div>
    </div>

    <div class="card-body">
      <dl class="row mb-0">
        <dt class="col-sm-4">Empleado</dt>
        <dd class="col-sm-8">{{ $empleado }}</dd>

        <dt class="col-sm-4">Puntos consumidos</dt>
        <dd class="col-sm-8"><strong>{{ $puntos }}</strong></dd>

        <dt class="col-sm-4">Negocio</dt>
        <dd class="col-sm-8">{{ $negocio }}</dd>

        <dt class="col-sm-4">Fecha</dt>
        <dd class="col-sm-8">{{ $fecha }}</dd>

        <dt class="col-sm-4">Nota</dt>
        <dd class="col-sm-8">{{ $nota }}</dd>

        <dt class="col-sm-4">Estado</dt>
        <dd class="col-sm-8">
          @if($estado === 'ANULADO')
            <span class="badge bg-danger">ANULADO</span>
          @else
            <span class="badge bg-success">CONFIRMADO</span>
          @endif
        </dd>
      </dl>
    </div>
  </div>
</div>
@endsection
