@extends('layouts.app')
@section('title','Rendiciones realizadas')

@section('content')
<style>
  .chip{
    display:inline-flex; align-items:center; gap:.45rem;
    border-radius:999px; padding:.35rem .75rem;
    background:#EAF2FF; color:#1e40af; font-weight:700;
  }
  .chip-ok{ background:#ECFDF5; color:#047857; }
  .small-muted{ color:#6c757d; font-size:.9rem; }
  .table td, .table th { vertical-align: middle; }
</style>

<div class="container py-3">
  <div class="card mat-card">
    <div class="mat-header flex-wrap">
      <h3 class="mat-title mb-0 flex-grow-1" style="min-width:240px;">
        <i class="bi bi-journal-check me-2"></i> Rendiciones realizadas
      </h3>

      <div class="ms-auto d-flex gap-2 mt-2 mt-lg-0">
        <a href="{{ route('redeems.rendiciones_empresa.index') }}" class="btn btn-outline-secondary btn-mat">
          <i class="bi bi-arrow-left"></i> Volver
        </a>
      </div>
    </div>

    <div class="card-body p-4">
      <x-flash />

      <form method="GET" class="row g-3 mb-3">
        <div class="col-12 col-md-3">
          <label class="form-label">Estado</label>
          <select name="estado" class="form-select">
            <option value="todas"    @selected(($estado ?? 'todas') === 'todas')>Todas</option>
            <option value="draft"    @selected(($estado ?? '') === 'draft')>Borrador</option>
            <option value="invoiced" @selected(($estado ?? '') === 'invoiced')>Facturada</option>
          </select>
        </div>

        <div class="col-12 col-md-2 d-flex align-items-end">
          <button class="btn btn-primary btn-mat w-100">
            <i class="bi bi-funnel"></i> Filtrar
          </button>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Empresa</th>
              <th>Negocio</th>
              <th>Período</th>
              <th class="text-end">Puntos</th>
              <th>Estado</th>
              <th>Factura</th>
              <th class="text-end"></th>
            </tr>
          </thead>
          <tbody>
          @forelse($rendiciones as $s)
            <tr>
              <td>{{ $s->id }}</td>
              <td>{{ $s->company?->name ?? '—' }}</td>
              <td>{{ $s->business?->name ?? '—' }}</td>
              <td class="small-muted">
                {{ $s->period_from?->format('d/m/Y') ?? '—' }}
                <span class="text-muted">a</span>
                {{ $s->period_to?->format('d/m/Y') ?? '—' }}
              </td>
              <td class="text-end fw-semibold">{{ number_format((int)$s->total_points, 0, ',', '.') }}</td>
              <td>
                @if($s->status === 'invoiced')
                  <span class="badge text-bg-primary">Facturada</span>
                @elseif($s->status === 'cancelled')
                  <span class="badge text-bg-danger">Anulada</span>
                @else
                  <span class="badge text-bg-warning">Borrador</span>
                @endif
              </td>
              <td>{{ $s->invoice_number ?? '—' }}</td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary"
                   href="{{ route('redeems.rendiciones_empresa.show', $s->id) }}">
                  <i class="bi bi-eye"></i> Ver
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                <i class="bi bi-inbox"></i> Sin rendiciones
              </td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>

      {{ $rendiciones->links() }}
    </div>
  </div>
</div>
@endsection
