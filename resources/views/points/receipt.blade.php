@extends('layouts.app')

@section('title', 'Comprobante de movimiento')

@push('styles')
<style>
  .receipt-card { max-width: 760px; margin: 1.5rem auto; }
  .receipt-title { font-weight: 800; color: #0f172a; }
  .receipt-label { color: #64748b; font-size: .82rem; text-transform: uppercase; letter-spacing: .04em; }
  .receipt-value { font-weight: 600; }
  .receipt-points { font-size: 2rem; font-weight: 800; }
  @media print {
    .topbar, .right-sidebar, .sidebar-nav, .no-print { display: none !important; }
    .app-shell { display: block !important; }
    .app-content { padding: 0 !important; }
    .receipt-card { max-width: none; margin: 0; box-shadow: none !important; }
    .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
  }
</style>
@endpush

@section('content')
<div class="card receipt-card">
  <div class="card-header d-flex align-items-center">
    <h3 class="receipt-title mb-0">
      <i class="bi bi-receipt me-2"></i> Comprobante de movimiento de puntos
    </h3>
    <div class="ms-auto d-flex gap-2 no-print">
      <a href="{{ route('points.index') }}" class="btn btn-outline-secondary btn-mat">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
      <button type="button" class="btn btn-primary btn-mat" onclick="window.print()">
        <i class="bi bi-printer"></i> Imprimir
      </button>
    </div>
  </div>

  <div class="card-body p-4">
    <div class="d-flex justify-content-between border-bottom pb-3 mb-4">
      <div>
        <div class="receipt-label">Comprobante</div>
        <div class="receipt-value">#{{ $movement->id }}</div>
      </div>
      <div class="text-end">
        <div class="receipt-label">Fecha del movimiento</div>
        <div class="receipt-value">{{ optional($movement->occurred_at)->format('d/m/Y H:i') ?? '—' }}</div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-md-6">
        <div class="receipt-label">Empleado</div>
        <div class="receipt-value">{{ $movement->employee->name ?? '—' }}</div>
        <div class="text-muted">CUIL: {{ $movement->employee->cuil ?? '—' }}</div>
      </div>
      <div class="col-md-6">
        <div class="receipt-label">Empresa</div>
        <div class="receipt-value">{{ $movement->company->name ?? '—' }}</div>
      </div>
      <div class="col-md-6">
        <div class="receipt-label">Tipo</div>
        <div class="receipt-value">{{ config("points.types.{$movement->type}", ucfirst($movement->type)) }}</div>
      </div>
      <div class="col-md-6">
        <div class="receipt-label">Referencia</div>
        <div class="receipt-value">{{ $movement->reference ?: '—' }}</div>
      </div>
    </div>

    <div class="text-center border rounded p-3 my-4">
      <div class="receipt-label">Puntos del movimiento</div>
      <div class="receipt-points {{ $movement->points >= 0 ? 'text-success' : 'text-danger' }}">
        {{ $movement->points >= 0 ? '+' : '' }}{{ number_format((float) $movement->points, 2, ',', '.') }}
      </div>
    </div>

    <div class="mb-3">
      <div class="receipt-label">Observación</div>
      <div class="receipt-value">{{ $movement->note ?: 'Sin observaciones' }}</div>
    </div>

    <div class="border-top pt-3 text-muted small">
      Registrado por: {{ $movement->createdBy->name ?? '—' }}
    </div>
  </div>
</div>
@endsection
